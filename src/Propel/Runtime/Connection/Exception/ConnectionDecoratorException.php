<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Exception;

/**
 * Thrown by `ConnectionFactory` when a consumer-supplied decorator chain
 * violates the canonical inner-to-outer order.
 *
 * @api Tier 2 — consumer-catchable; deprecation runway through 4.0.
 */
final class ConnectionDecoratorException extends ConnectionException
{
}
