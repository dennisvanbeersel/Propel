# Phase I — Round 1 Review (Architecture + BC + Observability specialist)

**Date:** 2026-05-07
**Cadence:** LOW-RISK 2-round (per umbrella §4.13.3).
**Trigger:** I.1.x + I.2.x complete (~50% of phase tasks). Per
`§4.13.3` Round 1 fires after ~30%; I.1 + I.2 + I.3 actually landed
fast enough that this is mid-phase but past the formal trigger point.
**Reviewer hat(s):** Architecture & SOLID + BC & migration realism +
Observability specialist (OpenTelemetry conventions).
**Code under review:** commits `1d36f9ed3` (I.1 SPI finalization),
`6f41fa577` (I.1.3 Composite), `7e29c34e0` (I.2 OTEL adapter),
`8f253fb08` (I.3 Prometheus adapter).

---

## Findings (tagged per §4.13.4)

### Architecture & SOLID

**[NICE] A.1 — `SpanInterface` extension model is sound.**
Phase E shipped `startQuerySpan(): object`; widening to
`startQuerySpan(): SpanInterface` is BC-safe (subtype IS-A object) and
gives adapters somewhere to hang per-adapter metadata
(`OtelSpan::getSdkSpan()`, `PrometheusSpan::getStartedAt()`,
`CompositeSpan::getChildren()`). Single-Responsibility intact —
`SpanInterface` exposes only sql/method/start.

**[NICE] A.2 — `CompositeTelemetry` LIFO close order is correct.**
End-spans walk children in reverse so when adapters wrap real OTEL spans,
the inner adapter's "child" closes before the outer "parent". Aligns
with W3C trace-context expectations.

**[SHOULD-FIX] A.3 — `endQuerySpan()` parameter type is still `object`.**
The interface declares `endQuerySpan(object $span, …)` for BC with
the Phase E stub. Concrete adapters all `instanceof`-check; that's
correct, but the interface could narrow to `SpanInterface` since we now
own the type. **Decision:** keep as `object` per the docblock comment —
narrowing here would be a Tier 2 SPI change requiring deprecation
runway. Acceptable trade-off: the runtime check inside each adapter
handles a foreign handle by returning early. **Status:** documented
in interface docblock, not a defect.

**[NICE] A.4 — Adapter constructor failure modes are consistent.**
Both `OtelTelemetry` and `PrometheusTelemetry` throw `RuntimeException`
with an actionable message + `composer require …` hint when their SDK
isn't installed. Stays out of the user's way until they actually
instantiate.

### BC & migration realism

**[SHOULD-FIX → CLOSED] B.1 — TelemetryInterface gained 5 methods.**
This is technically a Tier 2 SPI break. Mitigated by:
- Phase E SPI was newly published (3.0.0) with a docblock note that
  "Phase I will provide real adapters that consume this interface" —
  consumers were forewarned the shape would expand.
- No third-party adapters exist yet to break.
- Existing recording-fake telemetry test doubles in `LoggingConnectionTest`
  + `ProfilingConnectionTest` were updated in I.1 to satisfy the new
  contract; suite GREEN.
**Status:** acceptable per §3.1's "Tier 2 SPI: contracts may expand
prior to first downstream adoption."

**[NICE] B.2 — Composer suggest entries match umbrella §2.5.**
`open-telemetry/sdk` and `promphp/prometheus_client_php` are in
`require-dev` (for static analysis to resolve types) AND `suggest`
(for production users to discover them). Production `require:` block
remains untouched — adapter packages remain optional per umbrella §2.5
"ship separately; default Propel ships only the no-op."

**[SHOULD-FIX] B.3 — Production install path not documented yet.**
Plan §I.5 lands docs/TELEMETRY.md in the next task group; without it,
consumers can read code and infer the install command but won't have
the canonical wire-up snippet. **Status:** scheduled in I.5; Round 2
must verify TELEMETRY.md ships before merge.

### Observability specialist (OTEL conventions)

**[NICE] O.1 — Span naming follows OpenTelemetry Spec convention.**
`propel.{$callingMethod}` (e.g. `propel.exec`) — matches the recommended
"library.operation" pattern. SpanKind::KIND_CLIENT is correct (Propel is
the DB client).

**[NICE] O.2 — Semantic-conventions attributes are correct.**
- `db.system` → `'propel'`. The DB-system convention's allowed
  values include vendor-specific identifiers; "propel" identifies the
  library, not the underlying DB. Adapter consumers may layer a span
  processor to override `db.system` to `mysql`/`postgresql` based on
  the actual driver.
- `db.statement` → SQL text — exactly per the spec.
- `db.operation` → calling method (`exec` / `prepare` / `query`) — also
  per the spec (was `OpenTelemetry.Trace.SemConv.DbAttributes`).

**[SHOULD-FIX] O.3 — Trace-context propagation across nested transactions.**
Phase I plan §5 risk #4 flagged this. The current adapter relies on
the SDK's implicit context-propagation: `spanBuilder()` will pick up the
current active span as parent if `Context::getCurrent()` has one.
However, `OtelTelemetry::startQuerySpan()` does NOT call `activate()`
on the span before returning, so child spans (e.g., nested transaction
+ contained query) WON'T see the outer span as their parent.
**Mitigation deferred:** activating spans creates a new closure-based
context scope which Propel's decorator chain doesn't naturally provide.
A correct fix needs Phase J's fiber-safe context binding. **Action:**
log as Phase J integration item; add a `// PHASE J` comment in
`OtelTelemetry::startQuerySpan()` so the work is visible.

**[NICE] O.4 — Metric naming follows OpenTelemetry & Prometheus conventions.**
- OTEL counters use dot-notation (`propel.prepared_statement_cache.hits`).
- Prometheus uses snake_case + `_total` suffix
  (`propel_prepared_statement_cache_hits_total`) — matches Prometheus
  best practice.

**[NICE] O.5 — Histogram units consistent.**
- OTEL histograms declared with `'ms'` unit; values converted from
  microseconds at the adapter.
- Prometheus histograms declared as `_seconds` per Prometheus convention;
  values converted from microseconds at the adapter.
Default buckets in PrometheusRegistry (100µs..10s logarithmic) match
the typical DB-call latency distribution.

**[SHOULD-FIX] O.6 — Up-down-counter delta computation is single-thread-safe only.**
`OtelTelemetry::recordTransactionDepth()` computes `delta = $depth -
$lastTxDepth` and stores `$lastTxDepth`. In a single-fiber-per-connection
model (Phase E's assumption, see `TransactionalConnection.php:23`)
this is fine. In Phase J's worker mode, multiple fibers sharing the
adapter would race on `$lastTxDepth`. **Action:** document the
single-fiber assumption in the class docblock; revisit in Phase J.

---

## Summary table

| Tag | Count | Status |
|---|---|---|
| MUST-FIX | 0 | — |
| SHOULD-FIX | 4 | A.3 → documented as design choice; B.3 → scheduled in I.5; O.3 → deferred to Phase J with code comment; O.6 → documented in code |
| NICE | 6 | accepted |

## Verdict

**PASS.** No MUST-FIX. The 4 SHOULD-FIX findings are tracked: 2 are
deferred to Phase J (worker-mode), 1 will land in I.5 (docs), 1 is a
documented design choice (interface BC). Round 2 must verify the I.5
docs ship and the deferred Phase J items are explicitly scoped out in
the phase summary.

## Action items for Round 2

- [ ] I.5 ships `docs/TELEMETRY.md` covering install + wire-up.
- [ ] `OtelTelemetry::startQuerySpan()` carries a `// PHASE J` comment
      where context activation is currently missing.
- [ ] `OtelTelemetry` class docblock documents the single-fiber
      assumption for `recordTransactionDepth`'s delta computation.
- [ ] `endQuerySpan(object)` parameter-typing rationale is captured in
      the interface's class docblock.
