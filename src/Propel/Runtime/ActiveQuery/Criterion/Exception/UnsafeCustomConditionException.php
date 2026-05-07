<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Criterion\Exception;

use Propel\Runtime\Exception\InvalidArgumentException;

/**
 * Thrown by Criteria::customCondition() when the supplied SQL fragment looks
 * like it might contain interpolated user input — heuristic flag, conservative.
 * The caller can bypass via `customCondition($name, $sql, $params, allowRawSql: true)`
 * after auditing that the SQL contains no user-controlled data.
 *
 * Closes umbrella §6.2 risk #2 ("Criteria::CUSTOM raw-SQL injection vector").
 *
 * @api Tier 2 — extension surface for catch-blocks; classification commitment.
 */
class UnsafeCustomConditionException extends InvalidArgumentException
{
}
