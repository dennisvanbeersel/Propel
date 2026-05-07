<?php

declare(strict_types=1);

/**
 * Run each Bench in tests/Benchmark/*Bench.php and emit a stable JSON file
 * consumable by the per-phase perf comparison CI job (umbrella spec §4.10).
 *
 * Output schema:
 *   {
 *     "captured_at": "<ISO 8601 UTC>",
 *     "php_version": "<8.x.y>",
 *     "benchmarks": {
 *       "<name>": {
 *         "iterations": N,
 *         "mean_ns": float,
 *         "min_ns": int,
 *         "max_ns": int,
 *         "memory_peak_bytes": int
 *       },
 *       ...
 *     }
 *   }
 *
 * Usage:  php tools/capture-perf-baseline.php > docs/reviews/perf-baseline.json
 */

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../autoload.php.dist';

$benchDir = __DIR__ . '/../tests/Propel/Tests/Benchmark';
$benchFiles = glob($benchDir . '/*Bench.php') ?: [];

$results = [];
foreach ($benchFiles as $file) {
    $shortName = basename($file, '.php');
    if ($shortName === 'AbstractBench') {
        continue;
    }
    $class = 'Propel\\Tests\\Benchmark\\' . $shortName;
    if (!class_exists($class)) {
        require_once $file;
    }
    if (!class_exists($class)) {
        fwrite(STDERR, "Could not load $class from $file\n");
        continue;
    }

    /** @var \Propel\Tests\Benchmark\AbstractBench $bench */
    $bench = new $class();
    $bench->setUp();

    for ($i = 0; $i < $bench->warmupIterations(); $i++) {
        $bench->run();
    }

    $samples = [];
    $startMem = memory_get_peak_usage(true);
    for ($i = 0; $i < $bench->measuredIterations(); $i++) {
        $start = hrtime(true);
        $bench->run();
        $samples[] = hrtime(true) - $start;
    }
    $endMem = memory_get_peak_usage(true);

    $bench->tearDown();

    $results[$bench->name()] = [
        'iterations' => count($samples),
        'mean_ns' => array_sum($samples) / count($samples),
        'min_ns' => min($samples),
        'max_ns' => max($samples),
        'memory_peak_bytes' => max(0, $endMem - $startMem),
    ];
}

$payload = [
    'captured_at' => gmdate('Y-m-d\TH:i:s\Z'),
    'php_version' => PHP_VERSION,
    'benchmarks' => $results,
];

echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
