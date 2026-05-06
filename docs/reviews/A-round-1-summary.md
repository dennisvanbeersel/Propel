# Phase A — Round 1 Review Summary

**Date:** 2026-05-06
**Phase progress at review:** Tasks A.1–A.13 complete (Groups 1–3); A.14–A.44 pending.
**Reviewers dispatched:** 3 (architecture & SOLID; BC & migration realism; tooling & CI specialist).
**Cadence:** LOW-RISK 2-round (umbrella spec §4.13.3 / §5.0).
**Reports committed:** `A-round-1-architecture.md`, `A-round-1-bc.md`, `A-round-1-tooling.md`.

## Consolidated findings

### MUST-FIX (8) — closed before Group 4 starts

| # | Finding (and which reviewers raised it) | Resolution commit |
|---|---|---|
| 1 | `tests/deprecations.allowlist.json` wrong format. Symfony bridge expects flat array of `{location, message, count}`; we shipped `{deprecations: [], _comment: ...}` which would fault on first emitted deprecation. (BC#1, Arch M1) | `39d3cf6f8` |
| 2 | `lint-generated` job permanent no-op. Used `--filter=BookstoreBuildTest` (no such test class) + `\|\| true` swallow + `hashFiles()` short-circuit on a gitignored build dir. The gate has been silently green on every run. (Arch M2, Tooling) | `39d3cf6f8` |
| 3 | `tracked-classes.txt` only ~8% of Tier 1/2 surface (13 classes). Missing 19 Criterion classes, 9-class PropelException hierarchy, 5 Util classes, 5 Connection management classes, OnDemandCollection. (BC#4, Arch M3) | `39d3cf6f8` |
| 4 | Snapshot script omits `extends`/`implements`. Phase A.27's planned `Serializable` drop from `Collection` would have slipped the gate undetected. (BC#2) | `39d3cf6f8` |
| 5 | Snapshot script omits `@method` PHPDoc parsing. `ActiveRecordInterface`'s `@method toArray()` contract (umbrella §3.1) was invisible to Reflection-only dump. (BC#3) | `39d3cf6f8` |
| 6 | Infection dead-on-arrival. configDir pointed at `tests/` but no `phpunit.xml`/.dist file existed there. (Tooling) | `1e6f8eed2`, `8f67794a3` |
| 7 | `composer testsuite` script omitted Deptrac. Local devs running the umbrella exit gate didn't get architecture conformance checks. (Tooling) | `39d3cf6f8` |
| 8 | `tools/check-baseline-monotonic.php` counting asymmetry between `fgets()` and `substr_count("\n")`; silently passed on invalid merge-base refs. (Tooling) | `39d3cf6f8` |

### SHOULD-FIX (closed)

- **Composer manifest drift.** `tests/composer/composer-symfony7-{min,max}.json` now include `infection/infection`, `deptrac/deptrac`, `innmind/black-box`, plus `infection/extension-installer` allow-plugin entry. CI matrix swaps to one of these manifests pre-test; previously it ran without the new tooling. Closed in `39d3cf6f8`.

### SHOULD-FIX (deferred to Round 2 / later phases — with reasoning)

See `A-waivers.md` for documented waivers.

### NICE (advisory; no action this round)

- BC-safe widening allowlist is comment-only (Arch S1) — the gate currently fails on ANY signature drift, which is the safer default. Real allowlist enforcement implementable in Phase B' when generator return-type changes land.
- black-box has no seedable RNG (Arch S5) — flagged as Phase F reproducibility risk; will be re-evaluated when Phase F's `replaceNames` PBT is drafted.
- `baseline-monotonic` no-ops on direct push to integration branch with empty `github.base_ref` (Arch S7) — partially addressed by my fallback to `origin/master`. Not a regression vs. pre-Phase-A state (where the check didn't exist at all).
- Deptrac has no monotonic gate (Tooling) — separately tracked; Deptrac baseline freezes today's violations and the workflow's `composer deptrac` exits non-zero on new violations; that's the gate. A line-count drawdown contract on `deptrac-baseline.yaml` is a Phase D candidate when behavior modifiers refactor.
- Round 2 should re-verify the 177-file Rector migration (e06188773) and the manual `QuotingTest.php` `Group` alias fix.

## Definition-of-Done check (mid-phase)

| DoD item (umbrella §4.9) | Status |
|---|---|
| Test matrix green | ✓ 2386/5126/21 on agnostic (mysql/pgsql require local DB) |
| Baselines decreased ≥20% | ⏳ Phase A.32–A.33 (pending) |
| Coverage delta ≥0% | ⏳ A.34 captures baseline first |
| Mutation MSI ≥65 | ⏳ Pre-existing test isolation issue surfaces under Infection runner; will run full run at Round 2 |
| Deptrac green | ✓ 0 new violations against 233-violation baseline |
| Perf benchmarks within ±5% | ⏳ A.34 captures initial perf baseline |
| CHANGELOG updated | ⏳ A.41 |
| Deprecation audit clean | ✓ Allowlist format fixed; 0 emitted |
| Generated-code lint parity | ✓ Gate now real (post-fix) |
| Golden-file diff | ⏳ A.35 |
| Phase plan retrospective | ⏳ end of phase |
| Reviewer reports committed | ✓ this commit |
| Surgical-test reports committed | ⏳ Round 2 |
| MUST-FIX closed | ✓ 8/8 |
| Iterations within budget | ✓ 1 cycle (this summary), well within 3-cycle budget |

## Iteration cycle log

- **Cycle 1 (this round):** 8 MUST-FIX + 1 SHOULD-FIX consolidated and resolved across commits `1e6f8eed2`, `8f67794a3`, `39d3cf6f8`. No verify-mode reviewer re-engagement required this cycle since the fixes are mechanical and visible in diffs (per §4.15.2). Round 2 reviewers will validate end-to-end.

Round 1 closed. Proceeding to Group 4 (Critical bug fixes A.14–A.20).
