<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Model\Diff;

use Propel\Generator\Model\Index;

/**
 * Service class for comparing Index objects
 * Heavily inspired by Doctrine2's Migrations
 * (see http://github.com/doctrine/dbal/tree/master/lib/Doctrine/DBAL/Schema/)
 */
class IndexComparator
{
    /**
     * Computes the difference between two index objects.
     *
     * @param \Propel\Generator\Model\Index $fromIndex
     * @param \Propel\Generator\Model\Index $toIndex
     * @param bool $caseInsensitive
     *
     * @return bool
     */
    public static function computeDiff(Index $fromIndex, Index $toIndex, bool $caseInsensitive = false): bool
    {
        // Check for removed index columns in $toIndex
        $fromIndexColumns = $fromIndex->getColumns();
        $max = count($fromIndexColumns);
        for ($i = 0; $i < $max; $i++) {
            $indexColumn = $fromIndexColumns[$i];
            if (!$toIndex->hasColumnAtPosition($i, $indexColumn, $fromIndex->getColumnSize($indexColumn), $caseInsensitive)) {
                return true;
            }
        }

        // Check for new index columns in $toIndex
        $toIndexColumns = $toIndex->getColumns();
        $max = count($toIndexColumns);
        for ($i = 0; $i < $max; $i++) {
            $indexColumn = $toIndexColumns[$i];
            if (!$fromIndex->hasColumnAtPosition($i, $indexColumn, $toIndex->getColumnSize($indexColumn), $caseInsensitive)) {
                return true;
            }
        }

        // Check for difference in unicity
        if ($fromIndex->isUnique() !== $toIndex->isUnique()) {
            return true;
        }

        // Phase D (umbrella §6.4 carry-forward): partial-index WHERE-clause drift.
        // PG: `CREATE INDEX ... WHERE status = 'active'`. Whitespace-normalised
        // comparison so cosmetic reformatting doesn't trigger a diff.
        if (self::normaliseWhitespace($fromIndex->getWhereClause()) !== self::normaliseWhitespace($toIndex->getWhereClause())) {
            return true;
        }

        // Phase D (umbrella §6.4 carry-forward): index-type drift
        // (USING gin|gist|hash|btree). Case-insensitive comparison since
        // PG accepts either case and normalises to lowercase internally.
        $fromType = $fromIndex->getIndexType();
        $toType = $toIndex->getIndexType();
        if (
            ($fromType === null) !== ($toType === null)
            || ($fromType !== null && $toType !== null && strcasecmp($fromType, $toType) !== 0)
        ) {
            return true;
        }

        return false;
    }

    /**
     * Phase D: collapse runs of whitespace to a single space and trim, so that
     * cosmetic differences in WHERE clauses (line-wrap, leading/trailing
     * whitespace) don't register as drift. Returns null unchanged.
     *
     * @param string|null $clause
     *
     * @return string|null
     */
    private static function normaliseWhitespace(?string $clause): ?string
    {
        if ($clause === null) {
            return null;
        }
        $collapsed = preg_replace('/\s+/', ' ', trim($clause));

        return $collapsed === null ? $clause : $collapsed;
    }
}
