<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Compiler;

/**
 * Tier 2 SPI per umbrella §2.2 — cache-key shape for prepared statement caching.
 *
 * Adopted from Phase E.4's CachingConnection::buildCacheKey shape (set inline
 * during Phase E; published here as a Tier 2 SPI per the Phase E end-of-phase
 * inversion documented in docs/PHASE-E-SUMMARY.md).
 *
 * Shape: `$sql` when `$driverOptions` is empty; otherwise `$sql . "\0" . serialize(ksort($driverOptions))`.
 * `ksort` normalizes `$driverOptions` order so semantically-equivalent arrays
 * produce identical keys.
 *
 * @api Tier 2 — frozen for the 3.x line. Shape change requires a deprecation runway.
 */
final class PreparedStatementKey
{
    /**
     * Build a stable cache key for a prepared statement.
     *
     * @param string $sql
     * @param array<int|string, mixed> $driverOptions
     *
     * @return string
     */
    public static function forSql(string $sql, array $driverOptions = []): string
    {
        if ($driverOptions === []) {
            return $sql;
        }

        ksort($driverOptions);

        return $sql . "\0" . serialize($driverOptions);
    }

    /**
     * Constant-time comparison — defensive against any future key-comparison
     * use site that could leak information through timing variation.
     *
     * @param string $a
     * @param string $b
     *
     * @return bool
     */
    public static function equals(string $a, string $b): bool
    {
        return hash_equals($a, $b);
    }
}
