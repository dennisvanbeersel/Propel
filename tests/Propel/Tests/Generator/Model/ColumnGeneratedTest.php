<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Model;

use Propel\Generator\Exception\EngineException;
use Propel\Generator\Model\Column;

/**
 * Phase C (umbrella §6.4): Column generated/invisible attribute coverage.
 */
class ColumnGeneratedTest extends ModelTestCase
{
    /**
     * @return void
     */
    public function testNotGeneratedByDefault(): void
    {
        $column = new Column('id');

        $this->assertFalse($column->isGenerated());
        $this->assertNull($column->getGenerationKind());
        $this->assertNull($column->getGenerationExpression());
    }

    /**
     * @return void
     */
    public function testStoredGenerated(): void
    {
        $database = $this->getDatabaseMock('bookstore');
        $table = $this->getTableMock('users', ['database' => $database]);

        $column = new Column('full_name');
        $column->setTable($table);
        $column->loadMapping([
            'name' => 'full_name',
            'type' => 'VARCHAR',
            'size' => '200',
            'generated' => 'stored',
            'expression' => "CONCAT(first_name, ' ', last_name)",
        ]);

        $this->assertTrue($column->isGenerated());
        $this->assertSame('stored', $column->getGenerationKind());
        $this->assertSame("CONCAT(first_name, ' ', last_name)", $column->getGenerationExpression());
    }

    /**
     * @return void
     */
    public function testVirtualGenerated(): void
    {
        $database = $this->getDatabaseMock('bookstore');
        $table = $this->getTableMock('users', ['database' => $database]);

        $column = new Column('full_name_v');
        $column->setTable($table);
        $column->loadMapping([
            'name' => 'full_name_v',
            'type' => 'VARCHAR',
            'size' => '200',
            'generated' => 'virtual',
            'expression' => 'first_name || last_name',
        ]);

        $this->assertTrue($column->isGenerated());
        $this->assertSame('virtual', $column->getGenerationKind());
    }

    /**
     * @return void
     */
    public function testMissingExpressionThrows(): void
    {
        $database = $this->getDatabaseMock('bookstore');
        $table = $this->getTableMock('users', ['database' => $database]);

        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches('/full_name.*generated.*expression/');

        $column = new Column('full_name');
        $column->setTable($table);
        $column->loadMapping([
            'name' => 'full_name',
            'generated' => 'stored',
        ]);
    }

    /**
     * @return void
     */
    public function testInvalidGeneratedKindThrows(): void
    {
        $database = $this->getDatabaseMock('bookstore');
        $table = $this->getTableMock('users', ['database' => $database]);

        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches('/expected "virtual" or "stored"/');

        $column = new Column('full_name');
        $column->setTable($table);
        $column->loadMapping([
            'name' => 'full_name',
            'generated' => 'persisted',
            'expression' => 'a + b',
        ]);
    }

    /**
     * @return void
     */
    public function testSetGeneratedManually(): void
    {
        $column = new Column('total');
        $column->setGenerated('stored', 'a + b');

        $this->assertTrue($column->isGenerated());
        $this->assertSame('stored', $column->getGenerationKind());
        $this->assertSame('a + b', $column->getGenerationExpression());
    }

    /**
     * @return void
     */
    public function testSetGeneratedRejectsInvalidKind(): void
    {
        $column = new Column('total');

        $this->expectException(EngineException::class);
        $column->setGenerated('weird', 'a + b');
    }

    /**
     * @return void
     */
    public function testInvisibleDefaultsFalse(): void
    {
        $column = new Column('a');

        $this->assertFalse($column->isInvisible());
    }

    /**
     * @return void
     */
    public function testInvisibleSetterAndLoadMapping(): void
    {
        $database = $this->getDatabaseMock('bookstore');
        $table = $this->getTableMock('audited', ['database' => $database]);

        $column = new Column('audit_token');
        $column->setTable($table);
        $column->loadMapping([
            'name' => 'audit_token',
            'type' => 'VARCHAR',
            'size' => '64',
            'invisible' => 'true',
        ]);

        $this->assertTrue($column->isInvisible());

        $column->setInvisible(false);
        $this->assertFalse($column->isInvisible());
    }

    /**
     * @return void
     */
    public function testInvisiblePrimaryKeyConflict(): void
    {
        $database = $this->getDatabaseMock('bookstore');
        $table = $this->getTableMock('t', ['database' => $database]);

        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches('/cannot be both INVISIBLE and a primary key/');

        $column = new Column('id');
        $column->setTable($table);
        $column->loadMapping([
            'name' => 'id',
            'type' => 'INTEGER',
            'primaryKey' => 'true',
            'invisible' => 'true',
        ]);
    }
}
