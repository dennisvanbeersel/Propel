<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection;

use PDO;
use PDOStatement;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\PdoConnection;
use Propel\Runtime\Exception\InvalidArgumentException;
use ReflectionClass;

/**
 * Unit tests for the Phase-E `final` {@see PdoConnection}.
 *
 * Covers: SQLite construction; string-name attribute resolution via
 * {@see \Propel\Runtime\Connection\Internal\PdoAttributeMap}; rejection
 * of unknown attribute names; `final` declaration.
 */
class PdoConnectionTest extends TestCase
{
    /**
     * @return \Propel\Runtime\Connection\PdoConnection
     */
    private function makeSqliteConnection(): PdoConnection
    {
        return new PdoConnection('sqlite::memory:');
    }

    /**
     * @return void
     */
    public function testClassIsFinal(): void
    {
        $this->assertTrue((new ReflectionClass(PdoConnection::class))->isFinal(), 'PdoConnection MUST be final per Phase E.');
    }

    /**
     * @return void
     */
    public function testImplementsConnectionInterface(): void
    {
        $this->assertInstanceOf(ConnectionInterface::class, $this->makeSqliteConnection());
    }

    /**
     * @return void
     */
    public function testSetAttributeAcceptsBareConstantName(): void
    {
        $con = $this->makeSqliteConnection();
        $this->assertTrue($con->setAttribute('ATTR_CASE', PDO::CASE_LOWER));
        $this->assertSame(PDO::CASE_LOWER, $con->getAttribute(PDO::ATTR_CASE));
    }

    /**
     * @return void
     */
    public function testSetAttributeAcceptsIntConstant(): void
    {
        $con = $this->makeSqliteConnection();
        $this->assertTrue($con->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER));
        $this->assertSame(PDO::CASE_UPPER, $con->getAttribute(PDO::ATTR_CASE));
    }

    /**
     * @return void
     */
    public function testSetAttributeRejectsUnknownStringName(): void
    {
        $con = $this->makeSqliteConnection();
        $this->expectException(InvalidArgumentException::class);

        $con->setAttribute('ATTR_THIS_IS_NOT_A_REAL_PDO_ATTR', 1);
    }

    /**
     * @return void
     */
    public function testConstructorOptionsAcceptStringKeys(): void
    {
        $con = new PdoConnection('sqlite::memory:', null, null, [
            'ATTR_CASE' => PDO::CASE_LOWER,
        ]);

        $this->assertSame(PDO::CASE_LOWER, $con->getAttribute(PDO::ATTR_CASE));
    }

    /**
     * @return void
     */
    public function testConstructorOptionsAcceptIntKeys(): void
    {
        $con = new PdoConnection('sqlite::memory:', null, null, [
            PDO::ATTR_CASE => PDO::CASE_UPPER,
        ]);

        $this->assertSame(PDO::CASE_UPPER, $con->getAttribute(PDO::ATTR_CASE));
    }

    /**
     * @return void
     */
    public function testNameAccessors(): void
    {
        $con = $this->makeSqliteConnection();
        $this->assertNull($con->getName());
        $con->setName('bookstore');
        $this->assertSame('bookstore', $con->getName());
    }

    /**
     * @return void
     */
    public function testQuoteRoundTrip(): void
    {
        $con = $this->makeSqliteConnection();
        $this->assertSame("'O''Brien'", $con->quote("O'Brien"));
    }

    /**
     * @return void
     */
    public function testTransactionLifecycle(): void
    {
        $con = $this->makeSqliteConnection();
        $this->assertFalse($con->inTransaction());
        $this->assertTrue($con->beginTransaction());
        $this->assertTrue($con->inTransaction());
        $this->assertTrue($con->commit());
        $this->assertFalse($con->inTransaction());
    }

    /**
     * @return void
     */
    public function testPrepareReturnsPdoStatement(): void
    {
        $con = $this->makeSqliteConnection();
        $stmt = $con->prepare('SELECT 1');
        $this->assertInstanceOf(PDOStatement::class, $stmt);
    }

    /**
     * @return void
     */
    public function testExecReturnsInt(): void
    {
        $con = $this->makeSqliteConnection();
        $rows = $con->exec('CREATE TABLE t (id INTEGER PRIMARY KEY)');
        $this->assertSame(0, $rows);
    }
}
