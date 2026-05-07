<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\ChaosTests\Connection;

use PDOException;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\Exception\ReplicaLagExceededException;
use Propel\Runtime\Connection\Internal\ReplicaRoutingConnection;
use Propel\Runtime\Connection\PdoConnection;
use Propel\Runtime\Connection\Routing\ReplicaLagSampler;
use Propel\Runtime\Connection\Routing\ResolverConfig;
use Propel\Runtime\Connection\Routing\RouteRequest;

/**
 * Phase E chaos test: replica connection refuses on every call. When
 * `fallbackToPrimary` is true (default), every read must succeed via
 * the primary fallback. When fallback is disabled, the call must
 * surface as `ReplicaLagExceededException` or `PDOException`.
 */
class ReplicaFailoverTest extends TestCase
{
    /**
     * @return void
     */
    public function testFallbackEnabledServesAllReadsFromPrimary(): void
    {
        $primary = new PdoConnection('sqlite::memory:');
        $primary->exec('CREATE TABLE t_failover (id INTEGER PRIMARY KEY, v TEXT)');
        $primary->exec("INSERT INTO t_failover (v) VALUES ('alive')");

        $replica = $this->makeAlwaysFailingReplica();
        $sampler = new ReplicaLagSampler();
        $sampler->recordSample('r1', 0.1); // healthy lag, but call will throw

        $routing = new ReplicaRoutingConnection(
            primary: $primary,
            replicas: ['r1' => $replica],
            config: new ResolverConfig(fallbackToPrimary: true),
            lagSampler: $sampler,
        );

        for ($i = 0; $i < 10; $i++) {
            $stmt = $routing->query('SELECT v FROM t_failover');
            $this->assertNotFalse($stmt);
        }
        // Every successful query falls back, so lastDecision is primary
        // with rationale containing 'fallback-from=replica:r1'.
        $rationale = $routing->getLastDecision()?->rationale ?? '';
        $this->assertStringContainsString('fallback-from=replica:r1', $rationale);
    }

    /**
     * @return void
     */
    public function testFallbackDisabledOnAllowReplicaWithLagPropagatesException(): void
    {
        $primary = $this->createMock(ConnectionInterface::class);
        $replica = $this->makeAlwaysFailingReplica();
        $sampler = new ReplicaLagSampler();
        $sampler->recordSample('r1', 5.0); // beyond threshold

        $routing = new ReplicaRoutingConnection(
            primary: $primary,
            replicas: ['r1' => $replica],
            config: new ResolverConfig(replicaLagThresholdSeconds: 1.0, fallbackToPrimary: false),
            lagSampler: $sampler,
        );
        $routing->setNextQueryHint(RouteRequest::HINT_ALLOW_REPLICA);

        $this->expectException(ReplicaLagExceededException::class);
        $routing->query('SELECT 1');
    }

    /**
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    private function makeAlwaysFailingReplica(): ConnectionInterface
    {
        $replica = $this->createMock(ConnectionInterface::class);
        $replica->method('exec')->willThrowException(new PDOException('replica refused'));
        $replica->method('prepare')->willThrowException(new PDOException('replica refused'));
        $replica->method('query')->willThrowException(new PDOException('replica refused'));

        return $replica;
    }
}
