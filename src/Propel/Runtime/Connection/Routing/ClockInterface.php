<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Routing;

/**
 * Minimal clock abstraction so routing components can be unit-tested
 * deterministically (Phase E §6.4 — clock-injected tests).
 *
 * Returns wall-clock time in microseconds since the Unix epoch.
 *
 * @api Tier 3.
 */
interface ClockInterface
{
    /**
     * @return int Current wall-clock time in microseconds since epoch.
     */
    public function nowMicros(): int;
}
