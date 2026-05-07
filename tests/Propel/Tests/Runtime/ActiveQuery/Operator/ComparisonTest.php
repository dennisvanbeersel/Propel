<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\ActiveQuery\Operator;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Operator\Comparison;

/**
 * Contract test: every Comparison enum case MUST have its `value` equal to the matching
 * Criteria::* constant. This test exists forever — it pins the alongside-not-replacing
 * promise from umbrella §3.1.
 */
class ComparisonTest extends TestCase
{
    public function testEnumValueEqualsCriteriaConstant(): void
    {
        self::assertSame(Criteria::EQUAL, Comparison::Equal->value);
        self::assertSame(Criteria::NOT_EQUAL, Comparison::NotEqual->value);
        self::assertSame(Criteria::ALT_NOT_EQUAL, Comparison::AltNotEqual->value);
        self::assertSame(Criteria::GREATER_THAN, Comparison::GreaterThan->value);
        self::assertSame(Criteria::LESS_THAN, Comparison::LessThan->value);
        self::assertSame(Criteria::GREATER_EQUAL, Comparison::GreaterEqual->value);
        self::assertSame(Criteria::LESS_EQUAL, Comparison::LessEqual->value);
        self::assertSame(Criteria::LIKE, Comparison::Like->value);
        self::assertSame(Criteria::NOT_LIKE, Comparison::NotLike->value);
        self::assertSame(Criteria::ILIKE, Comparison::ILike->value);
        self::assertSame(Criteria::NOT_ILIKE, Comparison::NotILike->value);
        self::assertSame(Criteria::IN, Comparison::In->value);
        self::assertSame(Criteria::NOT_IN, Comparison::NotIn->value);
        self::assertSame(Criteria::ISNULL, Comparison::IsNull->value);
        self::assertSame(Criteria::ISNOTNULL, Comparison::IsNotNull->value);
        self::assertSame(Criteria::BINARY_AND, Comparison::BinaryAnd->value);
        self::assertSame(Criteria::BINARY_OR, Comparison::BinaryOr->value);
        self::assertSame(Criteria::BINARY_ALL, Comparison::BinaryAll->value);
        self::assertSame(Criteria::BINARY_NONE, Comparison::BinaryNone->value);
        self::assertSame(Criteria::CONTAINS_ALL, Comparison::ContainsAll->value);
        self::assertSame(Criteria::CONTAINS_SOME, Comparison::ContainsSome->value);
        self::assertSame(Criteria::CONTAINS_NONE, Comparison::ContainsNone->value);
        self::assertSame(Criteria::ALL, Comparison::All->value);
        self::assertSame(Criteria::CUSTOM, Comparison::Custom->value);
        self::assertSame(Criteria::CUSTOM_EQUAL, Comparison::CustomEqual->value);
        self::assertSame(Criteria::RAW, Comparison::Raw->value);
    }

    public function testEnumIsBackedString(): void
    {
        self::assertSame('=', Comparison::Equal->value);
        self::assertSame('<>', Comparison::NotEqual->value);
        self::assertSame('!=', Comparison::AltNotEqual->value);
    }

    public function testFromAcceptsConstantValue(): void
    {
        self::assertSame(Comparison::Equal, Comparison::from(Criteria::EQUAL));
        self::assertSame(Comparison::NotEqual, Comparison::from(Criteria::NOT_EQUAL));
    }

    public function testTryFromUnknownValueReturnsNull(): void
    {
        self::assertNull(Comparison::tryFrom('NOT_AN_OPERATOR'));
    }
}
