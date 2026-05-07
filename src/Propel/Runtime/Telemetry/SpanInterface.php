<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Telemetry;

/**
 * Typed handle returned by {@see TelemetryInterface::startQuerySpan()}.
 *
 * Phase I §I.1.1: Phase E shipped `startQuerySpan(): object` (opaque). The
 * adapters in Phase I (`OtelTelemetry`, `PrometheusTelemetry`) need to carry
 * adapter-specific metadata (W3C trace-context, microtime start) on the
 * span handle. Promoting the return type to a typed interface lets adapters
 * extend it with their own state while keeping the SPI uniform.
 *
 * Existing callers (`LoggingConnection`, `ProfilingConnection`) treat the
 * handle as opaque, so widening from `object` to `SpanInterface` is BC-safe
 * — `SpanInterface` IS-A `object`.
 *
 * @api Tier 2 — deprecation runway required for any signature change.
 *
 * @since 3.0.0
 */
interface SpanInterface
{
    /**
     * The SQL text (or method label) the span was opened for.
     *
     * @return string
     */
    public function getSql(): string;

    /**
     * The decorator method that opened the span (`'exec'`, `'prepare'`, `'query'`,
     * `'beginTransaction'`, etc.).
     *
     * @return string
     */
    public function getCallingMethod(): string;

    /**
     * The fractional-second timestamp captured at span open (microtime).
     *
     * @return float
     */
    public function getStartedAt(): float;
}
