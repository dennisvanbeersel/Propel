<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Generator\Platform;

use Propel\Generator\Builder\Util\SchemaReader;
use Propel\Generator\Platform\DefaultPlatform;
use Propel\Generator\Platform\PlatformInterface;
use Propel\Tests\TestCase;

/**
 * Base class for all Platform tests
 */
abstract class PlatformTestBase extends TestCase
{
    protected function getDatabaseFromSchema($schema)
    {
        $xtad = new SchemaReader($this->getPlatform());
        $appData = $xtad->parseString($schema);

        return $appData->getDatabase();
    }

    protected function getTableFromSchema($schema, $tableName = 'foo')
    {
        return $this->getDatabaseFromSchema($schema)->getTable($tableName);
    }

    /**
     * Get platform for static context (data providers). Override in subclasses for platform-specific tests.
     */
    protected static function getStaticPlatform(): PlatformInterface
    {
        return new DefaultPlatform();
    }

    /**
     * Static version for use in data providers
     */
    protected static function getDatabaseFromSchemaStatic(string $schema, ?PlatformInterface $platform = null)
    {
        $platform = $platform ?? static::getStaticPlatform();
        $xtad = new SchemaReader($platform);
        $appData = $xtad->parseString($schema);

        return $appData->getDatabase();
    }

    /**
     * Static version for use in data providers
     */
    protected static function getTableFromSchemaStatic(string $schema, string $tableName = 'foo', ?PlatformInterface $platform = null)
    {
        return static::getDatabaseFromSchemaStatic($schema, $platform)->getTable($tableName);
    }
}
