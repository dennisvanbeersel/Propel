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
 * SQL JOIN type enum — alongside Criteria::*_JOIN constants per umbrella §3.1.
 *
 * @api Tier 2 — frozen for the 3.x line.
 */
enum JoinType: string
{
    case Left = Criteria::LEFT_JOIN;
    case Right = Criteria::RIGHT_JOIN;
    case Inner = Criteria::INNER_JOIN;
    case Default = Criteria::JOIN;
}
