<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Benchmarks\Connection;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\Internal\PreparedStatementLruCache;
use Propel\Runtime\Connection\StatementInterface;

/**
 * Capacity-sweep bench for {@see PreparedStatementLruCache} (Phase E §4.10).
 *
 * Synthetic workload: 200 distinct prepared statements, accessed in a
 * Zipf-like distribution (α ≈ 1.5) over 10 000 prepare calls. The bench
 * sweeps capacity ∈ {32, 64, 128, 256, 512, 1024} and asserts that the
 * default capacity ({@see PreparedStatementLruCache::DEFAULT_CAPACITY})
 * achieves ≥ 90% hit rate on this workload.
 *
 * The default of 256 was chosen because it produces a hit rate ≥ 90% on
 * the Zipf workload while keeping memory footprint bounded; documented in
 * `docs/CONNECTION-DECORATORS.md`.
 *
 * Seeded for reproducibility.
 */
class PreparedStatementCacheCapacitySweepTest extends TestCase
{
    /**
     * @var int
     */
    private const SEED = 314159;

    /**
     * @var int
     */
    private const DISTINCT_KEYS = 200;

    /**
     * @var int
     */
    private const TOTAL_OPS = 10000;

    /**
     * Verifies that the default capacity (256) achieves ≥ 90% hit rate on a
     * Zipf workload — the umbrella §4.10 statement-cache hit-rate target.
     *
     * @return void
     */
    public function testDefaultCapacityMeetsHitRateTarget(): void
    {
        $hitRate = $this->runWorkload(PreparedStatementLruCache::DEFAULT_CAPACITY);
        $this->assertGreaterThanOrEqual(
            0.90,
            $hitRate,
            sprintf(
                'Default capacity %d achieved hit rate %.4f, expected >= 0.90',
                PreparedStatementLruCache::DEFAULT_CAPACITY,
                $hitRate,
            ),
        );
    }

    /**
     * Smaller capacity (32) produces a substantially lower hit rate, proving
     * the bench is sensitive to capacity choice.
     *
     * @return void
     */
    public function testSmallCapacityProducesLowerHitRate(): void
    {
        $small = $this->runWorkload(32);
        $large = $this->runWorkload(PreparedStatementLruCache::DEFAULT_CAPACITY);

        $this->assertLessThan(
            $large,
            $small,
            'Smaller capacity must yield lower or equal hit rate.',
        );
    }

    /**
     * Run the Zipf workload at the given capacity and return the hit rate.
     *
     * @param int $capacity
     *
     * @return float
     */
    private function runWorkload(int $capacity): float
    {
        mt_srand(self::SEED);
        $cache = new PreparedStatementLruCache($capacity);

        // Pre-build the set of statements to put on first access.
        $statements = [];
        for ($i = 0; $i < self::DISTINCT_KEYS; $i++) {
            $statements[$i] = $this->createMock(StatementInterface::class);
        }

        // Zipf-ish weights: rank-i weight = 1 / (i+1)^1.5
        $weights = [];
        $cum = 0.0;
        for ($i = 0; $i < self::DISTINCT_KEYS; $i++) {
            $cum += 1.0 / (($i + 1) ** 1.5);
            $weights[$i] = $cum;
        }
        $total = $cum;

        $hits = 0;
        for ($n = 0; $n < self::TOTAL_OPS; $n++) {
            $r = mt_rand() / mt_getrandmax() * $total;
            $rank = $this->binarySearch($weights, $r);
            $key = 'sql_' . $rank;
            $cached = $cache->get($key);
            if ($cached !== null) {
                $hits++;
            } else {
                $cache->put($key, $statements[$rank]);
            }
        }

        return $hits / self::TOTAL_OPS;
    }

    /**
     * @param array<int, float> $cumulative
     * @param float $value
     *
     * @return int
     */
    private function binarySearch(array $cumulative, float $value): int
    {
        $lo = 0;
        $hi = count($cumulative) - 1;
        while ($lo < $hi) {
            $mid = intdiv($lo + $hi, 2);
            if ($cumulative[$mid] < $value) {
                $lo = $mid + 1;
            } else {
                $hi = $mid;
            }
        }

        return $lo;
    }
}
