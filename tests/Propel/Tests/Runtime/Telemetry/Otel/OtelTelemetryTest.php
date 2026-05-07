<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Telemetry\Otel;

use OpenTelemetry\API\Trace\TracerProviderInterface as TracerProviderInterfaceContract;
use OpenTelemetry\SDK\Metrics\MeterProviderBuilder;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\Telemetry\Otel\OtelSpan;
use Propel\Runtime\Telemetry\Otel\OtelTelemetry;
use Propel\Runtime\Telemetry\SpanInterface;
use RuntimeException;

/**
 * Integration test for {@see OtelTelemetry}.
 *
 * Skipped if `open-telemetry/sdk` is not installed (signalled by absence of
 * `TracerProvider`); otherwise runs against the SDK's in-memory exporter.
 */
class OtelTelemetryTest extends TestCase
{
    /**
     * @return void
     */
    #[\Override]
    protected function setUp(): void
    {
        if (!class_exists(TracerProvider::class)) {
            $this->markTestSkipped(
                'OpenTelemetry SDK not installed; run `composer require open-telemetry/sdk`.',
            );
        }
    }

    /**
     * @return void
     */
    public function testStartQuerySpanReturnsOtelSpan(): void
    {
        $exporter = new InMemoryExporter();
        $tracerProvider = new TracerProvider(new SimpleSpanProcessor($exporter));
        $meterProvider = (new MeterProviderBuilder())->build();

        $telemetry = new OtelTelemetry($tracerProvider, $meterProvider);
        $span = $telemetry->startQuerySpan('SELECT 1', 'query');

        $this->assertInstanceOf(SpanInterface::class, $span);
        $this->assertInstanceOf(OtelSpan::class, $span);
        $this->assertSame('SELECT 1', $span->getSql());
        $this->assertSame('query', $span->getCallingMethod());
    }

    /**
     * @return void
     */
    public function testEndQuerySpanProducesExportedSpan(): void
    {
        $exporter = new InMemoryExporter();
        $tracerProvider = new TracerProvider(new SimpleSpanProcessor($exporter));
        $meterProvider = (new MeterProviderBuilder())->build();

        $telemetry = new OtelTelemetry($tracerProvider, $meterProvider);
        $span = $telemetry->startQuerySpan('UPDATE t SET v=1 WHERE id=?', 'exec');
        $telemetry->endQuerySpan($span, 0.005);

        $tracerProvider->shutdown();
        $exported = $exporter->getSpans();
        $this->assertCount(1, $exported);
        $sdkSpan = $exported[0];
        $this->assertSame('propel.exec', $sdkSpan->getName());
        $attrs = $sdkSpan->getAttributes()->toArray();
        $this->assertSame('propel', $attrs['db.system']);
        $this->assertSame('UPDATE t SET v=1 WHERE id=?', $attrs['db.statement']);
        $this->assertSame('exec', $attrs['db.operation']);
    }

    /**
     * @return void
     */
    public function testEndQuerySpanWithErrorSetsStatusAndRecordsException(): void
    {
        $exporter = new InMemoryExporter();
        $tracerProvider = new TracerProvider(new SimpleSpanProcessor($exporter));
        $meterProvider = (new MeterProviderBuilder())->build();

        $telemetry = new OtelTelemetry($tracerProvider, $meterProvider);
        $span = $telemetry->startQuerySpan('BAD SQL', 'exec');
        $err = new RuntimeException('syntax error');
        $telemetry->endQuerySpan($span, 0.001, $err);

        $tracerProvider->shutdown();
        $exported = $exporter->getSpans();
        $this->assertCount(1, $exported);
        $sdkSpan = $exported[0];
        $this->assertSame('Error', $sdkSpan->getStatus()->getCode());
        $events = $sdkSpan->getEvents();
        $this->assertNotEmpty($events, 'recordException should produce an event');
    }

    /**
     * @return void
     */
    public function testEndQuerySpanIgnoresForeignSpanHandle(): void
    {
        $exporter = new InMemoryExporter();
        $tracerProvider = new TracerProvider(new SimpleSpanProcessor($exporter));
        $meterProvider = (new MeterProviderBuilder())->build();

        $telemetry = new OtelTelemetry($tracerProvider, $meterProvider);
        // Pass a foreign span instance that isn't an OtelSpan; should silently no-op.
        $foreign = new \Propel\Runtime\Telemetry\NoOpSpan('???', 'unknown', microtime(true));
        $telemetry->endQuerySpan($foreign, 0.0);

        $tracerProvider->shutdown();
        $this->assertCount(0, $exporter->getSpans());
    }

    /**
     * @return void
     */
    public function testRecordersDoNotThrow(): void
    {
        $exporter = new InMemoryExporter();
        $tracerProvider = new TracerProvider(new SimpleSpanProcessor($exporter));
        $meterProvider = (new MeterProviderBuilder())->build();

        $telemetry = new OtelTelemetry($tracerProvider, $meterProvider);
        $telemetry->recordPreparedCacheHit(true);
        $telemetry->recordPreparedCacheHit(false);
        $telemetry->recordTransactionDepth(0);
        $telemetry->recordTransactionDepth(1);
        $telemetry->recordTransactionDepth(2);
        $telemetry->recordTransactionDepth(0);
        $telemetry->recordHydrationDuration('Foo\\Bar', 12.5);
        $telemetry->recordReplicaRoutingDecision('replica', 'allowed');
        $telemetry->recordReplicaRoutingDecision('primary', 'session_consistency');
        $telemetry->recordIdentityGeneration('native', 0.5);

        $this->expectNotToPerformAssertions();
    }

    /**
     * @return void
     */
    public function testTracerProviderInterfaceContractIsAccepted(): void
    {
        // Ensures the constructor accepts the SDK Tracer{Provider,}Interface
        // — guards against accidental tightening to the concrete TracerProvider.
        $exporter = new InMemoryExporter();
        $tracerProvider = new TracerProvider(new SimpleSpanProcessor($exporter));
        $meterProvider = (new MeterProviderBuilder())->build();

        $this->assertInstanceOf(TracerProviderInterfaceContract::class, $tracerProvider);
        $telemetry = new OtelTelemetry($tracerProvider, $meterProvider);
        $this->assertInstanceOf(OtelTelemetry::class, $telemetry);
    }
}
