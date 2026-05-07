<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Operator;

use Propel\Runtime\ActiveQuery\Criteria;

/**
 * ORDER BY direction enum — alongside Criteria::ASC / Criteria::DESC.
 *
 * @api Tier 2 — frozen for the 3.x line.
 */
enum SortOrder: string
{
    case Asc = Criteria::ASC;
    case Desc = Criteria::DESC;
}
