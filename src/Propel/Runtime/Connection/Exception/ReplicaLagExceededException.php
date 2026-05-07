<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Exception;

/**
 * Thrown when ALL replicas exceed lag threshold, the consumer hint is
 * `allow-replica`, AND `fallbackToPrimary` is disabled — i.e., the
 * consumer explicitly opted into "fail rather than serve stale".
 *
 * @api Tier 2 — consumer-catchable; deprecation runway through 4.0.
 */
final class ReplicaLagExceededException extends ConnectionException
{
}
