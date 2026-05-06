<?php

declare(strict_types=1);

/**
 * Regenerate the bookstore fixture and copy to tests/snapshots/bookstore-golden/.
 *
 * Per umbrella spec §4.7, the golden tree is committed and diffed character-
 * for-character on every PR. Reviewers approve any drift line-by-line — this
 * is the strongest mechanism for catching unintended generator-output changes
 * (signature-diff catches Tier 1 drift, but golden catches comment / order /
 * whitespace drift too).
 *
 * Usage:
 *   php tools/regen-golden.php
 *
 * Then `git diff tests/snapshots/bookstore-golden/` shows the PR's impact on
 * generated code. Commit the regeneration in the same commit as the
 * generator-side change.
 */

$repoRoot = dirname(__DIR__);
$buildDir = $repoRoot . '/tests/Fixtures/bookstore/build/classes';
$goldenDir = $repoRoot . '/tests/snapshots/bookstore-golden';

if (!is_dir($buildDir)) {
    fwrite(STDERR, "Build dir $buildDir does not exist. Run tests/bin/setup.sqlite.sh first.\n");
    exit(1);
}

// Clean golden first so deletions in the build dir surface as removed files.
if (is_dir($goldenDir)) {
    rrmdir($goldenDir);
}
mkdir($goldenDir, 0755, true);

rcopy($buildDir, $goldenDir);

// Normalize file modes for deterministic diff.
exec('find ' . escapeshellarg($goldenDir) . ' -type f -exec chmod 644 {} +');
exec('find ' . escapeshellarg($goldenDir) . ' -type d -exec chmod 755 {} +');

fwrite(STDOUT, "Golden tree refreshed at $goldenDir\n");
fwrite(STDOUT, "Run: git diff tests/snapshots/bookstore-golden/  to see drift.\n");

function rcopy(string $src, string $dst): void
{
    $dir = opendir($src);
    if ($dir === false) {
        return;
    }
    while (($file = readdir($dir)) !== false) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        $srcPath = $src . '/' . $file;
        $dstPath = $dst . '/' . $file;
        if (is_dir($srcPath)) {
            mkdir($dstPath, 0755, true);
            rcopy($srcPath, $dstPath);
        } else {
            copy($srcPath, $dstPath);
        }
    }
    closedir($dir);
}

function rrmdir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . '/' . $item;
        if (is_dir($path)) {
            rrmdir($path);
        } else {
            unlink($path);
        }
    }
    rmdir($dir);
}
