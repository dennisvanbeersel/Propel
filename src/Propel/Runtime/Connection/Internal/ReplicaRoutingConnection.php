<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Internal;

use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\Exception\ReplicaLagExceededException;
use Propel\Runtime\Connection\Routing\ReplicaLagSampler;
use Propel\Runtime\Connection\Routing\ResolverConfig;
use Propel\Runtime\Connection\Routing\RouteRequest;
use Propel\Runtime\Connection\Routing\RouteResolver;
use Propel\Runtime\Connection\Routing\RoutingDecision;
use Propel\Runtime\Connection\Routing\SessionConsistencyWindow;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

/**
 * Replica-routing decorator: routes per-query to primary or a replica
 * based on operation classification, the per-query hint, the
 * session-consistency window, and replica-lag samples.
 *
 * Phase E §6.4 — the only NEW capability group in Phase E. Wraps
 * the primary connection AND a map of named replica connections; on
 * each call:
 *
 *   1. Classify the operation as 'read' or 'write' (leading-keyword
 *      heuristic — Phase F's tokenizer-based parser will replace).
 *   2. Build a `RouteRequest` from the classification + current hint
 *      + session window + cached lag samples.
 *   3. Ask the `RouteResolver` for a decision.
 *   4. Dispatch to the chosen target.
 *   5. On `\Throwable` from a replica, fall back to primary if
 *      `fallbackToPrimary` is enabled (default).
 *   6. After a write, mark the session-consistency window.
 *
 * Composition order: outermost-of-all when active (umbrella §2.1) —
 * each target's full inner chain (TX → Logging → Caching → Profiling)
 * is constructed once by the factory and shared by name.
 *
 * @internal Phase E §6.4 — Tier 3.
 *
 * @psalm-suppress UnusedClass instantiated by `ConnectionFactory` when
 *                 replicas are configured.
 */
final class ReplicaRoutingConnection extends AbstractConnectionDecorator
{
    /**
     * @var \Propel\Runtime\Connection\ConnectionInterface Primary chain.
     */
    private ConnectionInterface $primary;

    /**
     * @var array<string, \Propel\Runtime\Connection\ConnectionInterface> Replicas keyed by name.
     */
    private array $replicas;

    /**
     * @var \Propel\Runtime\Connection\Routing\RouteResolver
     */
    private RouteResolver $resolver;

    /**
     * @var \Propel\Runtime\Connection\Routing\ResolverConfig
     */
    private ResolverConfig $config;

    /**
     * @var \Propel\Runtime\Connection\Routing\SessionConsistencyWindow
     */
    private SessionConsistencyWindow $sessionWindow;

    /**
     * @var \Propel\Runtime\Connection\Routing\ReplicaLagSampler
     */
    private ReplicaLagSampler $lagSampler;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var string Hint to apply to the next query; reset after dispatch.
     */
    private string $nextHint = RouteRequest::HINT_AUTO;

    /**
     * @var \Propel\Runtime\Connection\Routing\RoutingDecision|null Last routing decision (introspection).
     */
    private ?RoutingDecision $lastDecision = null;

    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface $primary
     * @param array<string, \Propel\Runtime\Connection\ConnectionInterface> $replicas
     * @param \Propel\Runtime\Connection\Routing\ResolverConfig|null $config
     * @param \Propel\Runtime\Connection\Routing\RouteResolver|null $resolver
     * @param \Propel\Runtime\Connection\Routing\SessionConsistencyWindow|null $sessionWindow
     * @param \Propel\Runtime\Connection\Routing\ReplicaLagSampler|null $lagSampler
     * @param \Psr\Log\LoggerInterface|null $logger
     */
    public function __construct(
        ConnectionInterface $primary,
        array $replicas = [],
        ?ResolverConfig $config = null,
        ?RouteResolver $resolver = null,
        ?SessionConsistencyWindow $sessionWindow = null,
        ?ReplicaLagSampler $lagSampler = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($primary);
        $this->primary = $primary;
        $this->replicas = $replicas;
        $this->config = $config ?? new ResolverConfig();
        $this->resolver = $resolver ?? new RouteResolver();
        $this->sessionWindow = $sessionWindow ?? new SessionConsistencyWindow();
        $this->lagSampler = $lagSampler ?? new ReplicaLagSampler();
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Sets the per-query routing hint. Cleared after the next dispatch.
     *
     * @psalm-api
     *
     * @param string $hint One of HINT_FORCE_PRIMARY/HINT_ALLOW_REPLICA/HINT_AUTO.
     *
     * @return void
     */
    public function setNextQueryHint(string $hint): void
    {
        $this->nextHint = $hint;
    }

    /**
     * @psalm-api
     *
     * @return \Propel\Runtime\Connection\Routing\RoutingDecision|null
     */
    public function getLastDecision(): ?RoutingDecision
    {
        return $this->lastDecision;
    }

    /**
     * @psalm-api
     *
     * @return \Propel\Runtime\Connection\Routing\SessionConsistencyWindow
     */
    public function getSessionWindow(): SessionConsistencyWindow
    {
        return $this->sessionWindow;
    }

    /**
     * @psalm-api
     *
     * @return \Propel\Runtime\Connection\Routing\ReplicaLagSampler
     */
    public function getLagSampler(): ReplicaLagSampler
    {
        return $this->lagSampler;
    }

    /**
     * @param string $statement
     *
     * @return int
     */
    #[\Override]
    public function exec(string $statement): int
    {
        $operation = $this->classifyOperation($statement);
        $target = $this->resolveTarget($statement, $operation);
        try {
            $result = $target->exec($statement);
        } catch (Throwable $e) {
            $result = $this->maybeFallback($e, $operation, fn (ConnectionInterface $c): int => $c->exec($statement));
        }
        $this->finalize($operation);

        return $result;
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
        $operation = $this->classifyOperation($statement);
        $target = $this->resolveTarget($statement, $operation);
        try {
            $result = $target->prepare($statement, $driverOptions);
        } catch (Throwable $e) {
            $result = $this->maybeFallback(
                $e,
                $operation,
                fn (ConnectionInterface $c) => $c->prepare($statement, $driverOptions),
            );
        }
        $this->finalize($operation);

        return $result;
    }

    /**
     * @param string $statement
     *
     * @return \Propel\Runtime\DataFetcher\DataFetcherInterface|\PDOStatement|false
     */
    #[\Override]
    public function query(string $statement)
    {
        $operation = $this->classifyOperation($statement);
        $target = $this->resolveTarget($statement, $operation);
        try {
            $result = $target->query($statement);
        } catch (Throwable $e) {
            $result = $this->maybeFallback($e, $operation, fn (ConnectionInterface $c) => $c->query($statement));
        }
        $this->finalize($operation);

        return $result;
    }

    /**
     * @return bool
     */
    #[\Override]
    public function beginTransaction(): bool
    {
        $this->sessionWindow->noteWrite();

        return $this->primary->beginTransaction();
    }

    /**
     * @return bool
     */
    #[\Override]
    public function commit(): bool
    {
        $this->sessionWindow->noteWrite();

        return $this->primary->commit();
    }

    /**
     * @return bool
     */
    #[\Override]
    public function rollBack(): bool
    {
        return $this->primary->rollBack();
    }

    /**
     * Resolves the target connection for a given operation. May throw
     * {@see ReplicaLagExceededException} when the consumer explicitly
     * opted out of primary fallback AND no replica is healthy.
     *
     * @param string $sql
     * @param string $operation
     *
     * @throws \Propel\Runtime\Connection\Exception\ReplicaLagExceededException
     *
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    private function resolveTarget(string $sql, string $operation): ConnectionInterface
    {
        $request = new RouteRequest(
            operation: $operation,
            hint: $this->nextHint,
            sessionLastWriteAtMicros: $this->sessionWindow->getLastWriteAtMicros(),
            replicaLagSamples: $this->lagSampler->getSamples(),
        );
        $decision = $this->resolver->resolve($request, $this->config);
        $this->lastDecision = $decision;
        $this->logger->info(
            sprintf('routing: target=%s, %s', $decision->target, $decision->rationale),
        );

        if ($decision->isPrimary()) {
            // Special case: consumer asked for replica AND fallback is off AND
            // resolver picked primary because no replica was eligible (lag).
            if (
                $this->nextHint === RouteRequest::HINT_ALLOW_REPLICA
                && !$this->config->fallbackToPrimary
                && str_contains($decision->rationale, 'all-replicas-lagging')
            ) {
                throw new ReplicaLagExceededException(
                    'allow-replica hint set, no eligible replica, fallbackToPrimary=false',
                );
            }

            return $this->primary;
        }

        $name = $decision->replicaName();
        if ($name === null || !isset($this->replicas[$name])) {
            return $this->primary;
        }

        return $this->replicas[$name];
    }

    /**
     * Handles a `\Throwable` from a replica: falls back to primary
     * when fallback is enabled; rethrows otherwise.
     *
     * @template T
     *
     * @param \Throwable $e
     * @param string $operation
     * @param callable(\Propel\Runtime\Connection\ConnectionInterface): T $fn
     *
     * @throws \Throwable Rethrown when fallback is disabled.
     *
     * @return T
     */
    private function maybeFallback(Throwable $e, string $operation, callable $fn)
    {
        $previousDecision = $this->lastDecision;
        if (!$this->config->fallbackToPrimary || ($previousDecision !== null && $previousDecision->isPrimary())) {
            throw $e;
        }

        $previousTarget = $previousDecision !== null ? $previousDecision->target : 'unknown';
        $previousMicros = $previousDecision !== null ? $previousDecision->routedAtMicros : 0;

        $this->logger->warning(
            sprintf(
                'routing: fallback target=primary, fallback-from=%s, error=%s',
                $previousTarget,
                $e->getMessage(),
            ),
        );
        $this->lastDecision = new RoutingDecision(
            RoutingDecision::TARGET_PRIMARY,
            sprintf(
                'reason=fallback, fallback-from=%s, error-class=%s',
                $previousTarget,
                $e::class,
            ),
            $previousMicros,
        );

        return $fn($this->primary);
    }

    /**
     * Post-dispatch bookkeeping: clear the per-query hint; for write
     * operations, note the write timestamp.
     *
     * @param string $operation
     *
     * @return void
     */
    private function finalize(string $operation): void
    {
        if ($operation === RouteRequest::OPERATION_WRITE) {
            $this->sessionWindow->noteWrite();
        }
        $this->nextHint = RouteRequest::HINT_AUTO;
    }

    /**
     * Heuristic SQL classifier. Leading-keyword scan plus a bounded
     * embedded-write check (`DELETE`/`UPDATE`/`INSERT` / `MERGE` after
     * a CTE prefix). Phase F's tokenizer will replace.
     *
     * @param string $sql
     *
     * @return string Either OPERATION_READ or OPERATION_WRITE.
     */
    public function classifyOperation(string $sql): string
    {
        $trimmed = ltrim($sql);
        $upperPrefix = strtoupper(substr($trimmed, 0, 16));

        if (
            str_starts_with($upperPrefix, 'INSERT')
            || str_starts_with($upperPrefix, 'UPDATE')
            || str_starts_with($upperPrefix, 'DELETE')
            || str_starts_with($upperPrefix, 'MERGE')
            || str_starts_with($upperPrefix, 'REPLACE')
            || str_starts_with($upperPrefix, 'TRUNCATE')
            || str_starts_with($upperPrefix, 'CALL')
            || str_starts_with($upperPrefix, 'CREATE')
            || str_starts_with($upperPrefix, 'DROP')
            || str_starts_with($upperPrefix, 'ALTER')
        ) {
            return RouteRequest::OPERATION_WRITE;
        }

        if (str_starts_with($upperPrefix, 'WITH')) {
            // Bounded embedded-write check on the first 256 chars after WITH.
            $window = strtoupper(substr($trimmed, 0, 256));
            foreach (['DELETE', 'UPDATE', 'INSERT', 'MERGE'] as $verb) {
                if (str_contains($window, $verb)) {
                    return RouteRequest::OPERATION_WRITE;
                }
            }
        }

        return RouteRequest::OPERATION_READ;
    }
}
