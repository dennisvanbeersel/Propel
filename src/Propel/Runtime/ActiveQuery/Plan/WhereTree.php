<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Plan;

use Propel\Runtime\ActiveQuery\Criterion\AbstractCriterion;

/**
 * Value object encapsulating the WHERE clause tree for a Criteria.
 *
 * Phase F.4.2 — extracted as a Tier 3 internal type. For now, Criteria
 * still owns the storage and tree-mutation operations; this class provides
 * a stable read-side seam (`walk`, `getCriterions`) for downstream
 * Tier 2 Criterion consumers.
 *
 * @api Tier 3 — public methods stable for downstream Compiler/Plan consumers.
 */
final class WhereTree
{
    /**
     * @var array<string, \Propel\Runtime\ActiveQuery\Criterion\AbstractCriterion>
     */
    private array $criterions;

    private ?AbstractCriterion $having;

    /**
     * @param array<string, \Propel\Runtime\ActiveQuery\Criterion\AbstractCriterion> $criterions
     * @param \Propel\Runtime\ActiveQuery\Criterion\AbstractCriterion|null $having
     */
    public function __construct(array $criterions = [], ?AbstractCriterion $having = null)
    {
        $this->criterions = $criterions;
        $this->having = $having;
    }

    /**
     * @param string $name
     * @param \Propel\Runtime\ActiveQuery\Criterion\AbstractCriterion $criterion
     *
     * @return void
     */
    public function add(string $name, AbstractCriterion $criterion): void
    {
        $this->criterions[$name] = $criterion;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function remove(string $name): void
    {
        unset($this->criterions[$name]);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->criterions[$name]);
    }

    /**
     * @param string $name
     *
     * @return \Propel\Runtime\ActiveQuery\Criterion\AbstractCriterion|null
     */
    public function get(string $name): ?AbstractCriterion
    {
        return $this->criterions[$name] ?? null;
    }

    /**
     * @return array<string, \Propel\Runtime\ActiveQuery\Criterion\AbstractCriterion>
     */
    public function getCriterions(): array
    {
        return $this->criterions;
    }

    /**
     * @return \Propel\Runtime\ActiveQuery\Criterion\AbstractCriterion|null
     */
    public function getHaving(): ?AbstractCriterion
    {
        return $this->having;
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\Criterion\AbstractCriterion|null $having
     *
     * @return void
     */
    public function setHaving(?AbstractCriterion $having): void
    {
        $this->having = $having;
    }

    /**
     * Walk every criterion in registration order. The visitor receives
     * (name, criterion) pairs.
     *
     * @param callable $visitor
     *
     * @return void
     */
    public function walk(callable $visitor): void
    {
        foreach ($this->criterions as $name => $criterion) {
            $visitor($name, $criterion);
        }
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->criterions === [];
    }
}
