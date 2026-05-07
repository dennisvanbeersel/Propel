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
}
