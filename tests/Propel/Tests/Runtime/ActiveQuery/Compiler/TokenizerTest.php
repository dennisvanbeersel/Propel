<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\ActiveQuery\Compiler;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Compiler\Token;
use Propel\Runtime\ActiveQuery\Compiler\Tokenizer;

class TokenizerTest extends TestCase
{
    private function tokenizer(): Tokenizer
    {
        return new Tokenizer();
    }

    /**
     * @return list<array{string, string}>  list of [type, value] pairs
     */
    private function typesAndValues(string $sql): array
    {
        $out = [];
        foreach ($this->tokenizer()->tokenize($sql) as $token) {
            $out[] = [$token->type, $token->value];
        }

        return $out;
    }

    public function testEmptyInputProducesNoTokens(): void
    {
        self::assertSame([], $this->tokenizer()->tokenize(''));
    }

    public function testWhitespaceOnly(): void
    {
        self::assertSame(
            [[Token::TYPE_WS, "  \t\n"]],
            $this->typesAndValues("  \t\n"),
        );
    }

    public function testIdentifier(): void
    {
        self::assertSame(
            [[Token::TYPE_IDENT, 'book']],
            $this->typesAndValues('book'),
        );
    }

    public function testQualifiedIdentifier(): void
    {
        self::assertSame(
            [[Token::TYPE_IDENT, 'book.author_id']],
            $this->typesAndValues('book.author_id'),
        );
    }

    public function testNamespacedIdentifier(): void
    {
        // legacy regex `/[\w\\\]+\.\w+/` matches namespaced class refs
        self::assertSame(
            [[Token::TYPE_IDENT, 'My\\Cls.column']],
            $this->typesAndValues('My\\Cls.column'),
        );
    }

    public function testSingleQuotedString(): void
    {
        self::assertSame(
            [[Token::TYPE_STRING, "'foo'"]],
            $this->typesAndValues("'foo'"),
        );
    }

    public function testDoubleQuotedString(): void
    {
        self::assertSame(
            [[Token::TYPE_STRING, '"foo"']],
            $this->typesAndValues('"foo"'),
        );
    }

    public function testStringWithBackslashEscape(): void
    {
        self::assertSame(
            [[Token::TYPE_STRING, "'a\\'b'"]],
            $this->typesAndValues("'a\\'b'"),
        );
    }

    public function testStringWithDoubledQuoteEscape(): void
    {
        // ANSI: '' inside a single-quoted string is an escaped single quote
        self::assertSame(
            [[Token::TYPE_STRING, "'foo''bar'"]],
            $this->typesAndValues("'foo''bar'"),
        );
    }

    public function testUnterminatedStringHealsToEof(): void
    {
        self::assertSame(
            [[Token::TYPE_STRING, "'foo"]],
            $this->typesAndValues("'foo"),
        );
    }

    public function testLineComment(): void
    {
        self::assertSame(
            [[Token::TYPE_LCOMMENT, '-- a comment']],
            $this->typesAndValues('-- a comment'),
        );
    }

    public function testLineCommentTerminatedByNewline(): void
    {
        self::assertSame(
            [
                [Token::TYPE_LCOMMENT, '-- comment'],
                [Token::TYPE_WS, "\n"],
                [Token::TYPE_IDENT, 'next'],
            ],
            $this->typesAndValues("-- comment\nnext"),
        );
    }

    public function testBlockComment(): void
    {
        self::assertSame(
            [[Token::TYPE_BCOMMENT, '/* a comment */']],
            $this->typesAndValues('/* a comment */'),
        );
    }

    public function testBlockCommentMultiline(): void
    {
        self::assertSame(
            [[Token::TYPE_BCOMMENT, "/* a\nb */"]],
            $this->typesAndValues("/* a\nb */"),
        );
    }

    public function testUnterminatedBlockCommentHealsToEof(): void
    {
        self::assertSame(
            [[Token::TYPE_BCOMMENT, '/* not closed']],
            $this->typesAndValues('/* not closed'),
        );
    }

    public function testBacktickIdent(): void
    {
        self::assertSame(
            [[Token::TYPE_BACKTICK_IDENT, '`book.author`']],
            $this->typesAndValues('`book.author`'),
        );
    }

    public function testNumberInteger(): void
    {
        self::assertSame(
            [[Token::TYPE_NUMBER, '123']],
            $this->typesAndValues('123'),
        );
    }

    public function testNumberFloat(): void
    {
        self::assertSame(
            [[Token::TYPE_NUMBER, '1.5']],
            $this->typesAndValues('1.5'),
        );
    }

    public function testNumberLeadingDot(): void
    {
        self::assertSame(
            [[Token::TYPE_NUMBER, '.5']],
            $this->typesAndValues('.5'),
        );
    }

    public function testNumberExponent(): void
    {
        self::assertSame(
            [[Token::TYPE_NUMBER, '1e10']],
            $this->typesAndValues('1e10'),
        );
        self::assertSame(
            [[Token::TYPE_NUMBER, '1.5e-3']],
            $this->typesAndValues('1.5e-3'),
        );
    }

    public function testOperators(): void
    {
        self::assertSame(
            [[Token::TYPE_OP, '=']],
            $this->typesAndValues('='),
        );
        self::assertSame(
            [[Token::TYPE_OP, '<>']],
            $this->typesAndValues('<>'),
        );
        self::assertSame(
            [[Token::TYPE_OP, '<=']],
            $this->typesAndValues('<='),
        );
    }

    public function testPunctuation(): void
    {
        self::assertSame(
            [
                [Token::TYPE_PUNCT, '('],
                [Token::TYPE_IDENT, 'a'],
                [Token::TYPE_PUNCT, ','],
                [Token::TYPE_IDENT, 'b'],
                [Token::TYPE_PUNCT, ')'],
            ],
            $this->typesAndValues('(a,b)'),
        );
    }

    public function testMixedComplexInput(): void
    {
        $sql = "book.author_id = 'foo''bar' AND b.title LIKE 'x%'";
        $tokens = $this->tokenizer()->tokenize($sql);

        // Just sample-check the salient ones; full equivalence is the PBT's job
        self::assertSame(Token::TYPE_IDENT, $tokens[0]->type);
        self::assertSame('book.author_id', $tokens[0]->value);

        // Find the embedded string with doubled-quote
        $strings = array_filter($tokens, fn (Token $t) => $t->type === Token::TYPE_STRING);
        $stringValues = array_map(fn (Token $t) => $t->value, $strings);
        self::assertContains("'foo''bar'", $stringValues);
        self::assertContains("'x%'", $stringValues);
    }

    public function testOffsetTracksBytePosition(): void
    {
        $tokens = $this->tokenizer()->tokenize('foo bar');
        self::assertSame(0, $tokens[0]->offset);
        self::assertSame(3, $tokens[1]->offset);
        self::assertSame(4, $tokens[2]->offset);
    }
}
