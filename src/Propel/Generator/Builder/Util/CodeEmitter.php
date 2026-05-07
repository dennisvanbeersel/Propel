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
     * @psalm-api
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
     * @psalm-api
     *
     * @return int
     */
    public function getIndent(): int
    {
        return $this->indent;
    }

    /**
     * Emit a docblock; multi-line text is split, each line star-prefixed,
     * delimited by docblock open/close markers indented at the current depth.
     *
     * @param string $text
     *
     * @return $this
     */
    public function docblock(string $text)
    {
        $rawLines = preg_split("/\r\n|\n|\r/", $text);
        if ($rawLines === false) {
            return $this;
        }

        $indentStr = str_repeat($this->indentString, $this->indent);
        $this->lines[] = $indentStr . '/**';
        foreach ($rawLines as $rawLine) {
            $trimmed = rtrim($rawLine);
            if ($trimmed === '') {
                $this->lines[] = $indentStr . ' *';

                continue;
            }
            $this->lines[] = $indentStr . ' * ' . $trimmed;
        }
        $this->lines[] = $indentStr . ' */';

        return $this;
    }

    /**
     * Emit a method signature line + open the method body block.
     *
     * Returns a scope that auto-closes the body on destruction (emits a
     * trailing `}` line at the parent indent before dedenting).
     *
     * Each $params element shape:
     *   - 'name' (required, without `$`): the parameter name
     *   - 'type' (optional): typehint (e.g. `string`, `?int`, `\Foo\Bar`)
     *   - 'default' (optional): PHP-source-formatted default expression
     *   - 'byRef' (optional): if true, prefix with `&`
     *   - 'variadic' (optional): if true, prefix with `...`
     *
     * @psalm-api
     *
     * @param string $name
     * @param string $visibility
     * @param array<int, array{name: string, type?: string, default?: string, byRef?: bool, variadic?: bool}> $params
     * @param string|null $returnType
     * @param string $docblock Pass empty string to skip.
     * @param bool $isStatic
     *
     * @return \Propel\Generator\Builder\Util\CodeEmitterScope
     */
    public function methodBody(
        string $name,
        string $visibility = 'public',
        array $params = [],
        ?string $returnType = null,
        string $docblock = '',
        bool $isStatic = false
    ): CodeEmitterScope {
        if ($docblock !== '') {
            $this->docblock($docblock);
        }

        $signature = $visibility;
        if ($isStatic) {
            $signature .= ' static';
        }
        $signature .= ' function ' . $name . '(';
        $signature .= $this->formatParams($params);
        $signature .= ')';
        if ($returnType !== null && $returnType !== '') {
            $signature .= ': ' . $returnType;
        }

        $this->line($signature);
        $this->line('{');
        $this->indent();

        $emitter = $this;

        return new CodeEmitterScope($this, static function () use ($emitter): void {
            $emitter->line('}');
        });
    }

    /**
     * Escape a value for use as a PHP single-quoted string literal.
     *
     * Returns the value with single quotes around it, with embedded `'` and
     * `\` characters backslash-escaped. Newlines are NOT converted (single
     * quotes preserve them as-is).
     *
     * @param string $value
     *
     * @return string
     */
    public static function phpString(string $value): string
    {
        return "'" . str_replace(['\\', "'"], ['\\\\', "\\'"], $value) . "'";
    }

    /**
     * Validate and return a PHP variable token (`$name`).
     *
     * Accepts either bare names (`foo`) or already-prefixed (`$foo`).
     * Throws on invalid identifiers.
     *
     * @psalm-api
     *
     * @param string $name
     *
     * @throws \Propel\Generator\Exception\InvalidArgumentException
     *
     * @return string
     */
    public static function phpVar(string $name): string
    {
        $candidate = ltrim($name, '$');
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $candidate) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid PHP variable name: %s', $name));
        }

        return '$' . $candidate;
    }

    /**
     * Emit an SQL identifier as a PHP-source string literal preserving any
     * embedded quoting characters.
     *
     * Same as `phpString()` but the contract is documented for SQL-identifier
     * use sites — escaping rules are identical (single-quoted PHP literal).
     *
     * @psalm-api
     *
     * @param string $name
     *
     * @return string
     */
    public static function sqlIdentifier(string $name): string
    {
        return self::phpString($name);
    }

    /**
     * Format a parameter list for `methodBody()`.
     *
     * @param array<int, array{name: string, type?: string, default?: string, byRef?: bool, variadic?: bool}> $params
     *
     * @return string
     */
    private function formatParams(array $params): string
    {
        $out = [];
        foreach ($params as $p) {
            $piece = '';
            if (isset($p['type']) && $p['type'] !== '') {
                $piece .= $p['type'] . ' ';
            }
            if (!empty($p['byRef'])) {
                $piece .= '&';
            }
            if (!empty($p['variadic'])) {
                $piece .= '...';
            }
            $piece .= '$' . $p['name'];
            if (isset($p['default']) && $p['default'] !== '') {
                $piece .= ' = ' . $p['default'];
            }
            $out[] = $piece;
        }

        return implode(', ', $out);
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
