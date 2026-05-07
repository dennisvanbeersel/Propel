<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Telemetry;

use Throwable;

/**
 * Tier 2 SPI per umbrella spec §2.5.
 *
 * Phase E shipped a 2-method stub (span lifecycle only). Phase I §I.1.2
 * finalizes the contract per the umbrella's promised shape:
 *
 * - `recordPreparedCacheHit` — emitted by `CachingConnection` on every
 *   `prepare()` call.
 * - `recordTransactionDepth` — emitted by `TransactionalConnection` on every
 *   begin/commit/rollback.
 * - `recordHydrationDuration` — emitted by formatters during ActiveRecord
 *   row→object hydration (first concrete callsite arrives in Phase G's
 *   streaming Generator-formatter; method ships in Phase I so adapters can
 *   register the histogram up-front).
 * - `recordReplicaRoutingDecision` — emitted by `ReplicaRoutingConnection`
 *   per route decision.
 * - `recordIdentityGeneration` — emitted by id-generation strategies
 *   (PG/MySQL `lastInsertId`, sequence-based, identity columns).
 *
 * Decorators ({@see \Propel\Runtime\Connection\Internal\LoggingConnection},
 * {@see \Propel\Runtime\Connection\Internal\ProfilingConnection},
 * {@see \Propel\Runtime\Connection\Internal\CachingConnection},
 * {@see \Propel\Runtime\Connection\Internal\TransactionalConnection},
 * {@see \Propel\Runtime\Connection\Internal\ReplicaRoutingConnection})
 * accept an optional `TelemetryInterface` parameter; default is `NoOpTelemetry`.
 *
 * Phase I ships two real adapters alongside this interface:
 * - {@see Otel\OtelTelemetry} — OpenTelemetry SDK (`open-telemetry/sdk` suggest).
 * - {@see Prometheus\PrometheusTelemetry} — Prometheus client
 *   (`promphp/prometheus_client_php` suggest).
 *
 * @api Tier 2 — deprecation runway required for any signature change.
 *
 * @since 3.0.0
 */
interface TelemetryInterface
{
    /**
     * Open a span for the duration of a single query (`prepare`, `exec`,
     * or `query` call). The returned object is opaque to the decorator;
     * adapters carry their own metadata (W3C trace-context for OTEL,
     * `microtime(true)` start for Prometheus).
     *
     * @param string $sql SQL text (or method label like 'beginTransaction').
     * @param string $callingMethod The decorated method that opened the span.
     *
     * @return \Propel\Runtime\Telemetry\SpanInterface Opaque span handle. Pass back to `endQuerySpan`.
     */
    public function startQuerySpan(string $sql, string $callingMethod): SpanInterface;

    /**
     * Close a span previously opened by {@see self::startQuerySpan()}.
     *
     * @param object $span Handle returned by `startQuerySpan`. Accepted as `object` for BC with the
     *                     Phase E stub signature; concrete implementations expect a `SpanInterface`.
     * @param float $durationSeconds Duration in fractional seconds.
     * @param \Throwable|null $error Set when the operation threw.
     *
     * @return void
     */
    public function endQuerySpan(object $span, float $durationSeconds, ?Throwable $error = null): void;

    /**
     * Record a prepared-statement cache hit or miss.
     *
     * Adapters MUST emit two distinct counters (`hits` + `misses`) rather
     * than a single signed counter so consumers can compute hit-rate
     * directly from the metric backend.
     *
     * @param bool $hit True when the cache served a stored statement; false on miss.
     *
     * @return void
     */
    public function recordPreparedCacheHit(bool $hit): void;

    /**
     * Record the current nested-transaction depth.
     *
     * Emitted on every begin/commit/rollback by `TransactionalConnection`.
     * Adapters typically expose this as a gauge or up-down-counter.
     *
     * @param int $depth Current nesting depth (0 = no tx, 1 = outermost, ...).
     *
     * @return void
     */
    public function recordTransactionDepth(int $depth): void;

    /**
     * Record the duration of an ActiveRecord row→object hydration cycle.
     *
     * Emitted by formatters; adapters should label by `$class` so per-entity
     * latency is visible. Microsecond resolution because hydration is a
     * hot path measured in tens-of-µs.
     *
     * @param string $class Fully-qualified class name being hydrated.
     * @param float $microseconds Duration in microseconds.
     *
     * @return void
     */
    public function recordHydrationDuration(string $class, float $microseconds): void;

    /**
     * Record a replica-routing decision.
     *
     * Emitted by `ReplicaRoutingConnection` once per query route. Allowed
     * `$decision` values: `'primary'`, `'replica'`. The `$reason` is a
     * short identifier (`'forced'`, `'allowed'`, `'session_consistency'`,
     * `'replica_lag'`, `'replica_failure'`, `'no_replicas'`).
     *
     * @param string $decision Either `'primary'` or `'replica'`.
     * @param string $reason Short reason identifier (see above).
     *
     * @return void
     */
    public function recordReplicaRoutingDecision(string $decision, string $reason): void;

    /**
     * Record the duration of an identity-generation strategy call.
     *
     * Strategies: `'native'` (MySQL AUTO_INCREMENT lastInsertId), `'sequence'`
     * (PG sequence nextval), `'identity'` (PG IDENTITY column), `'idoffset'`
     * (custom offset-based). Microsecond resolution.
     *
     * @param string $strategy Strategy identifier.
     * @param float $microseconds Duration in microseconds.
     *
     * @return void
     */
    public function recordIdentityGeneration(string $strategy, float $microseconds): void;
}
