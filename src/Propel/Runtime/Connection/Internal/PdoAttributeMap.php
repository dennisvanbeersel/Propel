<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Internal;

use PDO;
use Propel\Runtime\Exception\InvalidArgumentException;
use ReflectionClass;

/**
 * Eager-resolved map from PDO/driver attribute string names to integer constants.
 *
 * Replaces the runtime `defined()`/`constant()` lookups in the legacy
 * `PdoConnection::__construct` and `PdoConnection::setAttribute` paths
 * (umbrella spec §6.2 risk #3). Map is populated once at class-load time
 * via Reflection over `\PDO`'s class constants — `defined()`/`constant()`
 * never run on the hot path.
 *
 * Accepted input forms:
 *  - bare attribute name: `'ATTR_ERRMODE'`, `'ATTR_CASE'`, `'MYSQL_ATTR_INIT_COMMAND'`
 *  - fully-qualified: `'PDO::ATTR_ERRMODE'`
 *  - leading-backslash form: `'\PDO::ATTR_ERRMODE'`
 *  - integer: passed through verbatim.
 *
 * Throws {@see InvalidArgumentException} for unknown attribute names.
 *
 * @internal Tier 3 — Phase E.
 */
final class PdoAttributeMap
{
    /**
     * @var array<string, int>|null
     */
    private static ?array $map = null;

    /**
     * Resolve a PDO attribute identifier to its integer constant.
     *
     * @param string|int $attribute
     *
     * @throws \Propel\Runtime\Exception\InvalidArgumentException When the name is not a recognized PDO constant.
     *
     * @return int
     */
    public static function resolve($attribute): int
    {
        if (is_int($attribute)) {
            return $attribute;
        }

        $name = self::normalizeName($attribute);
        $map = self::map();

        if (!isset($map[$name])) {
            throw new InvalidArgumentException(sprintf(
                'Invalid PDO option/attribute name specified: `%s`.',
                $attribute,
            ));
        }

        return $map[$name];
    }

    /**
     * Returns the full eager-resolved attribute map. Cached after first call.
     *
     * @return array<string, int>
     */
    public static function map(): array
    {
        if (self::$map === null) {
            self::$map = self::buildMap();
        }

        return self::$map;
    }

    /**
     * @return array<string, int>
     */
    private static function buildMap(): array
    {
        $map = [];
        $constants = (new ReflectionClass(PDO::class))->getConstants();
        foreach ($constants as $name => $value) {
            if (is_int($value)) {
                $map[$name] = $value;
            }
        }

        return $map;
    }

    /**
     * @param string $attribute
     *
     * @return string
     */
    private static function normalizeName(string $attribute): string
    {
        // Strip any 'PDO::' / '\PDO::' prefix to land on the bare constant name.
        $normalized = ltrim($attribute, '\\');
        if (str_starts_with($normalized, 'PDO::')) {
            $normalized = substr($normalized, 5);
        }

        return $normalized;
    }
}
