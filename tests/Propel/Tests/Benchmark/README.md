# Performance Benchmarks

Per umbrella spec §4.10 + §4.14, performance benchmarks are committed deliverables. Phase A captures the baseline numbers; Phases E (connection collapse), F (Criteria split), G (PHP 8.4 lazy objects), and J (worker mode) measure improvement against this baseline.

## Running

```bash
php tools/capture-perf-baseline.php > docs/reviews/perf-baseline.json
```

The script runs each benchmark in `tests/Benchmark/*Bench.php`, captures `hrtime` deltas + memory peaks, and emits a stable JSON file consumable by the perf comparison CI job.

## Targets (umbrella spec §4.10)

| Metric | Today (Phase A baseline) | 4.0 target |
|---|---|---|
| Hydrate 100k-row collection | TBD ms | ≥30% faster |
| Memory peak for 100k-row hydrate | TBD MB | ≤TBD MB (no regression) |
| Query overhead per call (vs raw PDO) | TBD µs | ≤2× raw PDO |
| Prepared-statement cache hit rate | TBD% | ≥90% |

The "TBD" values are filled in by Phase A's `capture-perf-baseline.php` run and committed to `docs/reviews/perf-baseline.json` as the canonical reference.

## Adding a benchmark

1. Create `tests/Benchmark/XxxBench.php` extending `AbstractBench`.
2. Override `name(): string`, `setUp(): void`, `run(): void`, optionally `tearDown(): void`.
3. The capture script picks it up automatically.
