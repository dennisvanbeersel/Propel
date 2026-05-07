<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\ActiveQuery;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Criterion\CustomCriterion;
use Propel\Runtime\ActiveQuery\Criterion\Exception\UnsafeCustomConditionException;
use Symfony\Bridge\PhpUnit\ExpectUserDeprecationMessageTrait;

/**
 * Phase F task F.7 — parameterized customCondition() replaces raw Criteria::CUSTOM.
 *
 * Closes umbrella §6.2 risk #2 (raw-SQL injection vector).
 */
#[Group('legacy')]
class CustomConditionTest extends TestCase
{
    use ExpectUserDeprecationMessageTrait;

    public function testRawCustomEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Criteria::add($name, $sql, Criteria::CUSTOM) interpolates raw SQL — vulnerable to injection. Use Criteria::customCondition($name, $sql, $params) instead. Removal of raw CUSTOM is not currently scheduled, but new code should use the parameterized form.',
        );

        $crit = new Criteria();
        $crit->add('foo', '1 = 1', Criteria::CUSTOM);
        self::assertTrue($crit->getMap() !== []);
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
