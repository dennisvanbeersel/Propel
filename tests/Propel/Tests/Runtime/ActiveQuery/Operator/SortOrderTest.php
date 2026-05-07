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
use Propel\Runtime\ActiveQuery\Operator\SortOrder;

class SortOrderTest extends TestCase
{
    public function testEnumValueEqualsCriteriaConstant(): void
    {
        self::assertSame(Criteria::ASC, SortOrder::Asc->value);
        self::assertSame(Criteria::DESC, SortOrder::Desc->value);
    }

    public function testFromAcceptsConstantValue(): void
    {
        self::assertSame(SortOrder::Asc, SortOrder::from(Criteria::ASC));
        self::assertSame(SortOrder::Desc, SortOrder::from(Criteria::DESC));
    }
}
