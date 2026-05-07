<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Telemetry\Prometheus;

use Propel\Runtime\Telemetry\SpanInterface;

/**
 * Span returned by {@see PrometheusTelemetry::startQuerySpan()}.
 *
 * Prometheus has no span concept; this carries the start microtime so
 * `endQuerySpan()` can observe the duration onto the
 * `propel_query_duration_seconds` histogram.
 *
 * @internal Phase I §I.3.2 — Tier 3.
 */
final readonly class PrometheusSpan implements SpanInterface
{
    /**
     * @param string $sql
     * @param string $callingMethod
     * @param float $startedAt
     */
    public function __construct(
        private string $sql,
        private string $callingMethod,
        private float $startedAt
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
}
