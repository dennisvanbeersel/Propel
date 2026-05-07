<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Internal;

use Propel\Runtime\ActiveQuery\Compiler\PreparedStatementKey;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Decorator owning the bounded prepared-statement LRU cache.
 *
 * Phase E §6.2 risk #4: replaces `ConnectionWrapper::cachedPreparedStatements`
 * (`:89`, `:407–414`, `:583`) with a {@see PreparedStatementLruCache}
 * bounded by capacity. Long-running CLI workers can no longer exhaust
 * memory through unique-prepare proliferation.
 *
 * Cache-key derivation preserves Phase A bug-fix #4 — `$driverOptions`
 * are part of the key so two prepares of the same SQL with different
 * fetch-mode options yield distinct entries.
 *
 * Composition order (umbrella §2.1): `CachingConnection` is INSIDE
 * `LoggingConnection` so cache hit/miss is observable, and INSIDE
 * `TransactionalConnection` so the cache state is independent of
 * tx accounting.
 *
 * Re-entrancy: `prepare()` is single-shot — even if an inner driver
 * triggers a recursive prepare during eviction, the LRU cache treats
 * each `put()` as atomic from the PHP-VM-step perspective. The cache's
 * `unset()`/`array_shift()` precede the new entry insertion; a held
 * statement reference outside the cache survives via PHP refcounting
 * (formal proof in tests/PropertyTests/Connection/PreparedStatementLruInvariantTest).
 *
 * @internal Phase E §2.1 — Tier 3.
 *
 * @psalm-suppress UnusedClass instantiated by `ConnectionFactory`
 *                 (Task E.7) + `ConnectionWrapper` BC shim (Task E.8).
 */
final class CachingConnection extends AbstractConnectionDecorator
{
    /**
     * @var \Propel\Runtime\Connection\Internal\PreparedStatementLruCache
     */
    private PreparedStatementLruCache $cache;

    /**
     * @var bool
     */
    private bool $enabled = true;

    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface $inner
     * @param \Propel\Runtime\Connection\Internal\PreparedStatementLruCache|null $cache
     */
    public function __construct(
        ConnectionInterface $inner,
        ?PreparedStatementLruCache $cache = null
    ) {
        parent::__construct($inner);
        $this->cache = $cache ?? new PreparedStatementLruCache();
    }

    /**
     * Returns the underlying LRU cache for telemetry / introspection.
     *
     * @psalm-api
     *
     * @return \Propel\Runtime\Connection\Internal\PreparedStatementLruCache
     */
    public function getCache(): PreparedStatementLruCache
    {
        return $this->cache;
    }

    /**
     * Toggles statement caching on/off (Tier 2 BC: ConnectionWrapper-style).
     *
     * @psalm-api
     *
     * @param bool $enabled
     *
     * @return void
     */
    public function setCachePreparedStatements(bool $enabled): void
    {
        $this->enabled = $enabled;
        if (!$enabled) {
            $this->cache->clear();
        }
    }

    /**
     * @psalm-api
     *
     * @return bool
     */
    public function isCachePreparedStatements(): bool
    {
        return $this->enabled;
    }

    /**
     * Drops every cache entry (BC: matches ConnectionWrapper::clearStatementCache).
     *
     * @psalm-api
     *
     * @return void
     */
    public function clearStatementCache(): void
    {
        $this->cache->clear();
    }

    /**
     * Stable cache key including driver options (Phase A bug-fix #4 preserved).
     *
     * Phase F.3: delegates to {@see PreparedStatementKey::forSql()} — same
     * shape, published as a Tier 2 SPI so third-party decorators that build
     * their own caches can reuse the canonical key derivation.
     *
     * @param string $sql
     * @param array<int|string, mixed> $driverOptions
     *
     * @return string
     */
    public static function buildCacheKey(string $sql, array $driverOptions): string
    {
        return PreparedStatementKey::forSql($sql, $driverOptions);
    }

    /**
     * @param string $statement
     * @param array<int|string, mixed> $driverOptions
     *
     * @return \Propel\Runtime\Connection\StatementInterface|\PDOStatement|false
     */
    #[\Override]
    public function prepare(string $statement, array $driverOptions = [])
    {
        if (!$this->enabled) {
            return $this->inner->prepare($statement, $driverOptions);
        }

        $key = self::buildCacheKey($statement, $driverOptions);
        $cached = $this->cache->get($key);
        if ($cached !== null) {
            return $cached;
        }

        $stmt = $this->inner->prepare($statement, $driverOptions);
        if ($stmt !== false) {
            $this->cache->put($key, $stmt);
        }

        return $stmt;
    }
}
