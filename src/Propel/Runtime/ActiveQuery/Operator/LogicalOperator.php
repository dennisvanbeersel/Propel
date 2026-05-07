<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Runtime\ActiveQuery\Operator;

use Propel\Runtime\ActiveQuery\Criteria;

/**
 * SQL boolean combination enum — alongside Criteria::LOGICAL_AND / Criteria::LOGICAL_OR.
 *
 * @api Tier 2 — frozen for the 3.x line.
 */
enum LogicalOperator: string
{
    case And = Criteria::LOGICAL_AND;
    case Or = Criteria::LOGICAL_OR;
}
