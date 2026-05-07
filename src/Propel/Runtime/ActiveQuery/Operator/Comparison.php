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
 * Comparison operator enum — alongside Criteria::* constants per umbrella spec §3.1.
 *
 * Tier 2 published surface (umbrella §3.2). The string `value` of each case MUST equal
 * the matching `Criteria::*` constant — that contract is enforced by ComparisonTest and
 * is permanent. The legacy `Criteria::*` constants stay frozen forever; this enum sits
 * alongside, never replacing.
 *
 * @api Tier 2 — frozen for the 3.x line; case removal requires a deprecation runway.
 */
enum Comparison: string
{
    case Equal = Criteria::EQUAL;
    case NotEqual = Criteria::NOT_EQUAL;
    case AltNotEqual = Criteria::ALT_NOT_EQUAL;
    case GreaterThan = Criteria::GREATER_THAN;
    case LessThan = Criteria::LESS_THAN;
    case GreaterEqual = Criteria::GREATER_EQUAL;
    case LessEqual = Criteria::LESS_EQUAL;
    case Like = Criteria::LIKE;
    case NotLike = Criteria::NOT_LIKE;
    case ILike = Criteria::ILIKE;
    case NotILike = Criteria::NOT_ILIKE;
    case In = Criteria::IN;
    case NotIn = Criteria::NOT_IN;
    case IsNull = Criteria::ISNULL;
    case IsNotNull = Criteria::ISNOTNULL;
    case BinaryAnd = Criteria::BINARY_AND;
    case BinaryOr = Criteria::BINARY_OR;
    case BinaryAll = Criteria::BINARY_ALL;
    case BinaryNone = Criteria::BINARY_NONE;
    case ContainsAll = Criteria::CONTAINS_ALL;
    case ContainsSome = Criteria::CONTAINS_SOME;
    case ContainsNone = Criteria::CONTAINS_NONE;
    case All = Criteria::ALL;
    case Custom = Criteria::CUSTOM;
    case CustomEqual = Criteria::CUSTOM_EQUAL;
    case Raw = Criteria::RAW;
}
