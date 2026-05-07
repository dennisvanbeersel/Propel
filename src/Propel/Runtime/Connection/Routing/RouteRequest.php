<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Routing;

/**
 * Per-query routing-decision input value object.
 *
 * Phase E §6.4 capability addition: data shape consumed by
 * {@see RouteResolver::resolve()}.
 *
 * @api Tier 3 — used by `ReplicaRoutingConnection` internals; consumers
 *      typically interact via `Criteria::forcePrimary()` /
 *      `Criteria::allowReplica()` instead.
 */
final readonly class RouteRequest
{
    public const string OPERATION_READ = 'read';

    public const string OPERATION_WRITE = 'write';

    public const string HINT_FORCE_PRIMARY = 'force-primary';

    public const string HINT_ALLOW_REPLICA = 'allow-replica';

    public const string HINT_AUTO = 'auto';

    /**
     * @param string $operation 'read' or 'write'.
     * @param string $hint 'force-primary', 'allow-replica', or 'auto'.
     * @param int|null $sessionLastWriteAtMicros Timestamp (microseconds) of the last write in the session, or null.
     * @param array<string, float> $replicaLagSamples Map of replica name → cached lag in seconds.
     */
    public function __construct(
        public string $operation,
        public string $hint,
        public ?int $sessionLastWriteAtMicros,
        public array $replicaLagSamples
    ) {
    }
}
