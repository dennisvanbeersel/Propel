<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Telemetry\Otel;

use OpenTelemetry\API\Metrics\CounterInterface;
use OpenTelemetry\API\Metrics\HistogramInterface;
use OpenTelemetry\API\Metrics\MeterProviderInterface;
use OpenTelemetry\API\Metrics\UpDownCounterInterface;
use OpenTelemetry\API\Trace\SpanInterface as OtelSdkSpan;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\API\Trace\TracerInterface;
use OpenTelemetry\API\Trace\TracerProviderInterface;
use Propel\Runtime\Telemetry\SpanInterface;
use Propel\Runtime\Telemetry\TelemetryInterface;
use RuntimeException;
use Throwable;

/**
 * OpenTelemetry adapter for {@see TelemetryInterface}.
 *
 * Phase I §I.2.2: ships dormant unless `open-telemetry/sdk` is installed.
 * Construction fails fast with an actionable {@see RuntimeException} when
 * the SDK is absent.
 *
 * Span semantics (W3C trace-context conventions):
 *   - span name: `propel.{$callingMethod}` (`propel.exec`, `propel.prepare`,
 *     `propel.query`, etc).
 *   - `db.system` attribute: `'propel'` (consumers MAY override post-hoc).
 *   - `db.statement` attribute: the SQL text.
 *   - `db.operation` attribute: the calling method.
 *   - `SpanKind::KIND_CLIENT` — Propel is the DB client.
 *   - On error: `setStatus(StatusCode::STATUS_ERROR, $msg)` + `recordException()`.
 *
 * Metric semantics:
 *   - `propel.prepared_statement_cache.hits` (counter, hit=true)
 *   - `propel.prepared_statement_cache.misses` (counter, hit=false)
 *   - `propel.transactions.depth` (up-down-counter; emits delta vs last call)
 *   - `propel.hydration.duration` (histogram, ms; class label)
 *   - `propel.replica.routing` (counter; decision + reason labels)
 *   - `propel.identity.generation.duration` (histogram, ms; strategy label)
 *
 * @api Tier 2 — instantiable when `open-telemetry/sdk` ^1.0 is installed.
 */
final class OtelTelemetry implements TelemetryInterface
{
    /**
     * @var \OpenTelemetry\API\Trace\TracerInterface
     */
    private TracerInterface $tracer;

    /**
     * @var \OpenTelemetry\API\Metrics\CounterInterface
     */
    private CounterInterface $cacheHits;

    /**
     * @var \OpenTelemetry\API\Metrics\CounterInterface
     */
    private CounterInterface $cacheMisses;

    /**
     * @var \OpenTelemetry\API\Metrics\UpDownCounterInterface
     */
    private UpDownCounterInterface $txDepth;

    /**
     * @var \OpenTelemetry\API\Metrics\HistogramInterface
     */
    private HistogramInterface $hydrationDuration;

    /**
     * @var \OpenTelemetry\API\Metrics\CounterInterface
     */
    private CounterInterface $replicaRouting;

    /**
     * @var \OpenTelemetry\API\Metrics\HistogramInterface
     */
    private HistogramInterface $identityGenerationDuration;

    /**
     * Last reported tx depth — used to compute the delta for the up-down-counter.
     */
    private int $lastTxDepth = 0;

    /**
     * @param \OpenTelemetry\API\Trace\TracerProviderInterface $tracerProvider
     * @param \OpenTelemetry\API\Metrics\MeterProviderInterface $meterProvider
     * @param string $instrumentationScope Defaults to `'io.propel'`.
     *
     * @throws \RuntimeException when the OpenTelemetry SDK is not installed.
     */
    public function __construct(
        TracerProviderInterface $tracerProvider,
        MeterProviderInterface $meterProvider,
        string $instrumentationScope = 'io.propel'
    ) {
        if (!interface_exists(OtelSdkSpan::class)) {
            throw new RuntimeException(
                'Cannot instantiate OtelTelemetry: open-telemetry/sdk is not installed. '
                . 'Run `composer require open-telemetry/sdk` to enable this adapter.',
            );
        }

        $this->tracer = $tracerProvider->getTracer($instrumentationScope);
        $meter = $meterProvider->getMeter($instrumentationScope);

        $this->cacheHits = $meter->createCounter(
            'propel.prepared_statement_cache.hits',
            '{hit}',
            'Number of prepared-statement cache hits.',
        );
        $this->cacheMisses = $meter->createCounter(
            'propel.prepared_statement_cache.misses',
            '{miss}',
            'Number of prepared-statement cache misses.',
        );
        $this->txDepth = $meter->createUpDownCounter(
            'propel.transactions.depth',
            '{tx}',
            'Current nested-transaction depth.',
        );
        $this->hydrationDuration = $meter->createHistogram(
            'propel.hydration.duration',
            'ms',
            'ActiveRecord row->object hydration duration.',
        );
        $this->replicaRouting = $meter->createCounter(
            'propel.replica.routing',
            '{decision}',
            'Replica vs primary routing decisions.',
        );
        $this->identityGenerationDuration = $meter->createHistogram(
            'propel.identity.generation.duration',
            'ms',
            'Identity-generation strategy call duration.',
        );
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
        $sdkSpan = $this->tracer->spanBuilder('propel.' . $callingMethod)
            ->setSpanKind(SpanKind::KIND_CLIENT)
            ->setAttribute('db.system', 'propel')
            ->setAttribute('db.statement', $sql)
            ->setAttribute('db.operation', $callingMethod)
            ->startSpan();

        return new OtelSpan($sql, $callingMethod, microtime(true), $sdkSpan);
    }

    /**
     * @param object $span
     * @param float $durationSeconds
     * @param \Throwable|null $error
     *
     * @return void
     */
    #[\Override]
    public function endQuerySpan(object $span, float $durationSeconds, ?Throwable $error = null): void
    {
        if (!$span instanceof OtelSpan) {
            return;
        }

        $sdkSpan = $span->getSdkSpan();
        if ($error !== null) {
            $sdkSpan->setStatus(StatusCode::STATUS_ERROR, $error->getMessage());
            $sdkSpan->recordException($error);
        }
        $sdkSpan->end();
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
            $this->cacheHits->add(1);
        } else {
            $this->cacheMisses->add(1);
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
        $delta = $depth - $this->lastTxDepth;
        if ($delta !== 0) {
            $this->txDepth->add($delta);
        }
        $this->lastTxDepth = $depth;
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
        // Convert to milliseconds (histogram unit declared as 'ms').
        $this->hydrationDuration->record($microseconds / 1000.0, ['class' => $class]);
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
        $this->replicaRouting->add(1, ['decision' => $decision, 'reason' => $reason]);
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
        $this->identityGenerationDuration->record($microseconds / 1000.0, ['strategy' => $strategy]);
    }
}
