<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection;

/**
 * Tier 2 SPI per umbrella spec §2.1.
 *
 * Implementations chain on top of a {@see ConnectionInterface}, exposing the
 * decorated inner connection through {@see self::getInner()}. Walk the chain
 * by repeated `getInner()` calls until the bottom is a non-decorator
 * `ConnectionInterface` (typically {@see PdoConnection}).
 *
 * @api Tier 2 — deprecation runway required for any signature change.
 *      Method addition requires a deprecation runway. Removal requires a
 *      major version bump (3.x → 4.0).
 *
 * @since 3.0.0 (Phase E — connection collapse + decorator chain)
 */
interface ConnectionDecoratorInterface extends ConnectionInterface
{
    /**
     * Returns the decorated inner connection.
     *
     * Walk the chain via repeated calls until the result is no longer a
     * `ConnectionDecoratorInterface`.
     *
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    public function getInner(): ConnectionInterface;
}
