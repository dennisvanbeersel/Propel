<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection\Routing;

use Propel\Runtime\Connection\Routing\ClockInterface;

/**
 * Test-only fixed clock for the routing-decision unit tests. Permits
 * setting the wall-clock to deterministic micros.
 */
class FixedClock implements ClockInterface
{
    /**
     * @param int $micros
     */
    public function __construct(private int $micros)
    {
    }

    /**
     * @return int
     */
    #[\Override]
    public function nowMicros(): int
    {
        return $this->micros;
    }

    /**
     * @param int $micros
     *
     * @return void
     */
    public function setMicros(int $micros): void
    {
        $this->micros = $micros;
    }
}
