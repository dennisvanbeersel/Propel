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
use Propel\Runtime\Connection\Internal\ProfilingConnection;
use Propel\Runtime\Telemetry\NoOpSpan;
use Propel\Runtime\Telemetry\SpanInterface;
use Propel\Runtime\Telemetry\TelemetryInterface;
use RuntimeException;
use Throwable;

/**
 * Unit tests for {@see ProfilingConnection}.
 *
 * Covers query-count + total-duration accounting, histogram-bucket
 * emission for prepare/exec/query, and telemetry span hooks.
 */
class ProfilingConnectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testQueryCountIncrementsForEachDecoratedCall(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->method('exec')->willReturn(0);
        $inner->method('prepare')->willReturn(false);
        $inner->method('query')->willReturn(false);

        $profiling = new ProfilingConnection($inner);
        $profiling->exec('SELECT 1');
        $profiling->prepare('SELECT 2');
        $profiling->query('SELECT 3');

        $this->assertSame(3, $profiling->getQueryCount());
        $this->assertGreaterThanOrEqual(0.0, $profiling->getTotalDurationSeconds());
    }

    /**
     * @return void
     */
    public function testHistogramBucketsCustomizable(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->method('exec')->willReturn(0);

        $profiling = new ProfilingConnection($inner, null, [0.001, 0.01]);
        $this->assertSame([0.001, 0.01], $profiling->getBuckets());
        // 3 buckets: 0.001, 0.01, +∞
        $this->assertCount(3, $profiling->getBucketCounts());
    }

    /**
     * @return void
     */
    public function testTelemetryHookFiresForEveryDecoratedCall(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->method('exec')->willReturn(0);
        $inner->method('prepare')->willReturn(false);
        $inner->method('query')->willReturn(false);

        $telemetry = $this->makeRecordingTelemetry();
        $profiling = new ProfilingConnection($inner, $telemetry);

        $profiling->exec('A');
        $profiling->prepare('B');
        $profiling->query('C');

        $this->assertSame(3, $telemetry->starts);
        $this->assertSame(3, $telemetry->ends);
    }

    /**
     * Even when the inner throws, the duration is still recorded
     * (try/finally pattern) — important for timeout investigations.
     *
     * @return void
     */
    public function testDurationRecordedEvenOnInnerException(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->method('exec')->willThrowException(new RuntimeException('boom'));

        $profiling = new ProfilingConnection($inner);

        try {
            $profiling->exec('FAILS');
        } catch (RuntimeException $e) {
            // expected
        }

        $this->assertSame(1, $profiling->getQueryCount());
    }

    /**
     * Buckets accumulate counts (sum equals the total query count).
     *
     * @return void
     */
    public function testBucketSumEqualsQueryCount(): void
    {
        $inner = $this->createMock(ConnectionInterface::class);
        $inner->method('exec')->willReturn(0);

        $profiling = new ProfilingConnection($inner);
        for ($i = 0; $i < 7; $i++) {
            $profiling->exec('SELECT ' . $i);
        }

        $this->assertSame(7, array_sum($profiling->getBucketCounts()));
    }

    /**
     * @return object
     */
    private function makeRecordingTelemetry(): object
    {
        return new class implements TelemetryInterface {
            public int $starts = 0;

            public int $ends = 0;

            /**
             * @param string $sql
             * @param string $callingMethod
             *
             * @return \Propel\Runtime\Telemetry\SpanInterface
             */
            #[\Override]
            public function startQuerySpan(string $sql, string $callingMethod): SpanInterface
            {
                $this->starts++;

                return new NoOpSpan($sql, $callingMethod, microtime(true));
            }

            /**
             * @param object $span
             * @param float $durationSeconds
             * @param \Throwable|null $error
             *
             * @return void
             */
            #[\Override]
            public function endQuerySpan(object $span, float $durationSeconds, ?Throwable $error = null): void
            {
                $this->ends++;
            }

            #[\Override]
            public function recordPreparedCacheHit(bool $hit): void
            {
            }

            #[\Override]
            public function recordTransactionDepth(int $depth): void
            {
            }

            #[\Override]
            public function recordHydrationDuration(string $class, float $microseconds): void
            {
            }

            #[\Override]
            public function recordReplicaRoutingDecision(string $decision, string $reason): void
            {
            }

            #[\Override]
            public function recordIdentityGeneration(string $strategy, float $microseconds): void
            {
            }
        };
    }
}
