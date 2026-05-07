<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Telemetry;

/**
 * Span returned by {@see NoOpTelemetry::startQuerySpan()}.
 *
 * Carries the bare minimum data so that test fakes (e.g., the recording-
 * telemetry test double) can assert against `getSql()` / `getCallingMethod()`
 * without instantiating their own `SpanInterface`. Adapters with real
 * trace-context (`OtelSpan`, `PrometheusSpan`) ship their own implementations.
 *
 * @internal Phase I §I.1.1 — Tier 3.
 */
final readonly class NoOpSpan implements SpanInterface
{
    /**
     * @param string $sql
     * @param string $callingMethod
     * @param float $startedAt Microtime captured at span open.
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
