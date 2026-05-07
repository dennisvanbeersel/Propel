<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Telemetry\Otel;

use OpenTelemetry\API\Trace\SpanInterface as OtelSdkSpan;
use Propel\Runtime\Telemetry\SpanInterface;

/**
 * Span returned by {@see OtelTelemetry::startQuerySpan()}.
 *
 * Wraps an OpenTelemetry SDK span ({@see \OpenTelemetry\API\Trace\SpanInterface}).
 * The wrapped instance is exposed via {@see self::getSdkSpan()} so the
 * adapter's `endQuerySpan()` can call `setStatus()` / `recordException()` /
 * `end()` on it.
 *
 * Phase I §I.2.1: this class file loads cleanly even when the OTEL SDK
 * is absent — the `use` import is resolved lazily by PHP's autoloader,
 * and instantiation only happens via {@see OtelTelemetry::startQuerySpan()},
 * which gates on `class_exists(OtelSdkSpan::class)` in its constructor.
 *
 * @internal Phase I §I.2.1 — Tier 3.
 */
final readonly class OtelSpan implements SpanInterface
{
    /**
     * @param string $sql
     * @param string $callingMethod
     * @param float $startedAt
     * @param \OpenTelemetry\API\Trace\SpanInterface $sdkSpan
     */
    public function __construct(
        private string $sql,
        private string $callingMethod,
        private float $startedAt,
        private OtelSdkSpan $sdkSpan
    ) {
    }

    /**
     * @return string
     */
    #[\Override]
    public function getSql(): string
    {
        return $this->sql;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getCallingMethod(): string
    {
        return $this->callingMethod;
    }

    /**
     * @return float
     */
    #[\Override]
    public function getStartedAt(): float
    {
        return $this->startedAt;
    }

    /**
     * @return \OpenTelemetry\API\Trace\SpanInterface
     */
    public function getSdkSpan(): OtelSdkSpan
    {
        return $this->sdkSpan;
    }
}
