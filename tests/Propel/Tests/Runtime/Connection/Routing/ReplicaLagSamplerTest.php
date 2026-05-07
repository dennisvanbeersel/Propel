<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection\Routing;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\Routing\ReplicaLagSampler;

/**
 * Unit tests for {@see ReplicaLagSampler}.
 *
 * Hot-path safety: `getSamples()` MUST NOT trigger any database call.
 * Sampling cadence: `isStale()` returns true once the per-replica
 * interval is exceeded.
 */
class ReplicaLagSamplerTest extends TestCase
{
    /**
     * @return void
     */
    public function testInitiallyNoSamples(): void
    {
        $sampler = new ReplicaLagSampler(10);
        $this->assertSame([], $sampler->getSamples());
        $this->assertTrue($sampler->isStale('any'));
    }

    /**
     * @return void
     */
    public function testRecordSampleStoresValue(): void
    {
        $clock = new FixedClock(1_000_000);
        $sampler = new ReplicaLagSampler(10, $clock);
        $sampler->recordSample('r1', 0.42);

        $this->assertSame(['r1' => 0.42], $sampler->getSamples());
    }

    /**
     * @return void
     */
    public function testIsStaleAfterIntervalExceeded(): void
    {
        $clock = new FixedClock(1_000_000);
        $sampler = new ReplicaLagSampler(10, $clock);
        $sampler->recordSample('r1', 0.5);

        // Move forward 11 seconds (interval is 10s).
        $clock->setMicros(1_000_000 + 11_000_000);
        $this->assertTrue($sampler->isStale('r1'));
    }

    /**
     * @return void
     */
    public function testIsNotStaleWithinInterval(): void
    {
        $clock = new FixedClock(1_000_000);
        $sampler = new ReplicaLagSampler(10, $clock);
        $sampler->recordSample('r1', 0.5);

        $clock->setMicros(1_000_000 + 5_000_000);
        $this->assertFalse($sampler->isStale('r1'));
    }

    /**
     * @return void
     */
    public function testInvalidateForcesReSample(): void
    {
        $clock = new FixedClock(1_000_000);
        $sampler = new ReplicaLagSampler(10, $clock);
        $sampler->recordSample('r1', 0.5);
        $sampler->invalidate('r1');

        $this->assertTrue($sampler->isStale('r1'));
        $this->assertSame([], $sampler->getSamples());
    }
}
