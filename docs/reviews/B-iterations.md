# Phase B — Iteration Log

Per umbrella spec §4.15.3: iteration cycles are tracked here. Budget is 3 per round (mirrors `A-iterations.md`).

## Round 1 (mid-phase, after Tasks B.2.1–B.2.10)

### Cycle 1 — Round 1 fix sweep

**Triggered by:** 1 MUST-FIX + 3 SHOULD-FIX findings from
`B-round-1-summary.md` (3 lenses + Code-Generator specialist; reviewer
mandate per umbrella §4.13.3).

**Resolution commits:** see commit log between `ce18cce20` and the cycle-1
HEAD; doc-only + one new test file. Single commit landing all four
fixes is acceptable per the workflow.

**Findings status:**

- **MUST-FIX L1-F1 / L2-F3 / L4-F4 (doc honesty about `: self` LSP)** —
  closed. Umbrella spec §3.5 rewritten honestly: both `: self` and `: static`
  fatal at class load against an override without a matching return type;
  `: self` is preferred because it's the easier remediation in consumer
  code. `MIGRATION-FROM-PRE-AI.md` "API surface changes" section now ships
  concrete before/after migration code, the FK-getter caveat, a grep pattern
  for finding offending overrides, and a Rector-rule pointer planned for
  Propel 4.0. `CHANGELOG.md` got a new "BC Breaks (Phase B)" subsection.
- **SHOULD-FIX L2-F2 (`__sleep` → `__serialize` wire-format break)** —
  closed. `MIGRATION-FROM-PRE-AI.md` has a new section near the existing
  `PropelDateTime` doc spelling out impact (sessions, PSR-6/16 caches,
  message queues, filesystem dumps), an invalidate-at-deploy migration
  path with concrete commands for Symfony cache / file sessions / Redis,
  and a one-time read-old-write-new pattern for high-value persisted state.
  `CHANGELOG.md` BC-Breaks entry references it.
- **SHOULD-FIX L2-F5 (`PropelTypes::*_NATIVE_TYPE` constant drift)** —
  closed. `MIGRATION-FROM-PRE-AI.md` documents the full constant table
  (`REAL/FLOAT/DOUBLE_NATIVE_TYPE`: `'double'` → `'float'`;
  `BOOLEAN/BOOLEAN_EMU_NATIVE_TYPE`: `'boolean'` → `'bool'`), a
  before/after code example showing why literal-string comparisons
  silently break, and a grep pattern for finding offending sites.
  `CHANGELOG.md` BC-Breaks entry references it.
- **SHOULD-FIX L3-F5 (typed-property regression test missing)** — closed.
  `tests/Propel/Tests/Generator/Builder/Om/GeneratedObjectTypedPropertiesTest.php`
  added: 5 test methods covering `?int`, `?string`, `?float`, `?bool`
  direct-assignment rejection plus a sanity test for null acceptance. The
  string and bool cases use array-typed sentinels because PHP juggles
  between scalars (str↔int, str↔bool); arrays are the canonical
  cannot-coerce sentinel.

**Quality stack final state (post-cycle):**

- `composer test:agnostic`: 2412 / 5165 / 21 — GREEN. (Up 5 from baseline 2407 — the new `GeneratedObjectTypedPropertiesTest`.)
- `composer cs-check`: clean.
- `composer stan`: 0 errors (baseline 403 lines unchanged).
- `composer psalm`: clean (baseline 1621 lines unchanged).
- `composer deptrac`: 0 violations / 171 allowed.

**Cycles remaining in Round 1 budget:** 2 of 3 unused. Cycle 1 sufficient.
Phase B cleared to advance to B.3 (EnumBuilder) and B.4 (Builder cleanup).
