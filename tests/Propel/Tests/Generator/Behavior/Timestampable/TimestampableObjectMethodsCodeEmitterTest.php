<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Behavior\Timestampable;

use Propel\Generator\Behavior\Timestampable\TimestampableBehavior;
use Propel\Generator\Builder\Om\AbstractOMBuilder;
use Propel\Generator\Model\Column;
use Propel\Generator\Model\Database;
use Propel\Generator\Model\Table;
use Propel\Generator\Platform\SqlitePlatform;
use Propel\Tests\TestCase;

/**
 * Locks the byte-exact output of TimestampableBehavior::objectMethods() after
 * the Phase D.1.4 CodeEmitter port. The string output must match the
 * pre-refactor concatenation byte-for-byte; this test snapshot pins the
 * expected bytes so any future drift is caught immediately.
 */
class TimestampableObjectMethodsCodeEmitterTest extends TestCase
{
    /**
     * @return void
     */
    public function testObjectMethodsByteIdenticalToPreRefactor(): void
    {
        $database = new Database('test_db');
        $database->setPlatform(new SqlitePlatform());

        $table = new Table('book');
        $database->addTable($table);

        $idColumn = new Column('id');
        $idColumn->setType('INTEGER');
        $idColumn->setPrimaryKey(true);
        $table->addColumn($idColumn);

        $updateColumn = new Column('updated_at');
        $updateColumn->setType('TIMESTAMP');
        $table->addColumn($updateColumn);

        $behavior = new TimestampableBehavior();
        $behavior->setName('timestampable');
        $table->addBehavior($behavior);

        $builder = $this->createMock(AbstractOMBuilder::class);
        $builder->method('getColumnConstant')->willReturn('BookTableMap::COL_UPDATED_AT');

        $expected = "\n"
            . "/**\n"
            . " * Mark the current object so that the update date doesn't get updated during next save\n"
            . " *\n"
            . ' * @return $this' . " The current object (for fluent API support)\n"
            . " */\n"
            . "public function keepUpdateDateUnchanged()\n"
            . "{\n"
            . '    $this->modifiedColumns[BookTableMap::COL_UPDATED_AT] = true;' . "\n"
            . "\n"
            . '    return $this;' . "\n"
            . "}\n";

        $this->assertSame($expected, $behavior->objectMethods($builder));
    }

    /**
     * @return void
     */
    public function testObjectMethodsEmptyWhenUpdatedAtDisabled(): void
    {
        $database = new Database('test_db');
        $database->setPlatform(new SqlitePlatform());

        $table = new Table('book');
        $database->addTable($table);

        $behavior = new TimestampableBehavior();
        $behavior->setName('timestampable');
        $behavior->addParameter(['name' => 'disable_updated_at', 'value' => 'true']);
        $table->addBehavior($behavior);

        $builder = $this->createMock(AbstractOMBuilder::class);

        $this->assertSame('', $behavior->objectMethods($builder));
    }
}
