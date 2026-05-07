<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection\Routing;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\Routing\ResolverConfig;
use Propel\Runtime\Connection\Routing\RouteRequest;
use Propel\Runtime\Connection\Routing\RouteResolver;
use Propel\Runtime\Connection\Routing\RoutingDecision;

/**
 * Pure-logic tests for {@see RouteResolver}: every branch of the
 * decision precedence covered by a dedicated test method.
 */
class RouteResolverTest extends TestCase
{
    /**
     * @return void
     */
    public function testWriteOpAlwaysRoutesToPrimary(): void
    {
        $resolver = new RouteResolver(new FixedClock(1_000_000));
        $request = new RouteRequest(
            RouteRequest::OPERATION_WRITE,
            RouteRequest::HINT_ALLOW_REPLICA,
            null,
            ['r1' => 0.0],
        );
        $decision = $resolver->resolve($request, new ResolverConfig());

        $this->assertTrue($decision->isPrimary());
        $this->assertStringContainsString('write-op', $decision->rationale);
    }

    /**
     * @return void
     */
    public function testForcePrimaryHintRoutesToPrimary(): void
    {
        $resolver = new RouteResolver(new FixedClock(1_000_000));
        $request = new RouteRequest(
            RouteRequest::OPERATION_READ,
            RouteRequest::HINT_FORCE_PRIMARY,
            null,
            ['r1' => 0.0],
        );
        $decision = $resolver->resolve($request, new ResolverConfig());

        $this->assertTrue($decision->isPrimary());
        $this->assertStringContainsString('hint-force-primary', $decision->rationale);
    }

    /**
     * @return void
     */
    public function testActiveSessionWindowRoutesToPrimary(): void
    {
        $clock = new FixedClock(2_000_000); // now = 2s
        $resolver = new RouteResolver($clock);
        $request = new RouteRequest(
            RouteRequest::OPERATION_READ,
            RouteRequest::HINT_AUTO,
            sessionLastWriteAtMicros: 1_000_000, // last write 1s ago
            replicaLagSamples: ['r1' => 0.0],
        );
        $decision = $resolver->resolve($request, new ResolverConfig(sessionConsistencyWindowSeconds: 5.0));

        $this->assertTrue($decision->isPrimary());
        $this->assertStringContainsString('session-consistency-window', $decision->rationale);
    }

    /**
     * @return void
     */
    public function testExpiredSessionWindowAllowsReplicaRouting(): void
    {
        $clock = new FixedClock(10_000_000); // now = 10s
        $resolver = new RouteResolver($clock);
        $request = new RouteRequest(
            RouteRequest::OPERATION_READ,
            RouteRequest::HINT_AUTO,
            sessionLastWriteAtMicros: 1_000_000, // 9s ago — well past 5s window
            replicaLagSamples: ['r1' => 0.5],
        );
        $decision = $resolver->resolve($request, new ResolverConfig(sessionConsistencyWindowSeconds: 5.0));

        $this->assertSame('replica:r1', $decision->target);
        $this->assertStringContainsString('allow-replica', $decision->rationale);
    }

    /**
     * @return void
     */
    public function testAllReplicasLaggingFallsBackToPrimary(): void
    {
        $resolver = new RouteResolver(new FixedClock(0));
        $request = new RouteRequest(
            RouteRequest::OPERATION_READ,
            RouteRequest::HINT_AUTO,
            null,
            ['r1' => 5.0, 'r2' => 10.0],
        );
        $decision = $resolver->resolve($request, new ResolverConfig(replicaLagThresholdSeconds: 2.0));

        $this->assertTrue($decision->isPrimary());
        $this->assertStringContainsString('all-replicas-lagging', $decision->rationale);
    }

    /**
     * @return void
     */
    public function testHealthyReplicaSelectedFromMultiple(): void
    {
        $resolver = new RouteResolver(new FixedClock(0));
        $request = new RouteRequest(
            RouteRequest::OPERATION_READ,
            RouteRequest::HINT_AUTO,
            null,
            ['r-slow' => 5.0, 'r-fast' => 0.1],
        );
        $decision = $resolver->resolve($request, new ResolverConfig(replicaLagThresholdSeconds: 2.0));

        $this->assertSame('replica:r-fast', $decision->target);
    }

    /**
     * @return void
     */
    public function testNoReplicasFallsBackToPrimary(): void
    {
        $resolver = new RouteResolver(new FixedClock(0));
        $request = new RouteRequest(
            RouteRequest::OPERATION_READ,
            RouteRequest::HINT_AUTO,
            null,
            [],
        );
        $decision = $resolver->resolve($request, new ResolverConfig());

        $this->assertTrue($decision->isPrimary());
    }

    /**
     * @return void
     */
    public function testRoutingDecisionTargetReplicaNameRoundTrip(): void
    {
        $decision = new RoutingDecision('replica:bookstore-r2', 'reason=test', 0);
        $this->assertSame('bookstore-r2', $decision->replicaName());
        $this->assertFalse($decision->isPrimary());
    }
}
