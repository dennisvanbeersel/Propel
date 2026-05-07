<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection\Internal;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\Internal\PreparedStatementLruCache;
use Propel\Runtime\Connection\StatementInterface;

/**
 * Unit tests for {@see PreparedStatementLruCache}.
 *
 * Covers: capacity validation; LRU ordering; eviction; hit/miss accounting;
 * the held-reference invariant (consumer reference outlives eviction).
 */
class PreparedStatementLruCacheTest extends TestCase
{
    /**
     * @return void
     */
    public function testZeroCapacityRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PreparedStatementLruCache(0);
    }

    /**
     * @return void
     */
    public function testNegativeCapacityRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new PreparedStatementLruCache(-1);
    }

    /**
     * @return void
     */
    public function testGetReturnsNullOnMiss(): void
    {
        $cache = new PreparedStatementLruCache(4);
        $this->assertNull($cache->get('missing'));
        $this->assertSame(0, $cache->hits());
        $this->assertSame(1, $cache->misses());
    }

    /**
     * @return void
     */
    public function testPutThenGetReturnsValue(): void
    {
        $cache = new PreparedStatementLruCache(4);
        $stmt = $this->makeStatement();
        $cache->put('k', $stmt);

        $this->assertSame($stmt, $cache->get('k'));
        $this->assertSame(1, $cache->hits());
        $this->assertSame(0, $cache->misses());
    }

    /**
     * @return void
     */
    public function testCapacityOneEvictsOnEachPut(): void
    {
        $cache = new PreparedStatementLruCache(1);
        $a = $this->makeStatement();
        $b = $this->makeStatement();

        $cache->put('a', $a);
        $cache->put('b', $b);

        $this->assertNull($cache->get('a'));
        $this->assertSame($b, $cache->get('b'));
        $this->assertSame(1, $cache->evictions());
    }

    /**
     * LRU semantics: after filling to capacity, the least-recently-used
     * entry is the evictee.
     *
     * @return void
     */
    public function testLruEviction(): void
    {
        $cache = new PreparedStatementLruCache(3);
        $a = $this->makeStatement();
        $b = $this->makeStatement();
        $c = $this->makeStatement();
        $d = $this->makeStatement();

        $cache->put('a', $a);
        $cache->put('b', $b);
        $cache->put('c', $c);
        // Bump 'a' to MRU.
        $cache->get('a');
        // Now 'b' is LRU; insertion of 'd' evicts 'b'.
        $cache->put('d', $d);

        $this->assertNull($cache->get('b'));
        $this->assertSame($a, $cache->get('a'));
        $this->assertSame($c, $cache->get('c'));
        $this->assertSame($d, $cache->get('d'));
    }

    /**
     * Re-putting an existing key updates the value AND bumps to MRU
     * without evicting another key.
     *
     * @return void
     */
    public function testRePutOnExistingKeyDoesNotEvict(): void
    {
        $cache = new PreparedStatementLruCache(2);
        $a = $this->makeStatement();
        $b = $this->makeStatement();
        $aPrime = $this->makeStatement();

        $cache->put('a', $a);
        $cache->put('b', $b);
        $cache->put('a', $aPrime); // refresh value + bump

        $this->assertSame($aPrime, $cache->get('a'));
        $this->assertSame($b, $cache->get('b'));
        $this->assertSame(0, $cache->evictions());
    }

    /**
     * @return void
     */
    public function testHasReportsMembership(): void
    {
        $cache = new PreparedStatementLruCache(2);
        $cache->put('a', $this->makeStatement());

        $this->assertTrue($cache->has('a'));
        $this->assertFalse($cache->has('b'));
    }

    /**
     * @return void
     */
    public function testClearEmptiesCache(): void
    {
        $cache = new PreparedStatementLruCache(2);
        $cache->put('a', $this->makeStatement());
        $cache->put('b', $this->makeStatement());

        $cache->clear();
        $this->assertSame(0, $cache->size());
        $this->assertNull($cache->get('a'));
    }

    /**
     * @return void
     */
    public function testKeysReturnedInLruOrder(): void
    {
        $cache = new PreparedStatementLruCache(3);
        $cache->put('a', $this->makeStatement());
        $cache->put('b', $this->makeStatement());
        $cache->put('c', $this->makeStatement());
        $cache->get('a'); // bump

        $this->assertSame(['b', 'c', 'a'], $cache->keys());
    }

    /**
     * Held-reference invariant (Phase E §6.2 risk register entry #2).
     *
     * After eviction, the held statement reference remains valid because
     * PHP's refcounting keeps the underlying PDOStatement alive.
     *
     * @return void
     */
    public function testHeldReferenceSurvivesEviction(): void
    {
        $cache = new PreparedStatementLruCache(2);
        $heldStatement = $this->makeStatement();
        $cache->put('held', $heldStatement);

        // Fill cache to overflow, evicting 'held'.
        $cache->put('b', $this->makeStatement());
        $cache->put('c', $this->makeStatement());

        $this->assertFalse($cache->has('held'));
        // Held reference still callable.
        $this->assertInstanceOf(StatementInterface::class, $heldStatement);
        $this->assertTrue($heldStatement->execute());
    }

    /**
     * @return \Propel\Runtime\Connection\StatementInterface
     */
    private function makeStatement(): StatementInterface
    {
        $stmt = $this->createMock(StatementInterface::class);
        $stmt->method('execute')->willReturn(true);

        return $stmt;
    }
}
