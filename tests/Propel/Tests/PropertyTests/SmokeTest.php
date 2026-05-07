<?php

declare(strict_types=1);

namespace Propel\Tests\PropertyTests;

use Innmind\BlackBox\Random;
use Innmind\BlackBox\Set;
use PHPUnit\Framework\TestCase;

/**
 * Confirms innmind/black-box is wired up and the property test suite runs.
 *
 * Real PBT additions land in subsequent phases (B/B', C, F).
 */
class SmokeTest extends TestCase
{
    public function testBlackBoxSetIntegersGeneratesValues(): void
    {
        $count = 0;
        $set = Set::integers()->between(0, 1000)->take(50);
        foreach ($set->values(Random::default) as $value) {
            $this->assertGreaterThanOrEqual(0, $value->unwrap());
            $this->assertLessThanOrEqual(1000, $value->unwrap());
            $count++;
        }
        $this->assertSame(50, $count);
    }
}
