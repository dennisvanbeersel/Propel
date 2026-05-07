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
use Propel\Runtime\Connection\Exception\RollbackException;
use Propel\Runtime\Connection\Internal\TransactionalConnection;

/**
 * Unit tests for {@see TransactionalConnection}.
 *
 * Mirrors the legacy `ConnectionWrapper` nested-tx accounting bit-for-bit;
 * the legacy class is the reference implementation.
 */
class TransactionalConnectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testInitialState(): void
    {
        $tx = new TransactionalConnection($this->createMock(ConnectionInterface::class));
        $this->assertSame(0, $tx->getNestedTransactionCount());
        $this->assertFalse($tx->isInTransaction());
        $this->assertFalse($tx->isCommitable());
        $this->assertFalse($tx->inTransaction());
    }

    /**
     * @return void
     */
    public function testShallowBeginCommit(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())->method('beginTransaction')->willReturn(true);
        $inner->expects($this->any())->method('inTransaction')->willReturnOnConsecutiveCalls(true, true);
        $inner->expects($this->once())->method('commit')->willReturn(true);

        $tx = new TransactionalConnection($inner);
        $this->assertTrue($tx->beginTransaction());
        $this->assertSame(1, $tx->getNestedTransactionCount());
        $this->assertTrue($tx->inTransaction());
        $this->assertTrue($tx->isCommitable());

        $this->assertTrue($tx->commit());
        $this->assertSame(0, $tx->getNestedTransactionCount());
        $this->assertFalse($tx->inTransaction());
    }

    /**
     * @return void
     */
    public function testNestedBeginsCallInnerOnceOnly(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())->method('beginTransaction')->willReturn(true);
        $inner->expects($this->any())->method('inTransaction')->willReturn(true);
        $inner->expects($this->once())->method('commit')->willReturn(true);

        $tx = new TransactionalConnection($inner);
        $tx->beginTransaction();
        $tx->beginTransaction();
        $tx->beginTransaction();
        $this->assertSame(3, $tx->getNestedTransactionCount());

        $this->assertTrue($tx->commit());
        $this->assertSame(2, $tx->getNestedTransactionCount());
        $tx->commit();
        $tx->commit();
        $this->assertSame(0, $tx->getNestedTransactionCount());
    }

    /**
     * @return void
     */
    public function testNestedRollbackTaintsTransaction(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())->method('beginTransaction')->willReturn(true);
        $inner->expects($this->any())->method('inTransaction')->willReturn(true);
        $inner->expects($this->never())->method('commit');

        $tx = new TransactionalConnection($inner);
        $tx->beginTransaction();
        $tx->beginTransaction();
        $tx->rollBack(); // nested → marks uncommitable
        $this->assertSame(1, $tx->getNestedTransactionCount());
        $this->assertFalse($tx->isCommitable());

        $this->expectException(RollbackException::class);
        $tx->commit();
    }

    /**
     * @return void
     */
    public function testOutermostRollbackCallsInner(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())->method('beginTransaction')->willReturn(true);
        $inner->expects($this->any())->method('inTransaction')->willReturn(true);
        $inner->expects($this->once())->method('rollBack')->willReturn(true);

        $tx = new TransactionalConnection($inner);
        $tx->beginTransaction();
        $this->assertTrue($tx->rollBack());
        $this->assertSame(0, $tx->getNestedTransactionCount());
    }

    /**
     * @return void
     */
    public function testForceRollBackResetsCounter(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->method('beginTransaction')->willReturn(true);
        $inner->method('inTransaction')->willReturn(true);
        $inner->expects($this->once())->method('rollBack')->willReturn(true);

        $tx = new TransactionalConnection($inner);
        $tx->beginTransaction();
        $tx->beginTransaction();
        $tx->beginTransaction();
        $this->assertTrue($tx->forceRollBack());
        $this->assertSame(0, $tx->getNestedTransactionCount());
        $this->assertFalse($tx->isInTransaction());
    }

    /**
     * @return void
     */
    public function testForceRollBackOutsideTransactionIsNoOp(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->never())->method('rollBack');

        $tx = new TransactionalConnection($inner);
        $this->assertTrue($tx->forceRollBack());
    }

    /**
     * @return void
     */
    public function testCommitOutsideTransactionIsNoOp(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->never())->method('commit');

        $tx = new TransactionalConnection($inner);
        $this->assertTrue($tx->commit());
    }

    /**
     * @return void
     */
    public function testIsCommitableTransitions(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->method('beginTransaction')->willReturn(true);
        $inner->method('inTransaction')->willReturn(true);
        $inner->method('commit')->willReturn(true);
        $inner->method('rollBack')->willReturn(true);

        $tx = new TransactionalConnection($inner);
        $this->assertFalse($tx->isCommitable());
        $tx->beginTransaction();
        $this->assertTrue($tx->isCommitable());
        $tx->beginTransaction();
        $this->assertTrue($tx->isCommitable());
        $tx->rollBack();
        $this->assertFalse($tx->isCommitable());
        // After the outermost path completes the wrapper resets counter; isCommitable depends only on count + flag.
    }
}
