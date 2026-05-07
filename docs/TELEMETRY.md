# Telemetry — Observability for Propel

Phase I (umbrella spec §2.5 + §6.4) ships a `TelemetryInterface` SPI plus two
ready-made adapters: OpenTelemetry and Prometheus. By default Propel installs
the no-op adapter — production deployments install one or both adapters
explicitly via Composer `suggest`.

## Quick reference

| Adapter | Composer package | Class |
|---|---|---|
| **No-op** (default) | bundled | `Propel\Runtime\Telemetry\NoOpTelemetry` |
| **OpenTelemetry** | `open-telemetry/sdk` | `Propel\Runtime\Telemetry\Otel\OtelTelemetry` |
| **Prometheus** | `promphp/prometheus_client_php` | `Propel\Runtime\Telemetry\Prometheus\PrometheusTelemetry` |
| **Composite** (multiplex) | bundled | `Propel\Runtime\Telemetry\CompositeTelemetry` |

## Installing OpenTelemetry

```bash
composer require open-telemetry/sdk
# Plus an exporter of your choice, e.g.:
composer require open-telemetry/exporter-otlp
```

```php
use OpenTelemetry\SDK\Metrics\MeterProviderBuilder;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Propel\Runtime\Telemetry\Otel\OtelTelemetry;

// Configure trace + meter providers per your OpenTelemetry deployment.
$tracerProvider = new TracerProvider($yourBatchSpanProcessor);
$meterProvider = (new MeterProviderBuilder())
    ->addReader($yourMetricReader)
    ->build();

$telemetry = new OtelTelemetry($tracerProvider, $meterProvider);
```

The adapter emits W3C trace-context spans named `propel.{operation}` with
attributes `db.system='propel'`, `db.statement`, `db.operation`. On error,
spans set `StatusCode::ERROR` and call `recordException()`.

Metrics emitted:

- `propel.prepared_statement_cache.hits` / `propel.prepared_statement_cache.misses` (counters)
- `propel.transactions.depth` (up-down-counter; emits delta vs last call)
- `propel.hydration.duration` (histogram, ms; class label)
- `propel.replica.routing` (counter; decision + reason labels)
- `propel.identity.generation.duration` (histogram, ms; strategy label)

## Installing Prometheus

```bash
composer require promphp/prometheus_client_php
```

```php
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;        // or InMemory, APC, APCng
use Propel\Runtime\Telemetry\Prometheus\PrometheusTelemetry;

$registry = new CollectorRegistry(new Redis(['host' => 'redis', 'port' => 6379]));
$telemetry = new PrometheusTelemetry($registry);
```

Metrics emitted (Prometheus snake_case + `_total` convention):

- `propel_query_duration_seconds` (histogram, label: `method`)
- `propel_prepared_statement_cache_hits_total` / `..._misses_total` (counters)
- `propel_transaction_depth` (gauge)
- `propel_hydration_duration_seconds` (histogram, label: `class`)
- `propel_replica_routing_total` (counter; labels: `decision`, `reason`)
- `propel_identity_generation_duration_seconds` (histogram, label: `strategy`)

Default histogram buckets: `0.0001, 0.001, 0.01, 0.1, 1.0, 10.0` (seconds).
Pass a second argument to `PrometheusTelemetry::__construct()` to customise.

## Wiring telemetry into the connection chain

Each Phase E connection decorator that emits telemetry data accepts an optional
`TelemetryInterface`:

```php
use Propel\Runtime\Connection\Internal\CachingConnection;
use Propel\Runtime\Connection\Internal\LoggingConnection;
use Propel\Runtime\Connection\Internal\ProfilingConnection;
use Propel\Runtime\Connection\Internal\ReplicaRoutingConnection;
use Propel\Runtime\Connection\Internal\TransactionalConnection;
use Propel\Runtime\Connection\PdoConnection;

$pdo = new PdoConnection('mysql:host=localhost;dbname=app', 'user', 'pass');
$tx = new TransactionalConnection($pdo, $telemetry);
$logging = new LoggingConnection($tx, $logger, $telemetry);
$caching = new CachingConnection($logging, null, $telemetry);
$profiling = new ProfilingConnection($caching, $telemetry);
```

All decorators default to `NoOpTelemetry` when the parameter is omitted, so
upgrading is purely additive.

`ConnectionFactory` will pick up a `TelemetryInterface` from the runtime
service container (PSR-11) when present.

## Multiple adapters at once

Use `CompositeTelemetry` to fan out to several adapters simultaneously:

```php
use Propel\Runtime\Telemetry\CompositeTelemetry;

$telemetry = new CompositeTelemetry($otelTelemetry, $prometheusTelemetry);
```

Spans returned from `startQuerySpan()` are composite handles carrying child
spans from each adapter; close-order is LIFO so adapter ordering matches
parent-then-child semantics.

## Migrating from pre-3.0 ProfilerConnectionWrapper

In pre-rewrite Propel, `ProfilerConnectionWrapper` exposed `getProfiler()`
with internal counters (`getQueryCount()`, `getBucketCounts()`). Phase E
removed this in favour of `ProfilingConnection` + `TelemetryInterface`.

| Pre-3.0 (deprecated) | Phase I (current) |
|---|---|
| `ProfilerConnectionWrapper::getQueryCount()` | `propel_query_duration_seconds_count` Prometheus metric |
| `ProfilerConnectionWrapper::getBucketCounts()` | `propel_query_duration_seconds_bucket` Prometheus histogram (or OTEL equivalent) |
| `ConnectionWrapper::cachedPreparedStatements` (LRU stats) | `propel_prepared_statement_cache_{hits,misses}_total` |
| `ConnectionWrapper::nestedTransactionCount` | `propel_transaction_depth` |

Production observability should consume the metrics adapter rather than reading
the `ProfilingConnection`'s internal counters directly — those remain available
for ad-hoc debugging but are not part of the Tier 2 SPI.

## Performance impact

`NoOpTelemetry` is the runtime default. Method dispatch through the no-op
implementation costs less than 100 ns per call (measured on PHP 8.4 with JIT
in the umbrella's `tests/Benchmark/` harness, well within umbrella §4.10's
"≤2× raw PDO" budget).

The OpenTelemetry adapter adds the SDK's per-span overhead (typically 5–15 µs
including span attributes + metric record). Prometheus adapter is similar
when paired with the InMemory storage backend, higher (1–5 ms) with Redis
storage due to network round-trip.

## Custom adapters

Implement `TelemetryInterface` directly. Tier 2 SPI; signature changes go
through the deprecation runway per umbrella §3.5.

```php
final class MyDataDogTelemetry implements TelemetryInterface
{
    // ... 7 methods, all required.
}
```
