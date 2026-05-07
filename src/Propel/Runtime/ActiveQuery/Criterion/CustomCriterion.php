<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Criterion;

use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Specialized Criterion used for custom expressions.
 *
 * Two construction paths:
 * - Legacy: `new CustomCriterion($outer, $rawSql)` — raw SQL inlined verbatim
 *   into the WHERE clause. Used by `Criteria::add($name, $rawSql, Criteria::CUSTOM)`.
 *   This is the path Phase F deprecates as a SQL-injection vector (see umbrella §6.2 risk #2).
 * - Parameterized (Phase F.7): `new CustomCriterion($outer, $sqlWithPlaceholders, $params)`.
 *   `$params` are bound through the standard prepared-statement pipeline used by
 *   BasicCriterion/InCriterion. Use via `Criteria::customCondition()`.
 */
class CustomCriterion extends AbstractCriterion
{
    /**
     * @var list<mixed>
     */
    private array $boundParams = [];

    /**
     * Create a new instance.
     *
     * @param \Propel\Runtime\ActiveQuery\Criteria $outer The outer class (this is an "inner" class).
     * @param string $value The condition to be added to the query string
     * @param array<int|string, mixed> $boundParams Optional bound parameters when $value contains
     *   `:name` placeholders that should be substituted via PDO at execution time.
     *   Empty list (default) preserves the legacy raw-SQL semantics.
     */
    public function __construct(Criteria $outer, string $value, array $boundParams = [])
    {
        $this->value = $value;
        $this->boundParams = array_values($boundParams);
        $this->init($outer);
    }

    /**
     * @return list<mixed>
     *
     * @psalm-api
     */
    public function getBoundParams(): array
    {
        return $this->boundParams;
    }

    /**
     * Appends a Prepared Statement representation of the Criterion onto the buffer
     *
     * @param string $sb The string that will receive the Prepared Statement
     * @param array $params A list to which Prepared Statement parameters will be appended
     *
     * @return void
     */
    #[\Override]
    protected function appendPsForUniqueClauseTo(string &$sb, array &$params): void
    {
        if ($this->value === '') {
            return;
        }

        if ($this->boundParams === []) {
            // Legacy raw-SQL path — value is inlined verbatim. Deprecated in 3.0
            // when reached through Criteria::add($n, $sql, Criteria::CUSTOM).
            $sb .= $this->value;

            return;
        }

        // Parameterized path: replace :name placeholders with positional :pN binds.
        // The order of $boundParams determines the binding order; the keys match
        // the user-supplied $params keys.
        $sql = $this->value;
        foreach ($this->boundParams as $value) {
            $params[] = ['table' => null, 'column' => null, 'value' => $value];
            $sql = (string)preg_replace('/\?/', ':p' . count($params), $sql, 1);
        }
        $sb .= $sql;
    }
}
