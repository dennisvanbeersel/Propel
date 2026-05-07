<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Platform;

use Propel\Generator\Exception\EngineException;
use Propel\Generator\Model\CheckConstraint;
use Propel\Generator\Model\Column;
use Propel\Generator\Model\Database;
use Propel\Generator\Model\PropelTypes;
use Propel\Generator\Model\Table;
use Propel\Generator\Platform\PlatformInterface;
use Propel\Generator\Platform\SqlitePlatform;

/**
 * Phase C (umbrella §1.2 + §6.4) — SQLite is frozen and rejects new DDL features.
 */
class SqlitePlatformPhaseCTest extends PlatformTestBase
{
    /**
     * @return \Propel\Generator\Platform\SqlitePlatform
     */
    protected function getPlatform(): PlatformInterface
    {
        return new SqlitePlatform();
    }

    /**
     * @return void
     */
    public function testSupportsAllFlagsFalse(): void
    {
        $p = $this->getPlatform();
        $this->assertFalse($p->supportsGeneratedColumns());
        $this->assertFalse($p->supportsInvisibleColumns());
        $this->assertFalse($p->supportsCheckConstraints());
    }

    /**
     * @return void
     */
    public function testGeneratedColumnRejected(): void
    {
        $platform = $this->getPlatform();
        $database = new Database();
        $database->setPlatform($platform);
        $table = new Table('users');
        $database->addTable($table);

        $col = new Column('full_name');
        $col->setDomain($platform->getDomainForType(PropelTypes::VARCHAR));
        $table->addColumn($col);
        $col->setGenerated('stored', "'a'");

        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches('/SQLite is frozen.*generated columns/');
        $platform->getColumnDDL($col);
    }

    /**
     * @return void
     */
    public function testInvisibleColumnRejected(): void
    {
        $platform = $this->getPlatform();
        $database = new Database();
        $database->setPlatform($platform);
        $table = new Table('users');
        $database->addTable($table);

        $col = new Column('audit');
        $col->setDomain($platform->getDomainForType(PropelTypes::VARCHAR));
        $table->addColumn($col);
        $col->setInvisible(true);

        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches('/SQLite is frozen.*INVISIBLE/');
        $platform->getColumnDDL($col);
    }

    /**
     * @return void
     */
    public function testJsonbColumnRejected(): void
    {
        $platform = $this->getPlatform();
        $database = new Database();
        $database->setPlatform($platform);
        $table = new Table('events');
        $database->addTable($table);

        $col = new Column('payload');
        $col->setType('JSONB');
        $col->setDomain($platform->getDomainForType(PropelTypes::JSONB));
        $table->addColumn($col);

        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches('/SQLite is frozen.*JSONB/');
        $platform->getColumnDDL($col);
    }

    /**
     * @return void
     */
    public function testJsonColumnPassthrough(): void
    {
        $platform = $this->getPlatform();
        $database = new Database();
        $database->setPlatform($platform);
        $table = new Table('events');
        $database->addTable($table);

        $col = new Column('payload');
        $col->setDomain($platform->getDomainForType(PropelTypes::JSON));
        $table->addColumn($col);

        // JSON folds to TEXT on SQLite (Domain mapping); no exception.
        $ddl = $platform->getColumnDDL($col);
        $this->assertStringContainsString('TEXT', $ddl);
    }

    /**
     * @return void
     */
    public function testCheckConstraintRejected(): void
    {
        $platform = $this->getPlatform();
        $cc = new CheckConstraint('ck_x', 'x > 0');

        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches('/SQLite is frozen.*CHECK constraints/');
        $platform->getCheckConstraintDDL($cc);
    }
}
