<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection;

use Propel\Runtime\Adapter\AdapterInterface;
use Propel\Runtime\Adapter\Exception\AdapterException;
use Propel\Runtime\Connection\Exception\ConnectionDecoratorException;
use Propel\Runtime\Connection\Exception\ConnectionException;
use Propel\Runtime\Connection\Internal\CachingConnection;
use Propel\Runtime\Connection\Internal\LoggingConnection;
use Propel\Runtime\Connection\Internal\PreparedStatementLruCache;
use Propel\Runtime\Connection\Internal\ProfilingConnection;
use Propel\Runtime\Connection\Internal\ReplicaRoutingConnection;
use Propel\Runtime\Connection\Internal\TransactionalConnection;
use Propel\Runtime\Connection\Routing\ResolverConfig;
use Propel\Runtime\Exception\InvalidArgumentException;

/**
 * Composes Propel connections from configuration.
 *
 * Phase E §2.1: when `connection.decorators` (or the BC `useProfilerConnection`
 * static, or a `replicas` block) is present, the factory builds the canonical
 * decorator chain instead of returning a bare `ConnectionWrapper`. The chain
 * order is:
 *
 *   PdoConnection
 *     ← TransactionalConnection
 *       ← LoggingConnection
 *         ← CachingConnection
 *           ← ProfilingConnection (optional)
 *             ← ReplicaRoutingConnection (optional, when replicas are configured)
 *
 * The factory rejects out-of-canonical-order decorator lists with
 * {@see ConnectionDecoratorException}.
 *
 * Legacy path: when `decorators` is unset AND no replicas configured AND
 * `useProfilerConnection` is false, the factory returns the legacy
 * `ConnectionWrapper` (now a thin BC shim — see Task E.8).
 */
class ConnectionFactory
{
    /**
     * @var string
     */
    public const DEFAULT_CONNECTION_CLASS = '\Propel\Runtime\Connection\ConnectionWrapper';

    /**
     * Canonical inner-to-outer decorator order.
     *
     * @var array<int, string>
     */
    private const CANONICAL_ORDER = ['transactional', 'logging', 'caching', 'profiling'];

    /**
     * If true, ConnectionFactory will inject `'profiling'` into the chain
     * (BC fallback when no explicit `decorators` list is configured).
     *
     * BC behavior preserved through the 3.x line; explicit
     * `connection.decorators` is the recommended path. Removal targeted
     * for 4.0; the static is intentionally NOT yet `@deprecated` so consumer
     * code does not produce phpstan noise.
     */
    public static bool $useProfilerConnection = false;

    /**
     * Open a database connection based on a configuration.
     *
     * @param array<string, mixed> $configuration
     * @param \Propel\Runtime\Adapter\AdapterInterface $adapter
     * @param string $defaultConnectionClass
     *
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    public static function create(
        array $configuration,
        AdapterInterface $adapter,
        string $defaultConnectionClass = self::DEFAULT_CONNECTION_CLASS
    ): ConnectionInterface {
        // A non-empty decorator list composes the explicit decorator chain. An empty list
        // (the default) routes to the ConnectionWrapper legacy path, which is the ORM-ready
        // connection: query()/prepare() yield DataFetcher/StatementWrapper instances and debug
        // logging works. The decorator chain is opt-in because, layered over a bare PdoConnection,
        // it does not provide those ORM-facing semantics and the caching decorator is not
        // SQLite-safe. A truly bare PdoConnection remains available via `classname`.
        $hasExplicitDecorators = isset($configuration['decorators']) && is_array($configuration['decorators']) && $configuration['decorators'] !== [];
        $hasReplicas = isset($configuration['replicas']) && is_array($configuration['replicas']) && $configuration['replicas'] !== [];

        if ($hasExplicitDecorators || $hasReplicas) {
            return self::createDecoratorChain($configuration, $adapter, $hasReplicas);
        }

        return self::createLegacyWrapper($configuration, $adapter, $defaultConnectionClass);
    }

    /**
     * Constructs the canonical decorator chain over a fresh PDO connection.
     *
     * @param array<string, mixed> $configuration
     * @param \Propel\Runtime\Adapter\AdapterInterface $adapter
     * @param bool $hasReplicas
     *
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    private static function createDecoratorChain(
        array $configuration,
        AdapterInterface $adapter,
        bool $hasReplicas
    ): ConnectionInterface {
        $decorators = self::resolveDecoratorList($configuration);
        self::validateOrder($decorators);

        $primaryChain = self::buildChainOver(
            self::openInner($configuration, $adapter),
            $decorators,
            $configuration,
        );

        if (!$hasReplicas) {
            return $primaryChain;
        }

        $replicaChains = [];
        /** @var array<string, array<string, mixed>> $replicaConfigs */
        $replicaConfigs = $configuration['replicas'];
        foreach ($replicaConfigs as $name => $replicaConfig) {
            $replicaConfig = array_merge($configuration, $replicaConfig);
            $replicaChains[$name] = self::buildChainOver(
                self::openInner($replicaConfig, $adapter),
                $decorators,
                $replicaConfig,
            );
        }

        $routingConfig = $configuration['routing'] ?? [];

        return new ReplicaRoutingConnection(
            primary: $primaryChain,
            replicas: $replicaChains,
            config: new ResolverConfig(
                sessionConsistencyWindowSeconds: (float)($routingConfig['sessionConsistencyWindowSeconds'] ?? 5.0),
                replicaLagThresholdSeconds: (float)($routingConfig['replicaLagThresholdSeconds'] ?? 2.0),
                fallbackToPrimary: (bool)($routingConfig['fallbackToPrimary'] ?? true),
            ),
        );
    }

    /**
     * Resolves the list of decorator names, accounting for the legacy
     * `useProfilerConnection` static.
     *
     * @param array<string, mixed> $configuration
     *
     * @return array<int, string>
     */
    private static function resolveDecoratorList(array $configuration): array
    {
        if (isset($configuration['decorators']) && is_array($configuration['decorators'])) {
            /** @var array<int, string> $list */
            $list = array_values($configuration['decorators']);

            return $list;
        }

        $defaults = ['transactional', 'logging', 'caching'];
        if (static::$useProfilerConnection) {
            $defaults[] = 'profiling';
        }

        return $defaults;
    }

    /**
     * Asserts the supplied decorator list respects the canonical order
     * (innermost-first). Throws {@see ConnectionDecoratorException} on
     * any out-of-order pair.
     *
     * @param array<int, string> $decorators
     *
     * @throws \Propel\Runtime\Connection\Exception\ConnectionDecoratorException
     *
     * @return void
     */
    private static function validateOrder(array $decorators): void
    {
        $rank = array_flip(self::CANONICAL_ORDER);
        $previousRank = -1;
        foreach ($decorators as $name) {
            if (!array_key_exists($name, $rank)) {
                throw new ConnectionDecoratorException(sprintf(
                    'Unknown connection decorator "%s". Allowed: %s.',
                    $name,
                    implode(', ', self::CANONICAL_ORDER),
                ));
            }
            $r = $rank[$name];
            if ($r < $previousRank) {
                throw new ConnectionDecoratorException(sprintf(
                    'Decorator "%s" violates canonical inner-to-outer order; expected one of: %s. See docs/CONNECTION-DECORATORS.md.',
                    $name,
                    implode(', ', self::CANONICAL_ORDER),
                ));
            }
            $previousRank = $r;
        }
    }

    /**
     * Builds the chain inner-to-outer over `$inner`.
     *
     * @param \Propel\Runtime\Connection\ConnectionInterface $inner
     * @param array<int, string> $decorators
     * @param array<string, mixed> $configuration
     *
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    private static function buildChainOver(
        ConnectionInterface $inner,
        array $decorators,
        array $configuration
    ): ConnectionInterface {
        $current = $inner;
        foreach ($decorators as $name) {
            $current = match ($name) {
                'transactional' => new TransactionalConnection($current),
                'logging' => new LoggingConnection($current),
                'caching' => new CachingConnection(
                    $current,
                    new PreparedStatementLruCache(
                        (int)($configuration['preparedStatementCacheCapacity'] ?? PreparedStatementLruCache::DEFAULT_CAPACITY),
                    ),
                ),
                'profiling' => new ProfilingConnection($current),
                default => $current,
            };
        }

        return $current;
    }

    /**
     * Opens the bare adapter-supplied PDO connection.
     *
     * @param array<string, mixed> $configuration
     * @param \Propel\Runtime\Adapter\AdapterInterface $adapter
     *
     * @throws \Propel\Runtime\Connection\Exception\ConnectionException
     *
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    private static function openInner(array $configuration, AdapterInterface $adapter): ConnectionInterface
    {
        try {
            return $adapter->getConnection($configuration);
        } catch (AdapterException $e) {
            throw new ConnectionException('Unable to open connection', 0, $e);
        }
    }

    /**
     * Legacy-shim path: returns the configured `ConnectionWrapper` (or
     * `ProfilerConnectionWrapper` when the static is enabled).
     *
     * @param array<string, mixed> $configuration
     * @param \Propel\Runtime\Adapter\AdapterInterface $adapter
     * @param string $defaultConnectionClass
     *
     * @throws \Propel\Runtime\Connection\Exception\ConnectionException
     * @throws \Propel\Runtime\Exception\InvalidArgumentException
     *
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    private static function createLegacyWrapper(
        array $configuration,
        AdapterInterface $adapter,
        string $defaultConnectionClass
    ): ConnectionInterface {
        if (static::$useProfilerConnection) {
            $connectionClass = ProfilerConnectionWrapper::class;
        } elseif (isset($configuration['classname']) && is_string($configuration['classname'])) {
            $connectionClass = $configuration['classname'];
        } else {
            $connectionClass = $defaultConnectionClass;
        }
        try {
            $adapterConnection = $adapter->getConnection($configuration);
        } catch (AdapterException $e) {
            throw new ConnectionException('Unable to open connection', 0, $e);
        }
        /** @var \Propel\Runtime\Connection\ConnectionInterface $connection */
        $connection = new $connectionClass($adapterConnection);

        if (isset($configuration['attributes']) && is_array($configuration['attributes'])) {
            foreach ($configuration['attributes'] as $option => $value) {
                if (is_string($value) && strpos($value, '::') !== false) {
                    if (!defined($value)) {
                        throw new InvalidArgumentException(sprintf(
                            'Invalid class constant specified "%s" while processing connection attributes for datasource "%s"',
                            $value,
                            $connection->getName(),
                        ));
                    }
                    $value = constant($value);
                }
                $connection->setAttribute($option, $value);
            }
        }

        return $connection;
    }
}
