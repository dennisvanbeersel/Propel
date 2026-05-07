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
 * Multiplexes telemetry events to multiple adapters.
 *
 * Phase I §I.1.3: lets a deployment install OpenTelemetry AND Prometheus
 * (or any combination of adapters) at the same time. Each event-recording
 * call fans out to every adapter; `startQuerySpan()` returns a composite
 * span carrying the per-adapter span handles, and `endQuerySpan()` walks
 * them in reverse order so adapter ordering matches LIFO span semantics.
 *
 * Reverse order on close: matches OpenTelemetry's expected child-then-parent
 * close order. If an adapter throws during close, the others still close
 * (try/finally chain).
 *
 * @api Tier 2 — instantiable.
 */
final class CompositeTelemetry implements TelemetryInterface
{
    /**
     * @var array<int, \Propel\Runtime\Telemetry\TelemetryInterface>
     */
    private array $adapters;

    /**
     * @param \Propel\Runtime\Telemetry\TelemetryInterface ...$adapters One or more child adapters.
     */
    public function __construct(TelemetryInterface ...$adapters)
    {
        $this->adapters = array_values($adapters);
    }

    /**
     * @param string $sql
     * @param string $callingMethod
     *
     * @return \Propel\Runtime\Telemetry\SpanInterface
     */
    #[\Override]
    public function startQuerySpan(string $sql, string $callingMethod): SpanInterface
    {
        $children = [];
        foreach ($this->adapters as $adapter) {
            $children[] = $adapter->startQuerySpan($sql, $callingMethod);
        }

        return new CompositeSpan($sql, $callingMethod, microtime(true), $children);
    }

    /**
     * @param object $span Expected to be a {@see CompositeSpan}; degrades gracefully
     *                    by ignoring per-adapter close when not.
     * @param float $durationSeconds
     * @param \Throwable|null $error
     *
     * @return void
     */
    #[\Override]
    public function endQuerySpan(object $span, float $durationSeconds, ?Throwable $error = null): void
    {
        if (!$span instanceof CompositeSpan) {
            return;
        }

        // Reverse iteration to match LIFO close semantics.
        $children = $span->getChildren();
        for ($i = count($children) - 1; $i >= 0; $i--) {
            $adapter = $this->adapters[$i] ?? null;
            $childSpan = $children[$i] ?? null;
            if ($adapter === null || $childSpan === null) {
                continue;
            }
            $adapter->endQuerySpan($childSpan, $durationSeconds, $error);
        }
    }

    /**
     * @param bool $hit
     *
     * @return void
     */
    #[\Override]
    public function recordPreparedCacheHit(bool $hit): void
    {
        foreach ($this->adapters as $adapter) {
            $adapter->recordPreparedCacheHit($hit);
        }
    }

    /**
     * @param int $depth
     *
     * @return void
     */
    #[\Override]
    public function recordTransactionDepth(int $depth): void
    {
        foreach ($this->adapters as $adapter) {
            $adapter->recordTransactionDepth($depth);
        }
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
        foreach ($this->adapters as $adapter) {
            $adapter->recordHydrationDuration($class, $microseconds);
        }
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
        foreach ($this->adapters as $adapter) {
            $adapter->recordReplicaRoutingDecision($decision, $reason);
        }
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
        foreach ($this->adapters as $adapter) {
            $adapter->recordIdentityGeneration($strategy, $microseconds);
        }
    }
}
