<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Telemetry\Prometheus;

use PHPUnit\Framework\TestCase;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\InMemory;
use Propel\Runtime\Telemetry\Prometheus\PrometheusRegistry;
use Propel\Runtime\Telemetry\Prometheus\PrometheusSpan;
use Propel\Runtime\Telemetry\Prometheus\PrometheusTelemetry;
use Propel\Runtime\Telemetry\SpanInterface;

/**
 * Integration test for {@see PrometheusTelemetry}.
 *
 * Skipped if `promphp/prometheus_client_php` is not installed.
 */
class PrometheusTelemetryTest extends TestCase
{
    /**
     * @return void
     */
    #[\Override]
    protected function setUp(): void
    {
        if (!class_exists(CollectorRegistry::class)) {
            $this->markTestSkipped(
                'promphp/prometheus_client_php not installed; '
                . 'run `composer require promphp/prometheus_client_php`.',
            );
        }
    }

    /**
     * @return \Prometheus\CollectorRegistry
     */
    private function makeRegistry(): CollectorRegistry
    {
        return new CollectorRegistry(new InMemory(), false);
    }

    /**
     * @return void
     */
    public function testStartQuerySpanReturnsPrometheusSpan(): void
    {
        $telemetry = new PrometheusTelemetry($this->makeRegistry());
        $span = $telemetry->startQuerySpan('SELECT 1', 'query');

        $this->assertInstanceOf(SpanInterface::class, $span);
        $this->assertInstanceOf(PrometheusSpan::class, $span);
        $this->assertSame('SELECT 1', $span->getSql());
        $this->assertSame('query', $span->getCallingMethod());
    }

    /**
     * @return void
     */
    public function testEndQuerySpanObservesDurationOnHistogram(): void
    {
        $registry = $this->makeRegistry();
        $telemetry = new PrometheusTelemetry($registry);

        $span = $telemetry->startQuerySpan('SELECT 1', 'query');
        $telemetry->endQuerySpan($span, 0.005);

        $samples = $registry->getMetricFamilySamples();
        $found = false;
        foreach ($samples as $family) {
            if ($family->getName() === 'propel_query_duration_seconds') {
                $found = true;
                $this->assertSame('histogram', $family->getType());
                break;
            }
        }
        $this->assertTrue($found, 'propel_query_duration_seconds histogram should be exported');
    }

    /**
     * @return void
     */
    public function testEndQuerySpanIgnoresForeignSpan(): void
    {
        $registry = $this->makeRegistry();
        $telemetry = new PrometheusTelemetry($registry);

        $foreign = new \Propel\Runtime\Telemetry\NoOpSpan('???', 'unknown', microtime(true));
        $telemetry->endQuerySpan($foreign, 0.0);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @return void
     */
    public function testRecordPreparedCacheHitIncrementsRightCounter(): void
    {
        $registry = $this->makeRegistry();
        $telemetry = new PrometheusTelemetry($registry);

        $telemetry->recordPreparedCacheHit(true);
        $telemetry->recordPreparedCacheHit(true);
        $telemetry->recordPreparedCacheHit(false);

        $samples = $this->familyByName($registry, 'propel_prepared_statement_cache_hits_total');
        $this->assertNotNull($samples);
        $this->assertSame(2.0, (float)$samples->getSamples()[0]->getValue());

        $samples = $this->familyByName($registry, 'propel_prepared_statement_cache_misses_total');
        $this->assertNotNull($samples);
        $this->assertSame(1.0, (float)$samples->getSamples()[0]->getValue());
    }

    /**
     * @return void
     */
    public function testRecordTransactionDepthSetsGauge(): void
    {
        $registry = $this->makeRegistry();
        $telemetry = new PrometheusTelemetry($registry);

        $telemetry->recordTransactionDepth(3);

        $family = $this->familyByName($registry, 'propel_transaction_depth');
        $this->assertNotNull($family);
        $this->assertSame('gauge', $family->getType());
        $this->assertSame(3.0, (float)$family->getSamples()[0]->getValue());
    }

    /**
     * @return void
     */
    public function testRecordReplicaRoutingDecisionLabels(): void
    {
        $registry = $this->makeRegistry();
        $telemetry = new PrometheusTelemetry($registry);

        $telemetry->recordReplicaRoutingDecision('replica', 'allowed');
        $telemetry->recordReplicaRoutingDecision('primary', 'session_consistency');
        $telemetry->recordReplicaRoutingDecision('replica', 'allowed');

        $family = $this->familyByName($registry, 'propel_replica_routing_total');
        $this->assertNotNull($family);
        $this->assertSame(['decision', 'reason'], $family->getLabelNames());
        $valuesByLabels = [];
        foreach ($family->getSamples() as $sample) {
            $valuesByLabels[implode('|', $sample->getLabelValues())] = (float)$sample->getValue();
        }
        $this->assertSame(2.0, $valuesByLabels['replica|allowed']);
        $this->assertSame(1.0, $valuesByLabels['primary|session_consistency']);
    }

    /**
     * @return void
     */
    public function testRecordHydrationDurationConvertsMicrosecondsToSeconds(): void
    {
        $registry = $this->makeRegistry();
        $telemetry = new PrometheusTelemetry($registry);

        $telemetry->recordHydrationDuration('Foo\\Bar', 12_500.0); // 12.5ms = 0.0125s

        $family = $this->familyByName($registry, 'propel_hydration_duration_seconds');
        $this->assertNotNull($family);
        $this->assertSame('histogram', $family->getType());
        $this->assertSame(['class'], $family->getLabelNames());
    }

    /**
     * @return void
     */
    public function testRecordIdentityGenerationLabelStrategy(): void
    {
        $registry = $this->makeRegistry();
        $telemetry = new PrometheusTelemetry($registry);

        $telemetry->recordIdentityGeneration('native', 250.0);
        $telemetry->recordIdentityGeneration('sequence', 1500.0);

        $family = $this->familyByName($registry, 'propel_identity_generation_duration_seconds');
        $this->assertNotNull($family);
        $this->assertSame('histogram', $family->getType());
        $this->assertSame(['strategy'], $family->getLabelNames());
    }

    /**
     * @return void
     */
    public function testGetMetricsExposesRegistry(): void
    {
        $registry = $this->makeRegistry();
        $telemetry = new PrometheusTelemetry($registry);

        $this->assertInstanceOf(PrometheusRegistry::class, $telemetry->getMetrics());
    }

    /**
     * @return void
     */
    public function testCustomBucketsArePropagated(): void
    {
        $registry = $this->makeRegistry();
        // Capacity for the constructor to accept custom buckets without error.
        $telemetry = new PrometheusTelemetry($registry, [0.5, 1.0, 5.0]);
        $span = $telemetry->startQuerySpan('SELECT 1', 'query');
        $telemetry->endQuerySpan($span, 0.6);

        $family = $this->familyByName($registry, 'propel_query_duration_seconds');
        $this->assertNotNull($family);
    }

    /**
     * @param \Prometheus\CollectorRegistry $registry
     * @param string $name
     *
     * @return \Prometheus\MetricFamilySamples|null
     */
    private function familyByName(CollectorRegistry $registry, string $name): ?\Prometheus\MetricFamilySamples
    {
        foreach ($registry->getMetricFamilySamples() as $family) {
            if ($family->getName() === $name) {
                return $family;
            }
        }

        return null;
    }
}
