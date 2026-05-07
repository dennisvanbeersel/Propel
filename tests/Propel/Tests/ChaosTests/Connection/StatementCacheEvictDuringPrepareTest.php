<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\ChaosTests\Connection;

use PDO;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\Internal\CachingConnection;
use Propel\Runtime\Connection\Internal\PreparedStatementLruCache;
use Propel\Runtime\Connection\PdoConnection;

/**
 * Phase E chaos test: simulate eviction occurring while a consumer holds a
 * prepared statement reference. The held reference MUST survive, mirroring
 * the umbrella §6.2 risk-register entry #2 invariant.
 *
 * Synchronous emulation: prepare(sqlA); fill cache to overflow with
 * sqlB...sqlN distinct statements (evicting sqlA's entry); call execute()
 * on the held sqlA statement. The execute MUST succeed because PHP's
 * refcounting keeps the underlying PDOStatement alive.
 *
 * Tier 3 — internal invariant test.
 */
class StatementCacheEvictDuringPrepareTest extends TestCase
{
    /**
     * @return void
     */
    public function testHeldStatementSurvivesAdversarialEviction(): void
    {
        $pdo = new PdoConnection('sqlite::memory:');
        $pdo->exec('CREATE TABLE t_chaos (id INTEGER PRIMARY KEY, v TEXT)');

        // Bound capacity to 2 to make eviction trivially achievable.
        $caching = new CachingConnection($pdo, new PreparedStatementLruCache(2));

        $heldStatement = $caching->prepare('INSERT INTO t_chaos (v) VALUES (?)');
        $this->assertNotFalse($heldStatement);

        // Adversarial: prepare 10 distinct statements, evicting the held one.
        for ($i = 0; $i < 10; $i++) {
            $caching->prepare("SELECT {$i} FROM t_chaos");
        }

        // Cache no longer holds the held statement key.
        $this->assertSame(2, $caching->getCache()->size());

        // But the held reference still works.
        $this->assertTrue($heldStatement->execute(['x']));
        $this->assertSame('x', $pdo->query('SELECT v FROM t_chaos LIMIT 1')->fetchColumn());
    }

    /**
     * @return void
     */
    public function testReusedHeldStatementBeforeAndAfterEviction(): void
    {
        $pdo = new PdoConnection('sqlite::memory:');
        $pdo->exec('CREATE TABLE t_chaos2 (id INTEGER PRIMARY KEY, v TEXT)');

        $caching = new CachingConnection($pdo, new PreparedStatementLruCache(2));
        $held = $caching->prepare('INSERT INTO t_chaos2 (v) VALUES (?)');
        $this->assertNotFalse($held);

        // Use it once before eviction.
        $this->assertTrue($held->execute(['pre']));

        // Force eviction.
        for ($i = 0; $i < 4; $i++) {
            $caching->prepare("SELECT {$i}");
        }

        // Use it again after eviction.
        $this->assertTrue($held->execute(['post']));

        $rows = $pdo->query('SELECT v FROM t_chaos2 ORDER BY id')->fetchAll(PDO::FETCH_COLUMN);
        $this->assertSame(['pre', 'post'], $rows);
    }
}
