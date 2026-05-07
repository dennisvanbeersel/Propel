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
 * No-op default implementation of {@see TelemetryInterface}.
 *
 * Phase E uses this everywhere a `TelemetryInterface` is optional. Phase I
 * keeps it as the default for the zero-overhead path and ships real adapters
 * (`OtelTelemetry`, `PrometheusTelemetry`) alongside.
 *
 * Phase I §I.1.1 promotes `startQuerySpan()`'s return type from `object` to
 * `SpanInterface`. The narrowing is BC-safe — `SpanInterface IS-A object` —
 * so existing decorator code continues to type-check.
 *
 * @api Tier 2 — instantiable contract for downstream noop usage.
 */
final class NoOpTelemetry implements TelemetryInterface
{
    /**
     * @param string $sql
     * @param string $callingMethod
     *
     * @return \Propel\Runtime\Telemetry\SpanInterface
     */
    #[\Override]
    public function startQuerySpan(string $sql, string $callingMethod): SpanInterface
    {
        return new NoOpSpan($sql, $callingMethod, microtime(true));
    }

    /**
     * @param object $span
     * @param float $durationSeconds
     * @param \Throwable|null $error
     *
     * @return void
     */
    #[\Override]
    public function endQuerySpan(object $span, float $durationSeconds, ?Throwable $error = null): void
    {
        // Intentionally empty.
    }

    /**
     * @param bool $hit
     *
     * @return void
     */
    #[\Override]
    public function recordPreparedCacheHit(bool $hit): void
    {
        // Intentionally empty.
    }

    /**
     * @param int $depth
     *
     * @return void
     */
    #[\Override]
    public function recordTransactionDepth(int $depth): void
    {
        // Intentionally empty.
    }

    /**
     * @param string $class
     * @param float $microseconds
     *
     * @return void
     */
    #[\Override]
    public function recordHydrationDuration(string $class, float $microseconds): void
    {
        // Intentionally empty.
    }

    /**
     * @param string $decision
     * @param string $reason
     *
     * @return void
     */
    #[\Override]
    public function recordReplicaRoutingDecision(string $decision, string $reason): void
    {
        // Intentionally empty.
    }

    /**
     * @param string $strategy
     * @param float $microseconds
     *
     * @return void
     */
    #[\Override]
    public function recordIdentityGeneration(string $strategy, float $microseconds): void
    {
        // Intentionally empty.
    }
}
