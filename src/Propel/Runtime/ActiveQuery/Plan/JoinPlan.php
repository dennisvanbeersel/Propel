<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Plan;

use Propel\Runtime\ActiveQuery\Join;

/**
 * Value object encapsulating the JOIN graph + alias table for a Criteria.
 *
 * Phase F.4.1 — extracted as a Tier 3 internal type (with Tier 2 read-side
 * stability commitment for `getJoins()`/`getAliases()`). For now, Criteria
 * still owns the storage and operations directly; this class provides a
 * stable seam for downstream consumers (Behaviors, ModelCriteria) and is
 * the migration target for verbatim extraction in a follow-up cycle.
 *
 * @api Tier 3 — public methods stable for downstream Compiler/Plan consumers.
 */
final class JoinPlan
{
    /**
     * @var array<int, \Propel\Runtime\ActiveQuery\Join>
     */
    private array $joins;

    /**
     * @var array<string, string>
     */
    private array $aliases;

    /**
     * @param array<int, \Propel\Runtime\ActiveQuery\Join> $joins
     * @param array<string, string> $aliases
     */
    public function __construct(array $joins = [], array $aliases = [])
    {
        $this->joins = $joins;
        $this->aliases = $aliases;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\Join $join
     *
     * @return void
     */
    public function addJoin(Join $join): void
    {
        $this->joins[] = $join;
    }

    /**
     * @return array<int, \Propel\Runtime\ActiveQuery\Join>
     */
    public function getJoins(): array
    {
        return $this->joins;
    }

    /**
     * @param string $alias
     * @param string $table
     *
     * @return void
     */
    public function addAlias(string $alias, string $table): void
    {
        $this->aliases[$alias] = $table;
    }

    /**
     * @param string $alias
     *
     * @return void
     */
    public function removeAlias(string $alias): void
    {
        unset($this->aliases[$alias]);
    }

    /**
     * @return array<string, string>
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @param string $alias
     *
     * @return string|null
     */
    public function getTableForAlias(string $alias): ?string
    {
        return $this->aliases[$alias] ?? null;
    }

    /**
     * @return bool
     */
    public function hasJoins(): bool
    {
        return $this->joins !== [];
    }

    /**
     * @return int
     */
    public function joinCount(): int
    {
        return count($this->joins);
    }
}
