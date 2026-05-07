# Phase I — Summary

**Status:** PASS (all I.1-I.5 tasks shipped; Round 1 review closed).
**Branch:** `ar-rewrite` post-Phase-I.
**Test suite:** 2869 / 14364 / 21 GREEN (up from 2839 / 13889 / 21 — +30 tests, +475 assertions).

## What Phase I delivered

Real observability adapters on top of the Phase E TelemetryInterface stub. Default Propel still ships only the no-op; production deployments opt into OpenTelemetry or Prometheus adapters via Composer `suggest`.

### TelemetryInterface finalization

- Span lifecycle: `startQuerySpan`, `endQuerySpan`.
- Counters: `recordPreparedCacheHit`, `recordTransactionDepth`, `recordHydrationDuration`, `recordReplicaRoutingDecision`.
- All methods Tier 2 SPI; signatures locked for the 3.x line.

### Adapters

- **`NoOpTelemetry`** — bundled default; zero-overhead.
- **`CompositeTelemetry`** — bundled multiplex (e.g., emit to OTel AND Prometheus simultaneously).
- **`Otel/OtelTelemetry`** — adapter for `open-telemetry/sdk`. W3C trace-context propagation. Composer `suggest` (not require) keeps core lean.
- **`Prometheus/PrometheusTelemetry`** — adapter for `promphp/prometheus_client_php`. Counters/histograms/gauges. Composer `suggest`.

### Decorator wire-up

Phase E's decorators previously emitted internal-only metrics. Phase I wires them through TelemetryInterface:

- **CachingConnection** → `recordPreparedCacheHit` per cache event.
- **TransactionalConnection** → `recordTransactionDepth` per begin/commit/rollback.
- **ReplicaRoutingConnection** → `recordReplicaRoutingDecision` per query.

### Documentation

`docs/TELEMETRY.md` — adapter selection, configuration, span semantics, multiplex via Composite.

## Quality gates (all stable)

| Gate | Phase H end | Phase I end |
|---|---|---|
| `phpstan-baseline.neon` | 403 lines | **403 lines** (unchanged) |
| `psalm-baseline.xml` | 1621 lines | **1621 lines** (unchanged) |
| `deptrac` | 0 violations / 233 baseline | **0 violations** |
| Tests | 2839 / 13889 / 21 | **2869 / 14364 / 21** (+30 / +475) |
| Tier 1 surface | stable | **stable** |

## Notable surprises

1. **CompositeTelemetry pattern is genuinely useful** — production setups commonly want both OTel (distributed traces) AND Prometheus (in-process counters). Multiplexer scaffolding fell out cleanly.
2. **OTel SDK semver** is unstable enough that we kept it as `suggest` not `require` — adapter is loose-typed against the SDK so v0.x → v1.x bumps don't break our code.
3. **Replica-routing decision events** turned out to be the most-asked-for telemetry from typical production workloads. Phase I added `recordReplicaRoutingDecision` (not in original umbrella §2.5) to capture this.

## Next: Phases G and J

Phase G (PHP 8.4 lazy objects + asymmetric visibility — the 4.0 release) is the last big architectural phase. Phase J (worker-mode / async / fiber-safety) lands post-G since it depends on the 8.4 substrate.
