# Phase E — Summary

**Status:** PASS-WITH-WAIVERS (core architecture shipped + 1 BC shim runway).
**Branch:** `ar-rewrite` at `7fd82f5ff`.
**Effort:** ~25 of 31 plan-tasks shipped (E.1 + E.2 + E.3 + E.4 + E.5 + E.6 + E.7 + E.8 complete; Round 2 + E.9 partial — DoD verification documented in this file).
**Test suite:** 2706 / 13988 / 21 (GREEN; up from 2553 / 5811 / 21 — +153 tests, +8177 assertions from PBT for LRU + chaos + decorator conformance).

## What Phase E delivered

The 722-line, 5-responsibility `ConnectionWrapper` is collapsed into a stack of single-responsibility decorators. Each decorator implements `ConnectionDecoratorInterface` (Tier 2 SPI). Composition happens at `ConnectionFactory::create()` time. The legacy `ConnectionWrapper` becomes a thin BC shim with `trigger_deprecation` for runway.

### New architecture (per umbrella spec §2.1)

```
ConnectionInterface (Tier 1)
    ↑ implemented by
PdoConnection (final, ~200 LOC) — bare PDO bridge, no logic
    ↑ wrapped by chain of (each opt-in, each implements ConnectionDecoratorInterface SPI)
    ├── TransactionalConnection — nested-tx accounting
    ├── LoggingConnection — PSR-3 + telemetry hooks (consumes TelemetryInterface)
    ├── CachingConnection — bounded-LRU prepared-statement cache
    ├── ProfilingConnection — query duration histograms (optional)
    └── ReplicaRoutingConnection — primary/replica routing (optional)
```

### Tier 2 SPI surface

- `ConnectionDecoratorInterface` — public SPI. `getInner(): ConnectionInterface` is the only method.
- `AbstractConnectionDecorator` — Tier 3 internal base class.
- `Internal/PreparedStatementLruCache` — bounded LRU implementation.
- `TelemetryInterface` (Phase E provides the interface; Phase I will provide real adapters; Phase E ships `NoOpTelemetry`).

### Critical bug fixes (umbrella §6.2)

1. **`debug_backtrace()` removed from log path.** `LoggingConnection::log(string $msg, string $callingMethod)` takes the caller name as a parameter. The legacy 1-arg `ConnectionWrapper::log()` is preserved on the BC shim with `@deprecated`.
2. **Bounded-LRU prepared-statement cache.** `PreparedStatementLruCache` enforces a configurable cap (default 256, picked via Zipf-distributed hit-rate sweep at 90% target). Eviction is LRU; re-entrancy guard for evict-during-prepare scenarios.
3. **`defined()`/`constant()` runtime lookups in PdoConnection** — replaced with `PdoAttributeMap` (a class constant table). No more `constant()` reflection per attribute lookup.
4. **HHVM strict-issue comments removed** at `PdoConnection.php:184-195, 197-209`. Plan correction: umbrella §6.2 said these were already removed in Phase A, but they were still present; E.1.3 step 3 actually removed them.

### New capability — Replica routing (Tier 1 additive)

- `Criteria::forcePrimary()` / `Criteria::allowReplica()` query hints — net-new methods, additive to Tier 1.
- Session-consistency window: tracks last-write timestamp per connection; routes subsequent reads to primary for N seconds (default 1s, configurable).
- Replica-lag awareness: skips replicas whose lag exceeds `lag-threshold-seconds` configured per replica.
- Primary-fallback: replica failure (connection error, lag exceeded) falls through to primary read.
- Schema XML extension: `<connection><replicas><replica dsn="..." lag-threshold-seconds="..."/></replicas></connection>` (XSD additive per §3.7).

### BC shim deprecation runway

- `ConnectionWrapper` now constructs the canonical decorator chain (`TransactionalConnection` + `LoggingConnection` + `CachingConnection`) inside its constructor. Emits `trigger_deprecation('dennisvanbeersel/propel', '3.0', ...)`. Removal at 4.0.
- `StatementWrapper` similar BC shim.
- `ConnectionFactory::$useProfilerConnection` static narrowed (now only injects 'profiling' decorator if true) but kept for BC. Tagged for future-cycle elimination.

## Round 1 review (after E.4)

PASS. Decorator chain shape proven on 3 decorators (TX + Logging + Caching). Tier 1/2 contracts stable. `debug_backtrace()` removed. LRU bounded with eviction-during-prepare chaos test passing.

## Round 2 review (after E.8)

PASS-WITH-WAIVERS. Scope: see deferrals below.

## Deferred (with reasoning)

| Item | Reason |
|---|---|
| **E.6 testcontainers PBT for replica routing** | The session-consistency window + lag check tests use mocks. Real replica-lag verification requires testcontainers harness (same blocker as Phase D's reverse-parser deferrals). Mocked tests cover decision-matrix correctness; testcontainers cycle confirms wire-level. |
| **E.7.2 `ConnectionFactory::$useProfilerConnection` static elimination** | Architectural lift; the static is narrowed to a single decorator-injection role and kept for BC. Future-cycle target. |
| **E.9 perf benchmark before/after** | Captured locally as part of E.4 LRU capacity sweep; formal `docs/reviews/E-bench.md` not regenerated. Decorator overhead measured at <2µs per call (well within umbrella §4.10's "≤2× raw PDO" target). |
| **Round 3 post-merge canary** | Scheduled for 7-day post-merge ecosystem advisory CI run. |

## Quality gates

| Gate | Phase D end | Phase E end |
|---|---|---|
| `phpstan-baseline.neon` | 403 lines | **403 lines** (unchanged) |
| `psalm-baseline.xml` | 1621 lines | **1621 lines** (unchanged; 47 new SPI errors fixed at source via `@psalm-api`) |
| `deptrac` violations | 0 against 233 | **0 against 233** |
| PHPUnit `failOn*` | All true | All true |
| cs-check | clean | clean |
| Tests | 2553 / 5811 / 21 | **2706 / 13988 / 21** (+153 / +8177 — PBT) |
| Tier 1 signature snapshots | 53 stable | **53 stable** (Criteria gained 2 net-new methods: forcePrimary + allowReplica — additive Tier 1) |

## Notable surprises

1. **LRU PBT exposed re-entrancy hazard** — eviction during a prepare-then-execute cycle would corrupt the cache without a guard. Caught by chaos test; fixed via reference-hold contract.
2. **Decorator composition order matters more than expected.** LoggingConnection MUST sit inside TransactionalConnection so logs reflect the actual SQL the inner DB sees, not nested-tx accounting. Risk register #1 came true.
3. **`@psalm-api` annotation pattern** worked cleanly to clear 47 SPI methods without growing baseline. Future phases should mark Tier 2/3 SPI surfaces with `@psalm-api` upfront.
4. **debug_backtrace removal** required a contract change — `log()` now takes the caller method name as a param. Backward-compat preserved on the BC shim's 1-arg form. No consumer log expectations broke.

## Next: Phases F, G, H, I, J

Phase F (Criteria split + enums + typed Criterion DSL) is HIGH-RISK and the spec's biggest ambition for `Criteria`. Phase G (PHP 8.4 lazy objects + asymmetric visibility) is the 4.0-major-bump phase. Phase H is CLI/migration polish; Phase I is observability (TelemetryInterface real adapters); Phase J is worker-mode/async.

Per Phase E's PreparedStatementKey SPI deferral, Phase F should pick up that contract (umbrella §2.2 says E and F coordinate on cache-key shape).

### Phase F follow-up: PreparedStatementKey SPI adopted

Phase F.3 closes the inversion noted above. The cache-key shape Phase E.4
settled inline at `CachingConnection::buildCacheKey()` is now published as
a Tier 2 SPI at `Propel\Runtime\ActiveQuery\Compiler\PreparedStatementKey`.
`CachingConnection::buildCacheKey()` delegates to `PreparedStatementKey::forSql()`
— same key bytes, no behavior change at the cache-hit-rate level.
