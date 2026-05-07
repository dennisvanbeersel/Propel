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
use Propel\Runtime\ActiveQuery\Operator\LogicalOperator;

class LogicalOperatorTest extends TestCase
{
    public function testEnumValueEqualsCriteriaConstant(): void
    {
        self::assertSame(Criteria::LOGICAL_AND, LogicalOperator::And->value);
        self::assertSame(Criteria::LOGICAL_OR, LogicalOperator::Or->value);
    }

    public function testFromAcceptsConstantValue(): void
    {
        self::assertSame(LogicalOperator::And, LogicalOperator::from(Criteria::LOGICAL_AND));
        self::assertSame(LogicalOperator::Or, LogicalOperator::from(Criteria::LOGICAL_OR));
    }
}
