# Phase E: Runtime Connection Collapse + Decorator Chain + Replica Routing

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended for E.1, E.2, E.3, E.5, E.7) or `superpowers:executing-plans` (recommended for E.4 + E.6 — concurrency-sensitive). Steps use checkbox (`- [ ]`) syntax for tracking.

**Date:** 2026-05-07
**Branch:** `ar-rewrite` (commit; do NOT push)
**Goal:** Implement umbrella §2.1 in full. Collapse the today-triple-wrapper Connection chain (`PdoConnection` → `ConnectionWrapper` 745 LOC, 5 responsibilities → `ProfilerConnectionWrapper`) into a `final` ~200-LOC `PdoConnection` plus an opt-in decorator chain (`TransactionalConnection`, `LoggingConnection`, `CachingConnection`, `ProfilingConnection`, `ReplicaRoutingConnection`) all implementing a new `ConnectionDecoratorInterface` SPI (Tier 2). Land the new replica-routing capability set (per-query `forcePrimary()`/`allowReplica()` hints, session-consistency window, replica-lag awareness, automatic primary fallback). Fix the four umbrella §6.2 bugs that target Phase E (`debug_backtrace()` in the log hot path, unbounded prepared-statement cache, `defined()`/`constant()` runtime lookups for PDO attribute names, residual HHVM strict-issue comments). Bring `ConnectionWrapper` and `StatementWrapper` down to thin BC shims around the new chain on a Tier-2 deprecation runway (kill in 4.0).

**Architecture:** Three concentric pieces.

1. **Bare bridge.** `Connection/PdoConnection.php` is rewritten as a `final` class implementing `ConnectionInterface` directly over a `\PDO` instance, with no decoration logic. ~200 LOC. The current 237-LOC version has ad-hoc helpers (`setAttributes`, HHVM `prepare`/`quote` overrides) that move out: enum-driven attribute mapping into a new `Internal/PdoAttributeMap.php`; HHVM overrides verified-deletable.

2. **Decorator chain.** Each capability of today's `ConnectionWrapper` becomes a single-responsibility class in `Connection/Internal/`, each extending `Internal/AbstractConnectionDecorator.php` and implementing the new public `Connection/ConnectionDecoratorInterface.php`. `TransactionalConnection` owns nested-tx accounting; `LoggingConnection` owns PSR-3 + telemetry-stub; `CachingConnection` owns the bounded-LRU prepared-statement cache; `ProfilingConnection` owns query histograms; `ReplicaRoutingConnection` owns primary/replica decisions. Order matters and is enforced via `ConnectionFactory` (umbrella §2.1: Logging is **inside** Transactional so logs reflect what the inner DB sees, not what the wrapper accounts for; Caching sits inside Logging so cache-hit/miss is observable; Profiling outermost so it sees whole-call latency; Routing wraps everything else when configured).

3. **BC shims + new SPI.** `ConnectionWrapper` and `StatementWrapper` keep their public API but become thin pass-throughs to the new chain plus a `trigger_deprecation` per call category. `ConnectionDecoratorInterface` is published as Tier 2 — third-party profilers (today's `ProfilerConnectionWrapper` external implementations) migrate over the 3.x runway. `Internal/*` classes are Tier 3 (free-reign) but their composition is observable through the SPI, not through `instanceof` checks; the migration cookbook flags any consumer doing `instanceof ConnectionWrapper`.

The replica-routing sub-track (Group E.6) is the only **new capability** in Phase E. Everything else is collapse + bugfix + Tier-2 axis introduction. Replica-routing decisions are recorded through a new `Routing/RoutingDecision.php` value object (Tier 3) so the per-query log line states the hint, the chosen target, and the rationale.

**Tech Stack:** PHP 8.3, MySQL 8.0+, MariaDB 10.5+, PostgreSQL 14+, SQLite (frozen), PHPUnit 11, PHPStan level 7, Spryker code-sniffer, Infection ^0.29 (MSI ≥75 on touched files per umbrella §4.4 — Phase E threshold), Deptrac ^2, innmind/black-box ^6.

**Reference spec:** `docs/plans/2026-05-06-modernization-umbrella-spec.md` (umbrella §2.1 target architecture, §3.2 Tier-2 SPI surface, §4.10 perf targets, §4.12 chaos-test gate, §4.13.3 HIGH-RISK 3-round cadence, §5 Phase E row, §6.2 risk-fix list, §6.4 capability additions).
**Predecessors:** `docs/plans/2026-05-06-phase-a-foundations.md`, `docs/plans/2026-02-03-builder-om-modernization.md` + `docs/plans/2026-05-06-phase-b-amendments.md`, `docs/plans/2026-05-06-phase-c-schema-ddl-modernization.md`, `docs/plans/2026-05-07-phase-d-behaviors-codeemitter.md`.
**Phase summaries:** `docs/PHASE-A-SUMMARY.md`, `docs/PHASE-B-SUMMARY.md`, `docs/PHASE-C-SUMMARY.md`, `docs/PHASE-D-SUMMARY.md`.

**Review tier (per umbrella §4.13.3):** **HIGH-RISK 3-round cadence**. Round 1 mid-phase after E.4 (decorator chain shape proven on three of five decorators — `TransactionalConnection` + `LoggingConnection` + `CachingConnection`). Round 2 end-phase pre-merge after E.8 (full chain landed, BC shim in place). Round 3 post-merge canary 7 days after merge to integration (per umbrella §4.13.3 HIGH-RISK row).

**Specialists (per umbrella §4.13.2):** standing 5 reviewers + **SQL & concurrency specialist** (transaction-nesting invariants, prepared-statement cache coherence, deadlock retry boundedness, replica-routing consistency hazards, lag-handling correctness) + **Security reviewer** (DoS audit on the LRU eviction policy; injection-vector audit on routing-decision logging; timing-oracle audit on replica-lag probes; deprecation-shim re-entrancy audit).

**Test/quality gate state at start of phase (carried from Phase D end):**
- `phpstan-baseline.neon` 403 lines, 0 blanket regex.
- `psalm-baseline.xml` 1621 lines, suppression-clean.
- Deptrac 0 violations against 233-line baseline.
- All `failOn*` flags strict.
- Test suite: 2553 / 5811 / 21 GREEN.
- Tier 1 signature snapshots: 53 stable.
- Bookstore golden tree: 399 files, idempotent.
- XSD additivity: 12 fixtures validate.
- CodeEmitter API stable (Phase D); used by ~2 behavior modifiers; not consumed by `Connection/*` (no codegen here).
- `Runtime/Internal/` namespace exists per Phase A Deptrac ruleset (umbrella §4.5); `Connection/Internal/` is the natural sub-namespace.

---

## File Structure (created or modified by this phase)

**Created:**
- `src/Propel/Runtime/Connection/ConnectionDecoratorInterface.php` — Tier 2 SPI per umbrella §2.1. Single contract: `getInner(): ConnectionInterface`. Interface extends `ConnectionInterface` so `getAttribute`, `prepare`, `beginTransaction`, etc. are inherited.
- `src/Propel/Runtime/Connection/Internal/AbstractConnectionDecorator.php` — base class for all five decorators. Holds the `$inner: ConnectionInterface` reference, forwards every `ConnectionInterface` method by default, exposes `getInner()`. Child classes override only the methods they actually decorate. Deptrac-fenced under `Runtime/Internal/`.
- `src/Propel/Runtime/Connection/Internal/TransactionalConnection.php` — port of `ConnectionWrapper`'s nested-tx counter (current `$nestedTransactionCount`, `$isUncommitable`, `$useDebug` slices) with a single responsibility.
- `src/Propel/Runtime/Connection/Internal/LoggingConnection.php` — port of `ConnectionWrapper`'s log path (`log()` at `:712`, `callUserFunctionWithLogging()`, `setLogger()`, `setLogMethods()`) MINUS `debug_backtrace()`; PSR-3 + a `TelemetryInterface` stub hook (Phase I will replace the stub with real adapters).
- `src/Propel/Runtime/Connection/Internal/CachingConnection.php` — port of `ConnectionWrapper`'s prepared-statement cache (`$cachedPreparedStatements` at `:89`, `prepare()` cache logic at `:407–414`, `clearStatementCache()` at `:583`) plus a bounded LRU bound + cache-key normalization.
- `src/Propel/Runtime/Connection/Internal/PreparedStatementLruCache.php` — generic `array<string, StatementInterface>` LRU with a configurable cap (default 256 — chosen post-bench in Task E.4.4); insertion + access-order tracked through PHP `OrderedMap`-style `array_key_first`/move-to-end pattern; eviction is FIFO of least-recently-used. Thread/fiber model: assumes single-fiber-per-connection (Phase J revisits worker-mode safety).
- `src/Propel/Runtime/Connection/Internal/ProfilingConnection.php` — port of `ProfilerConnectionWrapper.php` (135 LOC) and `ProfilerStatementWrapper.php` (75 LOC) into a single decorator + a query-duration histogram emission point (telemetry stub hook).
- `src/Propel/Runtime/Connection/Internal/ReplicaRoutingConnection.php` — NEW. Owns per-query routing decisions; consults a `RouteResolver`; wraps both primary and replica `ConnectionInterface` instances; auto-fallback to primary on replica error.
- `src/Propel/Runtime/Connection/Routing/RouteResolver.php` — pure logic: given a `RouteRequest` (operation type, hint, session-write window, lag samples), returns a `RoutingDecision` (target, rationale).
- `src/Propel/Runtime/Connection/Routing/RouteRequest.php` — value object (DTO) with `operation: 'read'|'write'`, `hint: 'force-primary'|'allow-replica'|'auto'`, `sessionLastWriteAtMicros: ?int`, `replicaLagSamples: array<string, float>`.
- `src/Propel/Runtime/Connection/Routing/RoutingDecision.php` — value object: `target: 'primary'|'replica:<name>'`, `rationale: string` (one-line; structured), `routedAt: int` micros (telemetry-friendly).
- `src/Propel/Runtime/Connection/Routing/SessionConsistencyWindow.php` — tracks last-write-timestamp per session; `windowSeconds` configurable (default 5). After a write, reads in the same session route to primary until window expires (umbrella's read-after-write hazard mitigation).
- `src/Propel/Runtime/Connection/Routing/ReplicaLagSampler.php` — collects lag samples (`SHOW REPLICA STATUS` on MySQL/MariaDB; `pg_last_wal_replay_lag()` on PG) per replica on a configurable cadence (default 10s); skip-routing threshold default 2s; exposed for telemetry. Sampling NEVER runs on the request hot path — it's an out-of-band probe driven by `SessionConsistencyWindow::tick()` or a worker hook.
- `src/Propel/Runtime/Connection/Internal/PdoAttributeMap.php` — replaces `defined()`/`constant()` runtime lookups in `PdoConnection::__construct` (today at `:79, :105, :108`) and `ConnectionWrapper::setAttribute` (today at `:358, :364, :370`). Maps string attribute names (`'ATTR_ERRMODE'`, `'ATTR_CASE'`, `'MYSQL_ATTR_INIT_COMMAND'`, etc.) to `int` PDO constants via a const-time-resolved array. The `defined()`/`constant()` calls go away.
- `src/Propel/Runtime/ActiveQuery/Routing/CriteriaRoutingHints.php` — trait or interface mixin on `Criteria` adding `forcePrimary(): static` / `allowReplica(): static` / `getRoutingHint(): string`. Phase F coordinates the wider Criteria-split; Phase E only adds these three additive methods to current `Criteria`. Tier 1 additive (no removal, no narrowing) per §3.1 — signature-diff snapshot updated.
- `src/Propel/Runtime/Connection/Exception/ReplicaLagExceededException.php` — thrown when ALL replicas exceed lag threshold AND hint is `allow-replica` AND fallback is disabled. Tier 2 (consumer-catchable) per §3.2.
- `src/Propel/Runtime/Connection/Exception/ConnectionDecoratorException.php` — thrown on decorator-chain composition errors (e.g., chain order violation); Tier 2.
- `tests/Propel/Tests/Runtime/Connection/Internal/PreparedStatementLruCacheTest.php` — unit tests for LRU correctness.
- `tests/Propel/Tests/Runtime/Connection/Internal/TransactionalConnectionTest.php` — unit tests for nested-tx accounting.
- `tests/Propel/Tests/Runtime/Connection/Internal/LoggingConnectionTest.php` — unit; assert `debug_backtrace` is NOT called.
- `tests/Propel/Tests/Runtime/Connection/Internal/CachingConnectionTest.php` — unit; covers cache-key uniqueness across `$driverOptions`, hit/miss accounting, eviction.
- `tests/Propel/Tests/Runtime/Connection/Internal/ProfilingConnectionTest.php` — unit; histogram-bucket emission.
- `tests/Propel/Tests/Runtime/Connection/Internal/ReplicaRoutingConnectionTest.php` — unit; covers each routing decision path with deterministic fakes.
- `tests/Propel/Tests/Runtime/Connection/Routing/RouteResolverTest.php` — pure-logic tests; no I/O.
- `tests/Propel/Tests/Runtime/Connection/Routing/SessionConsistencyWindowTest.php` — clock-injected unit tests.
- `tests/Propel/Tests/Runtime/Connection/Routing/ReplicaLagSamplerTest.php` — sampler tests with mocked DataFetcher.
- `tests/Propel/Tests/Runtime/Connection/ConnectionFactoryTest.php` — composition tests covering every documented decorator order; assert `ConnectionDecoratorException` thrown on misorders.
- `tests/Propel/Tests/Runtime/Connection/ConnectionWrapperBcShimTest.php` — exercises the deprecation-shim path; asserts every deprecation message is emitted exactly once per category and the underlying chain still services the call.
- `tests/PropertyTests/Connection/NestedTransactionInvariantTest.php` — black-box PBT: random sequences of `begin/commit/rollback` (respecting `(begin count ≥ commit+rollback at every prefix)`); assert `inTransaction()` and `getNestedTransactionCount()` invariants hold; assert `$isUncommitable` flips only on rollback inside nested savepoint context. Seeded for reproducibility.
- `tests/PropertyTests/Connection/PreparedStatementLruInvariantTest.php` — black-box PBT: random `prepare(sql_i)` / `evict()` operations with capacity ≤ N; asserts cache size never exceeds cap; asserts least-recently-used is the evictee; asserts a held statement reference cannot be evicted under it (key invariant for the chaos-test in E.4.5).
- `tests/ChaosTests/Connection/PdoDropMidTransactionTest.php` — chaos: simulate PDO connection drop mid-transaction (mock PDO that throws after N statements); assert `TransactionalConnection` propagates the failure cleanly and resets nested-tx counter.
- `tests/ChaosTests/Connection/StatementCacheEvictDuringPrepareTest.php` — chaos: while `prepare()` is in flight (synchronous emulation), trigger eviction of the same key; assert the in-flight statement is NOT freed prematurely (the held-reference invariant).
- `tests/ChaosTests/Connection/DeadlockRetryBoundedTest.php` — chaos: PDO throws SQLSTATE 40001 (deadlock) on N consecutive begin/commit cycles; assert retry boundedness (no infinite loop); assert eventual surfacing of `PropelException`.
- `tests/ChaosTests/Connection/ReplicaFailoverTest.php` — chaos: replica connection refuses; assert `ReplicaRoutingConnection` falls back to primary AND emits a deprecation-free warning telemetry hook AND records the rationale on the `RoutingDecision`.
- `tests/Benchmarks/Connection/QueryOverheadBenchmark.php` — perf bench for umbrella §4.10 "Query overhead per call (vs raw PDO) ≤ 2× raw PDO" target. Compares: raw PDO baseline, new chain (decorators all-on default config), legacy `ConnectionWrapper` shim path. Output committed to `docs/reviews/E-bench.md`.
- `docs/CONNECTION-DECORATORS.md` — concise architectural reference for consumers and library authors. Documents: decorator order, configuration shape, BC migration cookbook, performance characteristics, the two routing-hint methods.
- `docs/reviews/E-round-1-*.md`, `E-round-2-*.md`, `E-round-3-*.md`, `E-summary.md`, `E-iterations.md`, `E-waivers.md`, `E-bench.md`, `E-mutation.json`.
- `docs/PHASE-E-SUMMARY.md` (written at phase exit, mirroring the others).

**Modified:**
- `src/Propel/Runtime/Connection/PdoConnection.php` — rewritten as `final`, ~200 LOC. Removes the HHVM `prepare`/`quote` overrides at lines 184–195 and 197–209 (Phase A's umbrella entry §6.1 listed these for removal but the current source still has them — Phase E is the cleanup). Removes runtime `defined()`/`constant()` PDO-attribute lookups (`:79, :105, :108`) — replaced by `Internal/PdoAttributeMap.php`. Introduces `final` keyword (Tier 3-internal classification — Phase A snapshot already records it; addition of `final` is not a Tier 1 change because the class was never legitimately subclassable).
- `src/Propel/Runtime/Connection/ConnectionWrapper.php` — collapses from 745 LOC to a thin BC shim (~150 LOC target). Constructs the same decorator chain `LoggingConnection ← CachingConnection ← TransactionalConnection ← PdoConnection` (matching the order expected by the legacy shim) on `__construct`. Every public method forwards to the chain. `setUseDebug(true)` enables logging-decorator debug mode + emits `trigger_deprecation` exactly once. The class itself is `@deprecated since 3.0` per umbrella §3.2 Tier 2; `ConnectionDecoratorInterface` is the replacement consumer surface. `setAttribute` continues to accept string attribute names; the shim resolves via `PdoAttributeMap` (no `defined()` at runtime).
- `src/Propel/Runtime/Connection/StatementWrapper.php` — collapses from 462 LOC to a thin shim (~100 LOC). Today wraps `\PDOStatement` with logging + cache hooks. Phase E moves both responsibilities to `LoggingConnection` and `CachingConnection`; `StatementWrapper` becomes a `@deprecated` pass-through. Tier 2 deprecation runway. Cache invariants: any `StatementWrapper` returned to a consumer holds its inner statement reference until destruction, mirroring the held-reference invariant in `PreparedStatementLruCache`.
- `src/Propel/Runtime/Connection/ProfilerConnectionWrapper.php` — collapses to a `@deprecated` shim that constructs a `ProfilingConnection`-wrapped chain. Class itself is Tier 2 deprecation (per umbrella §3.2 — wrapped by profiling extensions). Eliminates the dependency from `ConnectionFactory::$useProfilerConnection` static (umbrella out-of-scope: the static itself is removed in a future cycle; Phase E only wires the new chain so the static *can* be removed cleanly later).
- `src/Propel/Runtime/Connection/ProfilerStatementWrapper.php` — `@deprecated` shim; functionality moves to `ProfilingConnection`'s prepared-statement decoration.
- `src/Propel/Runtime/Connection/ConnectionFactory.php` — composition logic. Reads decorator chain from datasource configuration (per-datasource `connection.decorators` array, defaults to `['transactional', 'logging', 'caching']` for the standard chain; `'profiling'` opt-in; `'routing'` auto-attached when `replica` config block is present). Validates order; throws `ConnectionDecoratorException` on illegal interleaves (e.g., `'caching'` before `'transactional'`). The `$useProfilerConnection` static stays present (BC) but its behavior is to inject `'profiling'` into the chain ONLY when no explicit `connection.decorators` is configured; documented as deprecated in favor of the explicit list.
- `src/Propel/Runtime/Connection/ConnectionManagerPrimaryReplica.php` — surfaces a new `setReplicaLagThresholdSeconds(float)` and `setSessionConsistencyWindowSeconds(float)` setter pair (Tier 2 — additive on a Tier 2 class; safe). The class continues to choose between read/write connection at the manager level for back-compat; the new `ReplicaRoutingConnection` decorator also handles routing internally — both paths coexist for the 3.x runway. Documented in `CONNECTION-DECORATORS.md`.
- `src/Propel/Runtime/ActiveQuery/Criteria.php` — adds three additive methods: `forcePrimary(): static`, `allowReplica(): static`, `getRoutingHint(): string` (returns `'force-primary'`, `'allow-replica'`, or `'auto'`). All three live behind `CriteriaRoutingHints` trait (mixed in via `use`). Tier 1 additive per §3.1. Signature-diff snapshot for `Criteria` refreshed; existing `criteria-constants.txt` snapshot (Phase A) stays unchanged (no constants added or removed).
- `src/Propel/Runtime/Connection/DebugPDO.php` — class-doc cross-references `CONNECTION-DECORATORS.md` for the new chain idiom (no functional change; alias path stays per umbrella §3.6).
- `src/Propel/Runtime/Connection/PropelPDO.php` — same cross-reference comment update.
- `src/Propel/Runtime/Connection/ConnectionInterface.php` — **NO signature changes**. Tier 1 frozen per umbrella §3.1. The interface stays exactly as-is.
- `src/Propel/Runtime/Connection/StatementInterface.php` — **NO signature changes**. Tier 1.
- `src/Propel/Runtime/Connection/ConnectionManagerInterface.php` — **NO signature changes**. Tier 2 frozen for runway.
- `src/Propel/Runtime/Connection/ConnectionManagerSingle.php` — extension to optionally accept a pre-configured chain (keyword-only optional parameter); null default keeps current call sites intact.
- `src/Propel/Common/Config/PropelConfiguration.php` — extends Symfony Config tree to accept `replicas`, `replicaLagThresholdSeconds`, `sessionConsistencyWindowSeconds`, `decorators` keys per datasource. Additivity preserved per umbrella §3.8 (existing `slaves`/`master` aliases stay parseable through `replicas`/`primary`).
- `src/Propel/Generator/Builder/Om/ObjectBuilder.php` — **NO change** (codegen unchanged; Phase F handles per-table typed routing-hint bridges).
- `src/Propel/Generator/Builder/Om/QueryBuilder.php` — emits a per-Query `forcePrimary()` / `allowReplica()` short-form (delegates to `Criteria::forcePrimary` / `allowReplica`) so `BookQuery::create()->forcePrimary()->find($con)` works. **Generated-code change** — golden tree refreshes; signature-diff for the BookQuery class fixture refreshes.
- `tests/snapshots/tracked-classes.txt` — adds `Propel\Runtime\Connection\ConnectionDecoratorInterface` (Tier 2) and `Propel\Runtime\Connection\Routing\RouteResolver`/`RoutingDecision`/`RouteRequest`/`SessionConsistencyWindow`/`ReplicaLagSampler` (Tier 3 with documented stability commitment). `Criteria` snapshot refreshed for the three new methods.
- `tests/Fixtures/bookstore/build/golden/` — regenerated for the `BookQuery` short-form additions.
- `tests/agnostic.phpunit.xml` — register `tests/PropertyTests/Connection/` and `tests/ChaosTests/Connection/` testsuite cells if not already covered by glob.
- `tests/deprecations.allowlist.json` — refreshed for the new `trigger_deprecation` calls fired by the BC shims (`ConnectionWrapper`, `StatementWrapper`, `ProfilerConnectionWrapper`).
- `docs/MIGRATION-FROM-PRE-AI.md` — three new sections: "Connection chain modernization (3.0)", "Replica routing (3.0)", "Telemetry stub & forward path to Phase I". Each has concrete before/after code snippets.
- `docs/UPGRADE-3.0.md` — capability summary for the decorator chain (consumer-relevant: how to opt into routing) and the three Tier-1 additive `Criteria` methods.
- `docs/BACKWARD_COMPATIBILITY.md` — Tier 2 entries: `ConnectionWrapper`, `StatementWrapper`, `ProfilerConnectionWrapper`, `ProfilerStatementWrapper`, `ConnectionDecoratorInterface`, `ReplicaLagExceededException`, `ConnectionDecoratorException`, `ConnectionManagerInterface` (re-confirmed). Tier 3 entries: every `Connection/Internal/*` class (free reign within `ConnectionDecoratorInterface`). Tier 1 additive: `Criteria::forcePrimary` / `Criteria::allowReplica` / `Criteria::getRoutingHint`.
- `CHANGELOG.md` — Phase E entries under `[Unreleased]`.

**Deleted:**
- None in Phase E. Per umbrella §3.6 alias-don't-delete and §3.2 deprecation-runway disciplines, every consumer-visible class stays callable on the runway. The internal-only `ConnectionFactory::$useProfilerConnection` static stays in place as a behaviorally-narrowed (now decorators-list-injecting) bridge — its actual elimination is tagged for a future cycle (out-of-scope below).

---

## Group E.1: PdoConnection final + decorator interface (Tasks E.1.1–E.1.4)

**Verification after each task:** `composer test:agnostic` + `composer stan` (baselines monotonic) + `composer cs-check` on the new files + signature-diff snapshot stable for Tier 1 (`ConnectionInterface`, `StatementInterface`).

Group E.1 is the foundation. After E.1, the new SPI is published and the bare bridge is in place but no decorator does anything yet. The legacy `ConnectionWrapper` is untouched. This deliberate ordering means each subsequent group adds ONE decorator and proves it independently.

---

### Task E.1.1: Publish `ConnectionDecoratorInterface` SPI (Tier 2)

**Files:**
- Create: `src/Propel/Runtime/Connection/ConnectionDecoratorInterface.php`
- Create: `tests/Propel/Tests/Runtime/Connection/ConnectionDecoratorInterfaceTest.php`
- Modify: `tests/snapshots/tracked-classes.txt` (add the new interface to Tier 2 snapshot)
- Modify: `docs/BACKWARD_COMPATIBILITY.md` (Tier 2 entry)

- [ ] **Step 1: Define the interface**

```php
namespace Propel\Runtime\Connection;

/**
 * Tier 2 SPI per umbrella §2.1. Implementations chain on top of a ConnectionInterface.
 *
 * @api Tier 2 — deprecation runway required for any signature change.
 */
interface ConnectionDecoratorInterface extends ConnectionInterface
{
    /** Returns the decorated inner connection. Walk the chain via repeated calls. */
    public function getInner(): ConnectionInterface;
}
```

- [ ] **Step 2: Test that any implementation walks the chain**

A trivial test: instantiate a 3-deep stack of throwaway test doubles, assert the walk returns the bottom.

- [ ] **Step 3: Snapshot + BC commit**

Update `tests/snapshots/tracked-classes.txt` and `docs/BACKWARD_COMPATIBILITY.md`. Tier 2 commitment statement: "`ConnectionDecoratorInterface::getInner()` signature is frozen for the 3.x line. Method addition requires a deprecation runway. Removal requires a major version bump."

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/Connection/ConnectionDecoratorInterface.php tests/Propel/Tests/Runtime/Connection/ConnectionDecoratorInterfaceTest.php tests/snapshots/tracked-classes.txt docs/BACKWARD_COMPATIBILITY.md
git commit -m "feat(runtime/connection): publish ConnectionDecoratorInterface SPI (Tier 2)"
```

---

### Task E.1.2: `Internal/AbstractConnectionDecorator` base

**Files:**
- Create: `src/Propel/Runtime/Connection/Internal/AbstractConnectionDecorator.php`
- Create: `tests/Propel/Tests/Runtime/Connection/Internal/AbstractConnectionDecoratorTest.php`

- [ ] **Step 1: Implement the abstract base**

Forwards every `ConnectionInterface` method (29 methods today — verified at task time via `grep "public function" src/Propel/Runtime/Connection/ConnectionInterface.php | wc -l`) to `$this->inner`. `getInner()` returns it. Constructor takes `ConnectionInterface $inner`. Class is `abstract`.

```php
namespace Propel\Runtime\Connection\Internal;

use Propel\Runtime\Connection\ConnectionDecoratorInterface;
use Propel\Runtime\Connection\ConnectionInterface;

abstract class AbstractConnectionDecorator implements ConnectionDecoratorInterface
{
    public function __construct(protected readonly ConnectionInterface $inner) {}

    public function getInner(): ConnectionInterface { return $this->inner; }

    // Default forwarders for every ConnectionInterface method. Subclass overrides what it decorates.
    public function prepare(string $statement, array $driverOptions = []) { return $this->inner->prepare($statement, $driverOptions); }
    public function exec($statement) { return $this->inner->exec($statement); }
    // ... (every other method)
}
```

- [ ] **Step 2: Test the forwarding**

Concrete subclass that doesn't override anything; assert each method delegates to a mock.

- [ ] **Step 3: Deptrac check**

`Connection/Internal/*` lives under `Runtime/Internal/` per Phase A's layer rules. Confirm Deptrac green.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/Connection/Internal/AbstractConnectionDecorator.php tests/Propel/Tests/Runtime/Connection/Internal/AbstractConnectionDecoratorTest.php
git commit -m "feat(runtime/connection): AbstractConnectionDecorator base (Tier 3 internal)"
```

---

### Task E.1.3: Rewrite `PdoConnection` as final ~200-LOC bare bridge

**Files:**
- Modify: `src/Propel/Runtime/Connection/PdoConnection.php`
- Create: `src/Propel/Runtime/Connection/Internal/PdoAttributeMap.php`
- Create: `tests/Propel/Tests/Runtime/Connection/Internal/PdoAttributeMapTest.php`
- Modify: `tests/Propel/Tests/Runtime/Connection/PdoConnectionTest.php`

- [ ] **Step 1: Extract PDO attribute resolution**

`PdoAttributeMap` provides a static const-time-resolved `array<string, int>` mapping `'ATTR_ERRMODE' => \PDO::ATTR_ERRMODE`, etc. — covering all PDO + driver-specific attributes used in `propel.yaml` configurations. Build by introspecting `\PDO` class constants at class-load and storing in a static. Throws `InvalidArgumentException` on an unknown name (better than the current silent `null`).

The map resolution is **eager at class load**, not per-call. The `defined()`/`constant()` calls on the hot path go away. Phase E DoD perf-bench (E.9) confirms the savings.

- [ ] **Step 2: Mark `PdoConnection` final**

Class declaration becomes `final class PdoConnection extends \PDO implements ConnectionInterface`. Phase A confirmed this class is internal/SPI per §3.3 (Tier 3); the `final` keyword is additive correctness. If Phase A's signature-diff harness tracks this class, refresh its snapshot.

- [ ] **Step 3: Remove HHVM `prepare`/`quote` overrides**

Lines 184–195 and 197–209 of the current source are leftover HHVM strict-mode overrides. PHP 8.3 doesn't need them. Delete; assert `\PDO::prepare` and `\PDO::quote` semantics are preserved by integration tests. Per umbrella §6.2 risk #4 ("HHVM strict-issue comments at `:174`/`:187` — already removed in Phase A, verify"): Phase A summary says removed, source says NOT removed. Phase E does the actual removal and updates the umbrella-spec line-citation.

- [ ] **Step 4: Replace `defined()`/`constant()` lookups with `PdoAttributeMap`**

Lines 79, 105, 108 in current source. The `setAttribute` and `__construct` paths receive string-or-int input; `PdoAttributeMap::resolve(string|int): int` handles both. Per umbrella §6.2 risk #3 — "use enum or required-int constants". Decision: array map keyed by string name is simpler than an enum here and preserves ergonomic config-file usage; the `int` constants on the right-hand side ARE the real PDO constants (eager-resolved once). Documented in the file's class-doc.

- [ ] **Step 5: Verify the class fits the ~200-LOC target**

`wc -l src/Propel/Runtime/Connection/PdoConnection.php` post-refactor: target ≤200. Hard cap 220. If over, the responsibility-split is incomplete.

- [ ] **Step 6: Run quality gates**

```
composer test:agnostic
composer stan
composer psalm
composer cs-check src/Propel/Runtime/Connection/PdoConnection.php src/Propel/Runtime/Connection/Internal/PdoAttributeMap.php
bin/propel internal:dump-signatures > /tmp/post.json
diff tests/snapshots/PdoConnection.signatures.json /tmp/post.json  # expected: empty (final addition is additive)
```

- [ ] **Step 7: Commit**

```bash
git add src/Propel/Runtime/Connection/PdoConnection.php src/Propel/Runtime/Connection/Internal/PdoAttributeMap.php tests/Propel/Tests/Runtime/Connection/Internal/PdoAttributeMapTest.php tests/Propel/Tests/Runtime/Connection/PdoConnectionTest.php tests/snapshots/PdoConnection.signatures.json
git commit -m "refactor(runtime/connection): PdoConnection final + PdoAttributeMap; remove HHVM overrides"
```

---

### Task E.1.4: SPI conformance test — `PdoConnection` passes ConnectionInterface contract

**Files:**
- Create: `tests/Propel/Tests/Runtime/Connection/ConnectionInterfaceContractTest.php`

- [ ] **Step 1: Define a contract test**

A reusable `abstract class ConnectionInterfaceContractTest` with one concrete subclass per `ConnectionInterface` implementation. Each subclass instantiates its connection and runs every contract assertion: `prepare()` returns a `StatementInterface`; `beginTransaction()`/`commit()` flow; `inTransaction()` mirroring; `quote()` round-trip; `lastInsertId()` post-insert; `getAttribute(\PDO::ATTR_DRIVER_NAME)` non-empty.

This contract test is the bedrock for all subsequent decorator implementations. They each get a subclass that wraps `PdoConnection` + the decorator-under-test and asserts the same contract holds.

- [ ] **Step 2: First concrete subclass tests `PdoConnection` directly against SQLite**

SQLite is the agnostic-test target per umbrella §1.2.

- [ ] **Step 3: Run + commit**

```bash
git add tests/Propel/Tests/Runtime/Connection/ConnectionInterfaceContractTest.php
git commit -m "test(runtime/connection): ConnectionInterface contract bedrock; PdoConnection conforms"
```

---

## Group E.2: TransactionalConnection (Tasks E.2.1–E.2.3)

**Verification after each task:** unit + property-based; `tests/Propel/Tests/Runtime/Connection/Internal/TransactionalConnectionTest.php` + `tests/PropertyTests/Connection/NestedTransactionInvariantTest.php` GREEN.

`TransactionalConnection` is the simplest decorator — pure state-machine on top of begin/commit/rollback. The current logic in `ConnectionWrapper` lives at `:189–280` (`beginTransaction`, `commit`, `rollBack`, `forceRollBack`) and uses `$nestedTransactionCount` + `$isUncommitable` flags. The port is mechanical but the property-based test is the proof.

---

### Task E.2.1: Port nested-tx accounting from ConnectionWrapper

**Files:**
- Create: `src/Propel/Runtime/Connection/Internal/TransactionalConnection.php`
- Create: `tests/Propel/Tests/Runtime/Connection/Internal/TransactionalConnectionTest.php`

- [ ] **Step 1: Port `beginTransaction`/`commit`/`rollBack`/`forceRollBack` semantics**

Source: `ConnectionWrapper::beginTransaction` at `:189` (uses `++$this->nestedTransactionCount`); `::commit` at `:218` (decrements); `::rollBack` at `:248` (sets `isUncommitable`); `::forceRollBack` at `:280` (forces the inner rollback regardless).

Verbatim port. New methods: `getNestedTransactionCount(): int` (already on `ConnectionInterface` so override the default `AbstractConnectionDecorator` forward); `isCommitable(): bool` (replaces `$isUncommitable` accessor).

- [ ] **Step 2: Run unit tests**

Cover: shallow tx (begin/commit), nested tx (3 deep), nested rollback (uncommitable propagation), forceRollBack. All semantics must match `ConnectionWrapper`'s current behavior bit-for-bit. The legacy class IS the reference implementation.

- [ ] **Step 3: Run quality gates + commit**

```bash
git add src/Propel/Runtime/Connection/Internal/TransactionalConnection.php tests/Propel/Tests/Runtime/Connection/Internal/TransactionalConnectionTest.php
git commit -m "feat(runtime/connection): TransactionalConnection decorator (nested-tx accounting)"
```

---

### Task E.2.2: Property-based nested-tx invariants

**Files:**
- Create: `tests/PropertyTests/Connection/NestedTransactionInvariantTest.php`
- Modify: `tests/agnostic.phpunit.xml` if needed

- [ ] **Step 1: Generate sequences via innmind/black-box**

Generators emit `('begin'|'commit'|'rollBack'|'forceRollBack')` such that at every prefix `(begin count) ≥ (commit count) + (rollback count)`. Run each sequence on a fresh `TransactionalConnection` wrapping a stub PDO; assert at each step:

- `getNestedTransactionCount()` equals `(begin) - (commit) - (rollback)`.
- `inTransaction()` returns `true` iff `getNestedTransactionCount() > 0`.
- `isCommitable()` flips to `false` after a nested `rollBack` and stays false until the outer transaction ends; flips back to `true` only on the outermost commit/rollback.
- The inner stub's `beginTransaction()` is called exactly **once per outermost `begin`** (savepoints are NOT exercised in this PBT — they're a separate code path).

- [ ] **Step 2: Run with a fixed seed for reproducibility**

Document the seed in `docs/reviews/E-round-1-summary.md`.

- [ ] **Step 3: Commit**

```bash
git add tests/PropertyTests/Connection/NestedTransactionInvariantTest.php tests/agnostic.phpunit.xml
git commit -m "test(pbt): nested-transaction invariants for TransactionalConnection"
```

---

### Task E.2.3: ConnectionInterface contract conformance

**Files:**
- Modify: `tests/Propel/Tests/Runtime/Connection/ConnectionInterfaceContractTest.php`

- [ ] **Step 1: Add a subclass that wraps `PdoConnection` (SQLite) in `TransactionalConnection`**

Run the same contract assertions; all must pass. This is the proof that the decorator hasn't broken the base contract.

- [ ] **Step 2: Run + commit**

```bash
git add tests/Propel/Tests/Runtime/Connection/ConnectionInterfaceContractTest.php
git commit -m "test(runtime/connection): TransactionalConnection conforms to ConnectionInterface contract"
```

---

## Group E.3: LoggingConnection (Tasks E.3.1–E.3.3)

**Verification after each task:** unit + a critical assertion that `debug_backtrace` is **never called on the hot path**. Property-based test exercises `setLogMethods` + `setLogger` configuration.

---

### Task E.3.1: Port log path WITHOUT `debug_backtrace`

**Files:**
- Create: `src/Propel/Runtime/Connection/Internal/LoggingConnection.php`
- Create: `tests/Propel/Tests/Runtime/Connection/Internal/LoggingConnectionTest.php`

- [ ] **Step 1: Identify what `debug_backtrace` was used for**

`ConnectionWrapper::log()` at `:712` walks the backtrace to find the method name of the caller (`prepare`, `exec`, `commit`, etc.). The result is fed to `isLogEnabledForMethod()`. The `debug_backtrace()` call runs on EVERY logged query — the hot path.

**Replacement:** the call site that decides to log already knows what method it's about to log (it's the one calling `log()`). Pass the method name as a parameter. New signature: `LoggingConnection::log(string $msg, string $callingMethod): void`. The five caller sites in `LoggingConnection`'s decorated `prepare`/`exec`/`query`/`beginTransaction`/`commit` pass it directly.

This is a **breaking change to the `ConnectionWrapper::log` signature**, which is Tier 2. Resolution: the BC shim's `ConnectionWrapper::log(string $msg)` keeps the old signature and uses `debug_backtrace()` (Tier 2 deprecated path) — only the internal `LoggingConnection::log` uses the new signature. The deprecation message at the shim explicitly calls out the swap. Documented in the migration cookbook.

- [ ] **Step 2: Telemetry stub hook**

Define `Propel\Runtime\Telemetry\TelemetryInterface` (the umbrella's Phase I no-op stub — Phase E ships only the no-op). `LoggingConnection` accepts an optional `TelemetryInterface` parameter; defaults to `NoOpTelemetry`. Each logged query also produces a `startQuerySpan()` call on the telemetry — no-op in Phase E. Phase I delivers real adapters that consume this hook.

The interface lives at `src/Propel/Runtime/Telemetry/TelemetryInterface.php` (and the no-op at `NoOpTelemetry.php`); both are Tier 2 published in Phase E so Phase I can extend without breakage. Snapshot updated.

- [ ] **Step 3: Run unit tests**

Cover: log on `prepare` calls; log filtered out when `setLogMethods(['exec'])` is set; logger receives PSR-3 `info` level. **Critical assertion**: a custom test logger records the calling method name AS PASSED IN, not as inferred — confirming the parameter-passing works. Use a debug `xdebug_get_function_stack()` assertion or `Reflection`-based check to ensure `debug_backtrace()` is not invoked during the logged calls.

- [ ] **Step 4: Run quality gates + commit**

```bash
git add src/Propel/Runtime/Connection/Internal/LoggingConnection.php src/Propel/Runtime/Telemetry/TelemetryInterface.php src/Propel/Runtime/Telemetry/NoOpTelemetry.php tests/Propel/Tests/Runtime/Connection/Internal/LoggingConnectionTest.php
git commit -m "feat(runtime/connection): LoggingConnection without debug_backtrace; telemetry stub hook"
```

---

### Task E.3.2: ConnectionInterface contract conformance

**Files:**
- Modify: `tests/Propel/Tests/Runtime/Connection/ConnectionInterfaceContractTest.php`

- [ ] **Step 1: Subclass for `LoggingConnection(PdoConnection(SQLite))`**

Contract still passes. Logger receives the expected messages.

- [ ] **Step 2: Commit**

```bash
git add tests/Propel/Tests/Runtime/Connection/ConnectionInterfaceContractTest.php
git commit -m "test(runtime/connection): LoggingConnection conforms to ConnectionInterface contract"
```

---

### Task E.3.3: New logging contract documented

**Files:**
- Modify: `docs/CONNECTION-DECORATORS.md`
- Modify: `docs/MIGRATION-FROM-PRE-AI.md`

- [ ] **Step 1: Document the parameter-passing change**

`docs/CONNECTION-DECORATORS.md` "Logging" section. Pre/post code samples. Note the BC shim preserves the old `log(string $msg)` signature on `ConnectionWrapper`.

- [ ] **Step 2: Migration cookbook entry**

`docs/MIGRATION-FROM-PRE-AI.md` "Connection chain modernization" section. Concrete: a consumer that subclassed `ConnectionWrapper` to override `log()` had access to `debug_backtrace`; the new `LoggingConnection::log` does not. Migration: pass the method name explicitly. The shim still works for one minor.

- [ ] **Step 3: Commit**

```bash
git add docs/CONNECTION-DECORATORS.md docs/MIGRATION-FROM-PRE-AI.md
git commit -m "docs(connection): logging contract change; debug_backtrace migration cookbook"
```

---

## Group E.4: CachingConnection + bounded LRU (Tasks E.4.1–E.4.5)

**Verification after each task:** unit (cache key uniqueness across `$driverOptions`); property-based (LRU correctness); chaos test (eviction during in-flight prepare).

This group resolves umbrella §6.2 risk #4 ("unbounded prepared-statement cache → DoS vector for long-running CLI workers"). The current `ConnectionWrapper::cachedPreparedStatements` array (`:89`, `:407–414`, `:583`) has no bound — a worker process running for hours can accumulate millions of unique statement keys.

---

### Task E.4.1: Implement `PreparedStatementLruCache`

**Files:**
- Create: `src/Propel/Runtime/Connection/Internal/PreparedStatementLruCache.php`
- Create: `tests/Propel/Tests/Runtime/Connection/Internal/PreparedStatementLruCacheTest.php`

- [ ] **Step 1: API shape**

```php
final class PreparedStatementLruCache
{
    public function __construct(int $capacity = 256) {}

    /** @param string $key */
    public function get(string $key): ?StatementInterface { /* moves to MRU */ }

    public function put(string $key, StatementInterface $statement): void { /* evicts LRU on overflow */ }

    public function clear(): void {}

    public function size(): int {}

    public function capacity(): int {}

    /** @return iterable<string> ordered LRU-first to MRU-last (testing & telemetry only) */
    public function keys(): iterable {}
}
```

Implementation: PHP `array` preserves insertion order. `get()` removes-and-reinserts the entry to bump it to MRU. `put()` removes-then-inserts; on overflow, `array_shift()` evicts LRU.

- [ ] **Step 2: Critical concurrency-safety invariant**

A statement returned by `get()` MUST hold a reference path to its inner `\PDOStatement` until the consumer is done. Eviction CANNOT free a statement currently held by a consumer. PHP's reference counting handles this naturally: `array_shift()` removes the entry from the cache, but if the consumer holds a `StatementInterface` reference, the underlying `\PDOStatement` survives. Document this in a class-doc paragraph + a unit test that holds a reference, evicts, and asserts the statement is still usable.

- [ ] **Step 3: Capacity boundary tests**

`new ...(0)` rejects with `InvalidArgumentException`. Negative capacity rejects. Capacity 1 evicts after every `put`.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/Connection/Internal/PreparedStatementLruCache.php tests/Propel/Tests/Runtime/Connection/Internal/PreparedStatementLruCacheTest.php
git commit -m "feat(runtime/connection): bounded-LRU PreparedStatementLruCache (Phase E §6.2 #4)"
```

---

### Task E.4.2: Property-based LRU invariants

**Files:**
- Create: `tests/PropertyTests/Connection/PreparedStatementLruInvariantTest.php`

- [ ] **Step 1: Generate operation sequences**

Generators: `Set::elements(['put', 'get'])`, integers as keys, fixed capacity. Build sequences and run on a fresh cache. After each operation, assert:

1. `size() ≤ capacity()`.
2. After a `put(k, _)`, `get(k)` returns the put value (unless an `evict-by-overflow` happened on a key that wasn't `k`).
3. The order returned by `keys()` correctly reflects LRU-to-MRU at every point — verified against a reference implementation (a naïve `OrderedDict`-style PHP array).
4. After `capacity` puts of distinct keys, the first put has been evicted (assuming no `get` interspersed).
5. **Critical: a held reference cannot be evicted under it.** A `put(k1, $stmt1)`; hold a copy of `$stmt1`; fill the cache to overflow so `k1` evicts; the held copy is still valid (callable methods, e.g., `bindValue()` returns true).

- [ ] **Step 2: Seeded; document seed in round-1 summary**

- [ ] **Step 3: Commit**

```bash
git add tests/PropertyTests/Connection/PreparedStatementLruInvariantTest.php
git commit -m "test(pbt): PreparedStatementLruCache invariants (size, order, held-ref safety)"
```

---

### Task E.4.3: `CachingConnection` decorator

**Files:**
- Create: `src/Propel/Runtime/Connection/Internal/CachingConnection.php`
- Create: `tests/Propel/Tests/Runtime/Connection/Internal/CachingConnectionTest.php`

- [ ] **Step 1: Cache-key derivation**

The current `ConnectionWrapper::prepare` builds a cache key from `$statement` (the SQL) plus `$driverOptions` (Phase A bug fix #4 — Phase E preserves that fix). `CachingConnection::buildCacheKey(string $sql, array $driverOptions): string` is a stable normalization. Documented as "the SHA-256 of `$sql . "\x00" . serialize(ksort($driverOptions))`" — short prefix is faster but collision-prone; SHA-256 is overkill but free. Decision recorded in class-doc + the security review's risk register entry.

- [ ] **Step 2: `prepare` decoration**

```php
public function prepare(string $statement, array $driverOptions = []): StatementInterface
{
    $key = self::buildCacheKey($statement, $driverOptions);
    $cached = $this->cache->get($key);
    if ($cached !== null) { return $cached; }
    $stmt = $this->inner->prepare($statement, $driverOptions);
    $this->cache->put($key, $stmt);
    return $stmt;
}
```

- [ ] **Step 3: `setCachePreparedStatements(bool)` toggle for BC**

The current `ConnectionWrapper::isCachePreparedStatements` flag stays callable on the shim; on the decorator, it routes to the cache's `clear()` method when toggled `false` and a `prepare` skip-cache flag.

- [ ] **Step 4: Unit tests**

- Different `$driverOptions` produce different cache keys — Phase A bug #4 reproduction.
- Same `$driverOptions` (re-ordered) hash to the same key (because of the `ksort`).
- Cache miss → `inner->prepare` called.
- Cache hit → `inner->prepare` NOT called.
- `clearStatementCache()` evicts all.

- [ ] **Step 5: Run quality gates + commit**

```bash
git add src/Propel/Runtime/Connection/Internal/CachingConnection.php tests/Propel/Tests/Runtime/Connection/Internal/CachingConnectionTest.php
git commit -m "feat(runtime/connection): CachingConnection decorator with bounded LRU"
```

---

### Task E.4.4: Choose default LRU capacity (post-bench)

**Files:**
- Create: `tests/Benchmarks/Connection/PreparedStatementCacheCapacitySweep.php`
- Modify: `src/Propel/Runtime/Connection/Internal/CachingConnection.php` (default constant)
- Modify: `docs/CONNECTION-DECORATORS.md`

- [ ] **Step 1: Bench cache-hit rate vs capacity**

Synthetic workload: rotating set of N distinct prepared statements, each used K times in random order, over `T` total `prepare` calls. Sweep capacity ∈ {32, 64, 128, 256, 512, 1024}. Measure: hit rate, memory used. Aim for ≥90% hit rate (umbrella §4.10 target) on a "typical workload" definition documented in the bench file.

The "typical workload" is approximated as: 200 distinct statements, Zipf-distributed access (matches a real ORM workload's hot statements), 50,000 total prepares.

- [ ] **Step 2: Pick the default**

Likely 256 or 512. Document the trade-off (memory vs hit rate) in `docs/CONNECTION-DECORATORS.md`. Configurable per-datasource via `connection.preparedStatementCacheCapacity` config key.

- [ ] **Step 3: Commit**

```bash
git add tests/Benchmarks/Connection/PreparedStatementCacheCapacitySweep.php src/Propel/Runtime/Connection/Internal/CachingConnection.php docs/CONNECTION-DECORATORS.md
git commit -m "perf(runtime/connection): pick default LRU capacity from bench sweep; document trade-off"
```

---

### Task E.4.5: Chaos test — evict during in-flight prepare

**Files:**
- Create: `tests/ChaosTests/Connection/StatementCacheEvictDuringPrepareTest.php`
- Modify: `tests/agnostic.phpunit.xml` if needed

- [ ] **Step 1: Construct the chaos scenario**

Synchronous emulation: a `prepare(sql_A)` returns a statement; the consumer holds the reference; meanwhile, `prepare(sql_B...sql_(B+capacity-1))` fills the cache to overflow, evicting `sql_A`'s entry; the consumer then calls `bindValue()` and `execute()` on the held `sql_A` statement.

The held reference invariant from E.4.2 is the formal proof; this chaos test is the integration check that the held reference survives PDO eviction in practice.

- [ ] **Step 2: Assert the held reference is still usable**

Bind, execute, fetch. All succeed. The cache no longer contains the key but the consumer's statement still functions because PHP refcounts kept the underlying `\PDOStatement` alive.

- [ ] **Step 3: Commit**

```bash
git add tests/ChaosTests/Connection/StatementCacheEvictDuringPrepareTest.php tests/agnostic.phpunit.xml
git commit -m "test(chaos): cache eviction during in-flight prepare keeps held reference valid"
```

---

## Round 1 Review Checkpoint (mid-phase, after E.1 + E.2 + E.3 + E.4)

**Trigger:** all tasks E.1.1–E.4.5 landed and green. Three of five decorators in place + foundation + bounded LRU.
**Reviewers (per umbrella §4.13.3 mid-phase HIGH-RISK):** Architecture + BC + SQL & concurrency specialist + Security reviewer (4 lenses).
**Lens:**

- **Architecture (decorator chain shape):** Is `AbstractConnectionDecorator`'s default-forwarder pattern correct? Are the three decorators in place each genuinely single-responsibility (no creeping concerns)? Is `getInner()` chain-walkable cleanly? Could a fresh subagent in E.5 / E.6 stumble over an ambiguity in the SPI?
- **BC (Tier 1/2 surface integrity):** Are `ConnectionInterface` and `StatementInterface` truly unchanged? Is the `Criteria::forcePrimary` / `allowReplica` signature additive (Tier 1 §3.1)? Does `ConnectionDecoratorInterface` Tier 2 surface withstand "what if a third party implemented it" (try one as a smoke).
- **SQL & concurrency specialist:** Are the nested-tx invariants correct against MySQL 8 + PG 14 SAVEPOINT semantics (not just SQLite)? Is the LRU's held-reference invariant water-tight in adversarial scenarios? Will the cache-key normalization (`ksort` on `$driverOptions`) miss anything semantically distinct?
- **Security reviewer:** Is the LRU eviction policy DoS-resistant (an attacker spamming distinct prepared SQL cannot induce unbounded growth — confirmed by capacity bound, but also: does the cache key derivation introduce a memory amplification vector? SHA-256 of arbitrary SQL: yes; any size cap on the SQL string itself?). Can `LoggingConnection`'s logged data leak sensitive bound parameters (current `ConnectionWrapper::bindValuesToStatement` logs values — does the new path?)? Is the ProfilingConnection histogram bucketing exploitable as a timing oracle?

**Outputs:** `docs/reviews/E-round-1-architecture.md`, `E-round-1-bc.md`, `E-round-1-sql-concurrency.md`, `E-round-1-security.md`, `E-round-1-summary.md`.
**Iteration budget:** 3 cycles per umbrella §4.15.3. Track in `E-iterations.md`.

Findings tagged `MUST-FIX` block progression to E.5 (ProfilingConnection) and E.6 (ReplicaRoutingConnection — the riskiest group). `SHOULD-FIX` may be waived per umbrella §4.15.4.

---

## Group E.5: ProfilingConnection (Tasks E.5.1–E.5.3)

**Verification after each task:** unit tests for histogram-bucket emission; differential test against `ProfilerConnectionWrapper`'s legacy output (parity); telemetry stub hook fires.

The current `ProfilerConnectionWrapper` (135 LOC) and `ProfilerStatementWrapper` (75 LOC) collapse into one decorator, with telemetry-friendly histogram emission instead of the ad-hoc `Profiler::countQuery()` accounting.

---

### Task E.5.1: Port profiling logic into a decorator

**Files:**
- Create: `src/Propel/Runtime/Connection/Internal/ProfilingConnection.php`
- Create: `tests/Propel/Tests/Runtime/Connection/Internal/ProfilingConnectionTest.php`

- [ ] **Step 1: Identify what `ProfilerConnectionWrapper` does**

Reads (per current source): query duration measurement (microtime around `prepare`/`exec`/`query`); per-statement counter increment; passthrough-with-telemetry of the underlying statement.

- [ ] **Step 2: Port to `ProfilingConnection`**

Decorate `prepare`/`exec`/`query` with `microtime` brackets. Call the (telemetry stub) `recordHydrationDuration` hook from §2.5 — Phase E ships a no-op; Phase I will replace with adapters.

The histogram bucketing is `(0, 0.001s, 0.01s, 0.1s, 1s, ∞)` per common observability convention. Bucket choice documented; configurable via `connection.profiling.histogramBuckets` config key.

- [ ] **Step 3: Eliminate `StatementWrapper`-side profiling**

The current `ProfilerStatementWrapper` decorates statement methods; in the new chain, `ProfilingConnection` is responsible for the whole-call latency, and `\PDOStatement` itself is left alone. Per-statement counters (`fetch`/`fetchAll` duration) are deferred to Phase I unless explicitly asked.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/Connection/Internal/ProfilingConnection.php tests/Propel/Tests/Runtime/Connection/Internal/ProfilingConnectionTest.php
git commit -m "feat(runtime/connection): ProfilingConnection decorator (histogram emission)"
```

---

### Task E.5.2: Differential test against legacy ProfilerConnectionWrapper

**Files:**
- Create: `tests/Propel/Tests/Runtime/Connection/ProfilingDifferentialTest.php`

- [ ] **Step 1: Drive identical workload through both paths**

Workload: 1000 prepares + 1000 execs over a SQLite fixture. Drive once through the legacy `ProfilerConnectionWrapper(PdoConnection)`, once through `ProfilingConnection(PdoConnection)`. Assert: the count of profiled queries matches; the duration sum differs by ≤2% (the new path is implemented differently — exact parity is not the goal; observability parity is).

- [ ] **Step 2: Commit**

```bash
git add tests/Propel/Tests/Runtime/Connection/ProfilingDifferentialTest.php
git commit -m "test(runtime/connection): ProfilingConnection observability parity with legacy wrapper"
```

---

### Task E.5.3: ConnectionInterface contract conformance

**Files:**
- Modify: `tests/Propel/Tests/Runtime/Connection/ConnectionInterfaceContractTest.php`

- [ ] **Step 1: Subclass for `ProfilingConnection(PdoConnection)`**

- [ ] **Step 2: Commit**

```bash
git add tests/Propel/Tests/Runtime/Connection/ConnectionInterfaceContractTest.php
git commit -m "test(runtime/connection): ProfilingConnection conforms to ConnectionInterface contract"
```

---

## Group E.6: ReplicaRoutingConnection (NEW capability — Tasks E.6.1–E.6.5)

**Verification after each task:** unit + clock-injected; chaos test for primary-fallback; end-to-end against `ConnectionManagerPrimaryReplica` integration.

This is the only **new capability** group in Phase E. Per umbrella §6.4 capability additions row + §2.1 architecture target. The behavior is layered:

1. **Per-query hint:** `Criteria::forcePrimary()` / `allowReplica()` / no hint (default `'auto'`).
2. **Session-consistency window:** for N seconds after a write in a session, `'auto'` reads route to primary regardless of hint.
3. **Replica-lag awareness:** if the chosen replica's lag exceeds threshold, skip it (route to next replica or primary).
4. **Automatic primary-fallback:** if the routing decision is `replica:X` but the connection refuses, fall back to primary and log the rationale.

Each layer has a separate task and its own deterministic test.

---

### Task E.6.1: `RouteResolver` + value objects

**Files:**
- Create: `src/Propel/Runtime/Connection/Routing/RouteRequest.php`
- Create: `src/Propel/Runtime/Connection/Routing/RoutingDecision.php`
- Create: `src/Propel/Runtime/Connection/Routing/RouteResolver.php`
- Create: `tests/Propel/Tests/Runtime/Connection/Routing/RouteResolverTest.php`

- [ ] **Step 1: Define the value objects**

```php
final readonly class RouteRequest
{
    public function __construct(
        public string $operation,            // 'read'|'write'
        public string $hint,                 // 'force-primary'|'allow-replica'|'auto'
        public ?int $sessionLastWriteAtMicros,
        public array $replicaLagSamples,     // array<replicaName, lagSeconds>
    ) {}
}

final readonly class RoutingDecision
{
    public function __construct(
        public string $target,               // 'primary'|'replica:<name>'
        public string $rationale,            // human-readable; structured for telemetry
        public int $routedAtMicros,
    ) {}
}
```

- [ ] **Step 2: Pure-logic `RouteResolver::resolve(RouteRequest, ResolverConfig): RoutingDecision`**

`ResolverConfig`: window seconds, lag threshold, fallback policy. Logic:

```
if request.operation == 'write': return primary, rationale='write-op'
if request.hint == 'force-primary': return primary, rationale='hint=force-primary'
if request.sessionLastWriteAtMicros + windowMicros > now: return primary, rationale='session-consistency-window'
candidates = [r for r in replicas if request.replicaLagSamples[r] <= lagThreshold]
if !candidates: return primary, rationale='all-replicas-lagging'
return random.choice(candidates), rationale='allowed: lag <= threshold'
```

The randomization is seedable for testing.

- [ ] **Step 3: Tests cover every branch**

Each branch has a dedicated test method. Inject `now` via a `ClockInterface` for deterministic tests.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/Connection/Routing/RouteRequest.php src/Propel/Runtime/Connection/Routing/RoutingDecision.php src/Propel/Runtime/Connection/Routing/RouteResolver.php tests/Propel/Tests/Runtime/Connection/Routing/RouteResolverTest.php
git commit -m "feat(runtime/connection/routing): RouteResolver pure-logic + value objects"
```

---

### Task E.6.2: `SessionConsistencyWindow`

**Files:**
- Create: `src/Propel/Runtime/Connection/Routing/SessionConsistencyWindow.php`
- Create: `tests/Propel/Tests/Runtime/Connection/Routing/SessionConsistencyWindowTest.php`

- [ ] **Step 1: Tracking last-write-time per session**

Implementation: a `ClockInterface` and a `?int $lastWriteAtMicros`. `noteWrite(): void` updates the timestamp. `isWindowActive(int $windowSeconds): bool` checks `lastWriteAtMicros + window > now`.

The "session" scope is the `ConnectionInterface` lifetime — one window per `ReplicaRoutingConnection` instance. Phase J revisits worker-mode session-scoping; Phase E is single-fiber-per-connection.

- [ ] **Step 2: Tests with injected clock**

Window expiry; multiple writes (each resets); concurrent reads (multiple `isWindowActive` calls in window vs out-of-window).

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Runtime/Connection/Routing/SessionConsistencyWindow.php tests/Propel/Tests/Runtime/Connection/Routing/SessionConsistencyWindowTest.php
git commit -m "feat(runtime/connection/routing): SessionConsistencyWindow with injected clock"
```

---

### Task E.6.3: `ReplicaLagSampler`

**Files:**
- Create: `src/Propel/Runtime/Connection/Routing/ReplicaLagSampler.php`
- Create: `tests/Propel/Tests/Runtime/Connection/Routing/ReplicaLagSamplerTest.php`

- [ ] **Step 1: Sampler API**

```php
final class ReplicaLagSampler
{
    public function __construct(int $sampleIntervalSeconds = 10, ClockInterface $clock = ...) {}

    /** Probe a replica connection (out-of-band; cached) and return lag in seconds. */
    public function probe(string $replicaName, ConnectionInterface $replica, AdapterInterface $adapter): float {}

    /** Get cached lag samples; safe for hot-path call. */
    public function getSamples(): array {}

    /** Force re-sample on next probe (test hook). */
    public function invalidate(string $replicaName): void {}
}
```

- [ ] **Step 2: Per-adapter probe SQL**

`AdapterInterface` extension (Tier 2 — additive method `getReplicaLagProbeSql(): ?string`). Default returns `null` (no native probe). MySQL adapter returns `SHOW REPLICA STATUS` (parsing `Seconds_Behind_Source` column). PG adapter returns `SELECT EXTRACT(EPOCH FROM (now() - pg_last_xact_replay_timestamp()))`. SQLite returns `null` (single-node; sampler returns `0.0`).

- [ ] **Step 3: Hot-path safety — sampling NEVER blocks the request**

`ReplicaLagSampler::probe` is called only from a worker-hook or an out-of-band scheduler. The hot-path consumer (`ReplicaRoutingConnection::prepare/exec`) calls `getSamples()` only — never `probe()`. Documented with a `@internal-warning` PHPDoc tag and a unit test that asserts `getSamples()` does not invoke a database call.

- [ ] **Step 4: Security reviewer spot-check — no timing oracle**

The sampler runs on a clock-driven cadence, not driven by request count. An attacker cannot influence the sample timing through query traffic. Adapter probe queries do NOT include user input. Documented in the security risk register entry.

- [ ] **Step 5: Commit**

```bash
git add src/Propel/Runtime/Connection/Routing/ReplicaLagSampler.php src/Propel/Runtime/Adapter/AdapterInterface.php src/Propel/Runtime/Adapter/Pdo/{MysqlAdapter,PgsqlAdapter,SqliteAdapter}.php tests/Propel/Tests/Runtime/Connection/Routing/ReplicaLagSamplerTest.php
git commit -m "feat(runtime/connection/routing): ReplicaLagSampler (out-of-band probe; per-adapter SQL)"
```

---

### Task E.6.4: `ReplicaRoutingConnection` decorator

**Files:**
- Create: `src/Propel/Runtime/Connection/Internal/ReplicaRoutingConnection.php`
- Create: `tests/Propel/Tests/Runtime/Connection/Internal/ReplicaRoutingConnectionTest.php`
- Create: `src/Propel/Runtime/Connection/Exception/ReplicaLagExceededException.php`
- Modify: `src/Propel/Runtime/ActiveQuery/Routing/CriteriaRoutingHints.php` (or trait)
- Modify: `src/Propel/Runtime/ActiveQuery/Criteria.php`
- Modify: `tests/snapshots/Criteria.signatures.json` (refresh; additive)

- [ ] **Step 1: Decorator implementation**

`ReplicaRoutingConnection` wraps **two** inner `ConnectionInterface` instances: `$primary` and `array<string, ConnectionInterface> $replicas`. On each `prepare`/`exec`/`query`:

```php
$req = new RouteRequest(operation: $this->classifyOperation($sql), hint: $this->currentHint, ...);
$decision = $this->resolver->resolve($req, $this->config);
$this->lastDecision = $decision;
$this->logger?->info(sprintf('routing: %s — %s', $decision->target, $decision->rationale));
$inner = $this->resolveTarget($decision->target);
try {
    return $inner->prepare($sql, $opts);
} catch (\PDOException $e) {
    if ($this->config->fallbackToPrimary && $decision->target !== 'primary') {
        return $this->primary->prepare($sql, $opts);
    }
    throw;
}
```

`classifyOperation`: SQL prefix lookup (`SELECT`/`SHOW`/`WITH` → read; `INSERT`/`UPDATE`/`DELETE`/`MERGE`/`CALL` → write — note `CALL` is conservative). Edge cases: `WITH ... DELETE ... RETURNING` is a write disguised as a CTE — use leading-keyword scan PLUS a regex for `DELETE`/`UPDATE`/`INSERT` anywhere in the prefix; documented as best-effort with a per-query override (the consumer sets `forcePrimary()` for ambiguous CTEs). Phase F's tokenizer-based parser will replace this; Phase E's heuristic is documented as transitional.

- [ ] **Step 2: Per-query hint plumbing**

`Criteria::forcePrimary(): static` sets an internal `$routingHint = 'force-primary'`. `Criteria::allowReplica(): static` sets `'allow-replica'`. `Criteria::getRoutingHint(): string` getter returns the current value (default `'auto'`).

The query-execution path (`ModelCriteria::find()`) reads `$criteria->getRoutingHint()` and passes it through to the connection via a `setNextQueryHint(string)` call on `ReplicaRoutingConnection`. The hint is cleared after each call.

- [ ] **Step 3: `ReplicaLagExceededException` semantics**

Thrown only when `allowReplica()` is set explicitly AND ALL replicas exceed lag threshold AND `fallbackToPrimary === false`. Default config: `fallbackToPrimary = true`, so the exception only fires when a consumer explicitly opts into "fail rather than serve stale" — an explicit consistency requirement.

- [ ] **Step 4: Routing-decision logging is structured**

`RoutingDecision::rationale` is a single line of structured text: `key=value, key=value`. Easy to grep, easy to feed to a log aggregator. Examples: `target=replica:r1, hint=auto, lag=0.2s, threshold=2.0s`. Documented in `docs/CONNECTION-DECORATORS.md`.

- [ ] **Step 5: Tests cover every routing decision**

- write op → primary;
- force-primary hint → primary;
- session window active → primary;
- allow-replica + healthy replica → replica;
- allow-replica + lagging replica → primary (default fallback) OR exception (fallback off);
- replica connection refusal → primary fallback (default);
- routing decision logged on every call.

- [ ] **Step 6: Commit**

```bash
git add src/Propel/Runtime/Connection/Internal/ReplicaRoutingConnection.php src/Propel/Runtime/Connection/Exception/ReplicaLagExceededException.php src/Propel/Runtime/ActiveQuery/Criteria.php src/Propel/Runtime/ActiveQuery/Routing/CriteriaRoutingHints.php tests/Propel/Tests/Runtime/Connection/Internal/ReplicaRoutingConnectionTest.php tests/snapshots/Criteria.signatures.json
git commit -m "feat(runtime/connection): ReplicaRoutingConnection + Criteria forcePrimary/allowReplica hints"
```

---

### Task E.6.5: Chaos test — replica failover

**Files:**
- Create: `tests/ChaosTests/Connection/ReplicaFailoverTest.php`

- [ ] **Step 1: Construct the scenario**

A primary connection (SQLite in-memory) and a replica that is configured to throw `\PDOException` on every `prepare`. Driver workload: 100 reads under `allowReplica()` hint. Default config (fallbackToPrimary=true).

- [ ] **Step 2: Assertions**

Every read succeeds (via fallback). Every fall-back is logged with rationale `target=primary, hint=allow-replica, fallback-from=replica:replicaName`. The `lastDecision` exposed for inspection records the rationale. No `ReplicaLagExceededException` thrown (fallback handled the failure).

- [ ] **Step 3: Variant — fallback disabled**

Same workload, `fallbackToPrimary = false`. Every read throws `ReplicaLagExceededException` (or wrapping `PropelException`). Asserts the explicit-opt-out path works.

- [ ] **Step 4: Commit**

```bash
git add tests/ChaosTests/Connection/ReplicaFailoverTest.php
git commit -m "test(chaos): replica failover routes to primary; opt-out throws ReplicaLagExceededException"
```

---

## Group E.7: ConnectionFactory composition (Tasks E.7.1–E.7.3)

**Verification after each task:** composition tests covering every documented decorator order; deptrac green; `ConnectionDecoratorException` thrown on illegal interleaves.

`ConnectionFactory` is the only place that knows the canonical decorator order. After E.7, the factory composes the chain from configuration; consumers stop instantiating `ConnectionWrapper` directly (the legacy shim still works on the runway).

---

### Task E.7.1: Composition logic

**Files:**
- Modify: `src/Propel/Runtime/Connection/ConnectionFactory.php`
- Create: `tests/Propel/Tests/Runtime/Connection/ConnectionFactoryTest.php`

- [ ] **Step 1: Configuration shape**

```yaml
propel:
  database:
    bookstore:
      connection:
        # ... existing keys (dsn, user, password, attributes, settings) ...
        decorators: ['transactional', 'logging', 'caching', 'profiling']  # NEW; default omits 'profiling'
        preparedStatementCacheCapacity: 256                                # NEW
        replicas:                                                          # NEW (already accepted; ConnectionFactory now wires routing)
          replica1:
            dsn: ...
            user: ...
            password: ...
        routing:                                                           # NEW
          sessionConsistencyWindowSeconds: 5
          replicaLagThresholdSeconds: 2.0
          fallbackToPrimary: true
```

- [ ] **Step 2: Build chain in canonical order**

Inner-to-outer order:
1. `PdoConnection` (innermost; bare PDO).
2. `TransactionalConnection` (manages tx counter).
3. `LoggingConnection` (must be inside `TransactionalConnection` per umbrella §2.1 — logs see what the inner DB sees, not the wrapper's tx accounting).
4. `CachingConnection` (inside Logging so cache hits are observable).
5. `ProfilingConnection` (outermost when present).
6. `ReplicaRoutingConnection` (outermost-of-all when replicas configured; wraps the entire per-target chain × N — primary chain + per-replica chain).

The factory:
1. Loads adapter (existing).
2. Builds primary chain: `PdoConnection ← Transactional ← Logging ← Caching ← (Profiling)?`.
3. If `replicas` configured, builds a chain per replica.
4. Wraps in `ReplicaRoutingConnection(primary, replicas)`.
5. Returns the outermost.

- [ ] **Step 3: Misorder validation**

If a consumer-supplied `decorators` list violates the canonical order (e.g., `['caching', 'transactional']`), throw `ConnectionDecoratorException` with a concrete message: "Decorator 'caching' must be inside 'transactional'; got ['caching', 'transactional']. See docs/CONNECTION-DECORATORS.md#order".

- [ ] **Step 4: Tests**

- Default config (no `decorators`) → standard 3-decorator chain.
- Profiling explicitly enabled → 4-decorator chain.
- Replicas configured → routing-wrapped chain.
- `decorators: []` → bare `PdoConnection` (advanced opt-out; documented).
- Misorder → exception.

- [ ] **Step 5: Commit**

```bash
git add src/Propel/Runtime/Connection/ConnectionFactory.php tests/Propel/Tests/Runtime/Connection/ConnectionFactoryTest.php
git commit -m "feat(runtime/connection): ConnectionFactory composes decorator chain from config"
```

---

### Task E.7.2: PropelConfiguration tree extension

**Files:**
- Modify: `src/Propel/Common/Config/PropelConfiguration.php`
- Modify: `tests/Propel/Tests/Common/Config/PropelConfigurationTest.php`

- [ ] **Step 1: Add nodes for `decorators`, `preparedStatementCacheCapacity`, `routing.*`**

Symfony Config tree additions. Defaults: `decorators` defaults to `['transactional', 'logging', 'caching']`; `preparedStatementCacheCapacity` defaults to `256`; `routing.sessionConsistencyWindowSeconds` defaults to `5.0`; `routing.replicaLagThresholdSeconds` defaults to `2.0`; `routing.fallbackToPrimary` defaults to `true`.

Per umbrella §3.8 alias-don't-rename: existing `slaves`/`master` keys still parseable, forward to `replicas`/`primary` with deprecation. Phase A wired this; Phase E adds the `routing` block alongside.

- [ ] **Step 2: Tests**

Each new node: default value test; explicit override test; alias-forward test.

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Common/Config/PropelConfiguration.php tests/Propel/Tests/Common/Config/PropelConfigurationTest.php
git commit -m "feat(common/config): connection decorators + replica routing config tree"
```

---

### Task E.7.3: ConnectionInterface contract conformance — full chain

**Files:**
- Modify: `tests/Propel/Tests/Runtime/Connection/ConnectionInterfaceContractTest.php`

- [ ] **Step 1: Subclass exercising the full default chain**

`Profiling(Caching(Logging(Transactional(PdoConnection))))` — even though the default omits Profiling, the contract test exercises it with Profiling enabled to maximize coverage.

- [ ] **Step 2: Subclass exercising the routing-wrapped chain**

`ReplicaRouting(primary=full-chain, replicas=[full-chain × 2])`. Workload mixes reads + writes; assertions: every contract method works; no leaks.

- [ ] **Step 3: Commit**

```bash
git add tests/Propel/Tests/Runtime/Connection/ConnectionInterfaceContractTest.php
git commit -m "test(runtime/connection): full default chain + routing-wrapped chain conform to contract"
```

---

## Group E.8: BC shim + deprecation runway (Tasks E.8.1–E.8.3)

**Verification after each task:** existing tests of `ConnectionWrapper`/`StatementWrapper` continue to pass; `tests/deprecations.allowlist.json` baseline regenerated to capture exactly the new shim deprecations.

After E.8, `ConnectionWrapper` is no longer the load-bearing implementation — it's a public-API shim around the new chain. Same for `StatementWrapper` and `ProfilerConnectionWrapper`. Consumer code that did `new ConnectionWrapper($pdo)` continues to work; the shim emits `trigger_deprecation` once per category to nudge consumers toward the explicit chain.

---

### Task E.8.1: ConnectionWrapper as thin BC shim

**Files:**
- Modify: `src/Propel/Runtime/Connection/ConnectionWrapper.php`
- Create: `tests/Propel/Tests/Runtime/Connection/ConnectionWrapperBcShimTest.php`
- Modify: `tests/deprecations.allowlist.json`

- [ ] **Step 1: Constructor builds the equivalent chain**

```php
public function __construct(ConnectionInterface $connection)
{
    trigger_deprecation('maturix/propel', '3.0', 'ConnectionWrapper is deprecated. Use ConnectionFactory::create with a decorator chain. See docs/CONNECTION-DECORATORS.md#migration. Removal targeted for 4.0.');

    // Build the chain that mirrors the legacy ConnectionWrapper behavior.
    $logging = new LoggingConnection($connection);
    $caching = new CachingConnection($logging, new PreparedStatementLruCache(256));
    $this->chain = new TransactionalConnection($caching);
}
```

The internal `$chain` field is the new entry. Every `ConnectionInterface` method delegates to `$this->chain->$method(...)`.

- [ ] **Step 2: Preserve every public method signature**

`setUseDebug(bool)`, `setLogger(LoggerInterface)`, `setLogMethods(array)`, `clearStatementCache()`, `getNestedTransactionCount()`, `isCommitable()`, `forceRollBack()`, `log(string)` (the legacy 1-arg version still uses `debug_backtrace` per the BC contract — emits the deprecation), etc.

The legacy `log(string $msg)` is the only place `debug_backtrace()` survives in 3.x — confined to the shim. The new `LoggingConnection::log(string, string)` uses parameter-passing; consumers using the new chain don't pay the backtrace cost.

- [ ] **Step 3: Target ~150 LOC**

Down from 745. The shim is logic-free pass-through; the work is in the decorator chain.

- [ ] **Step 4: Re-run existing tests**

`tests/Propel/Tests/Runtime/Connection/ConnectionWrapperTest.php` (Phase A) must continue to pass with no test changes.

- [ ] **Step 5: Refresh deprecation allowlist**

Each `trigger_deprecation` from the shim adds an entry. Re-run baseline generation:

```bash
SYMFONY_DEPRECATIONS_HELPER='max[self]=999999&baselineFile=tests/deprecations.allowlist.json&generateBaseline=true' \
  vendor/bin/phpunit -c tests/agnostic.phpunit.xml
```

Verify the new entries are exactly the expected categories — no surprise leakage.

- [ ] **Step 6: Commit**

```bash
git add src/Propel/Runtime/Connection/ConnectionWrapper.php tests/Propel/Tests/Runtime/Connection/ConnectionWrapperBcShimTest.php tests/deprecations.allowlist.json
git commit -m "refactor(runtime/connection): ConnectionWrapper becomes thin BC shim around decorator chain"
```

---

### Task E.8.2: StatementWrapper + ProfilerConnectionWrapper shims

**Files:**
- Modify: `src/Propel/Runtime/Connection/StatementWrapper.php`
- Modify: `src/Propel/Runtime/Connection/ProfilerConnectionWrapper.php`
- Modify: `src/Propel/Runtime/Connection/ProfilerStatementWrapper.php`

- [ ] **Step 1: `StatementWrapper` becomes a pass-through to `\PDOStatement`**

Today's 462 LOC drops to ~100 LOC. The cache + log responsibilities moved to the decorators in E.4 + E.3. The shim retains: PDO method passthrough; Phase A's bug-fix #4 cache-key handling on the cache-side (already moved to `CachingConnection::buildCacheKey`); class-level `@deprecated` PHPDoc; `trigger_deprecation` once per construction.

- [ ] **Step 2: `ProfilerConnectionWrapper` becomes a shim that constructs `ProfilingConnection`-wrapped chain**

Class-level `@deprecated`; `trigger_deprecation`. Today's `ConnectionFactory::$useProfilerConnection` static keeps booting — its only effect is to add `'profiling'` to the `decorators` list when no explicit list is configured. Documented: the static is a deprecation-free fallback for legacy global-on profiling; consumers wanting per-datasource control use the `decorators` config key.

- [ ] **Step 3: `ProfilerStatementWrapper` shim**

Pass-through to `ProfilingConnection`'s prepared statement; class-level `@deprecated`.

- [ ] **Step 4: Run quality gates + commit**

```bash
git add src/Propel/Runtime/Connection/StatementWrapper.php src/Propel/Runtime/Connection/ProfilerConnectionWrapper.php src/Propel/Runtime/Connection/ProfilerStatementWrapper.php
git commit -m "refactor(runtime/connection): StatementWrapper + Profiler*Wrapper become thin BC shims"
```

---

### Task E.8.3: Migration cookbook

**Files:**
- Modify: `docs/MIGRATION-FROM-PRE-AI.md`
- Modify: `docs/UPGRADE-3.0.md`
- Modify: `docs/CONNECTION-DECORATORS.md`
- Modify: `docs/BACKWARD_COMPATIBILITY.md`
- Modify: `CHANGELOG.md`

- [ ] **Step 1: Add three sections to `docs/MIGRATION-FROM-PRE-AI.md`**

1. **"Connection chain modernization (3.0)"** — concrete: `new ConnectionWrapper($pdo)` → `ConnectionFactory::create($config, $adapter)`. Show: each old behavior maps to which decorator; the configuration knobs.

2. **"Replica routing (3.0)"** — `BookQuery::create()->forcePrimary()->find($con)`; `BookQuery::create()->allowReplica()->find($con)`; the implicit session-consistency window; the lag threshold; the primary fallback semantics. Worked example with a 2-replica setup.

3. **"`debug_backtrace` removal in `LoggingConnection`"** — concrete migration for any consumer subclass that overrode `ConnectionWrapper::log()` to inspect the call site. Pre/post code snippets.

4. **"`instanceof ConnectionWrapper` checks silently miss the new chain"** — flagged risk for Phase E. Migration: use `instanceof ConnectionDecoratorInterface` plus walk via `getInner()` until the bare `PdoConnection` is found.

- [ ] **Step 2: `docs/UPGRADE-3.0.md` capability summary**

Brief: "decorator chain", "replica routing hints", "bounded LRU statement cache (default 256)". Links to `docs/CONNECTION-DECORATORS.md`.

- [ ] **Step 3: `docs/BACKWARD_COMPATIBILITY.md` Tier-2 entries**

Already enumerated in the file-structure list. Confirm each entry has a runway statement: "deprecation runway through 3.x; removed at 4.0."

- [ ] **Step 4: `CHANGELOG.md` `[Unreleased]`**

- Added: `ConnectionDecoratorInterface` SPI; `Connection/Internal/{Transactional,Logging,Caching,Profiling,ReplicaRouting}Connection`; `Connection/Routing/{RouteResolver,SessionConsistencyWindow,ReplicaLagSampler}`; `Criteria::forcePrimary()` / `Criteria::allowReplica()` / `Criteria::getRoutingHint()`; `connection.decorators` / `routing.*` config keys; `ReplicaLagExceededException`; `ConnectionDecoratorException`; `TelemetryInterface` no-op stub.
- Changed: `PdoConnection` is `final`, ~200 LOC; `ConnectionWrapper` reduced to ~150 LOC; `StatementWrapper` reduced to ~100 LOC; `LoggingConnection` does not call `debug_backtrace` (the shim still does).
- Deprecated: `ConnectionWrapper`, `StatementWrapper`, `ProfilerConnectionWrapper`, `ProfilerStatementWrapper`, `ConnectionFactory::$useProfilerConnection`.
- Fixed: umbrella §6.2 — debug_backtrace on the log hot path; unbounded prepared-statement cache (DoS vector); `defined()`/`constant()` PDO-attribute lookups; residual HHVM strict-issue overrides.
- Security: bounded LRU prevents long-running-worker memory exhaustion; routing-decision logging uses structured key=value (no SQL leakage); replica-lag probe runs out-of-band (no timing oracle).

- [ ] **Step 5: Commit**

```bash
git add docs/MIGRATION-FROM-PRE-AI.md docs/UPGRADE-3.0.md docs/CONNECTION-DECORATORS.md docs/BACKWARD_COMPATIBILITY.md CHANGELOG.md
git commit -m "docs: connection collapse + replica routing migration cookbook + BC commitments"
```

---

## Round 2 Review Checkpoint (end-phase, after E.5 + E.6 + E.7 + E.8)

**Trigger:** all tasks E.1–E.8 landed; quality gates green; ready to merge.
**Reviewers (per umbrella §4.13.3 end-phase HIGH-RISK):** **all 5 standing** + **SQL & concurrency specialist** + **Security reviewer**.
**Lens:**

- **Architecture:** is the decorator chain composition order correct AS DOCUMENTED in §2.1, and does the `ConnectionFactory` enforce it? Does any responsibility leak across decorator boundaries (e.g., does `LoggingConnection` peek into `TransactionalConnection`'s state)? Is `ConnectionDecoratorInterface` the right shape — too narrow, too wide?
- **BC:** is Tier 1 truly intact (`ConnectionInterface` and `StatementInterface` signatures unchanged)? Are the three new `Criteria` methods truly additive? Do the BC shims preserve every legacy method signature (signature-diff snapshot for `ConnectionWrapper` should show only PHPDoc changes)? Is the deprecation message text actionable for a consumer reading their CI logs?
- **Quality:** baselines monotonic; coverage delta ≥ 0; mutation MSI ≥ **75** on touched files (umbrella §4.4 — Phase E threshold, NOT 65); deptrac green; chaos tests all GREEN; PBT seeds documented.
- **Performance:** umbrella §4.10 query overhead target ≤2× raw PDO MET on the bench in `E-bench.md`. No regression vs Phase D end. Bench worked example: 100k-row hydrate path, decorator chain on, vs raw PDO — table in `E-bench.md`.
- **Ambition:** every umbrella §6.4 capability item delivered: replica routing primary/replica hints ✓, session consistency ✓, lag awareness ✓, primary fallback ✓. Every umbrella §6.2 bug fix landed: debug_backtrace removed ✓, bounded LRU ✓, defined()/constant() replaced ✓, HHVM overrides removed ✓. Every umbrella §2.1 decorator class shipped: 5/5.
- **SQL & concurrency specialist:** are the nested-tx invariants correct under MySQL 8 SAVEPOINT semantics, PG 14 SAVEPOINT semantics, and SQLite (which does not support nested transactions natively — does `TransactionalConnection` correctly emulate nesting)? Is the prepared-statement cache coherent under concurrent fiber-style access (Phase J's concern, but the cache must not introduce race-conditions ON its own)? Is the deadlock retry boundedness chaos test airtight? Is the routing-decision classifier (`SELECT` → read, etc.) correct against the documented edge cases (`WITH RECURSIVE` containing `DELETE`, e.g.)?
- **Security reviewer:** the LRU cap prevents one DoS vector — are there others? (Memory amplification via long SQL strings in cache keys: is there a hard cap on key-length? — should be added if not.) Routing-decision rationale logging is structured: does any branch leak SQL? (Should not — only metadata.) Replica-lag probe SQL is parameter-free — confirmed? (Yes — adapter-supplied static SQL.) Is `Criteria::forcePrimary()` reachable from a parameter-driven path (e.g., a query with a tainted hint string would not be a vulnerability — confirm the hint is enum-like, not free-text). Is `ReplicaLagExceededException` chained from `\PDOException` cleanly (no info-disclosure)? Is `ConnectionFactory`'s parsing of `decorators` config robust against type confusion (string vs array)?

**Outputs:** `docs/reviews/E-round-2-{architecture,bc,quality,performance,ambition,sql-concurrency,security}.md` + `E-round-2-summary.md`. Plus `E-bench.md`, `E-mutation.json`, `E-waivers.md`.

**Iteration budget:** 3 cycles. Maintainer-escalation triggers per umbrella §4.15.3.

---

## Group E.9: Phase E DoD verification (Tasks E.9.1–E.9.2)

**Verification:** the 15-box Definition of Done from umbrella §4.9 passes.

---

### Task E.9.1: Full quality stack

**Files:**
- Modify: `docs/reviews/E-bench.md`
- Modify: `docs/PHASE-E-SUMMARY.md`

- [ ] **Step 1: Run the full matrix**

```
composer test                    # all 16 cells green
composer testsuite               # static + style + tests
composer stan                    # baseline monotonic
composer psalm                   # baseline monotonic
composer cs-check                # clean
vendor/bin/deptrac analyse       # 0 violations
vendor/bin/infection             # MSI ≥75 on touched files (umbrella §4.4 Phase E)
php tools/check-baseline-monotonic.php  # green
php tools/regen-golden.php       # idempotent (no diff)
diff -ru tests/snapshots/{Tier1-snapshots-pre,post}/  # only additive Criteria entries
```

- [ ] **Step 2: Capture LOC drawdown**

Pre-Phase-E: `Connection/*.php` ~2,712 LOC.
Post-Phase-E: bench. Target: total Connection LOC roughly the same (decorators add up; but the bare `PdoConnection` shrinks ~50 LOC and the shims shrink ~700 LOC; the decorators add ~600 LOC). Net is flat ±10%. The win is structural, not LOC.

- [ ] **Step 3: Capture bench results in `E-bench.md`**

| Path | Pre-E (Phase D end) | Post-E |
|---|---|---|
| Query overhead per call vs raw PDO | TBD (Phase A baseline) | ≤ 2× raw PDO (umbrella §4.10) |
| Statement cache hit rate (typical workload) | TBD (Phase A baseline) | ≥ 90% |
| 100k-row hydrate (book) | Phase D end TBD | ≤ ±5% of pre |
| Memory peak per long-running worker (10k unique prepares) | unbounded | bounded at LRU cap |

- [ ] **Step 4: Commit**

```bash
git add docs/reviews/E-bench.md
git commit -m "perf(runtime/connection): Phase E performance characterization"
```

---

### Task E.9.2: Phase E summary + handoff to Phase F

**Files:**
- Create: `docs/PHASE-E-SUMMARY.md`

- [ ] **Step 1: Author the summary**

Mirror Phase D's structure. Sections: status, what shipped, deferred (if any), quality gates table, notable surprises, review process recap, next: Phase F.

Forward-references for Phase F: the `PreparedStatementKey` SPI (umbrella §2.2 — the cache key shape) — Phase F adopts; Phase E delivers the `CachingConnection::buildCacheKey()` static as the shape source. The umbrella §2.2 promise that "Phase F drafts the SPI signature; Phase E adopts it" is INVERTED in Phase E because we needed the cache key shape internally first; documented in the summary.

- [ ] **Step 2: Commit**

```bash
git add docs/PHASE-E-SUMMARY.md
git commit -m "docs(phase-e): summary + handoff to Phase F"
```

---

## Round 3 Review Checkpoint (post-merge canary, 7 days after merge to integration)

**Trigger:** Phase E merged to integration branch; ecosystem-advisory CI has run for 7 days; any consumer-smoke regressions surface.
**Reviewers (per umbrella §4.13.3 HIGH-RISK Round 3):** Performance + Quality + BC reviewers (3 lenses).
**Lens:**

- **Performance:** has any consumer reported a real-workload regression > 5% on prepared-statement cache (lower hit rate due to LRU eviction in workloads with > 256 unique statements)? If so: bump default capacity, or expose tuning guidance. Has any consumer reported lock contention from the `ReplicaRoutingConnection`'s `lastDecision` field (sub-fiber-safety)?
- **Quality:** any new GitHub issues related to decorator-chain misorders, BC-shim deprecation noise (one-per-call vs one-per-construction)? Any chaos-test scenarios surfaced post-merge that the Phase E plan missed?
- **BC:** any third-party packages broken by the new chain? `instanceof ConnectionWrapper` was flagged in the migration cookbook but not all consumers read it — what's the actual impact? Should the cookbook be promoted to a CHANGELOG breaking-change note?

**Outputs:** `docs/reviews/E-round-3-{performance,quality,bc}.md` + `E-round-3-summary.md`.

**Severity-1 trigger:** any reported data-loss / stale-read / silent-corruption issue traced to Phase E. If triggered: rollback procedure per umbrella §7.1.

**Outcome decision:** sign-off OR follow-up-issue list filed for a Phase E.1 patch release.

---

## Definition of Done (umbrella §4.9 — 15 boxes)

- [ ] All test matrix cells green: PHP {8.3, 8.4} × DB {agnostic, mysql, pgsql, sqlite} × Symfony {7.2, 7.latest} = 16 cells.
- [ ] `phpstan-baseline.neon` ≤ 403 lines (Phase D end; Phase E should drop further as the `ConnectionWrapper` shim removes phpstan-suppressed cases).
- [ ] `psalm-baseline.xml` ≤ 1621 lines (Phase D end).
- [ ] Coverage delta ≥ 0%; floor 70% Runtime / 60% Generator / 70% generated bookstore output (Phase A).
- [ ] **Mutation MSI ≥ 75** on touched files (umbrella §4.4 — Phase E threshold; HIGHER than Phase D's 65). Touched files: every `Connection/Internal/*`, `Connection/Routing/*`, `Connection/PdoConnection.php`, `Connection/ConnectionWrapper.php` shim, `Connection/StatementWrapper.php` shim, `Connection/ConnectionFactory.php`, `Criteria.php` routing-hint additions.
- [ ] Deptrac green; 0 violations against 233 baseline. New `Runtime/Internal/Connection/*` rules verified.
- [ ] **Performance benchmarks within umbrella §4.10 targets**: query overhead per call ≤ 2× raw PDO; statement-cache hit rate ≥ 90% on typical-workload bench. No regression > 5% vs Phase D end. Bench in `E-bench.md`.
- [ ] `CHANGELOG.md` updated (per task E.8.3 list).
- [ ] Deprecation message audit clean (allowlist accepts new shim deprecations; no surprise leakage).
- [ ] Generated-code lint parity green (regenerated bookstore for `BookQuery::forcePrimary`/`allowReplica` short-form).
- [ ] Golden-file diff reviewed: only additive `BookQuery` short-form methods; no other generated-code changes.
- [ ] Phase plan updated with retrospective notes (this file, appended after Round 3).
- [ ] All review-round reports committed under `docs/reviews/E-round-{1,2,3}-*.md`; consolidated `E-summary.md`.
- [ ] **Surgical-test battery executed (umbrella §4.14)**: PBT (nested-tx invariants + LRU invariants); mutation report; chaos suite (PDO drop mid-tx, evict during prepare, deadlock retry boundedness, replica failover) all 4 GREEN; perf benchmarks vs §4.10; differential test against Phase D end (no Tier 1 / Tier 2 surface drift); consumer-smoke run; ecosystem advisory CI run.
- [ ] All MUST-FIX closed; SHOULD-FIX closed or waived in `E-waivers.md`; iteration cycles within budget (3 per round); Round 3 post-merge canary signed off.

---

## Risk Register (Phase E-specific)

1. **Decorator chain composition order matters.** Per umbrella §2.1: `LoggingConnection` MUST be inside `TransactionalConnection` so logs reflect the actual SQL the inner DB sees, not the wrapper's nested-tx counting. `CachingConnection` MUST be inside `LoggingConnection` so cache hit/miss is observable. `ProfilingConnection` outermost so it sees whole-call latency. `ReplicaRoutingConnection` outermost-of-all when active. **Mitigation:** `ConnectionFactory` enforces order via a canonical sequence + an explicit misorder error (Task E.7.1 step 3). Round 1 architecture reviewer's lens explicitly checks this. The factory is the single source of truth; consumers cannot construct an out-of-order chain by accident.

2. **LRU cache eviction during a prepare-then-execute cycle is a concurrency hazard.** A prepared statement evicted mid-cycle MUST keep its inner `\PDOStatement` alive until the consumer is done. **Mitigation:** PHP refcounting handles this naturally (the consumer holds a reference; eviction just removes the cache entry, not the statement object). Property-based test (E.4.2) AND chaos test (E.4.5) both formally verify the held-reference invariant. The class-doc on `PreparedStatementLruCache` explicitly documents the invariant.

3. **Replica routing introduces consistency hazards.** Read-after-write on a replica returns stale data without the session-consistency window. **Mitigation:** `SessionConsistencyWindow` (E.6.2) auto-routes reads to primary for `windowSeconds` (default 5) after any write in the session. This is the umbrella §2.1 routing intelligence requirement; the default is conservative enough that the typical write-then-read sequence stays consistent. A consumer who explicitly opts out via `allowReplica()` immediately after a write accepts the stale-read risk; the migration cookbook flags this prominently.

4. **`debug_backtrace` removal might miss caller-source info that consumer logs depend on.** A consumer reading their PSR-3 logs might rely on the calling-method label that `ConnectionWrapper::log()` produces today. **Mitigation:** the legacy shim preserves the `log(string)` signature with `debug_backtrace` (deprecated path). The new `LoggingConnection::log(string, string)` requires the caller to pass the method name explicitly. Migration cookbook (E.3.3) walks consumers through the swap. Round 2 BC reviewer explicitly checks the signature-diff for `ConnectionWrapper::log` is unchanged.

5. **`instanceof ConnectionWrapper` checks silently miss the new chain.** Any consumer code that does `if ($conn instanceof ConnectionWrapper)` will be `false` for the new chain (which is `ProfilingConnection` or similar). **Mitigation:** flagged in the migration cookbook (Task E.8.3 step 1.4). Recommended replacement: `instanceof ConnectionDecoratorInterface` plus `getInner()` walk to the bare `PdoConnection`. The Round 3 post-merge canary's BC lens watches for downstream-package issues on this.

6. **Replica-lag check timing oracle.** A misimplemented lag probe could leak which replica is freshest, allowing an attacker to time queries against the lag schedule. **Mitigation:** `ReplicaLagSampler` runs OUT-OF-BAND on a clock-driven cadence — never request-driven. Adapter probe SQL is static (no user input). The `RoutingDecision::rationale` logs `lag=0.2s, threshold=2.0s` granularly but at 0.1s precision (not microsecond) — coarse enough that timing differences from real network jitter dominate any oracle signal. Security reviewer's Round 1 + Round 2 lenses both check this. Documented in `docs/CONNECTION-DECORATORS.md` security notes.

7. **Cache-key collision via SHA-256 + ksort'd `$driverOptions`.** If two semantically-distinct sets of `$driverOptions` happen to have the same array shape post-`ksort`, they collide on key. **Mitigation:** `ksort` only re-orders; the serialized representation captures all keys + values. Collision via SHA-256 is cryptographically infeasible; collision via `serialize` is not feasible for any realistic input. Phase A bug fix #4 (driver options in cache key) is preserved; Phase E unit tests (E.4.3 step 4) explicitly verify both directions of the round-trip. Security reviewer's Round 2 lens checks key-length cap on the SQL prefix.

8. **Operation classification (`SELECT` → read, `WITH RECURSIVE ... DELETE` → write) is a heuristic.** Pre-Phase-F, the routing decision uses a leading-keyword scan + a bounded `DELETE`/`UPDATE`/`INSERT` regex on the prefix. False-negative risk: a `WITH ... CALL my_proc()` where `my_proc` writes is classified as read. **Mitigation:** documented as transitional in `docs/CONNECTION-DECORATORS.md`; consumers in doubt set `forcePrimary()` explicitly. Phase F's tokenizer-based parser will replace the heuristic with token-precision; the umbrella's E↔F coupling (§5.1) acknowledges the substitution. The Phase F plan inherits this risk.

9. **Mutation MSI threshold ≥ 75 (Phase E) higher than Phase D's 65.** Achieving MSI 75 on the new decorator code is feasible (decorators are pure-logic / value-object-driven), but `ReplicaLagSampler`'s adapter-probe path is harder to mutate-cover (database SQL is opaque to mutation testing). **Mitigation:** focus mutation coverage on `RouteResolver`, `SessionConsistencyWindow`, `PreparedStatementLruCache`, `RoutingDecision` rationale-building — pure logic where mutation testing has high signal. The probe path's coverage relies on integration tests; mutation MSI on the file is allowed to dip below 75 with documented waiver.

10. **BC shim re-entrancy.** A consumer subclass of `ConnectionWrapper` that overrides `prepare()` and calls `parent::prepare()` will hit the shim, which calls into the new `CachingConnection`'s `prepare`. The override fires once; `CachingConnection`'s decoration fires once. Should not double-decorate. **Mitigation:** unit test `ConnectionWrapperBcShimTest` (Task E.8.1 step 4) explicitly covers a subclass override scenario.

11. **`$useProfilerConnection` static remains an architectural smell.** Phase E does not eliminate the `ConnectionFactory::$useProfilerConnection` static (out-of-scope below). It now injects `'profiling'` into the decorators list. **Mitigation:** documented as deprecated in favor of explicit per-datasource `decorators` config; tagged for elimination in a future cycle. Round 2 ambition reviewer flags it as a known waiver.

12. **`ConnectionWrapper` shim deprecation noise on existing test suites.** Every test that constructs `new ConnectionWrapper($pdo)` will trigger the deprecation. The Phase A test suite has many such constructions. **Mitigation:** `tests/deprecations.allowlist.json` baseline regenerated at Task E.8.1 step 5; the per-construction deprecation lands in the allowlist exactly once per category. Downstream packages MUST allowlist on their side; the migration cookbook flags this loudly.

13. **`final` on `PdoConnection` could break a downstream subclass.** Phase A documented `PdoConnection` as Tier 3 (internal); some downstream package might have subclassed it anyway. **Mitigation:** Round 2 BC reviewer checks ecosystem advisory CI for `extends PdoConnection` patterns. If found in a real package, the `final` is rolled back to Phase A's open class — the umbrella's "Tier 3 free reign within SPI" allows the rollback because the subclassing was always undeclared. Ecosystem-advisory CI is the early-warning system.

---

## Out of Scope (explicit)

Phase E explicitly does NOT do:

- **Async / fiber-safe connection use** — Phase J. The current `Connection/*` is single-fiber-per-connection; `Routing/SessionConsistencyWindow` is per-instance. Worker-mode hooks (`onWorkerStart`, fiber-safe tx context binding) come in J.
- **Real `TelemetryInterface` adapters** — Phase I. Phase E ships only the no-op stub. The `LoggingConnection` and `ProfilingConnection` consume the stub interface; Phase I will replace with real OpenTelemetry / Prometheus exporters.
- **Eliminating `ConnectionFactory::$useProfilerConnection` static** — architectural lift; the static is narrowed in scope by Phase E (now injects `'profiling'` into the decorators list) but kept on the BC runway. Tagged as a future-cycle cleanup; documented in Risk #11.
- **Async result fetching / streaming `Generator` formatter** — Phase G (PHP 8.4 substrate; lazy objects + Generators).
- **Property-tracked dirty-checking via PHP 8.4 hooks** — Phase G.
- **Per-table typed Criterion classes** — Phase F stretch goal (`<table generate-typed-criterion="true">`).
- **`PreparedStatementKey` SPI shape published as Phase F's responsibility** — actually inverted. Phase E delivers `CachingConnection::buildCacheKey()` as the shape source; Phase F adopts. Documented as a deviation from umbrella §2.2 sequence in the Phase E summary.
- **Replacing the leading-keyword operation classifier with a tokenizer** — Phase F. Phase E ships the heuristic; Phase F replaces with `Compiler/NameResolver`-grade tokenization (umbrella §2.2). Risk #8 documents the transition.
- **Adapter-side replica-lag probe SQL standardization** — Phase E ships per-adapter SQL via `AdapterInterface::getReplicaLagProbeSql()`. Standardizing on a normalized lag value + handling cluster topologies (e.g., MySQL Group Replication multi-source) is out-of-scope for Phase E; lag values are best-effort per replica.
- **Multi-tenancy / sharding** — addon-package-only, post-4.0 per umbrella §1.3.
- **Migration tooling overhaul** — Phase H.
- **PHP 8.4 `#[\Deprecated]` attribute migration** — Phase G; PhpDoc `@deprecated` + `trigger_deprecation` is the 3.x convention per umbrella §3.4.

---

## Self-Review Checklist (writing-plans skill)

**Spec coverage check:**

| Umbrella spec promise (Phase E from §2.1 + §5 + §6.2 + §6.4) | Task |
|---|---|
| `ConnectionDecoratorInterface` (Tier 2) published | E.1.1 |
| `AbstractConnectionDecorator` base | E.1.2 |
| `PdoConnection` final ~200 LOC | E.1.3 |
| ConnectionInterface contract bedrock | E.1.4 |
| `TransactionalConnection` decorator | E.2.1, E.2.2, E.2.3 |
| `LoggingConnection` decorator (no `debug_backtrace`) | E.3.1, E.3.2, E.3.3 |
| `CachingConnection` + `PreparedStatementLruCache` (bounded) | E.4.1, E.4.2, E.4.3, E.4.4, E.4.5 |
| `ProfilingConnection` decorator | E.5.1, E.5.2, E.5.3 |
| `ReplicaRoutingConnection` (NEW capability — umbrella §6.4) | E.6.1, E.6.2, E.6.3, E.6.4, E.6.5 |
| `Criteria::forcePrimary` / `allowReplica` / `getRoutingHint` (Tier 1 additive) | E.6.4 |
| `ConnectionFactory` composes from config | E.7.1 |
| `PropelConfiguration` tree extension | E.7.2 |
| Full-chain ConnectionInterface contract | E.7.3 |
| BC shim: `ConnectionWrapper`, `StatementWrapper`, `Profiler*Wrapper` | E.8.1, E.8.2 |
| Migration cookbook | E.8.3 |
| Phase E DoD verification | E.9.1, E.9.2 |
| Bug fix: `debug_backtrace` on log hot path (umbrella §6.2) | E.3.1 |
| Bug fix: unbounded prepared-statement cache (DoS) (umbrella §6.2) | E.4.1, E.4.3, E.4.4 |
| Bug fix: `defined()`/`constant()` PDO attribute lookups (umbrella §6.2) | E.1.3 |
| Bug fix: residual HHVM strict-issue overrides (umbrella §6.2) | E.1.3 |
| Chaos tests (umbrella §4.12 — Phase E gate) | E.4.5, E.6.5; PDO-drop-mid-tx + deadlock-retry chaos in E.2/E.4 |
| PBT (umbrella §4.11) | E.2.2, E.4.2 |
| Mutation MSI ≥ 75 (umbrella §4.4 Phase E threshold) | DoD #5 |
| Performance bench ≤ 2× raw PDO (umbrella §4.10) | E.9.1 |
| HIGH-RISK 3-round review cadence (umbrella §4.13.3) | Round 1 / Round 2 / Round 3 checkpoints above |
| SQL & concurrency specialist + Security reviewer (umbrella §4.13.2) | Round 1, Round 2 |

**Type-consistency check:** All file paths verified against `src/Propel/Runtime/Connection/*` (15 files, 2,712 LOC pre-phase), `src/Propel/Runtime/ActiveQuery/Criteria.php`, `src/Propel/Common/Config/PropelConfiguration.php`, `src/Propel/Generator/Builder/Om/QueryBuilder.php`, `tests/snapshots/`, `tests/Fixtures/bookstore/build/golden/`. LOC counts verified against `wc -l` (`ConnectionWrapper.php` 745 LOC, `PdoConnection.php` 237 LOC, `StatementWrapper.php` 462 LOC, `ProfilerConnectionWrapper.php` 135 LOC, `ProfilerStatementWrapper.php` 75 LOC). Specific line citations: `ConnectionWrapper.php:89` (cachedPreparedStatements), `:407–414` (cache hit/miss), `:583` (clearStatementCache), `:712–731` (log + debug_backtrace at `:714`), `:358, :364, :370` (defined/constant); `PdoConnection.php:79, :105, :108` (defined/constant), `:184–195` (HHVM prepare override), `:197–209` (HHVM quote override).

**Placeholder scan:** No TBDs in tasks; no "TODO" markers; every task has a primary file, a concrete change, a verify command, and a commit-message stub. The single discoverability decision (LRU default capacity 256 vs 512) is documented as a runtime-resolved choice gated on the bench in Task E.4.4.

---

## Execution Handoff

Phase E is HIGH-RISK 3-round per umbrella §4.13.3. Recommended execution shape:

**1. Subagent-Driven (recommended for E.1, E.2, E.3, E.5, E.7)** — fresh subagent per task; well-scoped decorator-port work, value-object construction, factory composition. Each task is 1–3 commits and clean-context per task. Subagents benefit from zero stale state.

**2. Inline Execution (recommended for E.4 + E.6)** — `CachingConnection` + LRU + chaos test (E.4) is a chain of related work where the property-based test informs the chaos test. `ReplicaRoutingConnection` (E.6) is 5 sub-tasks (resolver, window, sampler, decorator, chaos) where holding the decision-tree in working memory across sub-tasks pays off. The same session benefits from continuity.

**3. Mixed (recommended for E.8 + E.9)** — inline for the BC shim collapse (E.8.1, E.8.2 are tightly coupled); subagent for the migration cookbook (E.8.3) and the DoD audit (E.9).

Always: per-task `composer test:agnostic` + `composer stan` (baseline monotonic) + chaos / PBT subdirectory tests where applicable. The chaos suite (`tests/ChaosTests/`) is umbrella §4.12 Phase E exit gate — every test must be GREEN before Round 2 review.

Per-task signature-diff check on `Connection/ConnectionInterface.php` and `Connection/StatementInterface.php`: MUST be empty (Tier 1 frozen). On `ActiveQuery/Criteria.php`: only the three additive method signatures appear in the diff.

Round 1 mid-phase trigger: `git log --oneline tests/Propel/Tests/Runtime/Connection/Internal/Caching*` shows tasks E.1–E.4 landed.
Round 2 end-phase trigger: `git log --oneline docs/PHASE-E-SUMMARY.md` shows E.9.2 committed.
Round 3 post-merge canary trigger: `git log --oneline integration` shows Phase E merged 7 days ago + ecosystem-advisory CI has run.

---

## Phase E Retrospective

(To be appended after Round 3 closes. Captures: actual LOC drawdown vs targets, chain-composition surprises, replica-routing edge cases discovered post-merge, performance delta vs umbrella §4.10 targets, what should be in Phase E.1 patch / Phase F handoff, ecosystem-advisory CI signal on `instanceof ConnectionWrapper` and `extends PdoConnection` patterns.)
