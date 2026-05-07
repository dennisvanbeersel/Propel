<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection\Internal;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\Internal\CachingConnection;
use Propel\Runtime\Connection\Internal\PreparedStatementLruCache;
use Propel\Runtime\Connection\StatementInterface;

/**
 * Unit tests for {@see CachingConnection}.
 *
 * Covers cache miss → inner.prepare; cache hit → no inner call;
 * different driverOptions yield distinct keys (Phase A bug-fix #4);
 * ksort'd driverOptions hash to the same key; clear/disable.
 */
class CachingConnectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testCacheMissCallsInnerPrepare(): void
    {
        $stmt = $this->createMock(StatementInterface::class);
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())
            ->method('prepare')
            ->with('SELECT 1')
            ->willReturn($stmt);

        $caching = new CachingConnection($inner);
        $result = $caching->prepare('SELECT 1');

        $this->assertSame($stmt, $result);
        $this->assertSame(1, $caching->getCache()->size());
        $this->assertSame(1, $caching->getCache()->misses());
    }

    /**
     * @return void
     */
    public function testCacheHitDoesNotCallInner(): void
    {
        $stmt = $this->createMock(StatementInterface::class);
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $caching = new CachingConnection($inner);
        $caching->prepare('SELECT 1');
        $second = $caching->prepare('SELECT 1');

        $this->assertSame($stmt, $second);
        $this->assertSame(1, $caching->getCache()->hits());
    }

    /**
     * Phase A bug-fix #4: different `$driverOptions` produce DIFFERENT
     * cache entries.
     *
     * @return void
     */
    public function testDifferentDriverOptionsYieldDistinctEntries(): void
    {
        $a = $this->createMock(StatementInterface::class);
        $b = $this->createMock(StatementInterface::class);
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($a, $b);

        $caching = new CachingConnection($inner);
        $caching->prepare('SELECT 1', ['cursor' => 'forward']);
        $caching->prepare('SELECT 1', ['cursor' => 'scroll']);

        $this->assertSame(2, $caching->getCache()->size());
    }

    /**
     * Re-ordered identical `$driverOptions` map to the same cache entry.
     *
     * @return void
     */
    public function testReorderedDriverOptionsHashToSameKey(): void
    {
        $stmt = $this->createMock(StatementInterface::class);
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $caching = new CachingConnection($inner);
        $caching->prepare('SELECT 1', ['a' => 1, 'b' => 2]);
        $caching->prepare('SELECT 1', ['b' => 2, 'a' => 1]);

        $this->assertSame(1, $caching->getCache()->hits());
    }

    /**
     * @return void
     */
    public function testBuildCacheKeyEmptyOptionsReturnsBareSql(): void
    {
        $this->assertSame('SELECT 1', CachingConnection::buildCacheKey('SELECT 1', []));
    }

    /**
     * @return void
     */
    public function testClearStatementCacheEmptiesCache(): void
    {
        $stmt = $this->createMock(StatementInterface::class);
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->method('prepare')->willReturn($stmt);

        $caching = new CachingConnection($inner);
        $caching->prepare('SELECT 1');
        $caching->clearStatementCache();

        $this->assertSame(0, $caching->getCache()->size());
    }

    /**
     * @return void
     */
    public function testDisableCachingPassthroughAndClears(): void
    {
        $stmt = $this->createMock(StatementInterface::class);
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($stmt);

        $caching = new CachingConnection($inner);
        $caching->prepare('SELECT 1');
        $caching->setCachePreparedStatements(false);
        $caching->prepare('SELECT 1');

        $this->assertFalse($caching->isCachePreparedStatements());
        $this->assertSame(0, $caching->getCache()->size());
    }

    /**
     * @return void
     */
    public function testFalseFromInnerIsNotCached(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn(false);

        $caching = new CachingConnection($inner);
        $this->assertFalse($caching->prepare('BAD SQL'));
        $this->assertFalse($caching->prepare('BAD SQL'));
        $this->assertSame(0, $caching->getCache()->size());
    }

    /**
     * @return void
     */
    public function testCustomCacheCapacityIsUsed(): void
    {
        $stmt = $this->createMock(StatementInterface::class);
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->method('prepare')->willReturn($stmt);

        $caching = new CachingConnection($inner, new PreparedStatementLruCache(2));
        $caching->prepare('A');
        $caching->prepare('B');
        $caching->prepare('C');

        $this->assertSame(2, $caching->getCache()->size());
        $this->assertSame(1, $caching->getCache()->evictions());
    }
}
