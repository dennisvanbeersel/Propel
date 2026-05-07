<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Benchmark;

/**
 * Minimal benchmark harness — no PHPUnit / pestphp dependency.
 * Each Bench subclass defines name() / setUp() / run() / tearDown(),
 * and tools/capture-perf-baseline.php measures hrtime + memory deltas.
 */
abstract class AbstractBench
{
    abstract public function name(): string;

    public function setUp(): void
    {
    }

    abstract public function run(): void;

    public function tearDown(): void
    {
    }

    /**
     * Number of iterations for warm-up (not counted toward timings).
     */
    public function warmupIterations(): int
    {
        return 1;
    }

    /**
     * Number of iterations whose timings are averaged.
     */
    public function measuredIterations(): int
    {
        return 5;
    }
}
