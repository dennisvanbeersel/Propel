<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Model;

use Propel\Generator\Exception\EngineException;
use Propel\Generator\Model\CheckConstraint;
use Propel\Generator\Model\Table;

/**
 * Phase C (umbrella §6.4): CheckConstraint model class coverage.
 */
class CheckConstraintTest extends ModelTestCase
{
    /**
     * @return void
     */
    public function testConstructDefaults(): void
    {
        $cc = new CheckConstraint();

        // No expression yet → name uses 'anon' table-part and md5('')
        $this->assertSame('ck_anon_d41d8cd9', $cc->getName());
        $this->assertSame('', $cc->getExpression());
        $this->assertTrue($cc->isEnforced());
    }

    /**
     * @return void
     */
    public function testConstructWithArgs(): void
    {
        $cc = new CheckConstraint('ck_orders_qty', 'quantity > 0', false);

        $this->assertSame('ck_orders_qty', $cc->getName());
        $this->assertSame('quantity > 0', $cc->getExpression());
        $this->assertFalse($cc->isEnforced());
    }

    /**
     * @return void
     */
    public function testLoadMappingFromXml(): void
    {
        $cc = new CheckConstraint();
        $cc->loadMapping([
            'name' => 'ck_orders_discount',
            'expression' => 'discount_pct BETWEEN 0 AND 100',
            'enforced' => 'false',
        ]);

        $this->assertSame('ck_orders_discount', $cc->getName());
        $this->assertSame('discount_pct BETWEEN 0 AND 100', $cc->getExpression());
        $this->assertFalse($cc->isEnforced());
    }

    /**
     * @return void
     */
    public function testLoadMappingDefaultsEnforcedTrue(): void
    {
        $cc = new CheckConstraint();
        $cc->loadMapping([
            'name' => 'ck_x',
            'expression' => 'x > 0',
        ]);

        $this->assertTrue($cc->isEnforced());
    }

    /**
     * @return void
     */
    public function testLoadMappingRejectsEmptyExpression(): void
    {
        $cc = new CheckConstraint();

        $this->expectException(EngineException::class);
        $this->expectExceptionMessageMatches('/non-empty expression/');

        $cc->loadMapping(['expression' => '']);
    }

    /**
     * @return void
     */
    public function testAutoNamingWithTable(): void
    {
        $table = new Table('orders');
        $cc = new CheckConstraint(null, 'quantity > 0');
        $cc->setTable($table);

        $name = $cc->getName();
        $this->assertStringStartsWith('ck_orders_', $name);
        $this->assertSame(8, strlen(substr($name, strrpos($name, '_') + 1)));
    }

    /**
     * @return void
     */
    public function testSettersAndGetters(): void
    {
        $cc = new CheckConstraint();
        $cc->setName('manual');
        $cc->setExpression('a + b > 0');
        $cc->setEnforced(false);

        $this->assertSame('manual', $cc->getName());
        $this->assertSame('a + b > 0', $cc->getExpression());
        $this->assertFalse($cc->isEnforced());
    }

    /**
     * @return void
     */
    public function testSetTableAndGetTable(): void
    {
        $table = new Table('t');
        $cc = new CheckConstraint('ck_x', 'x > 0');
        $cc->setTable($table);

        $this->assertSame($table, $cc->getTable());
    }

    /**
     * @return void
     */
    public function testIsEquivalent(): void
    {
        $a = new CheckConstraint('ck_x', 'x > 0');
        $b = new CheckConstraint('ck_x', 'x > 0');
        $c = new CheckConstraint('ck_x', 'x >= 0');
        $d = new CheckConstraint('ck_x', 'x > 0', false);

        $this->assertTrue($a->isEquivalent($b));
        $this->assertFalse($a->isEquivalent($c));
        $this->assertFalse($a->isEquivalent($d));
    }
}
