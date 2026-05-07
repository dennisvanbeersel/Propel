<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Generator\Builder\Om;

use DateTimeImmutable;
use GeneratedObjectDateColumnTypeEntity;
use PDO;
use Propel\Generator\Util\QuickBuilder;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\StatementInterface;
use Propel\Runtime\Connection\StatementWrapper;
use Propel\Tests\TestCase;

class GeneratedObjectDateColumnTypeTest extends TestCase
{
    /**
     * @return void
     */
    public function setUp(): void
    {
        if (!class_exists('GeneratedObjectDateColumnTypeEntity')) {
            $schema = <<<'XML'
<database name="generated_object_date_column_type">
    <table name="generated_object_date_column_type_entity">
        <column name="id" primaryKey="true" type="INTEGER" autoIncrement="true"/>
        <column name="datecolumn" type="DATE"/>
    </table>
</database>
XML;
            QuickBuilder::buildSchema($schema);
        }
    }

    /**
     * @return void
     */
    public function testInsertDateColumn(): void
    {
        assert(class_exists(GeneratedObjectDateColumnTypeEntity::class));
        $entity = new GeneratedObjectDateColumnTypeEntity();
        $this->assertTrue(method_exists($entity, 'setDatecolumn'));
        $this->assertTrue(method_exists($entity, 'save'));
        $dateValue = new DateTimeImmutable('2021-06-25 12:26');
        $entity->setDatecolumn($dateValue);

        $insertStatement = $this->createMockInsertStatement();
        $bindValueCalls = [];
        $insertStatement
            ->method('bindValue')
            ->willReturnCallback(function ($param, $value, $type) use (&$bindValueCalls) {
                $bindValueCalls[] = [$param, $value, $type];

                return true;
            });

        $con = $this->createMockConnection();
        $con
            ->expects($this->once())
            ->method('prepare')
            ->willReturnCallback(function ($sql) use ($insertStatement) {
                $this->assertEquals(
                    'INSERT INTO generated_object_date_column_type_entity '
                    . '(id, datecolumn) VALUES (:p0, :p1)',
                    $sql,
                );

                return $insertStatement;
            });
        $entity->save($con);

        // Verify bindValue calls were made with correct parameters
        $this->assertCount(2, $bindValueCalls);
        $this->assertEquals([':p0', null, PDO::PARAM_INT], $bindValueCalls[0]);
        $this->assertEquals([':p1', $dateValue->format('Y-m-d'), PDO::PARAM_STR], $bindValueCalls[1]);
    }

    /**
     * @return \Propel\Runtime\Connection\ConnectionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createMockConnection(): ConnectionInterface
    {
        // PdoConnection became `final` in Phase E; use the interface for mocking.
        $con = $this->getMockBuilder(ConnectionInterface::class)
            ->onlyMethods([
                'prepare',
                'transaction',
                'lastInsertId',
                'beginTransaction',
                'commit',
                'rollBack',
                'inTransaction',
                'getAttribute',
                'setAttribute',
                'exec',
                'query',
                'quote',
                'setName',
                'getName',
                'getDataFetcher',
                'getSingleDataFetcher',
            ])
            ->getMock();
        $con
            ->method('transaction')
            ->willReturnCallback(function ($callable) {
                return call_user_func($callable);
            });
        $con
            ->method('lastInsertId')
            ->willReturn(2);

        return $con;
    }

    /**
     * @return \Propel\Runtime\Connection\StatementInterface&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createMockInsertStatement(): StatementInterface
    {
        $insertStatement = $this->createPartialMock(StatementWrapper::class, [
            'bindValue',
            'execute',
        ]);
        $insertStatement
            ->method('execute')
            ->willReturn(true);

        return $insertStatement;
    }
}
