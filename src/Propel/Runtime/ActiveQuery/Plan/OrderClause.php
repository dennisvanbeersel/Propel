<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Plan;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Operator\OperatorAcceptor;
use Propel\Runtime\ActiveQuery\Operator\SortOrder;

/**
 * Value object encapsulating ORDER BY accumulation for a Criteria.
 *
 * Phase F.4.3 — extracted as a Tier 3 internal type.
 *
 * @api Tier 3 — public methods stable for downstream Compiler/Plan consumers.
 */
final class OrderClause
{
    /**
     * @var list<string>
     */
    private array $columns;

    /**
     * @param list<string> $columns
     */
    public function __construct(array $columns = [])
    {
        $this->columns = $columns;
    }

    /**
     * @param string $column
     *
     * @return void
     */
    public function addAscending(string $column): void
    {
        $entry = $column . ' ' . Criteria::ASC;
        if (!in_array($entry, $this->columns, true)) {
            $this->columns[] = $entry;
        }
    }

    /**
     * @param string $column
     *
     * @return void
     */
    public function addDescending(string $column): void
    {
        $entry = $column . ' ' . Criteria::DESC;
        if (!in_array($entry, $this->columns, true)) {
            $this->columns[] = $entry;
        }
    }

    /**
     * @param string $column
     * @param \Propel\Runtime\ActiveQuery\Operator\SortOrder|string $direction
     *
     * @return void
     */
    public function add(string $column, string|SortOrder $direction): void
    {
        $dir = OperatorAcceptor::normalizeSortOrder($direction);
        if ($dir === Criteria::ASC) {
            $this->addAscending($column);
        } else {
            $this->addDescending($column);
        }
    }

    /**
     * @return list<string>
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->columns = [];
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->columns === [];
    }
}
