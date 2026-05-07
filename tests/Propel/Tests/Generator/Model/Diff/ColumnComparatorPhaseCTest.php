<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Model\Diff;

use Propel\Generator\Model\Column;
use Propel\Generator\Model\Diff\ColumnComparator;
use Propel\Generator\Platform\MysqlPlatform;
use Propel\Tests\TestCase;

/**
 * Phase C (umbrella §6.4) — ColumnComparator detects collation/comment/CHECK/generated/INVISIBLE drift.
 */
class ColumnComparatorPhaseCTest extends TestCase
{
    private MysqlPlatform $platform;

    protected function setUp(): void
    {
        $this->platform = new MysqlPlatform();
    }

    /**
     * @return void
     */
    public function testNoDriftWhenSame(): void
    {
        $a = new Column('x');
        $a->getDomain()->copy($this->platform->getDomainForType('VARCHAR'));
        $a->getDomain()->replaceSize(255);

        $b = new Column('x');
        $b->getDomain()->copy($this->platform->getDomainForType('VARCHAR'));
        $b->getDomain()->replaceSize(255);

        $this->assertSame([], ColumnComparator::compareColumns($a, $b));
    }

    /**
     * @return void
     */
    public function testDescriptionDrift(): void
    {
        $a = new Column('x');
        $a->getDomain()->copy($this->platform->getDomainForType('VARCHAR'));
        $a->getDomain()->setDescription('old');

        $b = new Column('x');
        $b->getDomain()->copy($this->platform->getDomainForType('VARCHAR'));
        $b->getDomain()->setDescription('new');

        $diff = ColumnComparator::compareColumns($a, $b);
        $this->assertArrayHasKey('description', $diff);
        $this->assertSame(['old', 'new'], $diff['description']);
    }

    /**
     * @return void
     */
    public function testInvisibleDrift(): void
    {
        $a = new Column('x');
        $a->getDomain()->copy($this->platform->getDomainForType('VARCHAR'));

        $b = new Column('x');
        $b->getDomain()->copy($this->platform->getDomainForType('VARCHAR'));
        $b->setInvisible(true);

        $diff = ColumnComparator::compareColumns($a, $b);
        $this->assertArrayHasKey('invisible', $diff);
        $this->assertSame([false, true], $diff['invisible']);
    }

    /**
     * @return void
     */
    public function testGenerationKindDrift(): void
    {
        $a = new Column('x');
        $a->getDomain()->copy($this->platform->getDomainForType('VARCHAR'));

        $b = new Column('x');
        $b->getDomain()->copy($this->platform->getDomainForType('VARCHAR'));
        $b->setGenerated('stored', "'a'");

        $diff = ColumnComparator::compareColumns($a, $b);
        $this->assertArrayHasKey('generationKind', $diff);
        $this->assertArrayHasKey('generationExpression', $diff);
        $this->assertSame([null, 'stored'], $diff['generationKind']);
        $this->assertSame([null, "'a'"], $diff['generationExpression']);
    }

    /**
     * @return void
     */
    public function testGenerationExpressionDrift(): void
    {
        $a = new Column('x');
        $a->getDomain()->copy($this->platform->getDomainForType('VARCHAR'));
        $a->setGenerated('stored', 'a + b');

        $b = new Column('x');
        $b->getDomain()->copy($this->platform->getDomainForType('VARCHAR'));
        $b->setGenerated('stored', 'a + b + c');

        $diff = ColumnComparator::compareColumns($a, $b);
        $this->assertArrayHasKey('generationExpression', $diff);
        $this->assertArrayNotHasKey('generationKind', $diff);
    }
}
