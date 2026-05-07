<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Rector\Set;

/**
 * Set identifier for the bundled Propel 3.x → 4.0 migration ruleset.
 *
 * Consumers reference this constant in `rector.php` via
 * `RectorConfig::configure()->withSets([Propel4Migration::SET])`.
 */
final class Propel4Migration
{
    /**
     * Path to the rector set definition (a configurator file shipped with this package).
     *
     * @var string
     */
    public const SET = __DIR__ . '/../../config/sets/propel-4-migration.php';
}
