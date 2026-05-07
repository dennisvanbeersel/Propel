<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Routing;

use Propel\Runtime\Connection\Routing\RouteRequest;

/**
 * Trait mixed into {@see \Propel\Runtime\ActiveQuery\Criteria} to add the
 * three Tier-1 additive routing-hint methods (`forcePrimary()`,
 * `allowReplica()`, `getRoutingHint()`).
 *
 * Phase E §6.4 capability addition. Tier 1 additivity per umbrella §3.1
 * (no removals; no signature narrowing on existing `Criteria` methods).
 *
 * @api Tier 3 (the trait) — but the methods it provides on `Criteria` are
 *      Tier 1 additive surface.
 */
trait CriteriaRoutingHints
{
    /**
     * @var string Current per-query routing hint.
     */
    protected string $routingHint = RouteRequest::HINT_AUTO;

    /**
     * Forces this query to run on the primary connection regardless of
     * read/write classification or session-consistency state.
     *
     * @return static
     */
    public function forcePrimary(): static
    {
        $this->routingHint = RouteRequest::HINT_FORCE_PRIMARY;

        return $this;
    }

    /**
     * Allows this query to run on a replica when otherwise eligible.
     * Overrides the session-consistency window (use with care).
     *
     * @return static
     */
    public function allowReplica(): static
    {
        $this->routingHint = RouteRequest::HINT_ALLOW_REPLICA;

        return $this;
    }

    /**
     * Returns the current routing hint: `'force-primary'`,
     * `'allow-replica'`, or `'auto'`.
     *
     * @return string
     */
    public function getRoutingHint(): string
    {
        return $this->routingHint;
    }
}
