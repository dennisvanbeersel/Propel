<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Runtime\ActiveQuery\Operator;

/**
 * Internal helper that normalises either an enum case OR the equivalent string
 * constant down to the canonical string form expected by the Tier 2 Criterion
 * classes.
 *
 * Both forms are accepted forever per umbrella §3.1: the legacy `Criteria::*`
 * string constants stay frozen, and the Phase F enums sit alongside.
 *
 * @api internal — public so generators / behaviors can call it; not part of Tier 1.
 */
final class OperatorAcceptor
{
    /**
     * @param string|\Propel\Runtime\ActiveQuery\Operator\Comparison $op
     *
     * @return string
     */
    public static function normalizeComparison(string|Comparison $op): string
    {
        return is_string($op) ? $op : $op->value;
    }

    /**
     * @param string|\Propel\Runtime\ActiveQuery\Operator\JoinType $op
     *
     * @return string
     */
    public static function normalizeJoinType(string|JoinType $op): string
    {
        return is_string($op) ? $op : $op->value;
    }

    /**
     * @param string|\Propel\Runtime\ActiveQuery\Operator\SortOrder $op
     *
     * @return string
     */
    public static function normalizeSortOrder(string|SortOrder $op): string
    {
        return is_string($op) ? $op : $op->value;
    }

    /**
     * @param string|\Propel\Runtime\ActiveQuery\Operator\LogicalOperator $op
     *
     * @return string
     */
    public static function normalizeLogicalOperator(string|LogicalOperator $op): string
    {
        return is_string($op) ? $op : $op->value;
    }
}
