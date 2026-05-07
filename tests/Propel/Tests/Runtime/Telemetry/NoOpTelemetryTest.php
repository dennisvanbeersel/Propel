<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Telemetry;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\Telemetry\NoOpSpan;
use Propel\Runtime\Telemetry\NoOpTelemetry;
use Propel\Runtime\Telemetry\SpanInterface;
use RuntimeException;

/**
 * Unit tests for {@see NoOpTelemetry}.
 *
 * Asserts: zero-overhead methods accept the contract input shapes and
 * never throw, span handle is a typed `SpanInterface`, span carries the
 * SQL/method/start metadata for test fakes that downstream introspect it.
 */
class NoOpTelemetryTest extends TestCase
{
    /**
     * @return void
     */
    public function testStartQuerySpanReturnsSpanInterface(): void
    {
        $telemetry = new NoOpTelemetry();
        $before = microtime(true);
        $span = $telemetry->startQuerySpan('SELECT 1', 'query');
        $after = microtime(true);

        $this->assertInstanceOf(SpanInterface::class, $span);
        $this->assertInstanceOf(NoOpSpan::class, $span);
        $this->assertSame('SELECT 1', $span->getSql());
        $this->assertSame('query', $span->getCallingMethod());
        $this->assertGreaterThanOrEqual($before, $span->getStartedAt());
        $this->assertLessThanOrEqual($after, $span->getStartedAt());
    }

    /**
     * @return void
     */
    public function testEndQuerySpanIsSafeWithAndWithoutError(): void
    {
        $telemetry = new NoOpTelemetry();
        $span = $telemetry->startQuerySpan('UPDATE t', 'exec');

        $telemetry->endQuerySpan($span, 0.0);
        $telemetry->endQuerySpan($span, 0.123, new RuntimeException('boom'));

        $this->expectNotToPerformAssertions();
    }

    /**
     * @return void
     */
    public function testRecordersAreNoOps(): void
    {
        $telemetry = new NoOpTelemetry();

        $telemetry->recordPreparedCacheHit(true);
        $telemetry->recordPreparedCacheHit(false);
        $telemetry->recordTransactionDepth(0);
        $telemetry->recordTransactionDepth(5);
        $telemetry->recordHydrationDuration('Foo\\Bar', 12.5);
        $telemetry->recordReplicaRoutingDecision('primary', 'forced');
        $telemetry->recordReplicaRoutingDecision('replica', 'allowed');
        $telemetry->recordIdentityGeneration('native', 0.5);

        $this->expectNotToPerformAssertions();
    }
}
