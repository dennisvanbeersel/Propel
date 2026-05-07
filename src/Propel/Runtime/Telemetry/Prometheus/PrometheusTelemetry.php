<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Telemetry\Prometheus;

use Prometheus\CollectorRegistry;
use Propel\Runtime\Telemetry\SpanInterface;
use Propel\Runtime\Telemetry\TelemetryInterface;
use RuntimeException;
use Throwable;

/**
 * Prometheus adapter for {@see TelemetryInterface}.
 *
 * Phase I §I.3.2: ships dormant unless `promphp/prometheus_client_php`
 * is installed. Construction fails fast with a {@see RuntimeException}
 * when the client lib is absent.
 *
 * Prometheus has no span concept, so {@see self::startQuerySpan()}
 * captures `microtime(true)` and {@see self::endQuerySpan()} observes
 * the elapsed duration onto the `propel_query_duration_seconds`
 * histogram. All other event-recording methods map directly to
 * {@see PrometheusRegistry} counters/gauges/histograms.
 *
 * Hydration + identity-generation durations arrive in microseconds
 * and are converted to seconds before observation (histogram unit
 * declared as seconds, the Prometheus convention).
 *
 * @api Tier 2 — instantiable when `promphp/prometheus_client_php` ^2.0 is installed.
 */
final class PrometheusTelemetry implements TelemetryInterface
{
    /**
     * @var \Propel\Runtime\Telemetry\Prometheus\PrometheusRegistry
     */
    private PrometheusRegistry $metrics;

    /**
     * @param \Prometheus\CollectorRegistry $registry
     * @param array<int, float>|null $bucketsSeconds Histogram bucket upper-bounds. NULL for defaults.
     *
     * @throws \RuntimeException When `promphp/prometheus_client_php` is not installed.
     */
    public function __construct(
        CollectorRegistry $registry,
        ?array $bucketsSeconds = null
    ) {
        if (!class_exists(CollectorRegistry::class)) {
            throw new RuntimeException(
                'Cannot instantiate PrometheusTelemetry: promphp/prometheus_client_php is not installed. '
                . 'Run `composer require promphp/prometheus_client_php` to enable this adapter.',
            );
        }

        $this->metrics = new PrometheusRegistry($registry, $bucketsSeconds);
    }

    /**
     * @return \Propel\Runtime\Telemetry\Prometheus\PrometheusRegistry
     */
    public function getMetrics(): PrometheusRegistry
    {
        return $this->metrics;
    }

    /**
     * @param string $sql
     * @param string $callingMethod
     *
     * @return \Propel\Runtime\Telemetry\SpanInterface
     */
    #[\Override]
    public function startQuerySpan(string $sql, string $callingMethod): SpanInterface
    {
        return new PrometheusSpan($sql, $callingMethod, microtime(true));
    }

    /**
     * @param object $span
     * @param float $durationSeconds
     * @param \Throwable|null $error Unused — Prometheus does not have an error-status concept on
     *                               histograms; consumers who need error/success bifurcation
     *                               should add a dedicated counter.
     *
     * @return void
     */
    #[\Override]
    public function endQuerySpan(object $span, float $durationSeconds, ?Throwable $error = null): void
    {
        if (!$span instanceof PrometheusSpan) {
            return;
        }

        $this->metrics->getQueryDuration()->observe($durationSeconds, [$span->getCallingMethod()]);
    }

    /**
     * @param bool $hit
     *
     * @return void
     */
    #[\Override]
    public function recordPreparedCacheHit(bool $hit): void
    {
        if ($hit) {
            $this->metrics->getCacheHits()->inc();
        } else {
            $this->metrics->getCacheMisses()->inc();
        }
    }

    /**
     * @param int $depth
     *
     * @return void
     */
    #[\Override]
    public function recordTransactionDepth(int $depth): void
    {
        $this->metrics->getTransactionDepth()->set($depth);
    }

    /**
     * @param string $class
     * @param float $microseconds
     *
     * @return void
     */
    #[\Override]
    public function recordHydrationDuration(string $class, float $microseconds): void
    {
        $this->metrics->getHydrationDuration()->observe($microseconds / 1_000_000.0, [$class]);
    }

    /**
     * @param string $decision
     * @param string $reason
     *
     * @return void
     */
    #[\Override]
    public function recordReplicaRoutingDecision(string $decision, string $reason): void
    {
        $this->metrics->getReplicaRouting()->incBy(1, [$decision, $reason]);
    }

    /**
     * @param string $strategy
     * @param float $microseconds
     *
     * @return void
     */
    #[\Override]
    public function recordIdentityGeneration(string $strategy, float $microseconds): void
    {
        $this->metrics->getIdentityGenerationDuration()->observe($microseconds / 1_000_000.0, [$strategy]);
    }
}
