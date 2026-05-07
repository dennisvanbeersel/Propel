<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Telemetry;

/**
 * Tier 2 SPI per umbrella spec §2.5.
 *
 * Phase E ships a no-op stub. Phase I delivers real OpenTelemetry /
 * Prometheus adapters that consume this interface.
 *
 * Decorators ({@see \Propel\Runtime\Connection\Internal\LoggingConnection},
 * {@see \Propel\Runtime\Connection\Internal\ProfilingConnection}) accept an
 * optional `TelemetryInterface` parameter; default is `NoOpTelemetry`.
 *
 * @api Tier 2 — deprecation runway required for any signature change.
 *
 * @since 3.0.0
 */
interface TelemetryInterface
{
    /**
     * Open a span for the duration of a single query (`prepare`, `exec`,
     * or `query` call). The returned object is opaque; the caller must
     * call {@see self::endQuerySpan()} with it once the operation completes.
     *
     * @param string $sql SQL text (or method label like 'beginTransaction').
     * @param string $callingMethod The decorated method that opened the span.
     *
     * @return object Opaque span handle. Pass back to `endQuerySpan`.
     */
    public function startQuerySpan(string $sql, string $callingMethod): object;

    /**
     * Close a span previously opened by {@see self::startQuerySpan()}.
     *
     * @param object $span Handle returned by `startQuerySpan`.
     * @param float $durationSeconds Duration in fractional seconds.
     * @param \Throwable|null $error Set when the operation threw.
     *
     * @return void
     */
    public function endQuerySpan(object $span, float $durationSeconds, ?\Throwable $error = null): void;
}
