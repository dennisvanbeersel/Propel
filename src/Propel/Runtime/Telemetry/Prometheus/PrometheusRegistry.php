<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Telemetry\Prometheus;

use Prometheus\CollectorRegistry;
use Prometheus\Counter;
use Prometheus\Gauge;
use Prometheus\Histogram;

/**
 * Initialises the Prometheus metric set used by {@see PrometheusTelemetry}.
 *
 * Phase I §I.3.1: defines the canonical metric names + types + buckets.
 * Centralised so the adapter and any consumer-side dashboards reference
 * the same identifiers.
 *
 * Metrics:
 *   - `propel_query_duration_seconds` (histogram, label: method)
 *   - `propel_prepared_statement_cache_hits_total` (counter)
 *   - `propel_prepared_statement_cache_misses_total` (counter)
 *   - `propel_transaction_depth` (gauge)
 *   - `propel_hydration_duration_seconds` (histogram, label: class)
 *   - `propel_replica_routing_total` (counter, labels: decision, reason)
 *   - `propel_identity_generation_duration_seconds` (histogram, label: strategy)
 *
 * Default histogram buckets — logarithmic 100µs..10s, the common
 * observability convention for DB-call latency.
 *
 * @internal Phase I §I.3.1 — Tier 3.
 */
final class PrometheusRegistry
{
    public const string NAMESPACE = 'propel';

    /**
     * Logarithmic latency buckets in seconds, 100µs..10s.
     */
    public const array DEFAULT_BUCKETS_SECONDS = [0.0001, 0.001, 0.01, 0.1, 1.0, 10.0];

    /**
     * @var \Prometheus\Histogram
     */
    private Histogram $queryDuration;

    /**
     * @var \Prometheus\Counter
     */
    private Counter $cacheHits;

    /**
     * @var \Prometheus\Counter
     */
    private Counter $cacheMisses;

    /**
     * @var \Prometheus\Gauge
     */
    private Gauge $transactionDepth;

    /**
     * @var \Prometheus\Histogram
     */
    private Histogram $hydrationDuration;

    /**
     * @var \Prometheus\Counter
     */
    private Counter $replicaRouting;

    /**
     * @var \Prometheus\Histogram
     */
    private Histogram $identityGenerationDuration;

    /**
     * @param \Prometheus\CollectorRegistry $registry
     * @param array<int, float>|null $bucketsSeconds Histogram bucket upper-bounds.
     */
    public function __construct(
        CollectorRegistry $registry,
        ?array $bucketsSeconds = null
    ) {
        $buckets = $bucketsSeconds ?? self::DEFAULT_BUCKETS_SECONDS;

        $this->queryDuration = $registry->getOrRegisterHistogram(
            self::NAMESPACE,
            'query_duration_seconds',
            'Query execution duration in seconds.',
            ['method'],
            $buckets,
        );
        $this->cacheHits = $registry->getOrRegisterCounter(
            self::NAMESPACE,
            'prepared_statement_cache_hits_total',
            'Number of prepared-statement cache hits.',
        );
        $this->cacheMisses = $registry->getOrRegisterCounter(
            self::NAMESPACE,
            'prepared_statement_cache_misses_total',
            'Number of prepared-statement cache misses.',
        );
        $this->transactionDepth = $registry->getOrRegisterGauge(
            self::NAMESPACE,
            'transaction_depth',
            'Current nested-transaction depth.',
        );
        $this->hydrationDuration = $registry->getOrRegisterHistogram(
            self::NAMESPACE,
            'hydration_duration_seconds',
            'ActiveRecord hydration duration in seconds.',
            ['class'],
            $buckets,
        );
        $this->replicaRouting = $registry->getOrRegisterCounter(
            self::NAMESPACE,
            'replica_routing_total',
            'Replica vs primary routing decisions.',
            ['decision', 'reason'],
        );
        $this->identityGenerationDuration = $registry->getOrRegisterHistogram(
            self::NAMESPACE,
            'identity_generation_duration_seconds',
            'Identity-generation strategy call duration in seconds.',
            ['strategy'],
            $buckets,
        );
    }

    /**
     * @return \Prometheus\Histogram
     */
    public function getQueryDuration(): Histogram
    {
        return $this->queryDuration;
    }

    /**
     * @return \Prometheus\Counter
     */
    public function getCacheHits(): Counter
    {
        return $this->cacheHits;
    }

    /**
     * @return \Prometheus\Counter
     */
    public function getCacheMisses(): Counter
    {
        return $this->cacheMisses;
    }

    /**
     * @return \Prometheus\Gauge
     */
    public function getTransactionDepth(): Gauge
    {
        return $this->transactionDepth;
    }

    /**
     * @return \Prometheus\Histogram
     */
    public function getHydrationDuration(): Histogram
    {
        return $this->hydrationDuration;
    }

    /**
     * @return \Prometheus\Counter
     */
    public function getReplicaRouting(): Counter
    {
        return $this->replicaRouting;
    }

    /**
     * @return \Prometheus\Histogram
     */
    public function getIdentityGenerationDuration(): Histogram
    {
        return $this->identityGenerationDuration;
    }
}
