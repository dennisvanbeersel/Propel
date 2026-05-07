# Phase B — Performance benchmark re-run

**Date:** 2026-05-06
**HEAD under review:** `1d4e0fca1` (Phase B end)
**Comparison:** `docs/reviews/perf-baseline.json` (Phase A end, captured 2026-05-06T11:02:17Z)

---

## Methodology

`tools/capture-perf-baseline.php` runs each `tests/Propel/Tests/Benchmark/*Bench.php`
class with the bench's own warmup and measured-iteration counts, capturing
`hrtime` deltas + `memory_get_peak_usage` deltas. Output is a JSON manifest
consumable by per-phase comparison.

The benchmark suite contains exactly **one** active bench:

- `criteria_build_filter_chain_1k` (`CriteriaBuildBench`) — exercises
  `Criteria::add` + `addAnd` chaining 1,000 times. **Runtime/ActiveQuery**
  hot-path, NOT generated-AR-shape.

This is a gap: Phase B touched generated AR shape (typed properties,
return types) and that path is **not** benchmarked. The umbrella spec
§4.10 lists "Hydrate 100k-row `Book` collection" as a target metric;
no `HydrateBench` exists. Flagged in lens-3 findings as SHOULD-FIX.

---

## Numbers

| Metric | Phase A end (baseline) | Phase B end (re-run) | Delta |
|---|---|---|---|
| `criteria_build_filter_chain_1k` mean_ns | 39,818,816.8 | 40,126,391.8 | **+0.77%** |
| `criteria_build_filter_chain_1k` min_ns | 39,623,833 | 39,903,625 | +0.71% |
| `criteria_build_filter_chain_1k` max_ns | 40,056,250 | 40,364,000 | +0.77% |
| `memory_peak_bytes` | 0 | 0 | unchanged |
| PHP version | 8.4.14 | 8.4.14 | unchanged |

**Verdict:** within ±5% acceptance gate per umbrella §4.10. NICE.

The +0.77% delta is well inside measurement noise (5 iterations × hrtime
jitter on a non-isolated host). No statistically meaningful regression.

---

## Phase B touch surface vs. benchmark coverage

Phase B affected:
1. **Generated AR class shape** — typed properties, `: self` setters, `: ?T`
   FK getters, `: static` `create()`, `__serialize`/`__unserialize`.
2. **Generated query class shape** — return types, `: static` factory.
3. **Generated enum classes** — new artifact per ENUM column.
4. **Builder internals** — match expressions, dead-code removal, variadic
   `declareClasses()`. Code-generation-time only; not on runtime hot path.

The benchmark covers **none** of (1)-(3). It covers a sliver of the
**Criteria** runtime, which Phase B did NOT touch. This is a gap in the
gate, not a gap in Phase B's deliverables — the gate was set up by Phase A
with only one bench.

**Recommendation for Phase B end-of-phase DoD:** mark the perf box
"GREEN with caveat" — within ±5% on the only available bench, but
hydrate-path was not measured. Block Phase C / Phase D from declaring
"performance regression-free" until a `HydrateBench` is added.

---

## Re-run captured at

```
2026-05-06T19:19:55Z
```

Raw JSON for posterity:

```json
{
    "captured_at": "2026-05-06T19:19:55Z",
    "php_version": "8.4.14",
    "benchmarks": {
        "criteria_build_filter_chain_1k": {
            "iterations": 5,
            "mean_ns": 40126391.8,
            "min_ns": 39903625,
            "max_ns": 40364000,
            "memory_peak_bytes": 0
        }
    }
}
```
