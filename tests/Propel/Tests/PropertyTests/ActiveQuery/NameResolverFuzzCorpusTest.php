<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\PropertyTests\ActiveQuery;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Compiler\NameResolver;

/**
 * Differential check: every line of the committed seed corpus is fed through
 * both the legacy implementation and the new NameResolver; results must be
 * byte-identical EXCEPT on inputs containing backticks (the documented
 * divergence — see NameResolverTokenEquivalenceTest::testIntentionalBacktickDivergence).
 *
 * The corpus is checked into `tests/Fuzzing/ActiveQuery/corpus/seed.txt` —
 * NOT regenerated per CI run (otherwise non-reproducible).
 *
 * Per umbrella §4.14 + Phase F plan F.2.5.
 */
class NameResolverFuzzCorpusTest extends TestCase
{
    private const CORPUS_PATH = __DIR__ . '/../../../../Fuzzing/ActiveQuery/corpus/seed.txt';

    public function testCorpusInputsMatchByteEquivalent(): void
    {
        $path = realpath(self::CORPUS_PATH);
        self::assertNotFalse($path, 'corpus file missing: ' . self::CORPUS_PATH);

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        self::assertNotFalse($lines);
        self::assertGreaterThan(900, count($lines), 'corpus too small');

        $resolver = new NameResolver();
        $cb = static fn (array $matches): string => '<<' . $matches[0] . '>>';

        $divergences = 0;
        $allowedDivergences = 0;
        foreach ($lines as $idx => $sql) {
            $legacy = LegacyReplaceNames::run($sql, $cb);
            $modern = $resolver->resolveWithMatchesCallback($sql, $cb);

            if ($legacy === $modern) {
                continue;
            }

            // Only allowlisted divergence: input contains a backtick.
            if (str_contains($sql, '`')) {
                $allowedDivergences++;

                continue;
            }

            $divergences++;
            self::fail(sprintf(
                "Line %d byte-divergence (no allowlist):\n  input:  %s\n  legacy: %s\n  modern: %s",
                $idx,
                var_export($sql, true),
                var_export($legacy, true),
                var_export($modern, true),
            ));
        }

        self::assertSame(0, $divergences, 'unexpected divergences');
        // The corpus generator does NOT emit backticks; allowedDivergences should be 0
        self::assertSame(0, $allowedDivergences, 'corpus produced unexpected backtick inputs');
    }
}
