# Phase A — Performance Benchmark Report

Per umbrella spec §4.10 / §4.14, Phase A is the **baseline-capture** phase (no perf-improvement gate). Numbers below feed Phase E/F/G/J targets.

## Captured baseline (`docs/reviews/perf-baseline.json`)

```json
{
    "captured_at": "2026-05-06T11:02:17Z",
    "php_version": "8.4.14",
    "benchmarks": {
        "criteria_build_filter_chain_1k": {
            "iterations": 5,
            "mean_ns": 39818816.8,
            "min_ns": 39623833,
            "max_ns": 40056250,
            "memory_peak_bytes": 0
        }
    }
}
```

## Re-run (review-time spot check, 2026-05-06 ~11:25Z)

```
mean_ns: 38_584_366.6   (Δ vs committed baseline: −3.1 %)
min_ns:  37_929_208     (−4.3 %)
max_ns:  39_039_542     (−2.5 %)
```

Within the documented ±5 % tolerance for an uncalibrated developer rig — the benchmark is reproducible.

## Reading

- `criteria_build_filter_chain_1k` measures 1000 `Criteria::add(...)` calls plus operator-comparison assembly. ~40 ms / 1k operations (~40 µs / op) on PHP 8.4.14 / Apple-Silicon dev machine. This is the **only** benchmark currently shipped — sufficient as a placeholder for §4.10 row "Query overhead per call (vs raw PDO)".
- `memory_peak_bytes: 0` is a **defect**: the bench plumbing reports `memory_peak_bytes` but the AbstractBench harness never assigns it. Listed as SHOULD-FIX in this round; trivial to repair before Phase E (when peak-memory targets become enforceable).
- The §4.10 baseline-row promises that are NOT yet captured:
  - Hydrate 100k-row collection — TBD.
  - Memory peak 100k-row hydrate — TBD.
  - Query overhead per call vs raw PDO — partially captured (Criteria assembly only; doesn't include `prepare`/`execute`).
  - Prepared-statement cache hit rate — not captured (cache is unbounded today; numbers will be meaningful after Phase E LRU lands).

## Verdict

Acceptable for a Phase-A baseline-capture phase. The single benchmark + raw JSON satisfies the umbrella requirement that "performance baselines exist and are reproducible." Phases E / F / G / J must add real hydration / connection / formatter benchmarks against this scaffold before their own DoD gates can be met — flagged as a forward-deferred deliverable, not a Phase-A blocker.
