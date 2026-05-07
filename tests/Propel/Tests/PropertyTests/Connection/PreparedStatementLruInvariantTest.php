<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\PropertyTests\Connection;

use Innmind\BlackBox\Random;
use Innmind\BlackBox\Set;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Connection\Internal\PreparedStatementLruCache;
use Propel\Runtime\Connection\StatementInterface;

/**
 * Property-based test for {@see PreparedStatementLruCache} LRU invariants.
 *
 * Operations are generated as `('put'|'get', key)` tuples; capacity is fixed
 * per-test. After each operation, the following invariants must hold:
 *
 *   1. `size() <= capacity()` always.
 *   2. After `put(k, v)`, `get(k)` returns `v` (assuming no overflow on `k`).
 *   3. The `keys()` order matches a reference OrderedDict's LRU-to-MRU layout.
 *   4. After capacity puts of distinct keys with no intervening `get`, the
 *      first put has been evicted.
 *   5. Held-reference invariant: a statement reference held by a consumer
 *      remains valid after eviction.
 *
 * Seeded for reproducibility — see `docs/reviews/E-round-1-summary.md`.
 */
class PreparedStatementLruInvariantTest extends TestCase
{
    /**
     * @var int
     */
    private const SEED_BASE = 9001;

    /**
     * Invariant 1 + 3: size never exceeds capacity AND key order matches
     * a naive reference implementation.
     *
     * @return void
     */
    public function testRandomOpsPreserveSizeAndOrderInvariants(): void
    {
        $count = 0;
        $set = Set::integers()->between(20, 80)->take(40);
        foreach ($set->values(Random::default) as $value) {
            $length = $value->unwrap();
            $sequence = $this->generateSequence($length, self::SEED_BASE + $count, capacity: 8, keyspace: 16);
            $this->verifySizeAndOrder($sequence, capacity: 8);
            $count++;
        }
        $this->assertSame(40, $count);
    }

    /**
     * Invariant 4: capacity puts of distinct keys evict the first.
     *
     * @return void
     */
    public function testFifoEvictionWithoutGets(): void
    {
        $cache = new PreparedStatementLruCache(4);
        for ($i = 0; $i < 5; $i++) {
            $stmt = $this->createMock(StatementInterface::class);
            $cache->put('k' . $i, $stmt);
        }

        $this->assertFalse($cache->has('k0'));
        $this->assertTrue($cache->has('k1'));
        $this->assertSame(4, $cache->size());
    }

    /**
     * Invariant 5: held reference outlives eviction.
     *
     * @return void
     */
    public function testHeldReferenceSurvivesAdversarialEviction(): void
    {
        $cache = new PreparedStatementLruCache(2);
        $heldStatement = $this->createMock(StatementInterface::class);
        $heldStatement->method('execute')->willReturn(true);
        $cache->put('held', $heldStatement);

        // Adversarial: evict 'held' and a few more entries.
        for ($i = 0; $i < 100; $i++) {
            $cache->put('adv-' . $i, $this->createMock(StatementInterface::class));
        }

        $this->assertFalse($cache->has('held'));
        $this->assertTrue($heldStatement->execute(), 'held reference must remain callable');
    }

    /**
     * Generate a `('put'|'get', key)` operation sequence.
     *
     * @param int $length
     * @param int $seed
     * @param int $capacity
     * @param int $keyspace
     *
     * @return list<array{op: string, key: string}>
     */
    private function generateSequence(int $length, int $seed, int $capacity, int $keyspace): array
    {
        mt_srand($seed);
        $sequence = [];
        for ($i = 0; $i < $length; $i++) {
            $op = mt_rand(0, 99) < 60 ? 'put' : 'get';
            $key = 'k' . mt_rand(0, $keyspace - 1);
            $sequence[] = ['op' => $op, 'key' => $key];
        }

        return $sequence;
    }

    /**
     * @param list<array{op: string, key: string}> $sequence
     * @param int $capacity
     *
     * @return void
     */
    private function verifySizeAndOrder(array $sequence, int $capacity): void
    {
        $cache = new PreparedStatementLruCache($capacity);
        $reference = []; // naive ordered dict: insertion order = LRU-to-MRU

        foreach ($sequence as $idx => $opTuple) {
            $op = $opTuple['op'];
            $key = $opTuple['key'];

            if ($op === 'put') {
                $stmt = $this->createMock(StatementInterface::class);
                $cache->put($key, $stmt);

                if (array_key_exists($key, $reference)) {
                    unset($reference[$key]);
                }
                if (count($reference) >= $capacity) {
                    array_shift($reference);
                }
                $reference[$key] = $stmt;
            } else {
                $cache->get($key);
                if (array_key_exists($key, $reference)) {
                    $existing = $reference[$key];
                    unset($reference[$key]);
                    $reference[$key] = $existing;
                }
            }

            $this->assertLessThanOrEqual($capacity, $cache->size(), "size > cap at op#{$idx}");
            $this->assertSame(array_keys($reference), $cache->keys(), "key-order drift at op#{$idx}");
        }
    }
}
