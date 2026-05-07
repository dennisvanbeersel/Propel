# Connection Decorators (Phase E)

> **Status:** Tier 2 SPI — frozen for the 3.x line; deprecation runway required for any signature change.
>
> **Reference:** umbrella spec §2.1 target architecture.

Phase E collapses the legacy mixed-responsibility `ConnectionWrapper` into a stack of single-responsibility decorators. Every decorator implements [`ConnectionDecoratorInterface`](../src/Propel/Runtime/Connection/ConnectionDecoratorInterface.php) and lives under `Propel\Runtime\Connection\Internal\*`.

## Canonical chain order

Inner-to-outer:

```
PdoConnection
  ← TransactionalConnection      (nested-tx accounting)
    ← LoggingConnection          (PSR-3 + telemetry hooks)
      ← CachingConnection        (bounded-LRU prepared-statement cache)
        ← (ProfilingConnection)  (optional — query histogram)
          ← (ReplicaRoutingConnection) (optional — primary/replica routing)
```

`ConnectionFactory` is the single source of truth for this order and rejects any consumer-supplied list that violates it (throws `ConnectionDecoratorException`).

### Why this order

- **Logging is INSIDE Transactional** so logs reflect the actual SQL the inner DB sees — not the wrapper's nested-tx counting (an outer-most-only commit/rollback is what gets logged, not every nested begin).
- **Caching is INSIDE Logging** so cache hit/miss is observable.
- **Profiling is OUTERMOST** so it sees whole-call latency including all decoration cost.
- **Routing wraps everything** when active so the routing decision picks the *primary* or *replica* full chain and forwards through it.

## Logging contract change (umbrella §6.2 risk #1)

The legacy `ConnectionWrapper::log(string $msg)` walked `debug_backtrace()` on every logged query to discover the calling method. The new `LoggingConnection::log(string $msg, string $callingMethod)` takes the calling method name as an explicit parameter — no per-query reflection.

### Before (deprecated)

```php
$conn = new ConnectionWrapper($pdo);
$conn->setUseDebug(true);
$conn->setLogger($logger);
// ConnectionWrapper::log() walks debug_backtrace internally.
```

### After

```php
$logging = new LoggingConnection(new PdoConnection($dsn), $logger);
$logging->setLogMethods(['exec', 'prepare']);
// Each decorated call passes its method name to log() explicitly.
```

The `ConnectionWrapper` BC shim keeps the 1-arg `log(string)` signature (with `debug_backtrace`) on the deprecation runway. Consumers reading PSR-3 logs see no difference; subclasses overriding `log()` should migrate to the explicit-parameter form.

## Bounded prepared-statement cache (umbrella §6.2 risk #4)

The legacy `ConnectionWrapper::cachedPreparedStatements` array was unbounded — a long-running CLI worker preparing millions of unique queries could exhaust memory. `CachingConnection` wraps a `PreparedStatementLruCache` with default capacity **256** entries (configurable via `connection.preparedStatementCacheCapacity`).

### Default capacity rationale

A capacity-sweep bench at `tests/Propel/Tests/Benchmarks/Connection/PreparedStatementCacheCapacitySweepTest.php` runs a Zipf(α≈1.5) workload of 10 000 prepares over 200 distinct statements. Capacity 256 produces ≥ 90% hit rate (umbrella §4.10 statement-cache target) while keeping memory footprint bounded. Smaller caches (e.g. 32) drop the hit rate substantially.

### Cache key

`CachingConnection::buildCacheKey($sql, $driverOptions)` returns:
- `$sql` if `$driverOptions === []`;
- otherwise, `$sql . "\0" . serialize(ksort'd $driverOptions)`.

`ksort` normalizes order so semantically-equivalent option arrays produce identical keys (Phase A bug-fix #4 preserved).

### Held-reference invariant

Eviction removes a cache entry but does NOT free a statement reference held by a consumer — PHP refcounting keeps the underlying `PDOStatement` alive. Formal proof: `tests/Propel/Tests/PropertyTests/Connection/PreparedStatementLruInvariantTest`.

## Profiling (umbrella §2.1 — replaces ProfilerConnectionWrapper)

`ProfilingConnection` decorates `prepare`/`exec`/`query` with `microtime`-bracketed timing and emits per-call durations to:

1. an in-memory query-count + total-duration accumulator (introspectable via `getQueryCount()` / `getTotalDurationSeconds()`);
2. a histogram of per-bucket counts (default buckets: `[0.001, 0.01, 0.1, 1.0]` seconds + `+∞` tail);
3. the injected `TelemetryInterface` (default `NoOpTelemetry`).

### Enabling

In `propel.yaml`:

```yaml
propel:
  database:
    bookstore:
      connection:
        decorators: ['transactional', 'logging', 'caching', 'profiling']
```

The legacy `ConnectionFactory::$useProfilerConnection` static keeps working: when truthy, it injects `'profiling'` into the chain unless an explicit `decorators` list is configured. The static is deprecation-tagged for removal in 4.0; use the explicit list for per-datasource control.

### Replaces

The 135-LOC `ProfilerConnectionWrapper` + 75-LOC `ProfilerStatementWrapper` collapse into one decorator. Per-statement profiling (fetch/fetchAll duration) is deferred to Phase I when telemetry adapters land.

## Telemetry stub (Phase I forward path)

Both `LoggingConnection` and `ProfilingConnection` accept a `TelemetryInterface` parameter (default `NoOpTelemetry`). Each decorated call produces:

```php
$span = $telemetry->startQuerySpan($sql, 'exec');
try {
    $result = $inner->exec($sql);
} finally {
    $telemetry->endQuerySpan($span, $duration, $maybeError);
}
```

Phase E ships only the no-op default. Phase I delivers OpenTelemetry / Prometheus adapters.

## Consumer migration

- `instanceof ConnectionWrapper` checks silently miss the new chain. Replace with `instanceof ConnectionDecoratorInterface` plus a `getInner()` walk.
- The legacy `ConnectionWrapper`/`StatementWrapper`/`ProfilerConnectionWrapper` keep working as thin BC shims for the entire 3.x line; each construction emits a single `trigger_deprecation`.
- `ConnectionFactory::create()` returns the canonical chain. Direct decorator instantiation is supported but discouraged outside test fixtures.
