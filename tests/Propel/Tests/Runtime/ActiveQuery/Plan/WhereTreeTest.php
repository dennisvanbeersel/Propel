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
use Propel\Runtime\ActiveQuery\Criterion\BasicCriterion;
use Propel\Runtime\ActiveQuery\Plan\WhereTree;

class WhereTreeTest extends TestCase
{
    private function makeCriterion(string $column = 'book.title', string $value = 'foo'): BasicCriterion
    {
        return new BasicCriterion(new Criteria(), $column, $value);
    }

    public function testStartsEmpty(): void
    {
        $tree = new WhereTree();
        self::assertTrue($tree->isEmpty());
        self::assertSame([], $tree->getCriterions());
        self::assertNull($tree->getHaving());
    }

    public function testAddAndGet(): void
    {
        $tree = new WhereTree();
        $c = $this->makeCriterion();
        $tree->add('book.title', $c);

        self::assertFalse($tree->isEmpty());
        self::assertTrue($tree->has('book.title'));
        self::assertSame($c, $tree->get('book.title'));
        self::assertNull($tree->get('nonexistent'));
    }

    public function testRemove(): void
    {
        $tree = new WhereTree();
        $c = $this->makeCriterion();
        $tree->add('book.title', $c);
        $tree->remove('book.title');
        self::assertFalse($tree->has('book.title'));
    }

    public function testHaving(): void
    {
        $tree = new WhereTree();
        $c = $this->makeCriterion();
        $tree->setHaving($c);
        self::assertSame($c, $tree->getHaving());
        $tree->setHaving(null);
        self::assertNull($tree->getHaving());
    }

    public function testWalkVisitsEachInOrder(): void
    {
        $a = $this->makeCriterion('book.a');
        $b = $this->makeCriterion('book.b');
        $tree = new WhereTree();
        $tree->add('a', $a);
        $tree->add('b', $b);

        $visited = [];
        $tree->walk(static function (string $name, $criterion) use (&$visited): void {
            $visited[] = $name;
        });

        self::assertSame(['a', 'b'], $visited);
    }
}
