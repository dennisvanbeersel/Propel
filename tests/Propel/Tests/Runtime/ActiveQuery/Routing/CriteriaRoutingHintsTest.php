<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\ActiveQuery\Routing;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Connection\Routing\RouteRequest;

/**
 * Tier 1 additive surface: `Criteria::forcePrimary()` /
 * `Criteria::allowReplica()` / `Criteria::getRoutingHint()`.
 */
class CriteriaRoutingHintsTest extends TestCase
{
    /**
     * @return void
     */
    public function testDefaultHintIsAuto(): void
    {
        $c = new Criteria();
        $this->assertSame(RouteRequest::HINT_AUTO, $c->getRoutingHint());
    }

    /**
     * @return void
     */
    public function testForcePrimarySetsHintAndReturnsStatic(): void
    {
        $c = new Criteria();
        $result = $c->forcePrimary();
        $this->assertSame($c, $result);
        $this->assertSame(RouteRequest::HINT_FORCE_PRIMARY, $c->getRoutingHint());
    }

    /**
     * @return void
     */
    public function testAllowReplicaSetsHintAndReturnsStatic(): void
    {
        $c = new Criteria();
        $result = $c->allowReplica();
        $this->assertSame($c, $result);
        $this->assertSame(RouteRequest::HINT_ALLOW_REPLICA, $c->getRoutingHint());
    }
}
