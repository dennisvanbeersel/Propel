# Phase I — Observability: TelemetryInterface real adapters

**Date:** 2026-05-07
**Risk tier:** LOW (per umbrella §4.13.3 — 2-round cadence)
**Goal:** Finalize the `TelemetryInterface` SPI surface (Phase E shipped a 2-method stub; umbrella §2.5 prescribes a 4-method shape), scaffold real adapters for OpenTelemetry and Prometheus inside core, wire the existing decorators (`LoggingConnection`, `ProfilingConnection`, `CachingConnection`, `TransactionalConnection`, `ReplicaRoutingConnection`) to emit through the SPI for the metrics they already produce internally but currently lose, and document installation + wire-up.
**Dependencies:** Phase E (`TelemetryInterface`, `NoOpTelemetry`, the decorator stack). Phase F (`PreparedStatementKey` SPI) for cache-key shape consistency.
**Specialist (per §4.13.2):** Observability specialist (OpenTelemetry conventions, span semantics).

## §1. Scope and non-goals

### In scope
- Finalize `TelemetryInterface` to match umbrella §2.5: add `recordPreparedCacheHit(bool)`, `recordTransactionDepth(int)`, `recordHydrationDuration(string $class, float $microseconds)`. Promote span return-type from opaque `object` to a typed `SpanInterface` value object so adapters can carry trace-context.
- Add two phase-specific methods that the existing decorators already produce data for and currently drop on the floor: `recordReplicaRoutingDecision(string $decision, string $reason)` (consumed by `ReplicaRoutingConnection`) and `recordIdentityGeneration(string $strategy, float $microseconds)` (consumed by id-generation adapters).
- Scaffold `Telemetry/Otel/{OtelTelemetry,OtelSpan}.php` — a real OpenTelemetry adapter using the `open-telemetry/sdk` package's `TracerProviderInterface` + `MeterProviderInterface`. Composer `suggest` entry only — no `require`. Class is dormant unless the SDK is installed.
- Scaffold `Telemetry/Prometheus/{PrometheusTelemetry,PrometheusRegistry}.php` — a Prometheus adapter using `promphp/prometheus_client_php` with counters/histograms/gauges. Composer `suggest` entry only.
- Wire `LoggingConnection` cache-hit/miss is N/A (caching lives in `CachingConnection`). Wire **`CachingConnection`** to emit `recordPreparedCacheHit(true|false)` on every `prepare()`. Wire **`TransactionalConnection`** to emit `recordTransactionDepth($n)` on each begin/commit/rollback. Wire **`ReplicaRoutingConnection`** to emit `recordReplicaRoutingDecision(...)` per route decision.
- Add a `Telemetry/CompositeTelemetry.php` so consumers can install both adapters in parallel.
- Documentation: `docs/TELEMETRY.md` covering install + wire-up + propel.yaml stanza + migration from internal counters.

### Out of scope (deferred / other phases)
- Worker-mode-specific telemetry (`onWorkerStart` etc) — Phase J.
- PHP 8.4 fiber-context propagation — Phase G (4.0 substrate).
- Hydration-duration emission from formatters — interface ships with the method; first concrete callsite arrives in Phase G when the streaming Generator-formatter lands.
- Standalone `propel/telemetry-otel` and `propel/telemetry-prometheus` Composer packages: per umbrella §2.5 / §6.4 these are SEPARATE packages. Phase I scaffolds the adapter classes inside core under `src/Propel/Runtime/Telemetry/{Otel,Prometheus}/*` with SDK deps as `suggest` (not `require`). Splitting into separate composer packages is a release-engineering activity for the 4.0 cycle.

## §2. File structure

```
src/Propel/Runtime/Telemetry/
├── TelemetryInterface.php           [MODIFY]   — add 4 new methods + SpanInterface return
├── NoOpTelemetry.php                [MODIFY]   — implement new methods
├── SpanInterface.php                [NEW]      — typed span handle with trace-context
├── CompositeTelemetry.php           [NEW]      — multiplex multiple adapters
├── Otel/
│   ├── OtelTelemetry.php            [NEW]      — OpenTelemetry adapter
│   └── OtelSpan.php                 [NEW]      — wraps SDK SpanInterface
└── Prometheus/
    ├── PrometheusTelemetry.php      [NEW]      — Prometheus adapter
    ├── PrometheusRegistry.php       [NEW]      — counter/histogram registration
    └── PrometheusSpan.php           [NEW]      — span timing helper

src/Propel/Runtime/Connection/Internal/
├── CachingConnection.php            [MODIFY]   — emit recordPreparedCacheHit
├── TransactionalConnection.php      [MODIFY]   — emit recordTransactionDepth
└── ReplicaRoutingConnection.php     [MODIFY]   — emit recordReplicaRoutingDecision

tests/Propel/Tests/Runtime/Telemetry/
├── NoOpTelemetryTest.php            [NEW]
├── CompositeTelemetryTest.php       [NEW]
├── Otel/OtelTelemetryTest.php       [NEW]      — uses fake exporter
└── Prometheus/PrometheusTelemetryTest.php [NEW]

composer.json                        [MODIFY]   — suggest entries
docs/TELEMETRY.md                    [NEW]      — install + wire-up
```

## §3. Task groups

Per-task quality cadence: each task ends with `composer test:agnostic`, `composer cs-check`, `composer stan`, `composer psalm`. ONE commit per task.

### I.1 — `TelemetryInterface` finalization (3 tasks)

#### I.1.1 Introduce `SpanInterface` typed handle.
- New file `src/Propel/Runtime/Telemetry/SpanInterface.php`.
- Methods: `getSql(): string`, `getCallingMethod(): string`, `getStartedAt(): float` (microtime).
- Stays `interface` so OTEL/Prometheus/NoOp can carry their own metadata.
- Add internal anonymous-class `NoOpSpan` returned by `NoOpTelemetry::startQuerySpan()`.

#### I.1.2 Add 4 new methods to `TelemetryInterface`.
- `recordPreparedCacheHit(bool $hit): void`
- `recordTransactionDepth(int $depth): void`
- `recordHydrationDuration(string $class, float $microseconds): void`
- `recordReplicaRoutingDecision(string $decision, string $reason): void`
- `recordIdentityGeneration(string $strategy, float $microseconds): void`
- Update `NoOpTelemetry` with empty implementations.
- Promote `startQuerySpan()` return type narrowing from `object` to `SpanInterface` (still object — no BC break for `LoggingConnection`/`ProfilingConnection` because both treat the handle as opaque).

#### I.1.3 `CompositeTelemetry` multiplex.
- New file `src/Propel/Runtime/Telemetry/CompositeTelemetry.php`.
- Constructor takes `TelemetryInterface ...$adapters`.
- Each event-recording method fans out to all adapters.
- `startQuerySpan()` returns a composite span carrying a span from each adapter; `endQuerySpan()` walks them in reverse.
- Tests in `tests/Propel/Tests/Runtime/Telemetry/CompositeTelemetryTest.php`.

### I.2 — OpenTelemetry adapter scaffold (3 tasks)

#### I.2.1 `OtelSpan` value object.
- Wraps `OpenTelemetry\API\Trace\SpanInterface` (SDK type).
- Class-exists guard: when SDK not installed, the file still loads but instantiation fails fast with clear `RuntimeException` directing user to install `open-telemetry/sdk`.
- Implements Propel's `SpanInterface`.

#### I.2.2 `OtelTelemetry` adapter.
- Constructor takes `TracerProviderInterface` + `MeterProviderInterface` (both SDK types). When SDK not installed, the constructor fails fast.
- Each event-recording method maps to OTEL primitives:
  - `startQuerySpan()` → `tracer->spanBuilder("propel.query")->setAttribute("db.statement", $sql)->startSpan()`.
  - `endQuerySpan()` → `span->end()`; on error sets status code + records exception.
  - `recordPreparedCacheHit()` → counter `propel.prepared_statement_cache.hits` / `propel.prepared_statement_cache.misses`.
  - `recordTransactionDepth()` → up-down-counter `propel.transactions.depth`.
  - `recordHydrationDuration()` → histogram `propel.hydration.duration` with class as label.
  - `recordReplicaRoutingDecision()` → counter `propel.replica.routing` with `decision` + `reason` labels.
  - `recordIdentityGeneration()` → histogram `propel.identity.generation.duration`.
- Spans use W3C trace-context conventions: `db.system`, `db.statement`, `db.operation` (inferred from `$callingMethod`).

#### I.2.3 `OtelTelemetry` test with in-memory exporter.
- Test only runs when SDK installed (`@requires extension`-style guard). The skip message tells the developer how to enable.
- Asserts: span name, attributes, parent-child relationships across nested transactions.

### I.3 — Prometheus adapter scaffold (3 tasks)

#### I.3.1 `PrometheusRegistry` initialiser.
- Defines the canonical metric names: `propel_query_duration_seconds` (histogram), `propel_prepared_statement_cache_hits_total`/`_misses_total` (counters), `propel_transaction_depth` (gauge), `propel_hydration_duration_seconds` (histogram, labeled by class), `propel_replica_routing_total` (counter, labeled by decision+reason), `propel_identity_generation_duration_seconds` (histogram).
- Default histogram buckets: 0.0001, 0.001, 0.01, 0.1, 1.0, 10.0 seconds (logarithmic, common observability convention).
- Constructor takes a `Prometheus\CollectorRegistry` (from `promphp/prometheus_client_php`).

#### I.3.2 `PrometheusTelemetry` adapter.
- Each `TelemetryInterface` method maps to a `Prometheus\Counter` / `Histogram` / `Gauge` `inc`/`observe`/`set`.
- `startQuerySpan()` returns a `PrometheusSpan` value object carrying `microtime(true)` only — Prometheus has no span concept; `endQuerySpan()` observes the duration.

#### I.3.3 `PrometheusTelemetry` test against in-memory storage.
- Uses `InMemory` collector backend.
- Asserts metric names, label sets, observed values.

### I.4 — Wire decorators to telemetry (3 tasks)

#### I.4.1 `CachingConnection` emits `recordPreparedCacheHit`.
- Inject optional `TelemetryInterface` (default `NoOpTelemetry`).
- On `prepare()`: `$this->telemetry->recordPreparedCacheHit($cached !== null)`.
- Update `CachingConnectionContractTest` + `CachingConnectionTest` to verify telemetry emission with a recording fake.

#### I.4.2 `TransactionalConnection` emits `recordTransactionDepth`.
- Inject optional `TelemetryInterface`.
- On every `beginTransaction`/`commit`/`rollBack`/`forceRollBack`: emit current depth.
- Update tests.

#### I.4.3 `ReplicaRoutingConnection` emits `recordReplicaRoutingDecision`.
- Inject optional `TelemetryInterface`.
- On every routing decision: emit `('primary', 'forced')`, `('replica', 'allowed')`, `('primary', 'session_consistency')`, `('primary', 'replica_lag')`, `('primary', 'replica_failure')`.
- Update tests.

### I.5 — Documentation (1 task)

#### I.5.1 `docs/TELEMETRY.md`.
- Install OTEL: `composer require open-telemetry/sdk`.
- Install Prometheus: `composer require promphp/prometheus_client_php`.
- Wire-up snippet for both, plus `CompositeTelemetry`.
- Migration note: pre-3.0 ProfilerConnectionWrapper internal counters are now exposed via telemetry adapters; consumers reading `getQueryCount()` / `getBucketCounts()` directly should switch to the adapter for production observability.
- Add link to `composer.json` `suggest` block.

### I.6 — Round 1 + Round 2 reviews + DoD (2 tasks)

#### I.6.1 Round 1 (mid-phase) — Architecture + BC + Observability specialist.
- After I.1 + I.2 complete (~30% of phase). Verifies SPI shape, OTEL conventions, naming.

#### I.6.2 Round 2 (end-phase) — All 5 standing + observability specialist.
- After I.5 complete. Final pre-merge gate.
- DoD checklist (15 boxes per §4.9) verified.
- Phase summary written to `docs/PHASE-I-SUMMARY.md`.

## §4. Definition of Done (per §4.9)

- [ ] All test matrix cells green (PHP 8.3 × agnostic).
- [ ] Baselines unchanged or decreased (phpstan 403, psalm 1621).
- [ ] Coverage delta ≥ 0%; coverage floor met.
- [ ] Mutation score ≥ 65% on touched files (Phase I is low-risk per §4.14).
- [ ] Deptrac green; no new layer violations against 233-rule baseline.
- [ ] Performance benchmarks within ±5%; OTEL/Prometheus adapters unused by default = zero overhead path.
- [ ] `CHANGELOG.md` updated.
- [ ] No new self-triggered deprecations.
- [ ] Generated-code lint parity green.
- [ ] Golden-file diff reviewed (none expected — Phase I touches Runtime only).
- [ ] Phase plan updated with retrospective notes.
- [ ] Review rounds 1 + 2 reports committed to `docs/reviews/I-round-*.md`.
- [ ] Surgical-test battery (telemetry contract test + composite multiplex + adapter integration test with in-memory exporter) committed.
- [ ] All MUST-FIX review findings closed; SHOULD-FIX closed or waived.
- [ ] Iteration cycles within budget.

## §5. Risk register (Phase I-specific)

| # | Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|---|
| 1 | OTEL SDK not installed → adapter file load explodes | Medium | High | All SDK type imports use `class_exists` runtime guards; constructor fails fast with actionable error message; no top-level `use` of SDK types where they'd cause load failure. |
| 2 | Prometheus client SDK API drift between versions | Low | Medium | Pin `suggest` entry to a tested version range; integration test asserts metric names + label sets. |
| 3 | `TelemetryInterface` BC break | Low | High | Adding methods is a Tier 2 BC break; mitigated by the interface being marked `@since 3.0.0` in Phase E with explicit deprecation runway language. Provide default no-op implementations on the abstract base if needed. Alternative considered: introduce a v2 interface — rejected because Phase E shipped only the stub, no third-party adapters exist yet to break. |
| 4 | Trace-context propagation across nested transactions | Medium | Medium | OTEL adapter MUST set the parent span context on each child span to keep nested-tx and prepared-stmt-cache spans correctly linked; covered by I.2.3 integration test with in-memory exporter. |
| 5 | Composite-telemetry slow-path overhead when both adapters configured | Low | Low | Each adapter's overhead is bounded by SDK; composite is a foreach loop. NoOp default = zero overhead. Performance test with composite of 2 NoOps must remain within 5% of single NoOp. |
| 6 | Adapter-package-split timing | Low | Low | §1 out-of-scope: adapters live inside core for now with SDK suggests. Splitting to `propel/telemetry-otel` etc is 4.0-release engineering. |

## §6. Out-of-scope

- Worker-mode hooks (`onWorkerStart`, `onRequestStart`, `onRequestEnd`) — Phase J.
- PHP 8.4 lazy-object property hooks for hydration duration — Phase G.
- DataDog adapter — community contribution after 4.0.
- Standalone `propel/telemetry-otel` / `propel/telemetry-prometheus` Composer package split — release engineering for 4.0.
- HTTP `/metrics` exporter endpoint — provided by the Prometheus client library directly; not Propel's responsibility to scaffold the framework wiring.
