<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Manager;

use RuntimeException;

/**
 * SHA-256 over the normalized body of a migration file.
 *
 * Phase H drift-detection helper. Recorded at apply time, verified at
 * next-run time. Whitespace + comments are stripped via
 * `php_strip_whitespace()` so cosmetic edits do not trigger false drift.
 *
 * Tier 3 with stability commitment to the public method shapes:
 *   - `compute(string $filePath): string` — returns 64 hex chars.
 *   - `verify(string $expectedHex, string $filePath): bool` — true if equal
 *     or if `$expectedHex` is empty / wrong length (legacy / baseline rows).
 *   - `ALGORITHM` const — pinned to "sha256" (HEX_LENGTH = 64).
 *
 * @psalm-api
 */
final class MigrationCheckSummer
{
    /**
     * @var string
     */
    public const string ALGORITHM = 'sha256';

    /**
     * @var int
     */
    public const int HEX_LENGTH = 64;

    /**
     * Compute the SHA-256 hex digest over the file's normalized body.
     *
     * @param string $migrationFilePath
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function compute(string $migrationFilePath): string
    {
        if (!is_file($migrationFilePath) || !is_readable($migrationFilePath)) {
            throw new RuntimeException(sprintf(
                'Migration file "%s" is not readable; cannot compute checksum.',
                $migrationFilePath,
            ));
        }
        $normalized = php_strip_whitespace($migrationFilePath);

        return hash(self::ALGORITHM, $normalized);
    }

    /**
     * Compare a recorded hex against the on-disk checksum.
     *
     * Empty / wrong-length expected hexes are treated as "not yet
     * recorded" (legacy or baseline rows) and report verified-true so
     * those rows do not surface as drift.
     *
     * @param string $expectedHex
     * @param string $migrationFilePath
     *
     * @return bool
     */
    public function verify(string $expectedHex, string $migrationFilePath): bool
    {
        if ($expectedHex === '' || strlen($expectedHex) !== self::HEX_LENGTH) {
            return true;
        }

        return hash_equals($expectedHex, $this->compute($migrationFilePath));
    }
}
