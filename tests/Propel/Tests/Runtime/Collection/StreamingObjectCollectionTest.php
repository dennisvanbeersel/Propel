<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Collection;

use Generator;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Collection\StreamingObjectCollection;
use Propel\Runtime\Exception\LogicException;

/**
 * Phase G.6.3 — verifies StreamingObjectCollection's IteratorAggregate +
 * Countable contract on top of a single-pass Generator.
 */
class StreamingObjectCollectionTest extends TestCase
{
    /**
     * @return \Generator<int, string>
     */
    private function makeGenerator(int $n): Generator
    {
        for ($i = 0; $i < $n; $i++) {
            yield $i => 'row-' . $i;
        }
    }

    /**
     * @return void
     */
    public function testIteratesViaForeachLikeAnyIterable(): void
    {
        $coll = new StreamingObjectCollection($this->makeGenerator(3));

        $rows = [];
        foreach ($coll as $key => $value) {
            $rows[$key] = $value;
        }

        $this->assertSame([0 => 'row-0', 1 => 'row-1', 2 => 'row-2'], $rows);
    }

    /**
     * @return void
     */
    public function testCountDrainsTheGeneratorAndCachesTheResult(): void
    {
        $coll = new StreamingObjectCollection($this->makeGenerator(5));

        $this->assertSame(5, count($coll));
        // Cached path returns the same value without re-iterating.
        $this->assertSame(5, count($coll));
    }

    /**
     * @return void
     */
    public function testSecondGetIteratorThrows(): void
    {
        $coll = new StreamingObjectCollection($this->makeGenerator(2));

        // First pass — drains.
        foreach ($coll as $row) {
            $this->assertNotNull($row);
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('single-pass');

        // Second foreach calls getIterator() again — must throw.
        foreach ($coll as $row) {
            $this->fail('expected throw before any row is yielded on second pass');
        }
    }

    /**
     * @return void
     */
    public function testCountAfterIterationThrows(): void
    {
        $coll = new StreamingObjectCollection($this->makeGenerator(4));

        foreach ($coll as $row) {
            $this->assertNotNull($row);
        }

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('already consumed');
        count($coll);
    }

    /**
     * @return void
     */
    public function testIterationAfterCountYieldsZeroRows(): void
    {
        $coll = new StreamingObjectCollection($this->makeGenerator(3));

        $this->assertSame(3, count($coll));

        $this->expectException(LogicException::class);
        // First foreach after count() must throw — count drained the generator,
        // and a silent zero-row pass would mask consumer bugs.
        foreach ($coll as $row) {
            $this->fail('count() drained the generator; foreach should throw');
        }
    }

    /**
     * @return void
     */
    public function testSatisfiesIterableTypeContract(): void
    {
        $coll = new StreamingObjectCollection($this->makeGenerator(2));

        $rows = $this->collectIterable($coll);
        $this->assertSame(['row-0', 'row-1'], $rows);
    }

    /**
     * @param iterable<int, string> $iter
     *
     * @return list<string>
     */
    private function collectIterable(iterable $iter): array
    {
        $out = [];
        foreach ($iter as $value) {
            $out[] = $value;
        }

        return $out;
    }
}
