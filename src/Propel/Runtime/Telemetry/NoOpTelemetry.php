<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Telemetry;

use stdClass;

/**
 * No-op default implementation of {@see TelemetryInterface}.
 *
 * Phase E uses this everywhere a `TelemetryInterface` is optional. Phase I
 * replaces the default with real OpenTelemetry / Prometheus adapters.
 *
 * @api Tier 2 — instantiable contract for downstream noop usage.
 */
final class NoOpTelemetry implements TelemetryInterface
{
    /**
     * @param string $sql
     * @param string $callingMethod
     *
     * @return object
     */
    #[\Override]
    public function startQuerySpan(string $sql, string $callingMethod): object
    {
        return new stdClass();
    }

    /**
     * @param object $span
     * @param float $durationSeconds
     * @param \Throwable|null $error
     *
     * @return void
     */
    #[\Override]
    public function endQuerySpan(object $span, float $durationSeconds, ?\Throwable $error = null): void
    {
        // Intentionally empty.
    }
}
