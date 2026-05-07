<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Builder\Util;

use Propel\Generator\Exception\InvalidArgumentException;
use Propel\Generator\Exception\LogicException;

/**
 * Indented-block PHP code emitter for behavior modifiers and (in later phases)
 * per-builder generators.
 *
 * Replaces the historical `"\n " . $code . "\n}"` string-concatenation
 * patterns with structured emission: indented block management, method
 * signature emission with typed parameters, expression escaping helpers.
 *
 * @internal Tier 3 internal helper. Public method shapes committed via
 * `tests/snapshots/tracked-classes.txt` per umbrella §3.3 (mirrored from
 * QuickBuilder precedent).
 */
final class CodeEmitter
{
    /**
     * @var list<string>
     */
    private array $lines = [];

    /**
     * @var int
     */
    private int $indent = 0;

    /**
     * @var string
     */
    private string $indentString;

    /**
     * @param int $startIndent Initial indent level (>= 0).
     * @param string $indentString Per-level indent string; default 4 spaces.
     *
     * @throws \Propel\Generator\Exception\InvalidArgumentException
     */
    public function __construct(int $startIndent = 0, string $indentString = '    ')
    {
        if ($startIndent < 0) {
            throw new InvalidArgumentException('startIndent must be >= 0');
        }
        $this->indent = $startIndent;
        $this->indentString = $indentString;
    }

    /**
     * Append a single line; auto-indented at the current depth.
     *
     * Trailing whitespace is stripped. An empty input emits a blank line
     * (no indent), matching `blank()` semantics for convenience.
     *
     * @param string $code
     *
     * @return $this
     */
    public function line(string $code = '')
    {
        $code = rtrim($code);

        if ($code === '') {
            $this->lines[] = '';

            return $this;
        }

        $this->lines[] = str_repeat($this->indentString, $this->indent) . $code;

        return $this;
    }

    /**
     * Append multiple lines from a heredoc / multi-line string.
     *
     * Each non-blank line is indented to the current depth; the heredoc's
     * leading common indentation is stripped first so callers can write
     * naturally indented PHP source inside the heredoc.
     *
     * @param string $multilineCode
     *
     * @return $this
     */
    public function lines(string $multilineCode)
    {
        $rawLines = preg_split("/\r\n|\n|\r/", $multilineCode);
        if ($rawLines === false) {
            return $this;
        }

        // Strip a single leading blank line if the heredoc opens with one.
        if ($rawLines !== [] && trim($rawLines[0]) === '') {
            array_shift($rawLines);
        }

        // Strip a single trailing blank line if the heredoc ends with one.
        if ($rawLines !== [] && trim($rawLines[count($rawLines) - 1]) === '') {
            array_pop($rawLines);
        }

        $minIndent = $this->detectCommonIndent($rawLines);

        foreach ($rawLines as $rawLine) {
            if (trim($rawLine) === '') {
                $this->lines[] = '';

                continue;
            }
            $stripped = $minIndent > 0 ? substr($rawLine, $minIndent) : $rawLine;
            $this->lines[] = str_repeat($this->indentString, $this->indent) . rtrim($stripped);
        }

        return $this;
    }

    /**
     * Open an indented block. Returns a scope handle that auto-dedents on
     * destruction (RAII), so the canonical pattern is:
     *
     *     $body = $emitter->block();
     *     $emitter->line('// inside block');
     *     unset($body); // closes the block
     *
     * If the scope handle is held in a variable that goes out of function
     * scope, the dedent fires automatically.
     *
     * @return \Propel\Generator\Builder\Util\CodeEmitterScope
     */
    public function block(): CodeEmitterScope
    {
        $this->indent();

        return new CodeEmitterScope($this);
    }

    /**
     * Manual indent control. Prefer `block()` for RAII.
     *
     * @return $this
     */
    public function indent()
    {
        $this->indent++;

        return $this;
    }

    /**
     * Manual dedent control. Prefer `block()` for RAII.
     *
     * @throws \Propel\Generator\Exception\LogicException
     *
     * @return $this
     */
    public function dedent()
    {
        if ($this->indent === 0) {
            throw new LogicException('Cannot dedent below zero indent');
        }
        $this->indent--;

        return $this;
    }

    /**
     * Emit a blank line (no indent).
     *
     * @return $this
     */
    public function blank()
    {
        $this->lines[] = '';

        return $this;
    }

    /**
     * Render the buffered lines as a single string, joined by `\n`.
     *
     * @return string
     */
    public function toString(): string
    {
        return implode("\n", $this->lines);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Current indent level (read-only).
     *
     * @return int
     */
    public function getIndent(): int
    {
        return $this->indent;
    }

    /**
     * Detect the minimum number of leading whitespace characters across all
     * non-blank lines. Used by `lines()` to strip a common leading indent.
     *
     * @param array<int, string> $rawLines
     *
     * @return int
     */
    private function detectCommonIndent(array $rawLines): int
    {
        $min = null;
        foreach ($rawLines as $rawLine) {
            if (trim($rawLine) === '') {
                continue;
            }
            $matched = preg_match('/^(\s*)/', $rawLine, $m);
            if ($matched !== 1) {
                continue;
            }
            $width = strlen($m[1]);
            if ($min === null || $width < $min) {
                $min = $width;
            }
        }

        return $min ?? 0;
    }
}
