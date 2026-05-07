<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection;

use PDO;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Bedrock contract test case for {@see ConnectionInterface} implementations.
 *
 * Each concrete subclass instantiates a connection via {@see self::makeConnection()}
 * and inherits the contract assertion suite. Phase E adds one subclass per
 * decorator (TransactionalConnection, LoggingConnection, etc.) to prove that
 * decoration does not break the base contract.
 *
 * Class name ends in `Case` (not `Test`) to avoid the PHPUnit-11 warning
 * about abstract test classes.
 */
abstract class ConnectionInterfaceContractTestCase extends TestCase
{
    /**
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    abstract protected function makeConnection(): ConnectionInterface;

    /**
     * @return void
     */
    public function testInstanceImplementsContract(): void
    {
        $this->assertInstanceOf(ConnectionInterface::class, $this->makeConnection());
    }

    /**
     * @return void
     */
    public function testNameRoundTrip(): void
    {
        $con = $this->makeConnection();
        $this->assertNull($con->getName());
        $con->setName('bookstore');
        $this->assertSame('bookstore', $con->getName());
    }

    /**
     * @return void
     */
    public function testGetAttributeDriverNameNonEmpty(): void
    {
        $con = $this->makeConnection();
        $name = $con->getAttribute(PDO::ATTR_DRIVER_NAME);
        $this->assertIsString($name);
        $this->assertNotSame('', $name);
    }

    /**
     * @return void
     */
    public function testQuoteRoundTrip(): void
    {
        $con = $this->makeConnection();
        $this->assertSame("'O''Brien'", $con->quote("O'Brien"));
    }

    /**
     * @return void
     */
    public function testTransactionLifecycle(): void
    {
        $con = $this->makeConnection();
        $this->assertFalse($con->inTransaction());
        $this->assertTrue($con->beginTransaction());
        $this->assertTrue($con->inTransaction());
        $this->assertTrue($con->commit());
        $this->assertFalse($con->inTransaction());
    }

    /**
     * @return void
     */
    public function testPrepareReturnsStatement(): void
    {
        $con = $this->makeConnection();
        $stmt = $con->prepare('SELECT 1 AS x');
        $this->assertNotFalse($stmt);
    }

    /**
     * @return void
     */
    public function testLastInsertIdAfterInsert(): void
    {
        $con = $this->makeConnection();
        $this->assertSame(0, $con->exec('CREATE TABLE t_contract (id INTEGER PRIMARY KEY AUTOINCREMENT, v TEXT)'));
        $this->assertSame(1, $con->exec("INSERT INTO t_contract (v) VALUES ('a')"));
        $lastId = $con->lastInsertId();
        $this->assertNotEmpty($lastId);
    }
}
