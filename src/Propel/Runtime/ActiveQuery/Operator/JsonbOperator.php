<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Operator;

/**
 * PostgreSQL JSONB operator helpers.
 *
 * Phase C (umbrella §6.4): a Tier 2 SPI commitment for the 3.x line. Use this
 * helper when constructing raw JSONB clauses so that PDO's positional `?`
 * placeholder doesn't collide with PG's literal `?` JSONB operator. PG's
 * `?` operator is escaped as `??` per PDO::ATTR_EMULATE_PREPARES semantics.
 *
 * Example:
 * <code>
 *   $clause = JsonbOperator::buildClause('event_log.payload', JsonbOperator::ContainsKey, ':key');
 *   // event_log.payload ?? :key
 * </code>
 *
 * @see docs/MIGRATION-FROM-PRE-AI.md for the full PG JSONB cookbook.
 */
enum JsonbOperator: string
{
    /**
     * `?` — Does the JSONB value contain the given top-level key (string)?
     * PDO escape: `??` (the `?` is doubled to avoid placeholder collision).
     */
    case ContainsKey = '?';

    /**
     * `?&` — Does the JSONB value contain ALL of the given top-level keys?
     * PDO escape: `??&`.
     */
    case ContainsAll = '?&';

    /**
     * `?|` — Does the JSONB value contain ANY of the given top-level keys?
     * PDO escape: `??|`.
     */
    case ContainsAny = '?|';

    /**
     * `@>` — Does the left JSONB value contain (as a superset) the right value?
     * No PDO escape needed — this operator does not contain `?`.
     */
    case JsonbContains = '@>';

    /**
     * `<@` — Is the left JSONB value contained by the right value?
     * No PDO escape needed.
     */
    case JsonbContainedBy = '<@';

    /**
     * Returns the SQL operator string with PDO-positional-placeholder escaping
     * applied where needed (any `?` is doubled to `??`).
     *
     * @return string
     */
    public function toSql(): string
    {
        return str_replace('?', '??', $this->value);
    }

    /**
     * Builds an SQL clause of the form: `<column> <op> <bind>`.
     *
     * @psalm-api
     *
     * @param string $column the qualified column reference (already quoted as needed)
     * @param self $op
     * @param string $bind the placeholder, typically a named placeholder like ":key" or "?"
     *
     * @return string
     */
    public static function buildClause(string $column, self $op, string $bind): string
    {
        return sprintf('%s %s %s', $column, $op->toSql(), $bind);
    }
}
