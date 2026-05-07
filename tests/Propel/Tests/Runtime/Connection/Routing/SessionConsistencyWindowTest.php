<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection\Routing;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\Routing\SessionConsistencyWindow;

/**
 * Clock-injected unit tests for {@see SessionConsistencyWindow}.
 */
class SessionConsistencyWindowTest extends TestCase
{
    /**
     * @return void
     */
    public function testIsWindowActiveFalseBeforeAnyWrite(): void
    {
        $clock = new FixedClock(1_000_000);
        $window = new SessionConsistencyWindow($clock);

        $this->assertFalse($window->isWindowActive(5.0));
        $this->assertNull($window->getLastWriteAtMicros());
    }

    /**
     * @return void
     */
    public function testWindowActiveImmediatelyAfterWrite(): void
    {
        $clock = new FixedClock(1_000_000);
        $window = new SessionConsistencyWindow($clock);
        $window->noteWrite();

        $this->assertTrue($window->isWindowActive(5.0));
        $this->assertSame(1_000_000, $window->getLastWriteAtMicros());
    }

    /**
     * @return void
     */
    public function testWindowExpiresAfterWindowSeconds(): void
    {
        $clock = new FixedClock(1_000_000);
        $window = new SessionConsistencyWindow($clock);
        $window->noteWrite();

        // Advance past the 5s window (5_000_001 micros after).
        $clock->setMicros(1_000_000 + 5_000_001);

        $this->assertFalse($window->isWindowActive(5.0));
    }

    /**
     * @return void
     */
    public function testNoteWriteRefreshesTheWindow(): void
    {
        $clock = new FixedClock(1_000_000);
        $window = new SessionConsistencyWindow($clock);
        $window->noteWrite();

        $clock->setMicros(4_000_000);
        $window->noteWrite(); // refresh — last write now at t=4s

        $clock->setMicros(8_000_000); // 4s after refresh — still within 5s
        $this->assertTrue($window->isWindowActive(5.0));
    }

    /**
     * @return void
     */
    public function testResetClearsWindow(): void
    {
        $clock = new FixedClock(1_000_000);
        $window = new SessionConsistencyWindow($clock);
        $window->noteWrite();
        $window->reset();

        $this->assertFalse($window->isWindowActive(5.0));
        $this->assertNull($window->getLastWriteAtMicros());
    }
}
