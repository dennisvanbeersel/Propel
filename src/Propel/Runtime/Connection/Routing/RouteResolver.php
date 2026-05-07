<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Routing;

/**
 * Pure-logic routing-decision oracle.
 *
 * Inputs: {@see RouteRequest} (operation classification + per-query hint
 * + session-write timestamp + per-replica lag samples) and a
 * {@see ResolverConfig} (window, lag threshold, fallback policy).
 *
 * Output: {@see RoutingDecision} naming the chosen target (`'primary'`
 * or `'replica:<name>'`) plus a structured rationale string. No I/O —
 * everything is decidable from the inputs alone, which makes the
 * resolver trivially unit-testable.
 *
 * Decision precedence (highest → lowest):
 *   1. Write op → primary.
 *   2. Hint `force-primary` → primary.
 *   3. Session-consistency window active → primary.
 *   4. No replica candidates with lag ≤ threshold → primary.
 *   5. Otherwise → first eligible replica (deterministic; randomization
 *      is the consumer's job to seed via the replica-name list ordering).
 *
 * @api Tier 3.
 *
 * @psalm-suppress UnusedClass instantiated by `ReplicaRoutingConnection`
 *                 (Task E.6.4) and `ConnectionFactory` (Task E.7).
 */
final class RouteResolver
{
    /**
     * @var \Propel\Runtime\Connection\Routing\ClockInterface
     */
    private ClockInterface $clock;

    /**
     * @param \Propel\Runtime\Connection\Routing\ClockInterface|null $clock
     */
    public function __construct(?ClockInterface $clock = null)
    {
        $this->clock = $clock ?? new SystemClock();
    }

    /**
     * @param \Propel\Runtime\Connection\Routing\RouteRequest $request
     * @param \Propel\Runtime\Connection\Routing\ResolverConfig $config
     *
     * @return \Propel\Runtime\Connection\Routing\RoutingDecision
     */
    public function resolve(RouteRequest $request, ResolverConfig $config): RoutingDecision
    {
        $now = $this->clock->nowMicros();

        if ($request->operation === RouteRequest::OPERATION_WRITE) {
            return new RoutingDecision(
                RoutingDecision::TARGET_PRIMARY,
                'reason=write-op, hint=' . $request->hint,
                $now,
            );
        }

        if ($request->hint === RouteRequest::HINT_FORCE_PRIMARY) {
            return new RoutingDecision(
                RoutingDecision::TARGET_PRIMARY,
                'reason=hint-force-primary',
                $now,
            );
        }

        $windowMicros = (int)($config->sessionConsistencyWindowSeconds * 1_000_000);
        if (
            $request->sessionLastWriteAtMicros !== null
            && $request->sessionLastWriteAtMicros + $windowMicros > $now
        ) {
            return new RoutingDecision(
                RoutingDecision::TARGET_PRIMARY,
                sprintf(
                    'reason=session-consistency-window, window=%.1fs',
                    $config->sessionConsistencyWindowSeconds,
                ),
                $now,
            );
        }

        $candidates = [];
        foreach ($request->replicaLagSamples as $name => $lag) {
            if ($lag <= $config->replicaLagThresholdSeconds) {
                $candidates[$name] = $lag;
            }
        }

        if ($candidates === []) {
            return new RoutingDecision(
                RoutingDecision::TARGET_PRIMARY,
                sprintf(
                    'reason=all-replicas-lagging, threshold=%.1fs, samples=%d',
                    $config->replicaLagThresholdSeconds,
                    count($request->replicaLagSamples),
                ),
                $now,
            );
        }

        $names = array_keys($candidates);
        $chosen = $names[0];
        $lag = $candidates[$chosen];

        return new RoutingDecision(
            RoutingDecision::TARGET_REPLICA_PREFIX . $chosen,
            sprintf(
                'reason=allow-replica, lag=%.2fs, threshold=%.1fs',
                $lag,
                $config->replicaLagThresholdSeconds,
            ),
            $now,
        );
    }
}
