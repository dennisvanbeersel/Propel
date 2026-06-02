<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Builder\Om;

use Propel\Generator\Util\QuickBuilder;
use Propel\Tests\TestCase;

/**
 * Phase G.3.3 — verifies that ObjectBuilder (via ReferrerBuilderTrait::addRefFKInit)
 * delegates to LazyRelationBuilder when the parent table opts in via
 * `<table useLazyObjects="true">`.
 *
 * The bookstore golden tree exercises the legacy (eager) branch — its
 * byte-equivalence is the negative-test guard that this commit's
 * integration is non-disruptive. This test covers the positive branch:
 * when a table opts in, the generated init<RelCol>() body switches to
 * the newLazyGhost form and a sister doInit<RelCol>() helper is emitted.
 */
class UseLazyObjectsObjectBuilderIntegrationTest extends TestCase
{
    /**
     * @return string
     */
    private function buildOptedInObjectClass(): string
    {
        $schema = <<<'XML'
<database name="lazy_test" defaultIdMethod="native">
    <table name="lazy_test_author" phpName="LazyTestAuthor" useLazyObjects="true">
        <column name="id" type="INTEGER" primaryKey="true" autoIncrement="true"/>
        <column name="name" type="VARCHAR" size="100" required="true"/>
    </table>
    <table name="lazy_test_book" phpName="LazyTestBook">
        <column name="id" type="INTEGER" primaryKey="true" autoIncrement="true"/>
        <column name="title" type="VARCHAR" size="100" required="true"/>
        <column name="author_id" type="INTEGER"/>
        <foreign-key foreignTable="lazy_test_author">
            <reference local="author_id" foreign="id"/>
        </foreign-key>
    </table>
</database>
XML;

        $builder = new QuickBuilder();
        $builder->setSchema($schema);

        $authorTable = $builder->getDatabase()->getTable('lazy_test_author');

        return $builder->getClassesForTable($authorTable, ['object']);
    }

    /**
     * @return string
     */
    private function buildOptedOutObjectClass(): string
    {
        $schema = <<<'XML'
<database name="eager_test" defaultIdMethod="native">
    <table name="eager_test_author" phpName="EagerTestAuthor">
        <column name="id" type="INTEGER" primaryKey="true" autoIncrement="true"/>
        <column name="name" type="VARCHAR" size="100" required="true"/>
    </table>
    <table name="eager_test_book" phpName="EagerTestBook">
        <column name="id" type="INTEGER" primaryKey="true" autoIncrement="true"/>
        <column name="title" type="VARCHAR" size="100" required="true"/>
        <column name="author_id" type="INTEGER"/>
        <foreign-key foreignTable="eager_test_author">
            <reference local="author_id" foreign="id"/>
        </foreign-key>
    </table>
</database>
XML;

        $builder = new QuickBuilder();
        $builder->setSchema($schema);

        $authorTable = $builder->getDatabase()->getTable('eager_test_author');

        return $builder->getClassesForTable($authorTable, ['object']);
    }

    /**
     * @return void
     */
    public function testOptedInTableEmitsNewLazyGhostInitializer(): void
    {
        $code = $this->buildOptedInObjectClass();

        $this->assertStringContainsString('public function initLazyTestBooks(bool $overrideExisting = true): void', $code);
        $this->assertStringContainsString(
            '$this->collLazyTestBooks = (new \ReflectionClass($collectionClassName))->newLazyGhost(',
            $code,
        );
        $this->assertStringContainsString(
            'fn (\Propel\Runtime\Collection\Collection $coll) => $this->doInitLazyTestBooks($coll)',
            $code,
        );
        $this->assertStringContainsString(
            'private function doInitLazyTestBooks(\Propel\Runtime\Collection\Collection $coll): void',
            $code,
        );
    }

    /**
     * @return void
     */
    public function testOptedOutTableEmitsLegacyEagerInitializer(): void
    {
        $code = $this->buildOptedOutObjectClass();

        $this->assertStringContainsString('public function initEagerTestBooks(bool $overrideExisting = true): void', $code);
        $this->assertStringNotContainsString('newLazyGhost', $code);
        $this->assertStringNotContainsString('doInitEagerTestBooks', $code);
        $this->assertStringContainsString('$this->collEagerTestBooks = new $collectionClassName;', $code);
    }
}
