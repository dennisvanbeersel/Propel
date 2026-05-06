<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Model;

use Propel\Generator\Model\PropelTypes;
use Propel\Generator\Platform\MysqlPlatform;
use Propel\Generator\Platform\PgsqlPlatform;
use Propel\Generator\Platform\SqlitePlatform;

/**
 * Phase C (umbrella §6.4): Domain mapping coverage for JSON/JSONB/INET/CIDR/TSVECTOR.
 */
class DomainJsonbTest extends ModelTestCase
{
    /**
     * @return void
     */
    public function testPropelTypesConstantsExist(): void
    {
        $this->assertSame('JSON', PropelTypes::JSON);
        $this->assertSame('JSONB', PropelTypes::JSONB);
        $this->assertSame('INET', PropelTypes::INET);
        $this->assertSame('CIDR', PropelTypes::CIDR);
        $this->assertSame('TSVECTOR', PropelTypes::TSVECTOR);
    }

    /**
     * @return void
     */
    public function testNewTypesIncludedInPropelTypesList(): void
    {
        $types = PropelTypes::getPropelTypes();
        $this->assertContains(PropelTypes::JSONB, $types);
        $this->assertContains(PropelTypes::INET, $types);
        $this->assertContains(PropelTypes::CIDR, $types);
        $this->assertContains(PropelTypes::TSVECTOR, $types);
    }

    /**
     * @return void
     */
    public function testNewTypesPhpNativeIsString(): void
    {
        foreach ([PropelTypes::JSON, PropelTypes::JSONB, PropelTypes::INET, PropelTypes::CIDR, PropelTypes::TSVECTOR] as $type) {
            $this->assertSame('string', PropelTypes::getPhpNative($type), "Native PHP type for {$type} should be 'string'");
        }
    }

    /**
     * @return void
     */
    public function testPgsqlPlatformMapsJsonbNatively(): void
    {
        $platform = new PgsqlPlatform();
        $domain = $platform->getDomainForType(PropelTypes::JSONB);
        $this->assertSame('JSONB', $domain->getSqlType());
    }

    /**
     * @return void
     */
    public function testPgsqlPlatformMapsInetCidrTsvector(): void
    {
        $platform = new PgsqlPlatform();

        $this->assertSame('INET', $platform->getDomainForType(PropelTypes::INET)->getSqlType());
        $this->assertSame('CIDR', $platform->getDomainForType(PropelTypes::CIDR)->getSqlType());
        $this->assertSame('TSVECTOR', $platform->getDomainForType(PropelTypes::TSVECTOR)->getSqlType());
    }

    /**
     * @return void
     */
    public function testMysqlPlatformFoldsJsonbToJson(): void
    {
        $platform = new MysqlPlatform();
        $domain = $platform->getDomainForType(PropelTypes::JSONB);
        $this->assertSame('JSON', $domain->getSqlType(), 'MySQL has no JSONB; folds to JSON');
    }

    /**
     * @return void
     */
    public function testMysqlPlatformMapsJsonNatively(): void
    {
        $platform = new MysqlPlatform();
        $domain = $platform->getDomainForType(PropelTypes::JSON);
        $this->assertSame('JSON', $domain->getSqlType());
    }

    /**
     * @return void
     */
    public function testSqliteFoldsAllToText(): void
    {
        $platform = new SqlitePlatform();
        foreach ([PropelTypes::JSON, PropelTypes::JSONB, PropelTypes::INET, PropelTypes::CIDR, PropelTypes::TSVECTOR] as $type) {
            $this->assertSame('TEXT', $platform->getDomainForType($type)->getSqlType(), "SQLite should fold {$type} to TEXT");
        }
    }
}
