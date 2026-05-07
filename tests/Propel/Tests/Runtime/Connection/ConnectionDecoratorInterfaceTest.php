<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\ConnectionDecoratorInterface;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Tier 2 SPI smoke test for {@see ConnectionDecoratorInterface}.
 *
 * Verifies that a 3-deep stack of throwaway implementations correctly walks
 * from the outermost decorator to the bottom plain `ConnectionInterface`.
 */
class ConnectionDecoratorInterfaceTest extends TestCase
{
    /**
     * @return void
     */
    public function testInterfaceExtendsConnectionInterface(): void
    {
        $this->assertContains(
            ConnectionInterface::class,
            class_implements(ConnectionDecoratorInterface::class) ?: [],
            'ConnectionDecoratorInterface MUST extend ConnectionInterface (umbrella spec §2.1).',
        );
    }

    /**
     * @return void
     */
    public function testGetInnerWalksThreeDeepStack(): void
    {
        $bottom = $this->createMock(ConnectionInterface::class);
        $middle = $this->createMock(ConnectionDecoratorInterface::class);
        $middle->method('getInner')->willReturn($bottom);
        $top = $this->createMock(ConnectionDecoratorInterface::class);
        $top->method('getInner')->willReturn($middle);

        $this->assertSame($middle, $top->getInner());
        $this->assertSame($bottom, $top->getInner()->getInner());
    }

    /**
     * @return void
     */
    public function testGetInnerReturnTypeIsConnectionInterface(): void
    {
        $bottom = $this->createMock(ConnectionInterface::class);
        $decorator = $this->createMock(ConnectionDecoratorInterface::class);
        $decorator->method('getInner')->willReturn($bottom);

        $this->assertInstanceOf(ConnectionInterface::class, $decorator->getInner());
    }
}
