<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Telemetry;

/**
 * Span returned by {@see CompositeTelemetry::startQuerySpan()}.
 *
 * Carries the per-adapter child spans alongside the SQL/method/start
 * metadata. `CompositeTelemetry::endQuerySpan()` walks `getChildren()` in
 * reverse to close each child span on its corresponding adapter.
 *
 * @internal Phase I §I.1.3 — Tier 3.
 */
final readonly class CompositeSpan implements SpanInterface
{
    /**
     * @param string $sql
     * @param string $callingMethod
     * @param float $startedAt
     * @param array<int, \Propel\Runtime\Telemetry\SpanInterface> $children Indexed by adapter offset.
     */
    public function __construct(
        private string $sql,
        private string $callingMethod,
        private float $startedAt,
        private array $children
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
     * @return array<int, \Propel\Runtime\Telemetry\SpanInterface>
     */
    public function getChildren(): array
    {
        return $this->children;
    }
}
