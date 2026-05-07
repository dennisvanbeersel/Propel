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
use Propel\Runtime\ActiveQuery\Operator\JoinType;

class JoinTypeTest extends TestCase
{
    public function testEnumValueEqualsCriteriaConstant(): void
    {
        self::assertSame(Criteria::LEFT_JOIN, JoinType::Left->value);
        self::assertSame(Criteria::RIGHT_JOIN, JoinType::Right->value);
        self::assertSame(Criteria::INNER_JOIN, JoinType::Inner->value);
        self::assertSame(Criteria::JOIN, JoinType::Default->value);
    }

    public function testFromAcceptsConstantValue(): void
    {
        self::assertSame(JoinType::Left, JoinType::from(Criteria::LEFT_JOIN));
        self::assertSame(JoinType::Inner, JoinType::from(Criteria::INNER_JOIN));
    }
}
