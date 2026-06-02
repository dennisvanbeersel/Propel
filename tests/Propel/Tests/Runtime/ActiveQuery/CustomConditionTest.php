<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\ActiveQuery;

use BadMethodCallException;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Criterion\CustomCriterion;
use Propel\Runtime\ActiveQuery\Criterion\Exception\UnsafeCustomConditionException;

/**
 * Phase F task F.7 — parameterized customCondition() replaces raw Criteria::CUSTOM.
 * Phase G.2.5 — raw add() path is now a hard error in 4.0.
 *
 * Closes umbrella §6.2 risk #2 (raw-SQL injection vector).
 */
class CustomConditionTest extends TestCase
{
    public function testRawCustomAddIsHardErrorInPropel4(): void
    {
        $crit = new Criteria();

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Criteria::add($name, $sql, Criteria::CUSTOM) was removed in Propel 4.0');

        $crit->add('foo', '1 = 1', Criteria::CUSTOM);
    }

    public function testRawCustomAddWithEmptyStringValueRemainsBenignNoOp(): void
    {
        // Carve-out per plan §G.2.5: empty-string $value is harmless because
        // CustomCriterion::appendPsForUniqueClauseTo() returns early on '' — the SQL
        // never enters the WHERE clause. Null $value is intentionally NOT carved out
        // because CriterionFactory would TypeError before the early-return runs.
        $crit = new Criteria();
        $crit->add('foo', '', Criteria::CUSTOM);

        self::assertNotEmpty($crit->getMap());
    }

    public function testCustomConditionAcceptsParameterizedSql(): void
    {
        $crit = new Criteria();
        $crit->customCondition('price_range', 'price BETWEEN ? AND ?', [10, 100]);

        // Inspect the resulting Criterion via the namedCriterions accessor surface
        // (the test asserts the criterion landed under the right name).
        $reflect = new \ReflectionProperty(Criteria::class, 'namedCriterions');
        $named = $reflect->getValue($crit);

        self::assertArrayHasKey('price_range', $named);
        self::assertInstanceOf(CustomCriterion::class, $named['price_range']);
        self::assertSame([10, 100], $named['price_range']->getBoundParams());
    }

    public function testCustomConditionWithNoParamsAcceptsSafeSql(): void
    {
        $crit = new Criteria();
        $crit->customCondition('flag', 'is_active = TRUE');

        $reflect = new \ReflectionProperty(Criteria::class, 'namedCriterions');
        $named = $reflect->getValue($crit);

        self::assertArrayHasKey('flag', $named);
    }

    public function testCustomConditionRejectsLiteralSingleQuoteWithoutParams(): void
    {
        $crit = new Criteria();
        $this->expectException(UnsafeCustomConditionException::class);
        $crit->customCondition('flag', "name = 'foo'");
    }

    public function testCustomConditionRejectsLiteralDoubleQuoteWithoutParams(): void
    {
        $crit = new Criteria();
        $this->expectException(UnsafeCustomConditionException::class);
        $crit->customCondition('flag', 'name = "foo"');
    }

    public function testCustomConditionAllowRawSqlBypassesHeuristic(): void
    {
        $crit = new Criteria();
        $crit->customCondition('flag', "name = 'audited'", [], allowRawSql: true);

        $reflect = new \ReflectionProperty(Criteria::class, 'namedCriterions');
        $named = $reflect->getValue($crit);

        self::assertArrayHasKey('flag', $named);
    }

    public function testCustomCriterionParameterizedAppendsViaPdoBinding(): void
    {
        $crit = new Criteria();
        $criterion = new CustomCriterion($crit, 'price BETWEEN ? AND ?', [10, 100]);
        $sb = '';
        $params = [];
        $criterion->appendPsTo($sb, $params);

        self::assertSame('price BETWEEN :p1 AND :p2', $sb);
        self::assertCount(2, $params);
        self::assertSame(10, $params[0]['value']);
        self::assertSame(100, $params[1]['value']);
    }

    public function testLegacyRawCustomCriterionStillWorks(): void
    {
        // The classic 1-arg constructor (no $params) preserves verbatim raw-SQL inlining
        $crit = new Criteria();
        $criterion = new CustomCriterion($crit, '1 = 1');
        $sb = '';
        $params = [];
        $criterion->appendPsTo($sb, $params);

        self::assertSame('1 = 1', $sb);
        self::assertSame([], $params);
    }
}
