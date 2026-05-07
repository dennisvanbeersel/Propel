<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Compiler;

/**
 * SQL tokenizer state machine.
 *
 * Replaces the hand-rolled char-by-char scan in the legacy
 * Criteria::replaceNames implementation (umbrella §6.1 Phase F bug-fix #1).
 *
 * Tier 3 internal — public methods are stable for downstream Compiler consumers.
 *
 * The state machine is O(n) over input length with one branch per character.
 * Token types are defined as constants on Token; the inner loop uses
 * scalar string types directly to keep dispatch simple.
 *
 * Edge cases:
 * - ANSI doubled-single-quote escape ('') treated as part of the string body.
 * - Backslash-escape preserved verbatim inside string body.
 * - Unterminated string / block comment heal-by-eof (do not throw — matches
 *   legacy parser which simply runs to end of input).
 * - Line comment `--` runs to end of line; block comment `/* ... *\/` is non-nested.
 */
final class Tokenizer
{
    /**
     * @param string $sql
     *
     * @return list<\Propel\Runtime\ActiveQuery\Compiler\Token>
     */
    public function tokenize(string $sql): array
    {
        $tokens = [];
        $len = strlen($sql);
        $pos = 0;

        while ($pos < $len) {
            $char = $sql[$pos];
            $start = $pos;

            // Whitespace run
            if ($char === ' ' || $char === "\t" || $char === "\n" || $char === "\r" || $char === "\f" || $char === "\v") {
                $end = $pos + 1;
                while ($end < $len) {
                    $c = $sql[$end];
                    if ($c !== ' ' && $c !== "\t" && $c !== "\n" && $c !== "\r" && $c !== "\f" && $c !== "\v") {
                        break;
                    }
                    $end++;
                }
                $tokens[] = new Token(Token::TYPE_WS, substr($sql, $start, $end - $start), $start);
                $pos = $end;

                continue;
            }

            // Line comment: `--` to end of line (or EOF)
            if ($char === '-' && $pos + 1 < $len && $sql[$pos + 1] === '-') {
                $end = $pos + 2;
                while ($end < $len && $sql[$end] !== "\n") {
                    $end++;
                }
                $tokens[] = new Token(Token::TYPE_LCOMMENT, substr($sql, $start, $end - $start), $start);
                $pos = $end;

                continue;
            }

            // Block comment: `/* ... */` (non-nested per ANSI / MySQL)
            if ($char === '/' && $pos + 1 < $len && $sql[$pos + 1] === '*') {
                $end = $pos + 2;
                while ($end + 1 < $len && !($sql[$end] === '*' && $sql[$end + 1] === '/')) {
                    $end++;
                }
                if ($end + 1 < $len) {
                    $end += 2; // consume `*/`
                } else {
                    $end = $len; // unterminated — heal to EOF
                }
                $tokens[] = new Token(Token::TYPE_BCOMMENT, substr($sql, $start, $end - $start), $start);
                $pos = $end;

                continue;
            }

            // Single- or double-quoted string literal
            if ($char === "'" || $char === '"') {
                $quote = $char;
                $end = $pos + 1;
                while ($end < $len) {
                    $c = $sql[$end];
                    if ($c === '\\' && $end + 1 < $len) {
                        $end += 2; // backslash-escape: skip the following char

                        continue;
                    }
                    if ($c === $quote) {
                        // ANSI doubled-quote escape: '' or "" → continues the string
                        if ($end + 1 < $len && $sql[$end + 1] === $quote) {
                            $end += 2;

                            continue;
                        }
                        $end++; // consume closing quote

                        break;
                    }
                    $end++;
                }
                $tokens[] = new Token(Token::TYPE_STRING, substr($sql, $start, $end - $start), $start);
                $pos = $end;

                continue;
            }

            // Backtick-quoted identifier (MySQL convention)
            if ($char === '`') {
                $end = $pos + 1;
                while ($end < $len && $sql[$end] !== '`') {
                    $end++;
                }
                if ($end < $len) {
                    $end++; // consume closing backtick
                }
                $tokens[] = new Token(Token::TYPE_BACKTICK_IDENT, substr($sql, $start, $end - $start), $start);
                $pos = $end;

                continue;
            }

            // Number: digit-led OR `.digit`
            if (ctype_digit($char) || ($char === '.' && $pos + 1 < $len && ctype_digit($sql[$pos + 1]))) {
                $end = $pos;
                $sawDot = ($char === '.');
                $sawExp = false;
                $end++;
                while ($end < $len) {
                    $c = $sql[$end];
                    if (ctype_digit($c)) {
                        $end++;

                        continue;
                    }
                    if ($c === '.' && !$sawDot && !$sawExp) {
                        $sawDot = true;
                        $end++;

                        continue;
                    }
                    if (($c === 'e' || $c === 'E') && !$sawExp) {
                        $sawExp = true;
                        $end++;
                        if ($end < $len && ($sql[$end] === '+' || $sql[$end] === '-')) {
                            $end++;
                        }

                        continue;
                    }

                    break;
                }
                $tokens[] = new Token(Token::TYPE_NUMBER, substr($sql, $start, $end - $start), $start);
                $pos = $end;

                continue;
            }

            // Identifier (incl. dot-separated qualified names): [\w\\]+
            // Match the legacy regex character class \w plus backslash for namespaced
            // class references (e.g. `My\Cls.column`).
            if ($this->isIdentStart($char)) {
                $end = $pos + 1;
                while ($end < $len && $this->isIdentPart($sql[$end])) {
                    $end++;
                }
                $tokens[] = new Token(Token::TYPE_IDENT, substr($sql, $start, $end - $start), $start);
                $pos = $end;

                continue;
            }

            // Operator runs (multi-char operators packed into one OP token)
            if ($this->isOperatorChar($char)) {
                $end = $pos + 1;
                while ($end < $len && $this->isOperatorChar($sql[$end])) {
                    $end++;
                }
                $tokens[] = new Token(Token::TYPE_OP, substr($sql, $start, $end - $start), $start);
                $pos = $end;

                continue;
            }

            // Punctuation (catch-all single char): parens, comma, semicolon, etc.
            $tokens[] = new Token(Token::TYPE_PUNCT, $char, $start);
            $pos++;
        }

        return $tokens;
    }

    /**
     * @param string $c
     *
     * @return bool
     */
    private function isIdentStart(string $c): bool
    {
        // \w = [A-Za-z0-9_]; the `.` separator is NOT a start char (handled below as part of name)
        return ($c >= 'A' && $c <= 'Z') || ($c >= 'a' && $c <= 'z') || $c === '_' || $c === '\\';
    }

    /**
     * @param string $c
     *
     * @return bool
     */
    private function isIdentPart(string $c): bool
    {
        // \w + dot + backslash; numeric is allowed inside ident (just not as start unless
        // it's a number, which is matched above).
        return ($c >= 'A' && $c <= 'Z')
            || ($c >= 'a' && $c <= 'z')
            || ($c >= '0' && $c <= '9')
            || $c === '_'
            || $c === '\\'
            || $c === '.';
    }

    /**
     * @param string $c
     *
     * @return bool
     */
    private function isOperatorChar(string $c): bool
    {
        return $c === '=' || $c === '<' || $c === '>' || $c === '!'
            || $c === '+' || $c === '-' || $c === '*' || $c === '/'
            || $c === '%' || $c === '|' || $c === '&' || $c === '^'
            || $c === '~';
    }
}
