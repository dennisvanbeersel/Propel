<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\PropertyTests\CodeEmitter;

use Innmind\BlackBox\Random;
use Innmind\BlackBox\Set;
use PHPUnit\Framework\TestCase;
use Propel\Generator\Builder\Util\CodeEmitter;

/**
 * Property-based test: any sequence of `block-open` / `block-close` /
 * `line` / `blank` operations on a CodeEmitter yields output where the
 * leading-whitespace count of every emitted non-blank line is exactly the
 * (open count - close count at emission time) × indent-string-length.
 *
 * Round-trip: the resulting PHP, wrapped in a function header, must lint
 * clean via `php -l`.
 */
class IndentationInvariantTest extends TestCase
{
    /**
     * @return void
     */
    public function testRandomOpCloseLineSequencePreservesIndentInvariant(): void
    {
        $count = 0;
        $set = Set::integers()->between(1, 60)->take(30);
        foreach ($set->values(Random::default) as $value) {
            $sequenceLength = $value->unwrap();
            $sequence = $this->generateSequence($sequenceLength, $count);
            $this->verifySequenceInvariant($sequence);
            $count++;
        }
        $this->assertSame(30, $count);
    }

    /**
     * @return void
     */
    public function testRandomEmittedSnippetIsPhpLintClean(): void
    {
        $count = 0;
        $set = Set::integers()->between(1, 30)->take(15);
        foreach ($set->values(Random::default) as $value) {
            $sequenceLength = $value->unwrap();
            $sequence = $this->generateSequence($sequenceLength, $count + 1000);
            $emitter = new CodeEmitter();
            $emitter->line('<?php');
            $emitter->line('function _pbt_test()');
            $emitter->line('{');
            $body = $emitter->block();
            /** @var array<int, \Propel\Generator\Builder\Util\CodeEmitterScope> $openScopes */
            $openScopes = [];
            foreach ($sequence as $op) {
                $this->applyOp($emitter, $op, $openScopes, true);
            }
            // Close all remaining scopes.
            while ($openScopes !== []) {
                array_pop($openScopes);
            }
            unset($body);
            $emitter->line('}');

            $php = $emitter->toString();
            $this->assertPhpLintsClean($php);
            $count++;
        }
        $this->assertSame(15, $count);
    }

    /**
     * Generate a deterministic sequence of operations with a balance-preserving
     * constraint (close ops only fire when there are open scopes to close).
     *
     * Operations are encoded as strings: 'open', 'close', 'line', 'blank'.
     *
     * @param int $length
     * @param int $seed
     *
     * @return list<string>
     */
    private function generateSequence(int $length, int $seed): array
    {
        $sequence = [];
        $depth = 0;
        // Deterministic pseudo-random based on seed.
        mt_srand($seed);
        for ($i = 0; $i < $length; $i++) {
            $r = mt_rand(0, 99);
            if ($r < 30) {
                if ($depth < 8) {
                    $sequence[] = 'open';
                    $depth++;
                } else {
                    $sequence[] = 'line';
                }
            } elseif ($r < 50) {
                if ($depth > 0) {
                    $sequence[] = 'close';
                    $depth--;
                } else {
                    $sequence[] = 'line';
                }
            } elseif ($r < 90) {
                $sequence[] = 'line';
            } else {
                $sequence[] = 'blank';
            }
        }

        return $sequence;
    }

    /**
     * Replay the sequence on a fresh emitter and assert: for every emitted
     * non-blank line, leading-whitespace-length == 4 × current depth.
     *
     * @param list<string> $sequence
     *
     * @return void
     */
    private function verifySequenceInvariant(array $sequence): void
    {
        $emitter = new CodeEmitter();
        /** @var array<int, \Propel\Generator\Builder\Util\CodeEmitterScope> $openScopes */
        $openScopes = [];
        $expectedDepths = [];

        foreach ($sequence as $op) {
            $this->applyOp($emitter, $op, $openScopes, false, $expectedDepths);
        }
        // Close all remaining scopes.
        while ($openScopes !== []) {
            array_pop($openScopes);
        }

        $output = $emitter->toString();
        if ($output === '') {
            $this->assertSame('', $output);

            return;
        }

        $lines = explode("\n", $output);
        $lineIdx = 0;
        foreach ($lines as $line) {
            if ($line === '') {
                $lineIdx++;

                continue;
            }
            $expectedDepth = $expectedDepths[$lineIdx] ?? 0;
            $expectedSpaces = $expectedDepth * 4;
            $actualSpaces = strspn($line, ' ');
            $this->assertSame(
                $expectedSpaces,
                $actualSpaces,
                sprintf('Line %d: expected %d leading spaces (depth %d), got %d. Line: %s', $lineIdx, $expectedSpaces, $expectedDepth, $actualSpaces, $line),
            );
            $lineIdx++;
        }
    }

    /**
     * Apply a single op. When $emittingPhp is true, lines emit syntactically
     * valid PHP statements so the resulting body lints clean.
     *
     * @param \Propel\Generator\Builder\Util\CodeEmitter $emitter
     * @param string $op
     * @param array<int, \Propel\Generator\Builder\Util\CodeEmitterScope> $openScopes
     * @param bool $emittingPhp
     * @param array<int, int> $expectedDepths Out-param appending the depth at which each emitted line landed.
     *
     * @return void
     */
    private function applyOp(
        CodeEmitter $emitter,
        string $op,
        array &$openScopes,
        bool $emittingPhp,
        array &$expectedDepths = []
    ): void {
        switch ($op) {
            case 'open':
                $openScopes[] = $emitter->block();

                break;
            case 'close':
                if ($openScopes !== []) {
                    array_pop($openScopes);
                }

                break;
            case 'line':
                $expectedDepths[] = $emitter->getIndent();
                if ($emittingPhp) {
                    $emitter->line('$x = 1;');
                } else {
                    $emitter->line('foo');
                }

                break;
            case 'blank':
                $expectedDepths[] = $emitter->getIndent();
                $emitter->blank();

                break;
        }
    }

    /**
     * Run `php -l` on the snippet via tempfile. Skip the test (not fail) if
     * the temp file cannot be written.
     *
     * @param string $php
     *
     * @return void
     */
    private function assertPhpLintsClean(string $php): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'codeemitter-pbt-');
        if ($tmp === false) {
            $this->markTestSkipped('Cannot create temp file for PHP lint');
        }
        try {
            file_put_contents($tmp, $php);
            $output = [];
            $exit = 0;
            exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($tmp) . ' 2>&1', $output, $exit);
            $this->assertSame(0, $exit, "Generated PHP failed php -l:\n" . implode("\n", $output) . "\n--- snippet:\n" . $php);
        } finally {
            @unlink($tmp);
        }
    }
}
