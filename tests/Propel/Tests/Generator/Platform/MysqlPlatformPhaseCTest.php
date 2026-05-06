<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Platform;

use Propel\Generator\Model\CheckConstraint;
use Propel\Generator\Model\Column;
use Propel\Generator\Model\Database;
use Propel\Generator\Model\PropelTypes;
use Propel\Generator\Model\Table;
use Propel\Generator\Platform\MysqlPlatform;
use Propel\Generator\Platform\PlatformInterface;

/**
 * Phase C (umbrella §6.4) — MySQL emits JSON / INVISIBLE / generated-column / CHECK DDL.
 */
class MysqlPlatformPhaseCTest extends PlatformTestBase
{
    /**
     * @return \Propel\Generator\Platform\MysqlPlatform
     */
    protected function getPlatform(): PlatformInterface
    {
        return new MysqlPlatform();
    }

    /**
     * @return void
     */
    public function testSupportsFlags(): void
    {
        $p = $this->getPlatform();
        $this->assertTrue($p->supportsGeneratedColumns());
        $this->assertTrue($p->supportsInvisibleColumns());
        $this->assertTrue($p->supportsCheckConstraints());
    }

    /**
     * @return void
     */
    public function testStoredGeneratedColumnDDL(): void
    {
        $platform = $this->getPlatform();
        $database = new Database();
        $database->setPlatform($platform);
        $table = new Table('users');
        $database->addTable($table);

        $col = new Column('full_name');
        $col->setDomain($platform->getDomainForType(PropelTypes::VARCHAR));
        $col->setSize(200);
        $table->addColumn($col);
        $col->setGenerated('stored', "CONCAT(first_name, ' ', last_name)");

        $ddl = $platform->getColumnDDL($col);
        $this->assertStringContainsString("GENERATED ALWAYS AS (CONCAT(first_name, ' ', last_name)) STORED", $ddl);
    }

    /**
     * @return void
     */
    public function testVirtualGeneratedColumnDDL(): void
    {
        $platform = $this->getPlatform();
        $database = new Database();
        $database->setPlatform($platform);
        $table = new Table('users');
        $database->addTable($table);

        $col = new Column('full_name_v');
        $col->setDomain($platform->getDomainForType(PropelTypes::VARCHAR));
        $col->setSize(200);
        $table->addColumn($col);
        $col->setGenerated('virtual', 'a + b');

        $ddl = $platform->getColumnDDL($col);
        $this->assertStringContainsString('GENERATED ALWAYS AS (a + b) VIRTUAL', $ddl);
    }

    /**
     * @return void
     */
    public function testInvisibleColumnDDL(): void
    {
        $platform = $this->getPlatform();
        $database = new Database();
        $database->setPlatform($platform);
        $table = new Table('audited');
        $database->addTable($table);

        $col = new Column('audit_token');
        $col->setDomain($platform->getDomainForType(PropelTypes::VARCHAR));
        $col->setSize(64);
        $table->addColumn($col);
        $col->setInvisible(true);

        $ddl = $platform->getColumnDDL($col);
        $this->assertStringContainsString('INVISIBLE', $ddl);
    }

    /**
     * @return void
     */
    public function testCheckConstraintDDLEnforced(): void
    {
        $platform = $this->getPlatform();
        $cc = new CheckConstraint('ck_orders_qty', 'quantity > 0');

        $ddl = $platform->getCheckConstraintDDL($cc);
        $this->assertStringContainsString('CONSTRAINT', $ddl);
        $this->assertStringContainsString('ck_orders_qty', $ddl);
        $this->assertStringContainsString('CHECK (quantity > 0)', $ddl);
        $this->assertStringNotContainsString('NOT ENFORCED', $ddl);
    }

    /**
     * @return void
     */
    public function testCheckConstraintDDLNotEnforced(): void
    {
        $platform = $this->getPlatform();
        $cc = new CheckConstraint('ck_orders_qty', 'quantity > 0', false);

        $ddl = $platform->getCheckConstraintDDL($cc);
        $this->assertStringContainsString('NOT ENFORCED', $ddl);
    }

    /**
     * @return void
     */
    public function testJsonColumnTypeRendersAsJson(): void
    {
        $platform = $this->getPlatform();
        $database = new Database();
        $database->setPlatform($platform);
        $table = new Table('events');
        $database->addTable($table);

        $col = new Column('payload');
        $col->setDomain($platform->getDomainForType(PropelTypes::JSON));
        $table->addColumn($col);

        $ddl = $platform->getColumnDDL($col);
        $this->assertStringContainsString('JSON', $ddl);
    }

    /**
     * @return void
     */
    public function testCreateTableInlineCheckConstraints(): void
    {
        $xml = <<<'XML'
<database name="phase_c">
    <table name="orders">
        <column name="id" type="INTEGER" primaryKey="true" autoIncrement="true"/>
        <column name="discount_pct" type="DECIMAL" size="5" scale="2"/>
        <check name="ck_orders_discount_range" expression="discount_pct BETWEEN 0 AND 100"/>
    </table>
</database>
XML;

        $table = $this->getTableFromSchema($xml, 'orders');
        $sql = $this->getPlatform()->getAddTableDDL($table);

        $this->assertStringContainsString('CONSTRAINT `ck_orders_discount_range` CHECK (discount_pct BETWEEN 0 AND 100)', $sql);
    }
}
