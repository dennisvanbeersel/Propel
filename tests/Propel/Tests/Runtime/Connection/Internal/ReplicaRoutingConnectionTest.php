<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection\Internal;

use PDOException;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\Exception\ReplicaLagExceededException;
use Propel\Runtime\Connection\Internal\ReplicaRoutingConnection;
use Propel\Runtime\Connection\Routing\ReplicaLagSampler;
use Propel\Runtime\Connection\Routing\ResolverConfig;
use Propel\Runtime\Connection\Routing\RouteRequest;
use Propel\Runtime\Connection\Routing\RouteResolver;
use Propel\Runtime\Connection\Routing\SessionConsistencyWindow;
use Propel\Runtime\Telemetry\NoOpSpan;
use Propel\Runtime\Telemetry\SpanInterface;
use Propel\Runtime\Telemetry\TelemetryInterface;
use Propel\Tests\Runtime\Connection\Routing\FixedClock;
use Throwable;

/**
 * Routing-decision matrix + failure-injection tests for
 * {@see ReplicaRoutingConnection}.
 */
class ReplicaRoutingConnectionTest extends TestCase
{
    /**
     * @return void
     */
    public function testWriteSqlRoutesToPrimary(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $primary->expects($this->once())->method('exec')->with('INSERT INTO t VALUES (1)')->willReturn(1);
        $replica = $this->createMock(ConnectionInterface::class);
        $replica->expects($this->never())->method('exec');

        $routing = $this->makeRouting($primary, ['r1' => $replica], lagSamples: ['r1' => 0.0]);
        $routing->exec('INSERT INTO t VALUES (1)');

        $this->assertTrue($routing->getLastDecision()?->isPrimary());
    }

    /**
     * @return void
     */
    public function testReadSqlWithHealthyReplicaRoutesToReplica(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $primary->expects($this->never())->method('query');
        $replica = $this->createMock(ConnectionInterface::class);
        $replica->expects($this->once())->method('query')->with('SELECT 1');

        $routing = $this->makeRouting($primary, ['r1' => $replica], lagSamples: ['r1' => 0.1]);
        $routing->query('SELECT 1');

        $this->assertSame('replica:r1', $routing->getLastDecision()?->target);
    }

    /**
     * @return void
     */
    public function testForcePrimaryHintRoutesToPrimary(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $primary->expects($this->once())->method('query');
        $replica = $this->createMock(ConnectionInterface::class);
        $replica->expects($this->never())->method('query');

        $routing = $this->makeRouting($primary, ['r1' => $replica], lagSamples: ['r1' => 0.0]);
        $routing->setNextQueryHint(RouteRequest::HINT_FORCE_PRIMARY);
        $routing->query('SELECT * FROM t');

        $this->assertTrue($routing->getLastDecision()?->isPrimary());
    }

    /**
     * @return void
     */
    public function testHintIsClearedAfterDispatch(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $primary->method('query');
        $replica = $this->createMock(ConnectionInterface::class);
        $replica->method('query');

        $routing = $this->makeRouting($primary, ['r1' => $replica], lagSamples: ['r1' => 0.0]);
        $routing->setNextQueryHint(RouteRequest::HINT_FORCE_PRIMARY);
        $routing->query('SELECT 1');
        // Second call has no hint — should NOT route to primary.
        $routing->query('SELECT 2');

        $this->assertSame('replica:r1', $routing->getLastDecision()?->target);
    }

    /**
     * @return void
     */
    public function testReplicaFailureFallsBackToPrimary(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $primary->expects($this->once())->method('exec')->with('SELECT 1')->willReturn(0);
        $replica = $this->createMock(ConnectionInterface::class);
        $replica->method('exec')->willThrowException(new PDOException('replica down'));

        $routing = $this->makeRouting($primary, ['r1' => $replica], lagSamples: ['r1' => 0.1]);
        $routing->exec('SELECT 1');

        $this->assertTrue($routing->getLastDecision()?->isPrimary());
        $this->assertStringContainsString('fallback-from=replica:r1', $routing->getLastDecision()?->rationale ?? '');
    }

    /**
     * @return void
     */
    public function testReplicaFailureWithoutFallbackRethrowsPdo(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $primary->expects($this->never())->method('exec');
        $replica = $this->createMock(ConnectionInterface::class);
        $replica->method('exec')->willThrowException(new PDOException('replica down'));

        $routing = $this->makeRouting(
            $primary,
            ['r1' => $replica],
            config: new ResolverConfig(fallbackToPrimary: false),
            lagSamples: ['r1' => 0.1],
        );

        $this->expectException(PDOException::class);
        $routing->exec('SELECT 1');
    }

    /**
     * @return void
     */
    public function testAllowReplicaWithNoEligibleAndFallbackOffThrows(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $replica = $this->createMock(ConnectionInterface::class);

        $routing = $this->makeRouting(
            $primary,
            ['r1' => $replica],
            config: new ResolverConfig(replicaLagThresholdSeconds: 1.0, fallbackToPrimary: false),
            lagSamples: ['r1' => 5.0],
        );
        $routing->setNextQueryHint(RouteRequest::HINT_ALLOW_REPLICA);

        $this->expectException(ReplicaLagExceededException::class);
        $routing->query('SELECT 1');
    }

    /**
     * @return void
     */
    public function testWriteOpUpdatesSessionWindow(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $primary->method('exec')->willReturn(1);

        $clock = new FixedClock(1_000_000);
        $window = new SessionConsistencyWindow($clock);
        $routing = $this->makeRouting($primary, [], window: $window);

        $this->assertNull($window->getLastWriteAtMicros());
        $routing->exec('UPDATE t SET v = 1');

        $this->assertSame(1_000_000, $window->getLastWriteAtMicros());
    }

    /**
     * @return void
     */
    public function testClassifyReadAndWrite(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $routing = $this->makeRouting($primary, []);

        $this->assertSame(RouteRequest::OPERATION_READ, $routing->classifyOperation('SELECT * FROM t'));
        $this->assertSame(RouteRequest::OPERATION_READ, $routing->classifyOperation('  SELECT 1'));
        $this->assertSame(RouteRequest::OPERATION_WRITE, $routing->classifyOperation('INSERT INTO t VALUES (1)'));
        $this->assertSame(RouteRequest::OPERATION_WRITE, $routing->classifyOperation('UPDATE t SET v = 1'));
        $this->assertSame(RouteRequest::OPERATION_WRITE, $routing->classifyOperation('DELETE FROM t'));
        $this->assertSame(RouteRequest::OPERATION_WRITE, $routing->classifyOperation('TRUNCATE t'));
        $this->assertSame(RouteRequest::OPERATION_WRITE, $routing->classifyOperation('CREATE TABLE t (id INT)'));
        // CTE with embedded write is classified as write.
        $this->assertSame(RouteRequest::OPERATION_WRITE, $routing->classifyOperation('WITH x AS (SELECT 1) DELETE FROM t'));
        // Pure CTE read.
        $this->assertSame(RouteRequest::OPERATION_READ, $routing->classifyOperation('WITH x AS (SELECT 1) SELECT * FROM x'));
    }

    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface $primary
     * @param array<string, \Propel\Runtime\Connection\ConnectionInterface> $replicas
     * @param \Propel\Runtime\Connection\Routing\ResolverConfig|null $config
     * @param array<string, float> $lagSamples
     * @param \Propel\Runtime\Connection\Routing\SessionConsistencyWindow|null $window
     *
     * @return \Propel\Runtime\Connection\Internal\ReplicaRoutingConnection
     */
    private function makeRouting(
        ConnectionInterface $primary,
        array $replicas,
        ?ResolverConfig $config = null,
        array $lagSamples = [],
        ?SessionConsistencyWindow $window = null
    ): ReplicaRoutingConnection {
        $clock = new FixedClock(1_000_000);
        $resolver = new RouteResolver($clock);
        $sampler = new ReplicaLagSampler(10, $clock);
        foreach ($lagSamples as $name => $lag) {
            $sampler->recordSample($name, $lag);
        }

        return new ReplicaRoutingConnection(
            primary: $primary,
            replicas: $replicas,
            config: $config ?? new ResolverConfig(),
            resolver: $resolver,
            sessionWindow: $window ?? new SessionConsistencyWindow($clock),
            lagSampler: $sampler,
        );
    }

    /**
     * Phase I §I.4.3: routing decisions and replica-failure fallbacks
     * fan out to TelemetryInterface with bounded-cardinality reason labels.
     *
     * @return void
     */
    public function testTelemetryReceivesRoutingDecisions(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $primary->method('exec')->willReturn(0);
        $primary->method('query')->willReturn(false);

        $replica = $this->createMock(ConnectionInterface::class);
        $replica->method('query')->willReturn(false);

        $clock = new FixedClock(1_000_000);
        $resolver = new RouteResolver($clock);
        $sampler = new ReplicaLagSampler(10, $clock);
        $sampler->recordSample('r1', 0.1);

        $telemetry = $this->makeRecordingTelemetry();

        $routing = new ReplicaRoutingConnection(
            primary: $primary,
            replicas: ['r1' => $replica],
            config: new ResolverConfig(),
            resolver: $resolver,
            sessionWindow: new SessionConsistencyWindow($clock),
            lagSampler: $sampler,
            telemetry: $telemetry,
        );

        // Read SQL → replica with reason 'allowed'.
        $routing->query('SELECT 1');
        // Write SQL → primary with reason 'write'.
        $routing->exec('INSERT INTO t VALUES (1)');
        // Force-primary hint → primary with reason 'forced'.
        $routing->setNextQueryHint(RouteRequest::HINT_FORCE_PRIMARY);
        $routing->query('SELECT 2');

        $this->assertSame(
            [['replica', 'allowed'], ['primary', 'write'], ['primary', 'forced']],
            $telemetry->routingDecisions,
        );
    }

    /**
     * @return void
     */
    public function testTelemetryEmitsReplicaFailureOnFallback(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $primary->method('query')->willReturn(false);

        $replica = $this->createMock(ConnectionInterface::class);
        $replica->method('query')->willThrowException(new PDOException('replica connection lost'));

        $clock = new FixedClock(1_000_000);
        $resolver = new RouteResolver($clock);
        $sampler = new ReplicaLagSampler(10, $clock);
        $sampler->recordSample('r1', 0.1);

        $telemetry = $this->makeRecordingTelemetry();
        $config = new ResolverConfig(fallbackToPrimary: true);

        $routing = new ReplicaRoutingConnection(
            primary: $primary,
            replicas: ['r1' => $replica],
            config: $config,
            resolver: $resolver,
            sessionWindow: new SessionConsistencyWindow($clock),
            lagSampler: $sampler,
            telemetry: $telemetry,
        );

        $routing->query('SELECT 1');

        // First decision: replica/allowed; then fallback to primary/replica_failure.
        $this->assertSame(
            [['replica', 'allowed'], ['primary', 'replica_failure']],
            $telemetry->routingDecisions,
        );
    }

    /**
     * @return object{routingDecisions: array<int, array{0: string, 1: string}>}
     */
    private function makeRecordingTelemetry(): object
    {
        return new class implements TelemetryInterface {
            /** @var array<int, array{0: string, 1: string}> */
            public array $routingDecisions = [];

            #[\Override]
            public function startQuerySpan(string $sql, string $callingMethod): SpanInterface
            {
                return new NoOpSpan($sql, $callingMethod, microtime(true));
            }

            #[\Override]
            public function endQuerySpan(object $span, float $durationSeconds, ?Throwable $error = null): void
            {
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
                $this->routingDecisions[] = [$decision, $reason];
            }

            #[\Override]
            public function recordIdentityGeneration(string $strategy, float $microseconds): void
            {
            }
        };
    }
}
