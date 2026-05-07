<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery\Compiler;

/**
 * Tokenizer-driven name resolver — replaces the hand-rolled char-by-char scan in
 * `Criteria::replaceNames` (umbrella §6.1 Phase F bug-fix #1).
 *
 * Behavioral contract: byte-equivalent to the legacy parser on inputs that contain
 * no backtick identifiers. The legacy parser does NOT treat backticks as quoting,
 * so input containing backticks is a documented divergence (the new resolver applies
 * replacement INSIDE backtick-quoted idents; the legacy treats them as plain chars).
 *
 * The legacy parser also does NOT recognise SQL comments — they are plain text and
 * receive replacement. The new resolver preserves this behavior for byte-equivalence:
 * COMMENT tokens are NOT treated as protected regions; they are joined back into the
 * non-string segment and the legacy regex callback is applied verbatim.
 *
 * Tier 3 internal — public methods are stable for downstream Compiler consumers.
 */
final class NameResolver
{
    /**
     * Legacy regex from Criteria::replaceNames — preserved verbatim for byte-equivalence.
     *
     * @var string
     */
    private const LEGACY_QUALIFIED_NAME_REGEX = '/[\w\\\]+\.\w+/';

    private Tokenizer $tokenizer;

    /**
     * @var list<array{string, string}>
     */
    private array $replacements = [];

    /**
     * @param \Propel\Runtime\ActiveQuery\Compiler\Tokenizer|null $tokenizer
     */
    public function __construct(?Tokenizer $tokenizer = null)
    {
        $this->tokenizer = $tokenizer ?? new Tokenizer();
    }

    /**
     * Replace qualified column names of the form `Class.Column` or `alias.column` in $sql,
     * mirroring the legacy `Criteria::replaceNames` behavior.
     *
     * The $resolveName callable signature is `function (string $name): string` — it
     * receives the matched qualified name and returns the replacement.
     *
     * @param string $sql
     * @param callable $resolveName Per-match replacement callable.
     * @param array<string, string> $aliases Reserved for future routing — not used by the
     *        regex replacement itself; kept for API symmetry.
     *
     * @return string The rewritten SQL.
     */
    public function resolve(string $sql, callable $resolveName, array $aliases = []): string
    {
        unset($aliases); // currently informational; resolver delegates the per-name decision to $resolveName
        $this->replacements = [];

        // Build pairs of (insideString?, segment) by walking tokens. Concatenation
        // of all segments equals the original $sql (round-trip property).
        $tokens = $this->tokenizer->tokenize($sql);
        $out = '';

        foreach ($tokens as $token) {
            if ($token->type === Token::TYPE_STRING || $token->type === Token::TYPE_BACKTICK_IDENT) {
                // Strings + backtick-quoted idents pass through verbatim — no replacement applied.
                // (Backtick handling is the documented divergence from the legacy parser; the
                // legacy parser treated backticks as plain chars and DID apply replacement.)
                $out .= $token->value;

                continue;
            }

            // Non-string tokens get the legacy regex pass.
            $out .= $this->applyReplacement($token->value, $resolveName);
        }

        return $out;
    }

    /**
     * Replace qualified column names using a regex-callback-shaped resolver, matching
     * the legacy `preg_replace_callback("/[\w\\\]+\.\w+/", $callback)` contract used
     * by `Criteria::replaceNames` exactly. The callback receives a `$matches` array;
     * `$matches[0]` is the qualified name; the return value is the replacement.
     *
     * Use this overload when wiring an existing `[$obj, 'doReplaceNameInExpression']`
     * style callback. For a `function (string $name): string` shape, use `resolve()`.
     *
     * @param string $sql
     * @param callable $matchesCallback Legacy preg_replace_callback shape.
     *
     * @return string The rewritten SQL.
     */
    public function resolveWithMatchesCallback(string $sql, callable $matchesCallback): string
    {
        $this->replacements = [];
        $tokens = $this->tokenizer->tokenize($sql);
        $out = '';

        foreach ($tokens as $token) {
            if ($token->type === Token::TYPE_STRING || $token->type === Token::TYPE_BACKTICK_IDENT) {
                $out .= $token->value;

                continue;
            }

            $segment = $token->value;
            $result = preg_replace_callback(
                self::LEGACY_QUALIFIED_NAME_REGEX,
                function (array $match) use ($matchesCallback): string {
                    $original = $match[0];
                    $replacement = (string)$matchesCallback($match);
                    $this->replacements[] = [$original, $replacement];

                    return $replacement;
                },
                $segment,
            );
            $out .= $result ?? $segment;
        }

        return $out;
    }

    /**
     * @return list<array{string, string}> list of [original, replacement] pairs found in last resolve()
     */
    public function getReplacements(): array
    {
        return $this->replacements;
    }

    /**
     * @param string $segment
     * @param callable $resolveName
     *
     * @return string
     */
    private function applyReplacement(string $segment, callable $resolveName): string
    {
        $result = preg_replace_callback(
            self::LEGACY_QUALIFIED_NAME_REGEX,
            function (array $match) use ($resolveName): string {
                $original = $match[0];
                $replacement = $resolveName($original);
                $this->replacements[] = [$original, $replacement];

                return $replacement;
            },
            $segment,
        );

        return $result ?? $segment;
    }
}
