<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Internal;

use InvalidArgumentException;

/**
 * Bounded LRU cache for prepared statements (Phase E §6.2 risk #4).
 *
 * Replaces the unbounded `ConnectionWrapper::cachedPreparedStatements`
 * array with a size-bounded LRU. A long-running CLI worker that prepares
 * millions of unique queries can no longer exhaust memory.
 *
 * Implementation: PHP's native `array` preserves insertion order. `get()`
 * removes-and-reinserts the entry to bump it to MRU. `put()` evicts the
 * oldest entry on overflow via `array_shift()`.
 *
 * Concurrency model (Phase E single-fiber-per-connection): a statement
 * returned by `get()` is held by the consumer; if eviction subsequently
 * removes the cache entry, the consumer's reference still points to a
 * live `PDOStatement` (PHP refcounting). The held-reference invariant is
 * formally tested by the LRU PBT in tests/PropertyTests/Connection/.
 *
 * Re-entrancy: a {@see CachingConnection::prepare()} call that triggers an
 * eviction-during-prepare scenario (e.g., an inner `prepare()` recursing
 * through driver-level callbacks) is single-shot from the cache's
 * perspective: `put()` evicts then inserts, both atomic-from-PHP-VM-step.
 *
 * @internal Phase E §2.1 — Tier 3.
 *
 * @psalm-suppress UnusedClass instantiated by `CachingConnection`
 *                 (Task E.4.2) + `ConnectionFactory` composition.
 */
final class PreparedStatementLruCache
{
    public const int DEFAULT_CAPACITY = 256;

    /**
     * Insertion-order map: keys are SHA-256 hashes of the (sql, driverOptions)
     * cache key; values are the prepared statements. PHP `array`
     * insertion-order is the LRU ordering.
     *
     * @var array<string, \Propel\Runtime\Connection\StatementInterface|\PDOStatement|false>
     */
    private array $entries = [];

    /**
     * @var int
     */
    private readonly int $capacity;

    /**
     * @var int
     */
    private int $hits = 0;

    /**
     * @var int
     */
    private int $misses = 0;

    /**
     * @var int
     */
    private int $evictions = 0;

    /**
     * @param int $capacity Strictly positive. Use {@see DEFAULT_CAPACITY} for the standard size.
     *
     * @throws \InvalidArgumentException if capacity < 1.
     */
    public function __construct(int $capacity = self::DEFAULT_CAPACITY)
    {
        if ($capacity < 1) {
            throw new InvalidArgumentException(sprintf(
                'PreparedStatementLruCache capacity must be ≥ 1; got %d.',
                $capacity,
            ));
        }
        $this->capacity = $capacity;
    }

    /**
     * Returns the cached statement for `$key`, bumping it to MRU position.
     *
     * @param string $key
     *
     * @return \Propel\Runtime\Connection\StatementInterface|\PDOStatement|false|null Null on miss.
     */
    public function get(string $key)
    {
        if (!array_key_exists($key, $this->entries)) {
            $this->misses++;

            return null;
        }

        $value = $this->entries[$key];
        unset($this->entries[$key]);
        $this->entries[$key] = $value;
        $this->hits++;

        return $value;
    }

    /**
     * Stores `$statement` under `$key`, evicting the LRU entry if at capacity.
     *
     * @param string $key
     * @param \Propel\Runtime\Connection\StatementInterface|\PDOStatement|false $statement
     *
     * @return void
     */
    public function put(string $key, $statement): void
    {
        if (array_key_exists($key, $this->entries)) {
            unset($this->entries[$key]);
            $this->entries[$key] = $statement;

            return;
        }

        if (count($this->entries) >= $this->capacity) {
            array_shift($this->entries);
            $this->evictions++;
        }

        $this->entries[$key] = $statement;
    }

    /**
     * @psalm-api
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return array_key_exists($key, $this->entries);
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->entries = [];
    }

    /**
     * @psalm-api
     *
     * @return int
     */
    public function size(): int
    {
        return count($this->entries);
    }

    /**
     * @psalm-api
     *
     * @return int
     */
    public function capacity(): int
    {
        return $this->capacity;
    }

    /**
     * @psalm-api
     *
     * @return int
     */
    public function hits(): int
    {
        return $this->hits;
    }

    /**
     * @psalm-api
     *
     * @return int
     */
    public function misses(): int
    {
        return $this->misses;
    }

    /**
     * @psalm-api
     *
     * @return int
     */
    public function evictions(): int
    {
        return $this->evictions;
    }

    /**
     * Test/telemetry-only: returns keys ordered LRU-first to MRU-last.
     *
     * @psalm-api
     *
     * @return array<int, string>
     */
    public function keys(): array
    {
        return array_keys($this->entries);
    }
}
