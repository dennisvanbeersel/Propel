<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\ActiveQuery\Plan;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Operator\SortOrder;
use Propel\Runtime\ActiveQuery\Plan\OrderClause;

class OrderClauseTest extends TestCase
{
    public function testStartsEmpty(): void
    {
        $clause = new OrderClause();
        self::assertTrue($clause->isEmpty());
        self::assertSame([], $clause->getColumns());
    }

    public function testAddAscending(): void
    {
        $clause = new OrderClause();
        $clause->addAscending('book.title');
        self::assertSame(['book.title ' . Criteria::ASC], $clause->getColumns());
    }

    public function testAddDescending(): void
    {
        $clause = new OrderClause();
        $clause->addDescending('book.title');
        self::assertSame(['book.title ' . Criteria::DESC], $clause->getColumns());
    }

    public function testAddDeduplicates(): void
    {
        $clause = new OrderClause();
        $clause->addAscending('book.title');
        $clause->addAscending('book.title');
        self::assertSame(['book.title ' . Criteria::ASC], $clause->getColumns());
    }

    public function testAddBothAscAndDescAsDistinct(): void
    {
        $clause = new OrderClause();
        $clause->addAscending('book.title');
        $clause->addDescending('book.title');
        self::assertCount(2, $clause->getColumns());
    }

    public function testAddWithStringDirection(): void
    {
        $clause = new OrderClause();
        $clause->add('book.title', Criteria::ASC);
        $clause->add('book.id', Criteria::DESC);
        self::assertSame(
            ['book.title ' . Criteria::ASC, 'book.id ' . Criteria::DESC],
            $clause->getColumns(),
        );
    }

    public function testAddWithEnumDirection(): void
    {
        $clause = new OrderClause();
        $clause->add('book.title', SortOrder::Asc);
        $clause->add('book.id', SortOrder::Desc);
        self::assertSame(
            ['book.title ' . Criteria::ASC, 'book.id ' . Criteria::DESC],
            $clause->getColumns(),
        );
    }

    public function testClear(): void
    {
        $clause = new OrderClause();
        $clause->addAscending('book.title');
        $clause->clear();
        self::assertTrue($clause->isEmpty());
    }
}
