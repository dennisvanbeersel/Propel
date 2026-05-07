<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Routing;

/**
 * Default {@see ClockInterface} backed by `microtime(true)`.
 *
 * @api Tier 3.
 *
 * @psalm-suppress UnusedClass instantiated by `RouteResolver` /
 *                 `SessionConsistencyWindow` defaults.
 */
final class SystemClock implements ClockInterface
{
    /**
     * @return int
     */
    #[\Override]
    public function nowMicros(): int
    {
        return (int)(microtime(true) * 1_000_000);
    }
}
