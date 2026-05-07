<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Model\Diff;

use Propel\Generator\Model\Column;
use Propel\Generator\Model\Database;
use Propel\Generator\Model\Diff\ForeignKeyComparator;
use Propel\Generator\Model\ForeignKey;
use Propel\Generator\Model\Table;
use Propel\Tests\TestCase;

/**
 * Phase D (umbrella §6.4 carry-forward): ForeignKeyComparator detects
 * PostgreSQL DEFERRABLE / INITIALLY DEFERRED drift.
 */
class ForeignKeyComparatorPhaseDTest extends TestCase
{
    /**
     * @return \Propel\Generator\Model\ForeignKey
     */
    private function makeFk(): ForeignKey
    {
        $database = new Database();

        $localTable = new Table('post');
        $database->addTable($localTable);
        $authorIdCol = new Column('author_id');
        $authorIdCol->getDomain()->setSqlType('INTEGER');
        $localTable->addColumn($authorIdCol);

        $foreignTable = new Table('author');
        $database->addTable($foreignTable);
        $idCol = new Column('id');
        $idCol->getDomain()->setSqlType('INTEGER');
        $foreignTable->addColumn($idCol);

        $fk = new ForeignKey('fk_post_author');
        $fk->setForeignTableCommonName('author');
        $localTable->addForeignKey($fk);
        $fk->addReference($authorIdCol, $idCol);

        return $fk;
    }

    /**
     * @return void
     */
    public function testNoDriftWhenDeferrableMatches(): void
    {
        $a = $this->makeFk();
        $a->setDeferrable('DEFERRABLE');
        $a->setInitiallyDeferred(true);

        $b = $this->makeFk();
        $b->setDeferrable('DEFERRABLE');
        $b->setInitiallyDeferred(true);

        $this->assertFalse(ForeignKeyComparator::computeDiff($a, $b));
    }

    /**
     * @return void
     */
    public function testDeferrableChangedIsDetected(): void
    {
        $a = $this->makeFk();
        $a->setDeferrable('DEFERRABLE');
        $b = $this->makeFk();
        $b->setDeferrable('NOT DEFERRABLE');

        $this->assertTrue(ForeignKeyComparator::computeDiff($a, $b));
    }

    /**
     * @return void
     */
    public function testNullVsNotDeferrableEquivalent(): void
    {
        // Existing schemas without `deferrable` shouldn't drift against
        // schemas that explicitly set the platform default.
        $a = $this->makeFk();
        $b = $this->makeFk();
        $b->setDeferrable('NOT DEFERRABLE');

        $this->assertFalse(ForeignKeyComparator::computeDiff($a, $b));
    }

    /**
     * @return void
     */
    public function testDeferrableCaseInsensitive(): void
    {
        $a = $this->makeFk();
        $a->setDeferrable('deferrable');
        $b = $this->makeFk();
        $b->setDeferrable('DEFERRABLE');

        $this->assertFalse(ForeignKeyComparator::computeDiff($a, $b));
    }

    /**
     * @return void
     */
    public function testInitiallyDeferredFlipIsDetected(): void
    {
        $a = $this->makeFk();
        $a->setDeferrable('DEFERRABLE');
        $a->setInitiallyDeferred(true);
        $b = $this->makeFk();
        $b->setDeferrable('DEFERRABLE');
        $b->setInitiallyDeferred(false);

        $this->assertTrue(ForeignKeyComparator::computeDiff($a, $b));
    }
}
