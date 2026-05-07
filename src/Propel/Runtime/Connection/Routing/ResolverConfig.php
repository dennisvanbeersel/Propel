<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Routing;

/**
 * Static configuration for a {@see RouteResolver} — window seconds, lag
 * threshold, fallback policy. Tier 3.
 */
final readonly class ResolverConfig
{
    /**
     * @param float $sessionConsistencyWindowSeconds Window after a write within which reads route to primary.
     * @param float $replicaLagThresholdSeconds Replicas with lag above this are skipped.
     * @param bool $fallbackToPrimary When true, replica failures fall back to primary.
     */
    public function __construct(
        public float $sessionConsistencyWindowSeconds = 5.0,
        public float $replicaLagThresholdSeconds = 2.0,
        public bool $fallbackToPrimary = true
    ) {
    }
}
