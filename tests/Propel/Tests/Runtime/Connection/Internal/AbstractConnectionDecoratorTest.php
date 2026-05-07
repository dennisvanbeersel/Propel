<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection\Internal;

use PDO;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\Internal\AbstractConnectionDecorator;

/**
 * Asserts that {@see AbstractConnectionDecorator} forwards every
 * {@see ConnectionInterface} method to the wrapped inner connection by default.
 */
class AbstractConnectionDecoratorTest extends TestCase
{
    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface $inner
     *
     * @return \Propel\Runtime\Connection\Internal\AbstractConnectionDecorator
     */
    private function makeNoopDecorator(ConnectionInterface $inner): AbstractConnectionDecorator
    {
        return new class ($inner) extends AbstractConnectionDecorator {
        };
    }

    /**
     * @return void
     */
    public function testGetInnerReturnsConstructorArgument(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $decorator = $this->makeNoopDecorator($inner);

        $this->assertSame($inner, $decorator->getInner());
    }

    /**
     * @return void
     */
    public function testForwardsSetGetName(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())->method('setName')->with('bookstore');
        $inner->expects($this->once())->method('getName')->willReturn('bookstore');

        $decorator = $this->makeNoopDecorator($inner);
        $decorator->setName('bookstore');
        $this->assertSame('bookstore', $decorator->getName());
    }

    /**
     * @return void
     */
    public function testForwardsTransactionLifecycle(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())->method('beginTransaction')->willReturn(true);
        $inner->expects($this->once())->method('commit')->willReturn(true);
        $inner->expects($this->once())->method('rollBack')->willReturn(true);
        $inner->expects($this->once())->method('inTransaction')->willReturn(false);

        $decorator = $this->makeNoopDecorator($inner);
        $this->assertTrue($decorator->beginTransaction());
        $this->assertTrue($decorator->commit());
        $this->assertTrue($decorator->rollBack());
        $this->assertFalse($decorator->inTransaction());
    }

    /**
     * @return void
     */
    public function testForwardsAttributeMethods(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())->method('getAttribute')->with(PDO::ATTR_DRIVER_NAME)->willReturn('sqlite');
        $inner->expects($this->once())->method('setAttribute')->with('ATTR_CASE', PDO::CASE_LOWER)->willReturn(true);

        $decorator = $this->makeNoopDecorator($inner);
        $this->assertSame('sqlite', $decorator->getAttribute(PDO::ATTR_DRIVER_NAME));
        $this->assertTrue($decorator->setAttribute('ATTR_CASE', PDO::CASE_LOWER));
    }

    /**
     * @return void
     */
    public function testForwardsExecPrepareQueryQuoteAndLastInsertId(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())->method('exec')->with('DELETE FROM book')->willReturn(7);
        $inner->expects($this->once())->method('prepare')->with('SELECT 1', [])->willReturn(false);
        $inner->expects($this->once())->method('query')->with('SELECT 1')->willReturn(false);
        $inner->expects($this->once())->method('quote')->with('o', PDO::PARAM_STR)->willReturn("'o'");
        $inner->expects($this->once())->method('lastInsertId')->with(null)->willReturn('42');

        $decorator = $this->makeNoopDecorator($inner);
        $this->assertSame(7, $decorator->exec('DELETE FROM book'));
        $this->assertFalse($decorator->prepare('SELECT 1'));
        $this->assertFalse($decorator->query('SELECT 1'));
        $this->assertSame("'o'", $decorator->quote('o'));
        $this->assertSame('42', $decorator->lastInsertId());
    }

    /**
     * @return void
     */
    public function testForwardsTransactionCallable(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->expects($this->once())
            ->method('transaction')
            ->willReturnCallback(static fn (callable $cb) => $cb());

        $decorator = $this->makeNoopDecorator($inner);
        $this->assertSame('result', $decorator->transaction(static fn () => 'result'));
    }
}
