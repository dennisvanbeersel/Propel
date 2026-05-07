<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\ActiveQuery\Compiler;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Compiler\PreparedStatementKey;
use Propel\Runtime\Connection\Internal\CachingConnection;

class PreparedStatementKeyTest extends TestCase
{
    public function testEmptyOptionsReturnsSqlVerbatim(): void
    {
        $key = PreparedStatementKey::forSql('SELECT 1');
        self::assertSame('SELECT 1', $key);
    }

    public function testEmptyOptionsExplicitArray(): void
    {
        $key = PreparedStatementKey::forSql('SELECT 1', []);
        self::assertSame('SELECT 1', $key);
    }

    public function testDeterministicAcrossCalls(): void
    {
        $a = PreparedStatementKey::forSql('SELECT 1', [1 => 'foo', 2 => 'bar']);
        $b = PreparedStatementKey::forSql('SELECT 1', [1 => 'foo', 2 => 'bar']);
        self::assertSame($a, $b);
    }

    public function testOptionOrderIndependent(): void
    {
        $a = PreparedStatementKey::forSql('SELECT 1', ['a' => 1, 'b' => 2]);
        $b = PreparedStatementKey::forSql('SELECT 1', ['b' => 2, 'a' => 1]);
        self::assertSame($a, $b);
    }

    public function testDistinctOptionsDistinctKeys(): void
    {
        $a = PreparedStatementKey::forSql('SELECT 1', ['a' => 1]);
        $b = PreparedStatementKey::forSql('SELECT 1', ['a' => 2]);
        self::assertNotSame($a, $b);
    }

    public function testDistinctSqlDistinctKeys(): void
    {
        $a = PreparedStatementKey::forSql('SELECT 1');
        $b = PreparedStatementKey::forSql('SELECT 2');
        self::assertNotSame($a, $b);
    }

    public function testEqualsConstantTime(): void
    {
        $a = PreparedStatementKey::forSql('SELECT 1');
        $b = PreparedStatementKey::forSql('SELECT 1');
        self::assertTrue(PreparedStatementKey::equals($a, $b));

        $c = PreparedStatementKey::forSql('SELECT 2');
        self::assertFalse(PreparedStatementKey::equals($a, $c));
    }

    /**
     * Coordination contract with Phase E: the SPI MUST produce the exact same
     * key bytes that CachingConnection::buildCacheKey produced inline. F.3.2
     * delegates the connection-side method to this SPI; both sides must agree
     * before that delegation is safe.
     */
    public function testKeyBytesMatchPhaseECachingConnection(): void
    {
        $sql = 'SELECT * FROM book WHERE id = ?';
        $options = [\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY];

        self::assertSame(
            CachingConnection::buildCacheKey($sql, $options),
            PreparedStatementKey::forSql($sql, $options),
        );

        // also empty options
        self::assertSame(
            CachingConnection::buildCacheKey($sql, []),
            PreparedStatementKey::forSql($sql, []),
        );
    }
}
