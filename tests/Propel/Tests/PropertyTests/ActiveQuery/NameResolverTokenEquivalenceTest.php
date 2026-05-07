<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\PropertyTests\ActiveQuery;

use Innmind\BlackBox\Random;
use Innmind\BlackBox\Set;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Compiler\NameResolver;

/**
 * Property-based test asserting byte-equivalence between the legacy hand-rolled
 * Criteria::replaceNames implementation (captured in LegacyReplaceNames) and the
 * new tokenizer-driven NameResolver, on a synthesised corpus.
 *
 * Documented divergences (excluded from the corpus):
 * - Backtick-quoted identifiers — legacy treats backticks as plain chars; new
 *   resolver treats them as quoting (per F.2 plan). Tested separately.
 *
 * Per umbrella §4.11 + Phase F plan F.2.4.
 */
class NameResolverTokenEquivalenceTest extends TestCase
{
    /**
     * Per-match callback equivalent to Criteria::doReplaceNameInExpression
     * (returns input verbatim — the equivalence we want is on the parsing side,
     * not on the replacement logic).
     */
    private function identityCallback(): callable
    {
        return static fn (array $matches): string => '<<' . $matches[0] . '>>';
    }

    public function testSyntheticCorpusByteEquivalence(): void
    {
        $resolver = new NameResolver();
        $cb = $this->identityCallback();

        // Generators compose into mixed SQL fragments. Excludes backticks.
        $names = Set::of(
            'Book.AuthorID',
            'b.title',
            'Author.Name',
            'a.id',
            'My\\Cls.col',
            't1.col1',
            'Book.X',
        );
        $strings = Set::of(
            "'plain'",
            "'foo''bar'",
            "'a\\'b'",
            '"plain"',
            '"foo""bar"',
            "'has Book.AuthorID inside'",
        );
        $operators = Set::of(' = ', ' < ', ' > ', ' <> ', ' LIKE ', ' AND ', ' OR ', ',');
        $literals = Set::of('?', '1', '42', '1.5', "'lit'");

        $atomChoices = Set::either($names, $strings, $literals);
        $segments = Set::compose(
            static fn (string $a, string $op, string $b): string => $a . $op . $b,
            $atomChoices,
            $operators,
            $atomChoices,
        );

        // 200 distinct generated cases (the full 10k corpus is in the F.2.5 fuzz file)
        $count = 0;
        foreach ($segments->take(200)->values(Random::default) as $value) {
            $sql = $value->unwrap();
            $expected = LegacyReplaceNames::run($sql, $cb);
            $actual = $resolver->resolveWithMatchesCallback($sql, $cb);
            self::assertSame($expected, $actual, 'Divergence on input: ' . var_export($sql, true));
            $count++;
        }
        self::assertSame(200, $count);
    }

    /**
     * Hand-picked edge cases that historically broke the legacy parser or are easy
     * to regress.
     */
    #[DataProvider('edgeCaseProvider')]
    public function testEdgeCasesByteEquivalent(string $sql): void
    {
        $resolver = new NameResolver();
        $cb = $this->identityCallback();
        self::assertSame(
            LegacyReplaceNames::run($sql, $cb),
            $resolver->resolveWithMatchesCallback($sql, $cb),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function edgeCaseProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'whitespace only' => ['   '];
        yield 'plain qualified' => ['Book.AuthorID = ?'];
        yield 'mixed quote styles' => ["a.b = 'foo' AND c.d = \"bar\""];
        yield 'doubled single' => ["a.b = 'foo''bar'"];
        yield 'doubled double' => ['a.b = "foo""bar"'];
        yield 'backslash escape single' => ["a.b = 'a\\'b'"];
        yield 'backslash escape double' => ['a.b = "a\\"b"'];
        yield 'unterminated single' => ["a.b = 'unclosed"];
        yield 'unterminated double' => ['a.b = "unclosed'];
        yield 'named in string' => ["'Book.AuthorID = ?'"];
        yield 'no match' => ['just plain text'];
        yield 'numbers' => ['a.b = 1.5e-3'];
        yield 'parens' => ['(Book.AuthorID = ?)'];
        yield 'multiple' => ['Book.X = ? AND Author.Name = ?'];
        yield 'namespaced class' => ['My\\Cls.col = ?'];
    }

    public function testIntentionalBacktickDivergence(): void
    {
        // Legacy treats backticks as plain chars — replacement happens INSIDE
        // backtick-quoted segments. New resolver treats them as quoting —
        // replacement does NOT happen inside.
        $resolver = new NameResolver();
        $cb = $this->identityCallback();
        $sql = '`Book.AuthorID`';

        $legacy = LegacyReplaceNames::run($sql, $cb);
        $modern = $resolver->resolveWithMatchesCallback($sql, $cb);

        // Documented divergence — legacy DID replace inside backticks; new does not.
        self::assertSame('`<<Book.AuthorID>>`', $legacy);
        self::assertSame('`Book.AuthorID`', $modern);
    }
}
