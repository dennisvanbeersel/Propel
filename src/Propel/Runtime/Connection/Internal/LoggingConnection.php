<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Internal;

use PDO;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Telemetry\NoOpTelemetry;
use Propel\Runtime\Telemetry\TelemetryInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Decorator owning PSR-3 logging + telemetry-stub hooks.
 *
 * Phase E §6.2 risk #1: replaces `ConnectionWrapper::log()`'s
 * `debug_backtrace()` walk with explicit caller-name parameters. Each
 * decorated method passes its own name as the second `log()` argument —
 * no per-query backtrace reflection.
 *
 * The legacy `ConnectionWrapper::log(string $msg)` 1-arg signature is
 * preserved on the BC shim (it still walks the backtrace and is
 * `@deprecated`); only this internal `log(string, string)` is
 * backtrace-free.
 *
 * Telemetry: each decorated call also produces `startQuerySpan()` /
 * `endQuerySpan()` on the injected `TelemetryInterface`. Default is
 * {@see NoOpTelemetry}; Phase I replaces with real adapters.
 *
 * @internal Phase E §2.1 — Tier 3.
 *
 * @psalm-suppress UnusedClass instantiated by `ConnectionFactory` and the
 *                 `ConnectionWrapper` BC shim in subsequent Phase-E tasks.
 */
final class LoggingConnection extends AbstractConnectionDecorator
{
    /**
     * The list of methods that trigger logging.
     *
     * @var array<int, string>
     */
    private array $logMethods = [
        'exec',
        'query',
        'execute',
        'prepare',
    ];

    private LoggerInterface $logger;

    private TelemetryInterface $telemetry;

    private bool $enabled;

    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface $inner
     * @param \Psr\Log\LoggerInterface|null $logger Defaults to NullLogger.
     * @param \Propel\Runtime\Telemetry\TelemetryInterface|null $telemetry Defaults to NoOpTelemetry.
     * @param bool $enabled When false, logging fast-paths past PSR-3 + telemetry calls.
     */
    public function __construct(
        ConnectionInterface $inner,
        ?LoggerInterface $logger = null,
        ?TelemetryInterface $telemetry = null,
        bool $enabled = true
    ) {
        parent::__construct($inner);
        $this->logger = $logger ?? new NullLogger();
        $this->telemetry = $telemetry ?? new NoOpTelemetry();
        $this->enabled = $enabled;
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @param array<int, string> $logMethods
     *
     * @return void
     */
    public function setLogMethods(array $logMethods): void
    {
        $this->logMethods = $logMethods;
    }

    /**
     * @return array<int, string>
     */
    public function getLogMethods(): array
    {
        return $this->logMethods;
    }

    /**
     * @param bool $enabled
     *
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Caller-passed-name version of `log()` — does NOT call `debug_backtrace()`.
     *
     * @param string $message
     * @param string $callingMethod
     *
     * @return void
     */
    public function log(string $message, string $callingMethod): void
    {
        if (!$this->enabled || $message === '' || !$this->isLogEnabledForMethod($callingMethod)) {
            return;
        }

        $this->logger->info($message);
    }

    /**
     * @param string $methodName
     *
     * @return bool
     */
    private function isLogEnabledForMethod(string $methodName): bool
    {
        return in_array($methodName, $this->logMethods, true);
    }

    /**
     * @param string $statement
     *
     * @return int
     */
    #[\Override]
    public function exec(string $statement): int
    {
        if (!$this->enabled) {
            return $this->inner->exec($statement);
        }

        $span = $this->telemetry->startQuerySpan($statement, 'exec');
        $start = microtime(true);
        try {
            $result = $this->inner->exec($statement);
            $this->log($statement, 'exec');

            return $result;
        } catch (\Throwable $e) {
            $this->telemetry->endQuerySpan($span, microtime(true) - $start, $e);

            throw $e;
        } finally {
            if (!isset($e)) {
                $this->telemetry->endQuerySpan($span, microtime(true) - $start);
            }
        }
    }

    /**
     * @param string $statement
     * @param array<int, mixed> $driverOptions
     *
     * @return \Propel\Runtime\Connection\StatementInterface|\PDOStatement|false
     */
    #[\Override]
    public function prepare(string $statement, array $driverOptions = [])
    {
        if (!$this->enabled) {
            return $this->inner->prepare($statement, $driverOptions);
        }

        $span = $this->telemetry->startQuerySpan($statement, 'prepare');
        $start = microtime(true);
        try {
            $result = $this->inner->prepare($statement, $driverOptions);
            $this->log($statement, 'prepare');

            return $result;
        } catch (\Throwable $e) {
            $this->telemetry->endQuerySpan($span, microtime(true) - $start, $e);

            throw $e;
        } finally {
            if (!isset($e)) {
                $this->telemetry->endQuerySpan($span, microtime(true) - $start);
            }
        }
    }

    /**
     * @param string $statement
     *
     * @return \Propel\Runtime\DataFetcher\DataFetcherInterface|\PDOStatement|false
     */
    #[\Override]
    public function query(string $statement)
    {
        if (!$this->enabled) {
            return $this->inner->query($statement);
        }

        $span = $this->telemetry->startQuerySpan($statement, 'query');
        $start = microtime(true);
        try {
            $result = $this->inner->query($statement);
            $this->log($statement, 'query');

            return $result;
        } catch (\Throwable $e) {
            $this->telemetry->endQuerySpan($span, microtime(true) - $start, $e);

            throw $e;
        } finally {
            if (!isset($e)) {
                $this->telemetry->endQuerySpan($span, microtime(true) - $start);
            }
        }
    }

    /**
     * @return bool
     */
    #[\Override]
    public function beginTransaction(): bool
    {
        $result = $this->inner->beginTransaction();
        if ($this->enabled) {
            $this->log('Begin transaction', 'beginTransaction');
        }

        return $result;
    }

    /**
     * @return bool
     */
    #[\Override]
    public function commit(): bool
    {
        $result = $this->inner->commit();
        if ($this->enabled) {
            $this->log('Commit transaction', 'commit');
        }

        return $result;
    }

    /**
     * @return bool
     */
    #[\Override]
    public function rollBack(): bool
    {
        $result = $this->inner->rollBack();
        if ($this->enabled) {
            $this->log('Rollback transaction', 'rollBack');
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function quote(string $string, int $parameterType = PDO::PARAM_STR): string
    {
        return $this->inner->quote($string, $parameterType);
    }
}
