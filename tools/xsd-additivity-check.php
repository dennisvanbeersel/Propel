#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * XSD additivity gate (umbrella §3.7 / Phase C task C.1.5).
 *
 * Walks every tests/Fixtures/**\/schema.xml and any other Phase C fixture under
 * tests/Fixtures/schemas/phase-c/, and validates each against
 * resources/xsd/database.xsd. Exit non-zero on any failure.
 *
 * The contract: any change to database.xsd MUST be additive — schemas valid
 * against the prior XSD must remain valid against the new XSD.
 *
 * Empty (zero-byte) schema files are skipped — those are intentional sentinels
 * used by recursive directory tests (e.g. tests/Fixtures/recursive/).
 */

$root = dirname(__DIR__);
$xsd = $root . '/resources/xsd/database.xsd';

if (!is_file($xsd)) {
    fwrite(STDERR, "  ! XSD not found at $xsd\n");
    exit(2);
}

$fixtureDirs = [
    $root . '/tests/Fixtures',
];

$schemas = [];
foreach ($fixtureDirs as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
    foreach ($iter as $file) {
        if (!$file->isFile()) {
            continue;
        }
        $name = $file->getFilename();
        if ($name !== 'schema.xml' && !str_ends_with($name, '.xml')) {
            continue;
        }
        $path = $file->getPathname();
        // Only consider files inside fixture dirs that look like schema files.
        if (str_contains($path, '/build/') || str_contains($path, '/fixtures_built/')) {
            continue;
        }
        if ($name === 'schema.xml' || str_contains($path, '/schemas/phase-c/')) {
            $schemas[] = $path;
        }
    }
}

sort($schemas);

if ($schemas === []) {
    fwrite(STDERR, "  ! No schema files discovered\n");
    exit(2);
}

$prev = libxml_use_internal_errors(true);
$failed = 0;
$skipped = 0;
$validated = 0;

foreach ($schemas as $schema) {
    if (filesize($schema) === 0) {
        $skipped++;
        continue;
    }
    $doc = new DOMDocument();
    if (!@$doc->load($schema)) {
        fwrite(STDERR, "  ✗ $schema: load failed\n");
        $failed++;
        libxml_clear_errors();
        continue;
    }
    if (!@$doc->schemaValidate($xsd)) {
        $errors = libxml_get_errors();
        libxml_clear_errors();
        fwrite(STDERR, "  ✗ $schema: " . trim((string) ($errors[0]?->message ?? 'validation failed')) . "\n");
        $failed++;
        continue;
    }
    fwrite(STDOUT, "  ✓ $schema\n");
    $validated++;
}

libxml_use_internal_errors($prev);

fwrite(STDOUT, sprintf("\nXSD additivity: %d validated, %d skipped (empty), %d failed\n", $validated, $skipped, $failed));

exit($failed > 0 ? 1 : 0);
