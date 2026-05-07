# Phase G.3.4 — Lazy-Relation Hydration Benchmark Report

Per plan `docs/plans/2026-05-07-phase-g-php-84-and-4-0-release.md` §G.3.4
and the umbrella §G rollback criterion: lazy-object emission for relation
initializers must stay within +5% of the legacy eager-init latency on a
100k-row `with()` query, otherwise the default flip target slips from
4.1 to 4.2.

## Methodology

- Benchmark harness: `tests/Propel/Tests/Benchmarks/ActiveRecord/LazyRelationBenchmarkTest.php`.
- Two parallel schemas built via `QuickBuilder` against in-memory SQLite,
  identical except for `<table useLazyObjects="true">` on the parent
  (`Author`) table:
  - `LazyBench\Author` / `LazyBench\Book`  (PHP 8.4 `newLazyGhost` form)
  - `EagerBench\Author` / `EagerBench\Book`  (legacy eager init)
- Workload: insert N authors with 3 books each, then issue
  `AuthorQuery::create()->leftJoinWith('Author.Book')->find()` and
  iterate the resulting collection (forcing any deferred work to
  materialize).
- Sampling: 1 warmup run discarded; 5 trials; report the median.
- Hardware: Apple Silicon dev machine, PHP 8.4.14, single thread.

## Captured measurements

Each row is the `lazy / eager` median-latency ratio. Values ≤ 1.05
satisfy the §G rollback criterion.

| N (authors × 3 books) | Eager median | Lazy median | Ratio | Verdict |
|---:|---:|---:|---:|:---|
| 1 000  | 0.0211 s | 0.0211 s | 0.996 | within threshold |
| 5 000  | 0.1060 s | 0.1068 s | 1.008 | within threshold |
| 25 000 | 0.5261 s | 0.5170 s | 0.983 | within threshold |
| **100 000** | **2.1963 s** | **2.1878 s** | **0.996** | **within threshold (plan's full scale)** |

Run output (100k):

```
[lazy-bench] N=100000 eager=2.1963s lazy=2.1878s ratio=0.996 threshold=1.05
.                                                                   1 / 1 (100%)

Time: 01:38.293, Memory: 843.14 MB

OK (1 test, 14 assertions)
```

## Interpretation

Across all four scales (1k → 100k), the lazy form is statistically
indistinguishable from the eager form. The 100k-row run — the plan's
full-scale gate — produces a ratio of **0.996** (lazy is 0.4% faster
than eager, within measurement noise). Variance across scales sits in
the ±2% band, well inside the 5% rollback budget.

This was not the expected outcome a-priori. The lazy form pays
ReflectionClass-instantiation overhead per `init<Rel>()` call relative
to eager `new $collectionClassName`; the upside (skipped `setModel` +
collection allocation when the relation is never read) doesn't apply
to a `leftJoinWith` workload because that path explicitly hydrates the
related collection. The observed parity is consistent with PHP 8.4's
internal lazy-ghost machinery being unusually cheap — the
`ReflectionClass` allocation is amortized by JIT/opcache and the
closure-driven init runs only once per parent entity, while the eager
path's `setModel` call still has to happen at the same point in the
hydration flow.

## Verdict

**PASS — the §G rollback criterion holds at the plan's full 100k scale.**

Implications per plan §G.3.4 and risk register #1:

- Opt-in remains the only path for Propel **4.0**. The benchmark gate is
  conditional on the consumer side enabling
  `<table useLazyObjects="true">`, so 4.0 is a measurement and
  documentation milestone, not a behavior flip.
- The default flip target stays **4.1**. On the strength of this
  benchmark, no migration to 4.2 is warranted.
- Re-run this bench at 4.1 cycle start before flipping the default —
  a regression in PHP 8.4.x patch releases or in the
  `LazyRelationBuilder` emission shape (e.g. property hooks landing in
  G.5 may interact) could shift the ratio.

## How to reproduce

```sh
# Default (CI sanity, N=1000, ~1s):
vendor/bin/phpunit -c tests/agnostic.phpunit.xml --testsuite benchmarks --filter LazyRelationBenchmark

# Full plan scale (N=100k, ~100s, ~1GB peak):
PROPEL_BENCH_N=100000 php -d memory_limit=4G \
    vendor/bin/phpunit -c tests/agnostic.phpunit.xml \
    --testsuite benchmarks --filter LazyRelationBenchmark
```

Captured: 2026-05-07 (see `git log` for the G.3.4 commit).
