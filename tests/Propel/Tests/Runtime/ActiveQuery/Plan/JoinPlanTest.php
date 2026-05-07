<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\ActiveQuery\Plan;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Join;
use Propel\Runtime\ActiveQuery\Plan\JoinPlan;

class JoinPlanTest extends TestCase
{
    public function testStartsEmpty(): void
    {
        $plan = new JoinPlan();
        self::assertSame([], $plan->getJoins());
        self::assertSame([], $plan->getAliases());
        self::assertFalse($plan->hasJoins());
        self::assertSame(0, $plan->joinCount());
    }

    public function testAddJoin(): void
    {
        $plan = new JoinPlan();
        $j1 = new Join();
        $plan->addJoin($j1);

        self::assertSame([$j1], $plan->getJoins());
        self::assertTrue($plan->hasJoins());
        self::assertSame(1, $plan->joinCount());
    }

    public function testAddAlias(): void
    {
        $plan = new JoinPlan();
        $plan->addAlias('b', 'book');
        $plan->addAlias('a', 'author');

        self::assertSame(['b' => 'book', 'a' => 'author'], $plan->getAliases());
        self::assertSame('book', $plan->getTableForAlias('b'));
        self::assertSame('author', $plan->getTableForAlias('a'));
        self::assertNull($plan->getTableForAlias('nonexistent'));
    }

    public function testRemoveAlias(): void
    {
        $plan = new JoinPlan(aliases: ['b' => 'book']);
        $plan->removeAlias('b');
        self::assertNull($plan->getTableForAlias('b'));
    }

    public function testConstructorAcceptsInitialState(): void
    {
        $j1 = new Join();
        $plan = new JoinPlan(joins: [$j1], aliases: ['x' => 'y']);
        self::assertSame([$j1], $plan->getJoins());
        self::assertSame(['x' => 'y'], $plan->getAliases());
    }
}
