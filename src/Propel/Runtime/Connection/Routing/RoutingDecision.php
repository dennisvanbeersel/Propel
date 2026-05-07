<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Routing;

/**
 * Output of {@see RouteResolver::resolve()}: the chosen target plus a
 * rationale string suitable for structured logging.
 *
 * `target` is `'primary'` or `'replica:<name>'`. `rationale` is a single
 * line of structured text in `key=value, key=value` form for easy
 * grep/aggregator ingestion.
 *
 * @api Tier 3.
 */
final readonly class RoutingDecision
{
    public const string TARGET_PRIMARY = 'primary';

    public const string TARGET_REPLICA_PREFIX = 'replica:';

    /**
     * @param string $target 'primary' or 'replica:<name>'.
     * @param string $rationale Structured key=value list.
     * @param int $routedAtMicros Wall-clock micros at decision time.
     */
    public function __construct(
        public string $target,
        public string $rationale,
        public int $routedAtMicros
    ) {
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->target === self::TARGET_PRIMARY;
    }

    /**
     * @return string|null Replica name when target is a replica, else null.
     */
    public function replicaName(): ?string
    {
        if (!str_starts_with($this->target, self::TARGET_REPLICA_PREFIX)) {
            return null;
        }

        return substr($this->target, strlen(self::TARGET_REPLICA_PREFIX));
    }
}
