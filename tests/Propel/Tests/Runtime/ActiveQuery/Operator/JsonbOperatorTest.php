<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\ActiveQuery\Operator;

use Propel\Runtime\ActiveQuery\Operator\JsonbOperator;
use Propel\Tests\TestCase;

/**
 * Phase C (umbrella §6.4) — JsonbOperator helper for PG JSONB operators.
 */
class JsonbOperatorTest extends TestCase
{
    /**
     * @return void
     */
    public function testRawValues(): void
    {
        $this->assertSame('?', JsonbOperator::ContainsKey->value);
        $this->assertSame('?&', JsonbOperator::ContainsAll->value);
        $this->assertSame('?|', JsonbOperator::ContainsAny->value);
        $this->assertSame('@>', JsonbOperator::JsonbContains->value);
        $this->assertSame('<@', JsonbOperator::JsonbContainedBy->value);
    }

    /**
     * @return void
     */
    public function testToSqlEscapesQuestionMark(): void
    {
        $this->assertSame('??', JsonbOperator::ContainsKey->toSql());
        $this->assertSame('??&', JsonbOperator::ContainsAll->toSql());
        $this->assertSame('??|', JsonbOperator::ContainsAny->toSql());
    }

    /**
     * @return void
     */
    public function testToSqlLeavesNonQuestionMarkOperatorsUnchanged(): void
    {
        $this->assertSame('@>', JsonbOperator::JsonbContains->toSql());
        $this->assertSame('<@', JsonbOperator::JsonbContainedBy->toSql());
    }

    /**
     * @return void
     */
    public function testBuildClauseWithContainsKey(): void
    {
        $clause = JsonbOperator::buildClause('event_log.payload', JsonbOperator::ContainsKey, ':key');
        $this->assertSame('event_log.payload ?? :key', $clause);
    }

    /**
     * @return void
     */
    public function testBuildClauseWithJsonbContains(): void
    {
        $clause = JsonbOperator::buildClause('event_log.payload', JsonbOperator::JsonbContains, ':subset');
        $this->assertSame('event_log.payload @> :subset', $clause);
    }
}
