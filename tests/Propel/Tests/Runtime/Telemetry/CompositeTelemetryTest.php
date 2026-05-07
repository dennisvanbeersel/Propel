<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Telemetry;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\Telemetry\CompositeSpan;
use Propel\Runtime\Telemetry\CompositeTelemetry;
use Propel\Runtime\Telemetry\NoOpSpan;
use Propel\Runtime\Telemetry\NoOpTelemetry;
use Propel\Runtime\Telemetry\SpanInterface;
use Propel\Runtime\Telemetry\TelemetryInterface;
use RuntimeException;
use Throwable;

/**
 * Unit tests for {@see CompositeTelemetry}.
 *
 * Asserts: fan-out across adapters, span LIFO close order, error propagation
 * through endQuerySpan, zero-adapter fast-path safety, mixed-adapter run
 * (NoOp + recording fake).
 */
class CompositeTelemetryTest extends TestCase
{
    /**
     * @return void
     */
    public function testZeroAdaptersIsSafe(): void
    {
        $composite = new CompositeTelemetry();

        // Should not throw, even with no adapters.
        $span = $composite->startQuerySpan('SELECT 1', 'query');
        $this->assertInstanceOf(SpanInterface::class, $span);
        $composite->endQuerySpan($span, 0.001);
        $composite->recordPreparedCacheHit(true);
        $composite->recordTransactionDepth(2);
        $composite->recordHydrationDuration('Foo', 1.5);
        $composite->recordReplicaRoutingDecision('primary', 'forced');
        $composite->recordIdentityGeneration('native', 0.5);

        $this->assertSame([], $span instanceof CompositeSpan ? $span->getChildren() : []);
    }

    /**
     * @return void
     */
    public function testFanOutAcrossTwoRecorders(): void
    {
        $a = $this->makeRecorder();
        $b = $this->makeRecorder();
        $composite = new CompositeTelemetry($a, $b);

        $span = $composite->startQuerySpan('SELECT 1', 'query');
        $composite->endQuerySpan($span, 0.002);
        $composite->recordPreparedCacheHit(true);
        $composite->recordPreparedCacheHit(false);
        $composite->recordTransactionDepth(3);
        $composite->recordHydrationDuration(\stdClass::class, 12.34);
        $composite->recordReplicaRoutingDecision('replica', 'allowed');
        $composite->recordIdentityGeneration('sequence', 5.0);

        foreach ([$a, $b] as $rec) {
            $this->assertSame(1, $rec->starts);
            $this->assertSame(1, $rec->ends);
            $this->assertSame([true, false], $rec->cacheHits);
            $this->assertSame([3], $rec->depths);
            $this->assertSame([[\stdClass::class, 12.34]], $rec->hydrations);
            $this->assertSame([['replica', 'allowed']], $rec->replicaDecisions);
            $this->assertSame([['sequence', 5.0]], $rec->idGenerations);
        }
    }

    /**
     * @return void
     */
    public function testEndSpanWalksAdaptersInReverseOrder(): void
    {
        $callOrder = [];
        $a = $this->makeOrderingRecorder('A', $callOrder);
        $b = $this->makeOrderingRecorder('B', $callOrder);
        $c = $this->makeOrderingRecorder('C', $callOrder);
        $composite = new CompositeTelemetry($a, $b, $c);

        $span = $composite->startQuerySpan('UPDATE t', 'exec');
        $callOrder = []; // reset; we only care about close order.
        $composite->endQuerySpan($span, 0.01);

        // LIFO close: last-registered closes first.
        $this->assertSame(['C', 'B', 'A'], $callOrder);
    }

    /**
     * @return void
     */
    public function testEndSpanIgnoresNonCompositeHandle(): void
    {
        $rec = $this->makeRecorder();
        $composite = new CompositeTelemetry($rec);

        // Pass a plain SpanInterface that isn't a CompositeSpan; composite
        // gracefully no-ops on close.
        $foreignSpan = new NoOpSpan('???', 'unknown', microtime(true));
        $composite->endQuerySpan($foreignSpan, 0.0);

        $this->assertSame(0, $rec->ends);
    }

    /**
     * @return void
     */
    public function testEndSpanCarriesErrorToAllAdapters(): void
    {
        $a = $this->makeRecorder();
        $b = $this->makeRecorder();
        $composite = new CompositeTelemetry($a, $b);

        $span = $composite->startQuerySpan('BAD', 'exec');
        $err = new RuntimeException('boom');
        $composite->endQuerySpan($span, 0.05, $err);

        $this->assertSame($err, $a->lastError);
        $this->assertSame($err, $b->lastError);
    }

    /**
     * @return void
     */
    public function testNoOpAdapterMixesCleanly(): void
    {
        $rec = $this->makeRecorder();
        $composite = new CompositeTelemetry(new NoOpTelemetry(), $rec);

        $span = $composite->startQuerySpan('SELECT 1', 'query');
        $composite->endQuerySpan($span, 0.001);

        $this->assertSame(1, $rec->starts);
        $this->assertSame(1, $rec->ends);
    }

    /**
     * @return object{starts: int, ends: int, cacheHits: array<int, bool>, depths: array<int, int>, hydrations: array<int, array{0: string, 1: float}>, replicaDecisions: array<int, array{0: string, 1: string}>, idGenerations: array<int, array{0: string, 1: float}>, lastError: \Throwable|null}
     */
    private function makeRecorder(): object
    {
        return new class implements TelemetryInterface {
            public int $starts = 0;

            public int $ends = 0;

            /** @var array<int, bool> */
            public array $cacheHits = [];

            /** @var array<int, int> */
            public array $depths = [];

            /** @var array<int, array{0: string, 1: float}> */
            public array $hydrations = [];

            /** @var array<int, array{0: string, 1: string}> */
            public array $replicaDecisions = [];

            /** @var array<int, array{0: string, 1: float}> */
            public array $idGenerations = [];

            public ?Throwable $lastError = null;

            #[\Override]
            public function startQuerySpan(string $sql, string $callingMethod): SpanInterface
            {
                $this->starts++;

                return new NoOpSpan($sql, $callingMethod, microtime(true));
            }

            #[\Override]
            public function endQuerySpan(object $span, float $durationSeconds, ?Throwable $error = null): void
            {
                $this->ends++;
                $this->lastError = $error;
            }

            #[\Override]
            public function recordPreparedCacheHit(bool $hit): void
            {
                $this->cacheHits[] = $hit;
            }

            #[\Override]
            public function recordTransactionDepth(int $depth): void
            {
                $this->depths[] = $depth;
            }

            #[\Override]
            public function recordHydrationDuration(string $class, float $microseconds): void
            {
                $this->hydrations[] = [$class, $microseconds];
            }

            #[\Override]
            public function recordReplicaRoutingDecision(string $decision, string $reason): void
            {
                $this->replicaDecisions[] = [$decision, $reason];
            }

            #[\Override]
            public function recordIdentityGeneration(string $strategy, float $microseconds): void
            {
                $this->idGenerations[] = [$strategy, $microseconds];
            }
        };
    }

    /**
     * @param string $label
     * @param array<int, string> $callOrder Reference passed by the closure for inspection.
     *
     * @return object
     */
    private function makeOrderingRecorder(string $label, array &$callOrder): object
    {
        return new class ($label, $callOrder) implements TelemetryInterface {
            /**
             * @param string $label
             * @param array<int, string> $callOrder
             */
            public function __construct(private readonly string $label, private array &$callOrder)
            {
            }

            #[\Override]
            public function startQuerySpan(string $sql, string $callingMethod): SpanInterface
            {
                $this->callOrder[] = $this->label;

                return new NoOpSpan($sql, $callingMethod, microtime(true));
            }

            #[\Override]
            public function endQuerySpan(object $span, float $durationSeconds, ?Throwable $error = null): void
            {
                $this->callOrder[] = $this->label;
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
