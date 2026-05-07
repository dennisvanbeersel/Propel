<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\Connection;

use Error;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Propel\Runtime\Connection\TransactionTrait;
use Propel\Tests\TestCase;
use Throwable;

/**
 * Tests the PdoConnection class
 *
 * @author Markus Staab <markus.staab@redaxo.de>
 */
class TransactionTraitTest extends TestCase
{
    /**
     * Build a mock for an abstract class that uses TransactionTrait.
     *
     * Replaces the deprecated getMockForTrait() (removed in PHPUnit 12).
     *
     * @return \Propel\Tests\Runtime\Connection\TransactionTraitTestHarness&\PHPUnit\Framework\MockObject\MockObject
     */
    private function getTransactionTraitMock(): MockObject
    {
        return $this->getMockBuilder(TransactionTraitTestHarness::class)
            ->onlyMethods(['beginTransaction', 'commit', 'rollBack'])
            ->getMock();
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function testTransactionRollback()
    {
        $con = $this->getTransactionTraitMock();

        $con->expects($this->once())->method('beginTransaction');
        $con->expects($this->once())->method('rollback');
        $con->expects($this->never())->method('commit');

        try {
            $con->transaction(function () {
                throw new Exception('boom');
            });
            $this->fail('missing exception');
        } catch (Exception $e) {
            $this->assertEquals('boom', $e->getMessage(), 'exception was rethrown');
        }
    }

    /**
     * @return void
     */
    public function testTransactionRollbackOnThrowable()
    {
        $con = $this->getTransactionTraitMock();

        $con->expects($this->once())->method('beginTransaction');
        $con->expects($this->once())->method('rollback');
        $con->expects($this->never())->method('commit');

        try {
            $con->transaction(function () {
                throw new Error('boom');
            });
            $this->fail('missing throwable');
        } catch (Throwable $e) {
            $this->assertEquals('boom', $e->getMessage(), 'exception was rethrown');
        }
    }

    /**
     * @return void
     */
    public function testTransactionCommit()
    {
        $con = $this->getTransactionTraitMock();

        $con->expects($this->once())->method('beginTransaction');
        $con->expects($this->never())->method('rollback');
        $con->expects($this->once())->method('commit');

        $this->assertNull($con->transaction(function () {
            // do nothing
        }), 'transaction() returns null by default');
    }

    public function testTransactionChaining()
    {
        $con = $this->getTransactionTraitMock();

        $con->expects($this->once())->method('beginTransaction');
        $con->expects($this->never())->method('rollback');
        $con->expects($this->once())->method('commit');

        $this->assertSame('myval', $con->transaction(function () {
            return 'myval';
        }), 'transaction() returns the returned value from the Closure');
    }

    /**
     * @return void
     */
    public function testTransactionNestedCommit()
    {
        $con = $this->getTransactionTraitMock();

        $con->expects($this->exactly(2))->method('beginTransaction');
        $con->expects($this->never())->method('rollback');
        $con->expects($this->exactly(2))->method('commit');

        $this->assertNull($con->transaction(function () use ($con) {
            $this->assertNull($con->transaction(function () {
                // do nothing
            }), 'transaction() returns null by default');
        }), 'transaction() returns null by default');
    }

    /**
     * @throws \Exception
     *
     * @return void
     */
    public function testTransactionNestedException()
    {
        $con = $this->getTransactionTraitMock();

        $con->expects($this->exactly(2))->method('beginTransaction');
        $con->expects($this->exactly(2))->method('rollback');
        $con->expects($this->never())->method('commit');

        try {
            $con->transaction(function () use ($con) {
                $con->transaction(function () {
                    throw new Exception('boooom');
                });
            });
            $this->fail('expecting a nested exception to be re-thrown');
        } catch (Exception $e) {
            $this->assertEquals('boooom', $e->getMessage());
        }
    }

    /**
     * @return void
     */
    public function testTransactionNestedThrowable()
    {
        $con = $this->getTransactionTraitMock();

        $con->expects($this->exactly(2))->method('beginTransaction');
        $con->expects($this->exactly(2))->method('rollback');
        $con->expects($this->never())->method('commit');

        try {
            $con->transaction(function () use ($con) {
                $con->transaction(function () {
                    throw new Error('boooom');
                });
            });
            $this->fail('expecting a nested throwable to be re-thrown');
        } catch (Throwable $e) {
            $this->assertEquals('boooom', $e->getMessage());
        }
    }
}

/**
 * Harness exposing TransactionTrait through an abstract class so it can be
 * mocked with the regular MockBuilder API (getMockForTrait() is deprecated and
 * removed in PHPUnit 12).
 *
 * @internal
 */
abstract class TransactionTraitTestHarness
{
    use TransactionTrait;
}
