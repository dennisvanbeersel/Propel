<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Internal;

use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Telemetry\NoOpTelemetry;
use Propel\Runtime\Telemetry\TelemetryInterface;

/**
 * Decorator owning query-duration profiling + histogram emission.
 *
 * Phase E §2.1 capability: replaces the legacy
 * {@see \Propel\Runtime\Connection\ProfilerConnectionWrapper} (135 LOC)
 * + {@see \Propel\Runtime\Connection\ProfilerStatementWrapper} (75 LOC)
 * with a single decorator that emits histogram-bucketed query durations
 * to the injected {@see TelemetryInterface}.
 *
 * Composition order: outermost when present (umbrella §2.1 — sees the
 * whole-call latency including all decoration cost). Wrapped only by
 * `ReplicaRoutingConnection` when replicas are configured.
 *
 * Histogram buckets default to (0.001, 0.01, 0.1, 1.0, ∞) seconds — the
 * common observability convention. Buckets configurable via constructor.
 *
 * @internal Phase E §2.1 — Tier 3.
 *
 * @psalm-suppress UnusedClass instantiated by `ConnectionFactory`
 *                 (Task E.7) when `connection.decorators` includes 'profiling'.
 */
final class ProfilingConnection extends AbstractConnectionDecorator
{
    /**
     * Bucket upper-bounds in seconds. Final implicit +∞ bucket
     * covers anything slower than the last entry.
     */
    public const array DEFAULT_BUCKETS_SECONDS = [0.001, 0.01, 0.1, 1.0];

    /**
     * @var array<int, float>
     */
    private array $bucketsSeconds;

    /**
     * @var array<int, int> Bucket counts; one slot per bucket plus the +∞ tail.
     */
    private array $bucketCounts;

    /**
     * @var int
     */
    private int $queryCount = 0;

    /**
     * @var float Cumulative duration in seconds.
     */
    private float $totalDurationSeconds = 0.0;

    /**
     * @var \Propel\Runtime\Telemetry\TelemetryInterface
     */
    private TelemetryInterface $telemetry;

    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface $inner
     * @param \Propel\Runtime\Telemetry\TelemetryInterface|null $telemetry Defaults to NoOpTelemetry.
     * @param array<int, float>|null $bucketsSeconds Histogram bucket upper-bounds.
     */
    public function __construct(
        ConnectionInterface $inner,
        ?TelemetryInterface $telemetry = null,
        ?array $bucketsSeconds = null
    ) {
        parent::__construct($inner);
        $this->telemetry = $telemetry ?? new NoOpTelemetry();
        $this->bucketsSeconds = $bucketsSeconds ?? self::DEFAULT_BUCKETS_SECONDS;
        sort($this->bucketsSeconds);
        // +1 for the implicit +∞ tail bucket.
        $this->bucketCounts = array_fill(0, count($this->bucketsSeconds) + 1, 0);
    }

    /**
     * @return int
     */
    public function getQueryCount(): int
    {
        return $this->queryCount;
    }

    /**
     * @return float Cumulative profiled duration in seconds.
     */
    public function getTotalDurationSeconds(): float
    {
        return $this->totalDurationSeconds;
    }

    /**
     * Returns a snapshot of histogram bucket counts.
     *
     * @return array<int, int>
     */
    public function getBucketCounts(): array
    {
        return $this->bucketCounts;
    }

    /**
     * @return array<int, float>
     */
    public function getBuckets(): array
    {
        return $this->bucketsSeconds;
    }

    /**
     * @param string $statement
     *
     * @return int
     */
    #[\Override]
    public function exec(string $statement): int
    {
        $start = microtime(true);
        try {
            return $this->inner->exec($statement);
        } finally {
            $this->record(microtime(true) - $start, $statement, 'exec');
        }
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
        $start = microtime(true);
        try {
            return $this->inner->prepare($statement, $driverOptions);
        } finally {
            $this->record(microtime(true) - $start, $statement, 'prepare');
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
        $start = microtime(true);
        try {
            return $this->inner->query($statement);
        } finally {
            $this->record(microtime(true) - $start, $statement, 'query');
        }
    }

    /**
     * Records a query's duration: increments count + total + bucket counter
     * and emits a telemetry span.
     *
     * @param float $durationSeconds
     * @param string $sql
     * @param string $callingMethod
     *
     * @return void
     */
    private function record(float $durationSeconds, string $sql, string $callingMethod): void
    {
        $this->queryCount++;
        $this->totalDurationSeconds += $durationSeconds;
        $this->bucketCounts[$this->bucketIndex($durationSeconds)]++;

        $span = $this->telemetry->startQuerySpan($sql, $callingMethod);
        $this->telemetry->endQuerySpan($span, $durationSeconds);
    }

    /**
     * @param float $durationSeconds
     *
     * @return int
     */
    private function bucketIndex(float $durationSeconds): int
    {
        foreach ($this->bucketsSeconds as $idx => $upperBound) {
            if ($durationSeconds <= $upperBound) {
                return $idx;
            }
        }

        return count($this->bucketsSeconds); // +∞ tail bucket.
    }
}
