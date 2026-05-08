<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Formatter;

use Generator;
use PHPUnit\Framework\TestCase;
use Propel\Generator\Util\QuickBuilder;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Formatter\StreamingObjectFormatter;
use Propel\Runtime\Telemetry\TelemetryInterface;

/**
 * Phase G.6.1 — verifies that StreamingObjectFormatter yields hydrated
 * rows lazily through a Generator, refuses one-to-many with(), and
 * routes hydration latency through the configured TelemetryInterface.
 */
class StreamingObjectFormatterTest extends TestCase
{
    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        if (class_exists('StreamingFormatterBench\\StreamAuthor')) {
            return;
        }

        $schema = <<<'XML'
<database name="streaming_formatter_bench" namespace="StreamingFormatterBench" defaultIdMethod="native">
    <table name="stream_author" phpName="StreamAuthor">
        <column name="id" type="INTEGER" primaryKey="true" autoIncrement="true"/>
        <column name="name" type="VARCHAR" size="100" required="true"/>
    </table>
    <table name="stream_book" phpName="StreamBook">
        <column name="id" type="INTEGER" primaryKey="true" autoIncrement="true"/>
        <column name="title" type="VARCHAR" size="100" required="true"/>
        <column name="author_id" type="INTEGER"/>
        <foreign-key foreignTable="stream_author">
            <reference local="author_id" foreign="id"/>
        </foreign-key>
    </table>
</database>
XML;

        $builder = new QuickBuilder();
        $builder->setSchema($schema);
        $builder->build();
    }

    /**
     * @return void
     */
    private function seed(int $n): void
    {
        $authorClass = 'StreamingFormatterBench\\StreamAuthor';
        $authorQueryClass = 'StreamingFormatterBench\\StreamAuthorQuery';
        $authorQueryClass::create()->deleteAll();
        for ($i = 0; $i < $n; $i++) {
            /** @var ActiveRecordInterface $author */
            $author = new $authorClass();
            /** @phpstan-ignore-next-line dynamic class with magic setter */
            $author->setName('Author ' . $i);
            /** @phpstan-ignore-next-line dynamic class with magic save */
            $author->save();
        }
    }

    /**
     * @return void
     */
    public function testFormatYieldsGeneratorOfHydratedEntities(): void
    {
        $this->seed(5);

        $query = ('StreamingFormatterBench\\StreamAuthorQuery')::create();
        $formatter = new StreamingObjectFormatter($query, $query->doSelect());
        $stream = $formatter->format();

        $this->assertInstanceOf(Generator::class, $stream);

        $count = 0;
        foreach ($stream as $row) {
            $this->assertInstanceOf(ActiveRecordInterface::class, $row);
            $count++;
        }
        $this->assertSame(5, $count);
    }

    /**
     * @return void
     */
    public function testStreamYieldsZeroBasedIntKeys(): void
    {
        $this->seed(3);

        $query = ('StreamingFormatterBench\\StreamAuthorQuery')::create();
        $formatter = new StreamingObjectFormatter($query, $query->doSelect());

        $keys = [];
        foreach ($formatter->format() as $key => $_object) {
            $keys[] = $key;
        }

        $this->assertSame([0, 1, 2], $keys);
    }

    /**
     * @return void
     */
    public function testTelemetryRecordsOneHydrationPerYieldedRow(): void
    {
        $this->seed(4);

        $telemetry = $this->createMock(TelemetryInterface::class);
        $telemetry->expects($this->exactly(4))
            ->method('recordHydrationDuration')
            ->with($this->equalTo('StreamingFormatterBench\\StreamAuthor'), $this->isType('float'));

        $query = ('StreamingFormatterBench\\StreamAuthorQuery')::create();
        $formatter = new StreamingObjectFormatter($query, $query->doSelect(), $telemetry);

        // Force iteration so the Generator runs to completion.
        $sink = [];
        foreach ($formatter->format() as $row) {
            $sink[] = $row;
        }
        $this->assertCount(4, $sink);
    }

    /**
     * @return void
     */
    public function testRejectsOneToManyWith(): void
    {
        $this->seed(1);

        $query = ('StreamingFormatterBench\\StreamAuthorQuery')::create()
            ->leftJoinWith('StreamingFormatterBench\\StreamAuthor.StreamBook');
        $formatter = new StreamingObjectFormatter($query, $query->doSelect());

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('StreamingObjectFormatter does not support one-to-many with()');

        // Generators are lazy — must drive iteration to surface the throw.
        foreach ($formatter->format() as $_) {
            $this->fail('expected throw before any row is yielded');
        }
    }

    /**
     * @return void
     */
    public function testSetTelemetryOverridesPostConstruction(): void
    {
        $this->seed(1);

        $telemetry = $this->createMock(TelemetryInterface::class);
        $telemetry->expects($this->once())->method('recordHydrationDuration');

        $query = ('StreamingFormatterBench\\StreamAuthorQuery')::create();
        $formatter = new StreamingObjectFormatter($query, $query->doSelect());
        $formatter->setTelemetry($telemetry);

        foreach ($formatter->format() as $_) {
            // drain
        }
    }
}
