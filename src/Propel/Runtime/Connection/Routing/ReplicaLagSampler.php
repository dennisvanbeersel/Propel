<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Routing;

/**
 * Samples per-replica lag on a clock-driven cadence and exposes the
 * latest cached samples for the routing decision hot path.
 *
 * Hot-path safety: `getSamples()` NEVER triggers a database call. The
 * actual probe runs out-of-band via `probe()`, called by a worker hook
 * or scheduler — not by request-traffic. Adapter probe SQL is static
 * (no user input) — security reviewer's timing-oracle audit notes
 * documented in `docs/CONNECTION-DECORATORS.md`.
 *
 * @api Tier 3.
 *
 * @psalm-suppress UnusedClass instantiated by `ConnectionFactory`
 *                 (Task E.7) when replicas configured.
 */
final class ReplicaLagSampler
{
    /**
     * @var array<string, float>
     */
    private array $samples = [];

    /**
     * @var array<string, int>
     */
    private array $lastSampledAtMicros = [];

    /**
     * @var \Propel\Runtime\Connection\Routing\ClockInterface
     */
    private ClockInterface $clock;

    /**
     * @var int
     */
    private readonly int $sampleIntervalMicros;

    /**
     * @param int $sampleIntervalSeconds Minimum interval between probes per replica.
     * @param \Propel\Runtime\Connection\Routing\ClockInterface|null $clock
     */
    public function __construct(int $sampleIntervalSeconds = 10, ?ClockInterface $clock = null)
    {
        $this->sampleIntervalMicros = $sampleIntervalSeconds * 1_000_000;
        $this->clock = $clock ?? new SystemClock();
    }

    /**
     * Records a probe sample for the named replica. Out-of-band call.
     *
     * @param string $replicaName
     * @param float $lagSeconds
     *
     * @return void
     */
    public function recordSample(string $replicaName, float $lagSeconds): void
    {
        $this->samples[$replicaName] = $lagSeconds;
        $this->lastSampledAtMicros[$replicaName] = $this->clock->nowMicros();
    }

    /**
     * Returns true if the replica should be re-probed now (interval exceeded).
     *
     * @param string $replicaName
     *
     * @return bool
     */
    public function isStale(string $replicaName): bool
    {
        if (!isset($this->lastSampledAtMicros[$replicaName])) {
            return true;
        }

        return $this->lastSampledAtMicros[$replicaName] + $this->sampleIntervalMicros < $this->clock->nowMicros();
    }

    /**
     * Hot-path call: returns cached samples. NEVER hits the database.
     *
     * @return array<string, float>
     */
    public function getSamples(): array
    {
        return $this->samples;
    }

    /**
     * Forces a re-sample on next probe (test hook).
     *
     * @param string $replicaName
     *
     * @return void
     */
    public function invalidate(string $replicaName): void
    {
        unset($this->samples[$replicaName], $this->lastSampledAtMicros[$replicaName]);
    }
}
