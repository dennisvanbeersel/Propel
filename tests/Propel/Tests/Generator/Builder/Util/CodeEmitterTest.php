<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Generator\Builder\Util;

use Propel\Generator\Builder\Util\CodeEmitter;
use Propel\Generator\Exception\InvalidArgumentException;
use Propel\Generator\Exception\LogicException;
use Propel\Tests\TestCase;

/**
 * Unit tests for CodeEmitter (Phase D.1.1 — indent management + line emission).
 */
#[\PHPUnit\Framework\Attributes\Group('database-agnostic')]
class CodeEmitterTest extends TestCase
{
    /**
     * @return void
     */
    public function testEmptyEmitterIsEmptyString(): void
    {
        $emitter = new CodeEmitter();
        $this->assertSame('', $emitter->toString());
    }

    /**
     * @return void
     */
    public function testSingleLineEmission(): void
    {
        $emitter = new CodeEmitter();
        $emitter->line('foo');
        $this->assertSame('foo', $emitter->toString());
    }

    /**
     * @return void
     */
    public function testTwoLinesAreJoinedByNewline(): void
    {
        $emitter = new CodeEmitter();
        $emitter->line('foo')->line('bar');
        $this->assertSame("foo\nbar", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testNestedBlockIndentsByFourSpaces(): void
    {
        $emitter = new CodeEmitter();
        $emitter->line('foo');
        $body = $emitter->block();
        $emitter->line('bar');
        unset($body);

        $this->assertSame("foo\n    bar", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testThreeNestedBlocksIndentTwelveSpaces(): void
    {
        $emitter = new CodeEmitter();
        $b1 = $emitter->block();
        $b2 = $emitter->block();
        $b3 = $emitter->block();
        $emitter->line('baz');
        unset($b3, $b2, $b1);

        $this->assertSame('            baz', $emitter->toString());
    }

    /**
     * @return void
     */
    public function testManualIndentDedent(): void
    {
        $emitter = new CodeEmitter();
        $emitter->indent()->line('a')->indent()->line('b')->dedent()->line('c')->dedent()->line('d');

        $this->assertSame("    a\n        b\n    c\nd", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testDedentBelowZeroThrows(): void
    {
        $emitter = new CodeEmitter();
        $this->expectException(LogicException::class);
        $emitter->dedent();
    }

    /**
     * @return void
     */
    public function testNegativeStartIndentThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new CodeEmitter(-1);
    }

    /**
     * @return void
     */
    public function testCustomIndentString(): void
    {
        $emitter = new CodeEmitter(0, "\t");
        $b = $emitter->block();
        $emitter->line('foo');
        unset($b);

        $this->assertSame("\tfoo", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testStartIndent(): void
    {
        $emitter = new CodeEmitter(2);
        $emitter->line('foo');
        $this->assertSame('        foo', $emitter->toString());
    }

    /**
     * @return void
     */
    public function testLinesHeredocIndentsToCurrentDepth(): void
    {
        $emitter = new CodeEmitter();
        $b = $emitter->block();
        $emitter->lines("foo\nbar");
        unset($b);

        $this->assertSame("    foo\n    bar", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testLinesStripsCommonLeadingIndent(): void
    {
        $emitter = new CodeEmitter();
        $b = $emitter->block();
        $emitter->lines("        if (\$x) {\n            return true;\n        }");
        unset($b);

        $this->assertSame("    if (\$x) {\n        return true;\n    }", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testLinesPreservesBlankLines(): void
    {
        $emitter = new CodeEmitter();
        $emitter->lines("foo\n\nbar");
        $this->assertSame("foo\n\nbar", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testLinesStripsLeadingAndTrailingBlankLines(): void
    {
        $emitter = new CodeEmitter();
        $emitter->lines("\nfoo\nbar\n");
        $this->assertSame("foo\nbar", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testTrailingWhitespaceOnInputStripped(): void
    {
        $emitter = new CodeEmitter();
        $emitter->line("foo   \t");
        $this->assertSame('foo', $emitter->toString());
    }

    /**
     * @return void
     */
    public function testLineWithEmptyStringEmitsBlankLine(): void
    {
        $emitter = new CodeEmitter();
        $b = $emitter->block();
        $emitter->line('foo')->line('')->line('bar');
        unset($b);

        $this->assertSame("    foo\n\n    bar", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testBlankAlwaysEmitsEmptyLine(): void
    {
        $emitter = new CodeEmitter();
        $b = $emitter->block();
        $emitter->line('foo')->blank()->line('bar');
        unset($b);

        $this->assertSame("    foo\n\n    bar", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testToStringMagicMethod(): void
    {
        $emitter = new CodeEmitter();
        $emitter->line('foo');
        $this->assertSame('foo', (string)$emitter);
    }

    /**
     * @return void
     */
    public function testGetIndentReturnsCurrentDepth(): void
    {
        $emitter = new CodeEmitter();
        $this->assertSame(0, $emitter->getIndent());
        $b = $emitter->block();
        $this->assertSame(1, $emitter->getIndent());
        unset($b);
        $this->assertSame(0, $emitter->getIndent());
    }

    /**
     * @return void
     */
    public function testScopeCloseIsIdempotent(): void
    {
        $emitter = new CodeEmitter();
        $b = $emitter->block();
        $b->close();
        $b->close();
        $this->assertSame(0, $emitter->getIndent());
    }

    /**
     * @return void
     */
    public function testNestedBlockSequence(): void
    {
        $emitter = new CodeEmitter();
        $emitter->line('class Foo');
        $emitter->line('{');
        $body = $emitter->block();
        $emitter->line('public function bar()');
        $emitter->line('{');
        $methodBody = $emitter->block();
        $emitter->line('return 42;');
        unset($methodBody);
        $emitter->line('}');
        unset($body);
        $emitter->line('}');

        $expected = "class Foo\n{\n    public function bar()\n    {\n        return 42;\n    }\n}";
        $this->assertSame($expected, $emitter->toString());
    }

    /**
     * @return void
     */
    public function testDocblockSingleLine(): void
    {
        $emitter = new CodeEmitter();
        $emitter->docblock('Hello world.');

        $this->assertSame("/**\n * Hello world.\n */", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testDocblockMultiLineWithBlank(): void
    {
        $emitter = new CodeEmitter();
        $emitter->docblock("Foo\n\nBar @return void");

        $this->assertSame("/**\n * Foo\n *\n * Bar @return void\n */", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testDocblockIndentsAtCurrentDepth(): void
    {
        $emitter = new CodeEmitter();
        $b = $emitter->block();
        $emitter->docblock('Hi.');
        unset($b);

        $this->assertSame("    /**\n     * Hi.\n     */", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testMethodBodyEmitsSignatureAndAutoClosingBrace(): void
    {
        $emitter = new CodeEmitter();
        $body = $emitter->methodBody('foo');
        $emitter->line('return 42;');
        unset($body);

        $this->assertSame("public function foo()\n{\n    return 42;\n}", $emitter->toString());
    }

    /**
     * @return void
     */
    public function testMethodBodyWithFullSignature(): void
    {
        $emitter = new CodeEmitter();
        $body = $emitter->methodBody(
            'foo',
            'protected',
            [
                ['name' => 'a', 'type' => 'int'],
                ['name' => 'b', 'type' => '?string', 'default' => 'null'],
            ],
            'bool',
            'Method foo.',
            true,
        );
        $emitter->line('return true;');
        unset($body);

        $expected = "/**\n * Method foo.\n */\nprotected static function foo(int \$a, ?string \$b = null): bool\n{\n    return true;\n}";
        $this->assertSame($expected, $emitter->toString());
    }

    /**
     * @return void
     */
    public function testMethodBodyByRefAndVariadic(): void
    {
        $emitter = new CodeEmitter();
        $body = $emitter->methodBody(
            'foo',
            'public',
            [
                ['name' => 'a', 'type' => 'array', 'byRef' => true],
                ['name' => 'rest', 'type' => 'mixed', 'variadic' => true],
            ],
        );
        unset($body);

        $expected = "public function foo(array &\$a, mixed ...\$rest)\n{\n}";
        $this->assertSame($expected, $emitter->toString());
    }

    /**
     * @return void
     */
    public function testMethodBodyNested(): void
    {
        $emitter = new CodeEmitter();
        $emitter->line('class Foo');
        $emitter->line('{');
        $cls = $emitter->block();
        $body = $emitter->methodBody('bar', 'public', [], 'self');
        $emitter->line('return $this;');
        unset($body);
        unset($cls);
        $emitter->line('}');

        $expected = "class Foo\n{\n    public function bar(): self\n    {\n        return \$this;\n    }\n}";
        $this->assertSame($expected, $emitter->toString());
    }

    /**
     * @return void
     */
    public function testPhpStringEscapesBackslashAndQuote(): void
    {
        $this->assertSame("'foo'", CodeEmitter::phpString('foo'));
        $this->assertSame("'don\\'t'", CodeEmitter::phpString("don't"));
        $this->assertSame("'a\\\\b'", CodeEmitter::phpString('a\\b'));
        $this->assertSame("''", CodeEmitter::phpString(''));
    }

    /**
     * @return void
     */
    public function testPhpStringPreservesNewlinesAndUnicode(): void
    {
        $this->assertSame("'a\nb'", CodeEmitter::phpString("a\nb"));
        $this->assertSame("'café'", CodeEmitter::phpString('café'));
    }

    /**
     * @return void
     */
    public function testPhpVarAcceptsBareAndPrefixed(): void
    {
        $this->assertSame('$foo', CodeEmitter::phpVar('foo'));
        $this->assertSame('$foo', CodeEmitter::phpVar('$foo'));
        $this->assertSame('$_under', CodeEmitter::phpVar('_under'));
        $this->assertSame('$camelCase123', CodeEmitter::phpVar('camelCase123'));
    }

    /**
     * @return void
     */
    public function testPhpVarRejectsInvalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CodeEmitter::phpVar('1foo');
    }

    /**
     * @return void
     */
    public function testPhpVarRejectsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CodeEmitter::phpVar('');
    }

    /**
     * @return void
     */
    public function testPhpVarRejectsSpecialChars(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CodeEmitter::phpVar('foo-bar');
    }

    /**
     * @return void
     */
    public function testSqlIdentifierEscapesLikePhpString(): void
    {
        $this->assertSame("'users'", CodeEmitter::sqlIdentifier('users'));
        $this->assertSame("'\"quoted\"'", CodeEmitter::sqlIdentifier('"quoted"'));
        $this->assertSame("'`backtick`'", CodeEmitter::sqlIdentifier('`backtick`'));
    }
}
