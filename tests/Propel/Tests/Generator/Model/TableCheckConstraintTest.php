<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Model;

use Propel\Generator\Builder\Util\SchemaReader;
use Propel\Generator\Exception\InvalidArgumentException;
use Propel\Generator\Model\CheckConstraint;
use Propel\Generator\Model\Table;

/**
 * Phase C (umbrella §6.4): Table CHECK constraint accumulation + nested <check> parsing.
 */
class TableCheckConstraintTest extends ModelTestCase
{
    /**
     * @return void
     */
    public function testEmptyByDefault(): void
    {
        $table = new Table('orders');
        $this->assertSame([], $table->getCheckConstraints());
    }

    /**
     * @return void
     */
    public function testAddCheckConstraintInstance(): void
    {
        $table = new Table('orders');
        $cc = new CheckConstraint('ck_orders_qty', 'quantity > 0');

        $returned = $table->addCheckConstraint($cc);

        $this->assertSame($cc, $returned);
        $this->assertSame($table, $cc->getTable());
        $this->assertCount(1, $table->getCheckConstraints());
        $this->assertSame($cc, $table->getCheckConstraints()[0]);
    }

    /**
     * @return void
     */
    public function testAddCheckConstraintFromArray(): void
    {
        $table = new Table('orders');
        $cc = $table->addCheckConstraint([
            'name' => 'ck_orders_discount',
            'expression' => 'discount BETWEEN 0 AND 100',
            'enforced' => 'true',
        ]);

        $this->assertInstanceOf(CheckConstraint::class, $cc);
        $this->assertSame('ck_orders_discount', $cc->getName());
        $this->assertSame('discount BETWEEN 0 AND 100', $cc->getExpression());
        $this->assertTrue($cc->isEnforced());
        $this->assertSame($table, $cc->getTable());
    }

    /**
     * @return void
     */
    public function testDuplicateNameThrows(): void
    {
        $table = new Table('orders');
        $table->addCheckConstraint(new CheckConstraint('ck_dup', 'a > 0'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/already exists/');

        $table->addCheckConstraint(new CheckConstraint('ck_dup', 'b > 0'));
    }

    /**
     * @return void
     */
    public function testInsertionOrderPreserved(): void
    {
        $table = new Table('orders');
        $table->addCheckConstraint(new CheckConstraint('ck_a', 'a > 0'));
        $table->addCheckConstraint(new CheckConstraint('ck_b', 'b > 0'));
        $table->addCheckConstraint(new CheckConstraint('ck_c', 'c > 0'));

        $names = array_map(static fn (CheckConstraint $cc): string => $cc->getName(), $table->getCheckConstraints());
        $this->assertSame(['ck_a', 'ck_b', 'ck_c'], $names);
    }

    /**
     * @return void
     */
    public function testTableScopedCheckParsing(): void
    {
        $xml = <<<'XML'
<database name="phase_c_check_table">
    <table name="orders">
        <column name="id" type="INTEGER" primaryKey="true"/>
        <column name="discount_pct" type="DECIMAL" size="5" scale="2"/>
        <check name="ck_discount_range" expression="discount_pct BETWEEN 0 AND 100"/>
    </table>
</database>
XML;

        $reader = new SchemaReader();
        $schema = $reader->parseString($xml);
        $orders = $schema->getDatabase()->getTable('orders');
        $checks = $orders->getCheckConstraints();

        $this->assertCount(1, $checks);
        $this->assertSame('ck_discount_range', $checks[0]->getName());
        $this->assertSame('discount_pct BETWEEN 0 AND 100', $checks[0]->getExpression());
    }

    /**
     * @return void
     */
    public function testColumnScopedCheckParsing(): void
    {
        $xml = <<<'XML'
<database name="phase_c_check_column">
    <table name="orders">
        <column name="id" type="INTEGER" primaryKey="true"/>
        <column name="quantity" type="INTEGER">
            <check expression="quantity &gt; 0"/>
        </column>
    </table>
</database>
XML;

        $reader = new SchemaReader();
        $schema = $reader->parseString($xml);
        $orders = $schema->getDatabase()->getTable('orders');
        $checks = $orders->getCheckConstraints();

        $this->assertCount(1, $checks);
        // Column-scoped sugar: stored on Table; auto-name derived from expression hash.
        $this->assertStringStartsWith('ck_orders_', $checks[0]->getName());
        $this->assertSame('quantity > 0', $checks[0]->getExpression());
    }

    /**
     * @return void
     */
    public function testMixedTableAndColumnScoped(): void
    {
        $xml = <<<'XML'
<database name="phase_c_mixed">
    <table name="orders">
        <column name="id" type="INTEGER" primaryKey="true"/>
        <column name="quantity" type="INTEGER">
            <check expression="quantity &gt; 0"/>
        </column>
        <column name="discount_pct" type="DECIMAL" size="5" scale="2"/>
        <check name="ck_orders_discount_range" expression="discount_pct BETWEEN 0 AND 100"/>
    </table>
</database>
XML;

        $reader = new SchemaReader();
        $schema = $reader->parseString($xml);
        $orders = $schema->getDatabase()->getTable('orders');
        $checks = $orders->getCheckConstraints();

        $this->assertCount(2, $checks);
        $expressions = array_map(static fn (CheckConstraint $cc): string => $cc->getExpression(), $checks);
        $this->assertContains('quantity > 0', $expressions);
        $this->assertContains('discount_pct BETWEEN 0 AND 100', $expressions);
    }
}
