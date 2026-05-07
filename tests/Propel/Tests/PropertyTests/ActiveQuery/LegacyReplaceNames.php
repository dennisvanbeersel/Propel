<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\PropertyTests\ActiveQuery;

/**
 * Frozen snapshot of the pre-Phase-F Criteria::replaceNames implementation,
 * captured verbatim. Used as the differential oracle for the byte-equivalence
 * property test (F.2.4) and the fuzzing corpus (F.2.5).
 *
 * Do not modify — the test suite asserts the new tokenizer-based resolver
 * produces the same output as this captured legacy.
 */
final class LegacyReplaceNames
{
    /**
     * Pre-Phase-F implementation, callback receives the regex-matches array.
     *
     * @param string $sql
     * @param callable $matchesCallback
     *
     * @return string The rewritten SQL.
     */
    public static function run(string $sql, callable $matchesCallback): string
    {
        $isAfterBackslash = false;
        $isInString = false;
        $stringQuotes = '';
        $parsedString = '';
        $stringToTransform = '';
        $len = strlen($sql);
        $pos = 0;
        while ($pos < $len) {
            $char = $sql[$pos];
            switch ($char) {
                case '\\':
                    $isAfterBackslash = true;

                    break;
                case "'":
                case '"':
                    if ($isInString && $stringQuotes === $char) {
                        if (!$isAfterBackslash) {
                            $isInString = false;
                        }
                    } elseif (!$isInString) {
                        $parsedString .= preg_replace_callback("/[\w\\\]+\.\w+/", $matchesCallback, $stringToTransform);
                        $stringToTransform = '';
                        $stringQuotes = $char;
                        $isInString = true;
                    }

                    break;
            }

            if ($char !== '\\') {
                $isAfterBackslash = false;
            }

            if ($isInString) {
                $parsedString .= $char;
            } else {
                $stringToTransform .= $char;
            }

            $pos++;
        }

        if ($stringToTransform) {
            $parsedString .= preg_replace_callback("/[\w\\\]+\.\w+/", $matchesCallback, $stringToTransform);
        }

        return $parsedString;
    }
}
