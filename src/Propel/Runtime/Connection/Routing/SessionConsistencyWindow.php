<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Routing;

/**
 * Tracks the timestamp of the last write in a connection session and
 * answers whether the session-consistency window is currently active.
 *
 * Phase E §6.4 read-after-write hazard mitigation: after a write, reads
 * issued via the same connection within `windowSeconds` route to primary
 * regardless of the auto-routing hint. The default window is 5 seconds.
 *
 * Scope: one window per `ReplicaRoutingConnection` instance. Phase J
 * revisits worker-mode session-scoping.
 *
 * @api Tier 3.
 *
 * @psalm-suppress UnusedClass instantiated by `ReplicaRoutingConnection`.
 */
final class SessionConsistencyWindow
{
    /**
     * @var \Propel\Runtime\Connection\Routing\ClockInterface
     */
    private ClockInterface $clock;

    /**
     * @var int|null
     */
    private ?int $lastWriteAtMicros = null;

    /**
     * @param \Propel\Runtime\Connection\Routing\ClockInterface|null $clock
     */
    public function __construct(?ClockInterface $clock = null)
    {
        $this->clock = $clock ?? new SystemClock();
    }

    /**
     * Marks "now" as the most recent write timestamp.
     *
     * @return void
     */
    public function noteWrite(): void
    {
        $this->lastWriteAtMicros = $this->clock->nowMicros();
    }

    /**
     * @return int|null
     */
    public function getLastWriteAtMicros(): ?int
    {
        return $this->lastWriteAtMicros;
    }

    /**
     * @param float $windowSeconds
     *
     * @return bool True if a write occurred within the past `windowSeconds`.
     */
    public function isWindowActive(float $windowSeconds): bool
    {
        if ($this->lastWriteAtMicros === null) {
            return false;
        }
        $windowMicros = (int)($windowSeconds * 1_000_000);

        return $this->lastWriteAtMicros + $windowMicros > $this->clock->nowMicros();
    }

    /**
     * Resets the window (test/maintenance hook).
     *
     * @return void
     */
    public function reset(): void
    {
        $this->lastWriteAtMicros = null;
    }
}
