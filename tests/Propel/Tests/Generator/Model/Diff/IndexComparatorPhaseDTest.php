<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Model\Diff;

use Propel\Generator\Model\Column;
use Propel\Generator\Model\Diff\IndexComparator;
use Propel\Generator\Model\Index;
use Propel\Tests\TestCase;

/**
 * Phase D (umbrella §6.4 carry-forward): IndexComparator detects partial-index
 * WHERE-clause drift and index-type drift (USING gin|gist|hash|btree).
 */
class IndexComparatorPhaseDTest extends TestCase
{
    /**
     * @return \Propel\Generator\Model\Index
     */
    private function makeIndex(string $name): Index
    {
        $idx = new Index($name);
        $idx->addColumn(new Column('id'));

        return $idx;
    }

    /**
     * @return void
     */
    public function testNoDriftWhenWhereClauseAndTypeMatch(): void
    {
        $a = $this->makeIndex('idx');
        $a->setWhereClause("status = 'active'");
        $a->setIndexType('btree');

        $b = $this->makeIndex('idx');
        $b->setWhereClause("status = 'active'");
        $b->setIndexType('btree');

        $this->assertFalse(IndexComparator::computeDiff($a, $b));
    }

    /**
     * @return void
     */
    public function testWhereClauseDifferenceIsDetected(): void
    {
        $a = $this->makeIndex('idx');
        $a->setWhereClause("status = 'active'");
        $b = $this->makeIndex('idx');
        $b->setWhereClause("status = 'archived'");

        $this->assertTrue(IndexComparator::computeDiff($a, $b));
    }

    /**
     * @return void
     */
    public function testWhereClauseAddedIsDetected(): void
    {
        $a = $this->makeIndex('idx');
        $b = $this->makeIndex('idx');
        $b->setWhereClause("status = 'active'");

        $this->assertTrue(IndexComparator::computeDiff($a, $b));
    }

    /**
     * @return void
     */
    public function testWhereClauseRemovedIsDetected(): void
    {
        $a = $this->makeIndex('idx');
        $a->setWhereClause("status = 'active'");
        $b = $this->makeIndex('idx');

        $this->assertTrue(IndexComparator::computeDiff($a, $b));
    }

    /**
     * @return void
     */
    public function testWhereClauseWhitespaceIsNormalised(): void
    {
        $a = $this->makeIndex('idx');
        $a->setWhereClause("status = 'active'");
        $b = $this->makeIndex('idx');
        $b->setWhereClause("  status   =\n  'active'  ");

        $this->assertFalse(IndexComparator::computeDiff($a, $b));
    }

    /**
     * @return void
     */
    public function testIndexTypeDifferenceIsDetected(): void
    {
        $a = $this->makeIndex('idx');
        $a->setIndexType('btree');
        $b = $this->makeIndex('idx');
        $b->setIndexType('gin');

        $this->assertTrue(IndexComparator::computeDiff($a, $b));
    }

    /**
     * @return void
     */
    public function testIndexTypeIsCaseInsensitive(): void
    {
        $a = $this->makeIndex('idx');
        $a->setIndexType('BTREE');
        $b = $this->makeIndex('idx');
        $b->setIndexType('btree');

        $this->assertFalse(IndexComparator::computeDiff($a, $b));
    }

    /**
     * @return void
     */
    public function testIndexTypeAddedIsDetected(): void
    {
        $a = $this->makeIndex('idx');
        $b = $this->makeIndex('idx');
        $b->setIndexType('gin');

        $this->assertTrue(IndexComparator::computeDiff($a, $b));
    }
}
