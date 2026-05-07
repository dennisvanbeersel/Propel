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
use Propel\Runtime\ActiveQuery\Operator\JoinType;
use Propel\Runtime\ActiveQuery\Operator\LogicalOperator;
use Propel\Runtime\ActiveQuery\Operator\OperatorAcceptor;
use Propel\Runtime\ActiveQuery\Operator\SortOrder;

class OperatorAcceptorTest extends TestCase
{
    public function testComparisonStringPassesThrough(): void
    {
        self::assertSame(Criteria::EQUAL, OperatorAcceptor::normalizeComparison(Criteria::EQUAL));
        self::assertSame(Criteria::IN, OperatorAcceptor::normalizeComparison(Criteria::IN));
    }

    public function testComparisonEnumNormalizes(): void
    {
        self::assertSame(Criteria::EQUAL, OperatorAcceptor::normalizeComparison(Comparison::Equal));
        self::assertSame(Criteria::IN, OperatorAcceptor::normalizeComparison(Comparison::In));
    }

    public function testJoinTypeBothForms(): void
    {
        self::assertSame(Criteria::LEFT_JOIN, OperatorAcceptor::normalizeJoinType(Criteria::LEFT_JOIN));
        self::assertSame(Criteria::LEFT_JOIN, OperatorAcceptor::normalizeJoinType(JoinType::Left));
    }

    public function testSortOrderBothForms(): void
    {
        self::assertSame(Criteria::ASC, OperatorAcceptor::normalizeSortOrder(Criteria::ASC));
        self::assertSame(Criteria::DESC, OperatorAcceptor::normalizeSortOrder(SortOrder::Desc));
    }

    public function testLogicalOperatorBothForms(): void
    {
        self::assertSame(Criteria::LOGICAL_AND, OperatorAcceptor::normalizeLogicalOperator(Criteria::LOGICAL_AND));
        self::assertSame(Criteria::LOGICAL_OR, OperatorAcceptor::normalizeLogicalOperator(LogicalOperator::Or));
    }
}
