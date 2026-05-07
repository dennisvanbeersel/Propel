<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\Connection;

use PDO;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\ConnectionWrapper;
use Propel\Runtime\Connection\StatementWrapper;
use ReflectionProperty;

/**
 * Regression: ConnectionWrapper's prepared-statement cache previously keyed
 * solely on the SQL string, ignoring $driverOptions. Two prepare() calls
 * with identical SQL but different cursor types / fetch modes silently
 * returned the cached statement from the first call, masking subtle bugs.
 *
 * Fix: cache key is $sql . "\0" . serialize($driverOptions).
 */
class ConnectionWrapperPrepareCacheTest extends TestCase
{
    public function testPreparedStatementCacheIncludesDriverOptionsInKey(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);

        $wrapper = new class ($inner) extends ConnectionWrapper {
            public int $createCount = 0;
            /** @var \Propel\Runtime\Connection\StatementWrapper|null */
            public ?StatementWrapper $stub = null;

            protected function createStatementWrapper(string $sql): StatementWrapper
            {
                $this->createCount++;

                return $this->stub;
            }
        };

        // Same StatementWrapper instance returned per-create; identity comparison
        // tells us how many distinct cache buckets exist.
        $reflectStub = new ReflectionProperty($wrapper, 'stub');
        $stubA = $this->createMock(StatementWrapper::class);
        $stubB = $this->createMock(StatementWrapper::class);

        $reflectCacheFlag = new ReflectionProperty(ConnectionWrapper::class, 'isCachePreparedStatements');
        $reflectCacheFlag->setValue($wrapper, true);

        $sql = 'SELECT 1';
        $optsA = [PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY];
        $optsB = [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL];

        // First prepare with optsA → miss → createStatementWrapper called → cache stubA.
        $reflectStub->setValue($wrapper, $stubA);
        $r1 = $wrapper->prepare($sql, $optsA);

        // Second with optsA → hit (no create call); confirms cache by identity.
        $reflectStub->setValue($wrapper, $stubB); // would-be-new wrapper if not cached
        $r2 = $wrapper->prepare($sql, $optsA);

        // Third with optsB → miss → new create → stubB returned.
        $r3 = $wrapper->prepare($sql, $optsB);

        $this->assertSame($stubA, $r1);
        $this->assertSame($stubA, $r2, 'identical (sql, driverOptions) must hit cache');
        $this->assertSame($stubB, $r3, 'different driverOptions must miss cache');
        $this->assertSame(2, $wrapper->createCount, 'expected 2 distinct creates: one per unique (sql, driverOptions) pair');
    }
}
