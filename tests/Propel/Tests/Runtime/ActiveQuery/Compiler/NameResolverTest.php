<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\ActiveQuery\Compiler;

use PHPUnit\Framework\TestCase;
use Propel\Runtime\ActiveQuery\Compiler\NameResolver;

class NameResolverTest extends TestCase
{
    /**
     * Per-match replacement: lowercase-and-underscore-ize the column part. Mimics what
     * Criteria::doReplaceNameInExpression does in spirit, simplified for testing.
     */
    private function uppercaseToLowerCallback(): callable
    {
        // Trivial replacement: lowercase the entire qualified name. Avoids
        // tricky CamelCase-to-snake heuristics — the test only needs to verify
        // that the SAME substring is fed to the callback that legacy fed.
        return static fn (string $name): string => strtolower($name);
    }

    private function customMappingCallback(): callable
    {
        // Used in tests where we need a deterministic, distinct mapping
        return static function (string $name): string {
            return match ($name) {
                'Book.AuthorID' => 'book.author_id',
                'Author.Name' => 'author.name',
                'Book.Title' => 'book.title',
                'Book.X' => 'book.x',
                'My\\Cls.col' => 'my_cls.col',
                default => strtolower($name),
            };
        };
    }

    public function testPlainQualifiedName(): void
    {
        $resolver = new NameResolver();
        $sql = 'Book.AuthorID = ?';
        $out = $resolver->resolve($sql, $this->uppercaseToLowerCallback());

        self::assertSame('book.authorid = ?', $out);
        self::assertSame([['Book.AuthorID', 'book.authorid']], $resolver->getReplacements());
    }

    public function testNameInsideStringIsNotReplaced(): void
    {
        $resolver = new NameResolver();
        $sql = "'Book.AuthorID'";
        $out = $resolver->resolve($sql, $this->uppercaseToLowerCallback());

        self::assertSame("'Book.AuthorID'", $out);
        self::assertSame([], $resolver->getReplacements());
    }

    public function testMixedNameAndString(): void
    {
        $resolver = new NameResolver();
        $sql = "Book.AuthorID = 'Book.Title' AND Author.Name LIKE 'foo''bar'";
        $out = $resolver->resolve($sql, $this->customMappingCallback());

        self::assertSame(
            "book.author_id = 'Book.Title' AND author.name LIKE 'foo''bar'",
            $out,
        );
        self::assertSame(
            [
                ['Book.AuthorID', 'book.author_id'],
                ['Author.Name', 'author.name'],
            ],
            $resolver->getReplacements(),
        );
    }

    public function testEmptyInput(): void
    {
        $resolver = new NameResolver();
        $out = $resolver->resolve('', $this->uppercaseToLowerCallback());
        self::assertSame('', $out);
        self::assertSame([], $resolver->getReplacements());
    }

    public function testCallbackNotInvokedWhenNoMatch(): void
    {
        $invoked = false;
        $resolver = new NameResolver();
        $resolver->resolve("'just a string'", static function (string $n) use (&$invoked) {
            $invoked = true;

            return $n;
        });

        self::assertFalse($invoked);
    }

    public function testStringWithDoubledQuoteEscape(): void
    {
        // ANSI '' is a single-quote inside the string. Names inside are NOT replaced.
        $resolver = new NameResolver();
        $sql = "Book.X = 'foo''Author.Name''bar'";
        $out = $resolver->resolve($sql, $this->customMappingCallback());

        self::assertSame("book.x = 'foo''Author.Name''bar'", $out);
    }

    public function testBackslashEscapeInString(): void
    {
        $resolver = new NameResolver();
        $sql = "Book.X = 'a\\'Author.Name\\'b'";
        $out = $resolver->resolve($sql, $this->customMappingCallback());

        self::assertSame("book.x = 'a\\'Author.Name\\'b'", $out);
    }

    public function testNamespacedClassReference(): void
    {
        $resolver = new NameResolver();
        $sql = 'My\\Cls.col = ?';
        $out = $resolver->resolve($sql, $this->customMappingCallback());

        // The legacy regex captures the entire `My\Cls.col` token
        self::assertSame('my_cls.col = ?', $out);
    }

    public function testSimpleIdentNotReplaced(): void
    {
        // No dot — not a qualified name, regex doesn't match
        $invoked = false;
        $resolver = new NameResolver();
        $out = $resolver->resolve('book = ?', static function (string $n) use (&$invoked) {
            $invoked = true;

            return $n;
        });

        self::assertSame('book = ?', $out);
        self::assertFalse($invoked);
    }
}
