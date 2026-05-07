<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Internal;

use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\Exception\RollbackException;
use Propel\Runtime\Telemetry\NoOpTelemetry;
use Propel\Runtime\Telemetry\TelemetryInterface;
use Throwable;

/**
 * Decorator owning nested-transaction accounting.
 *
 * Verbatim port of `ConnectionWrapper`'s `$nestedTransactionCount` /
 * `$isUncommitable` semantics — only the outermost begin actually opens the
 * inner transaction; nested begins increment a counter; nested rollbacks
 * mark the transaction uncommitable so the outermost commit throws.
 *
 * Single-fiber-per-connection assumption holds (Phase J revisits worker
 * mode). PHP refcounting protects held statement references — `forceRollBack`
 * resets the counter atomically.
 *
 * @internal Phase E §2.1 — Tier 3.
 *
 * @psalm-suppress UnusedClass instantiated by `ConnectionFactory` and the
 *                 `ConnectionWrapper` BC shim in subsequent Phase-E tasks.
 */
final class TransactionalConnection extends AbstractConnectionDecorator
{
    /**
     * Counter of nested begin/commit/rollback cycles. The inner connection's
     * actual transaction is only opened when this transitions 0 → 1 and only
     * committed/rolled back when it transitions 1 → 0.
     */
    private int $nestedTransactionCount = 0;

    /**
     * Set true when an inner rollback occurred mid-nest. The outermost commit
     * throws {@see RollbackException} rather than silently swallowing the
     * partial-rollback.
     */
    private bool $isUncommitable = false;

    /**
     * @var \Propel\Runtime\Telemetry\TelemetryInterface
     */
    private TelemetryInterface $telemetry;

    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface $inner
     * @param \Propel\Runtime\Telemetry\TelemetryInterface|null $telemetry Defaults to NoOpTelemetry.
     */
    public function __construct(
        ConnectionInterface $inner,
        ?TelemetryInterface $telemetry = null
    ) {
        parent::__construct($inner);
        $this->telemetry = $telemetry ?? new NoOpTelemetry();
    }

    /**
     * @psalm-api
     *
     * @return int
     */
    #[\Override]
    public function getNestedTransactionCount(): int
    {
        return $this->nestedTransactionCount;
    }

    /**
     * @return bool
     */
    public function isInTransaction(): bool
    {
        return $this->nestedTransactionCount > 0;
    }

    /**
     * @psalm-api
     *
     * @return bool True iff inside a transaction AND no nested rollback has tainted it.
     */
    #[\Override]
    public function isCommitable(): bool
    {
        return $this->isInTransaction() && !$this->isUncommitable;
    }

    /**
     * @return bool
     */
    #[\Override]
    public function beginTransaction(): bool
    {
        $return = true;
        if ($this->nestedTransactionCount === 0) {
            $return = $this->inner->beginTransaction();
            $this->isUncommitable = false;
        }
        $this->nestedTransactionCount++;
        $this->telemetry->recordTransactionDepth($this->nestedTransactionCount);

        return $return;
    }

    /**
     * @throws \Propel\Runtime\Connection\Exception\RollbackException When the
     *         outermost commit follows a nested rollback that tainted the tx.
     *
     * @return bool
     */
    #[\Override]
    public function commit(): bool
    {
        $return = true;
        $opcount = $this->nestedTransactionCount;

        if ($opcount > 0 && $this->inner->inTransaction()) {
            if ($opcount === 1) {
                if ($this->isUncommitable) {
                    throw new RollbackException('Cannot commit because a nested transaction was rolled back');
                }

                $return = $this->inner->commit();
            }

            $this->nestedTransactionCount--;
            $this->telemetry->recordTransactionDepth($this->nestedTransactionCount);
        }

        return $return;
    }

    /**
     * @return bool
     */
    #[\Override]
    public function rollBack(): bool
    {
        $return = true;
        $opcount = $this->nestedTransactionCount;

        if ($opcount > 0 && $this->inner->inTransaction()) {
            if ($opcount === 1) {
                $return = $this->inner->rollBack();
            } else {
                $this->isUncommitable = true;
            }

            $this->nestedTransactionCount--;
            $this->telemetry->recordTransactionDepth($this->nestedTransactionCount);
        }

        return $return;
    }

    /**
     * Override AbstractConnectionDecorator's passthrough so the callable runs
     * inside our nested-tx-aware begin/commit cycle rather than the inner
     * PdoConnection's bare PDO begin/commit. Without this override, callers
     * that combine an outer ->beginTransaction() with an inner ->transaction()
     * (e.g. test setUp + AR doInsert) would fault with "active transaction".
     *
     * @param callable $callable
     *
     * @throws \Throwable Re-throws any exception the callable raises.
     *
     * @return mixed
     */
    #[\Override]
    public function transaction(callable $callable)
    {
        $this->beginTransaction();

        try {
            $result = $callable();

            $this->commit();

            return $result;
        } catch (Throwable $e) {
            $this->rollBack();

            throw $e;
        }
    }

    /**
     * Rollback the whole transaction regardless of nesting level. Resets
     * `$nestedTransactionCount` to 0.
     *
     * @psalm-api
     *
     * @return bool
     */
    public function forceRollBack(): bool
    {
        $return = true;

        if ($this->nestedTransactionCount > 0) {
            $return = $this->inner->rollBack();
            $this->nestedTransactionCount = 0;
            $this->isUncommitable = false;
            $this->telemetry->recordTransactionDepth(0);
        }

        return $return;
    }

    /**
     * Tracks the wrapper's view of "in transaction" — equivalent to
     * `$nestedTransactionCount > 0`. Mirrors the wrapper's accounting,
     * NOT the inner connection's `inTransaction()` (which only reports
     * the outermost-physical state).
     *
     * @return bool
     */
    #[\Override]
    public function inTransaction(): bool
    {
        return $this->isInTransaction();
    }
}
