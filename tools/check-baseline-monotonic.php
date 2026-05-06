#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Asserts phpstan-baseline.neon and psalm-baseline.xml line counts have not
 * increased vs the merge base (umbrella spec §4.1: monotonic shrinkage).
 *
 * Usage: tools/check-baseline-monotonic.php <merge-base-ref>
 *
 * Exits 0 if baselines stable or shrinking; 1 if any grew.
 */

$mergeBase = $argv[1] ?? 'origin/master';
$files = [
    'phpstan-baseline.neon',
    'psalm-baseline.xml',
];

$failed = false;
foreach ($files as $file) {
    if (!is_file($file)) {
        fwrite(STDERR, "  ! $file not found at repo root — skipping\n");
        continue;
    }

    $current = countLines($file);
    $base = countLinesAtRef($file, $mergeBase);

    if ($base === null) {
        fwrite(STDOUT, "  ✓ $file: new (no base to compare against)\n");
        continue;
    }

    $delta = $current - $base;
    if ($delta > 0) {
        fwrite(
            STDERR,
            sprintf("  ✗ %s grew: %d → %d (+%d lines) — baseline drawdown is monotonic per umbrella spec §4.1\n", $file, $base, $current, $delta),
        );
        $failed = true;
    } else {
        $sign = $delta === 0 ? '=' : '↓';
        fwrite(STDOUT, sprintf("  ✓ %s: %d → %d (%s%d)\n", $file, $base, $current, $sign, $delta));
    }
}

exit($failed ? 1 : 0);

/**
 * Both countLines() and countLinesAtRef() count newlines via substr_count to keep
 * the comparison apples-to-apples. fgets()-based counting differs from substr_count
 * by 1 on files lacking a trailing newline — Round 1 reviewer caught this asymmetry.
 */
function countLines(string $path): int
{
    $contents = file_get_contents($path);
    if ($contents === false) {
        return 0;
    }

    return substr_count($contents, "\n");
}

function countLinesAtRef(string $path, string $ref): ?int
{
    // Verify the ref actually resolves; otherwise the silent shell_exec failure
    // below would let a typo'd merge-base sail through with a passing exit code.
    $refExists = shell_exec(sprintf('git rev-parse --verify %s 2>/dev/null', escapeshellarg($ref . '^{commit}')));
    if ($refExists === null || trim((string) $refExists) === '') {
        fwrite(STDERR, sprintf("  ! merge base ref '%s' does not resolve — refusing to silently pass\n", $ref));
        exit(2);
    }

    $output = shell_exec(sprintf('git show %s:%s 2>/dev/null', escapeshellarg($ref), escapeshellarg($path)));
    if ($output === null || $output === false || $output === '') {
        return null;
    }

    return substr_count($output, "\n");
}
