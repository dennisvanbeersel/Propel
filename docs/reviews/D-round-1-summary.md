# Phase D Round 1 Summary (mid-phase)

**Date:** 2026-05-07
**Branch:** `ar-rewrite`
**Trigger:** D.1 + D.2 + D.5 (partial) + D.6 (partial) landed, ahead of D.3 / D.4 byte-identical refactor.

## Scope of this round

Per the plan (umbrella §4.13.3 mid-phase trigger), Round 1 was scheduled after CodeEmitter introduction + Sortable refactor landed. The execution shape diverged from the original phasing: D.1 (CodeEmitter intro) landed cleanly, D.2 (NestedSet deprecation) and D.5 (Timestampable native ON UPDATE) and D.6.3-D.6.6 (Phase C deferrals — UUID heuristic, --reverse-format flag, IndexComparator extensions, ForeignKeyComparator DEFERRABLE drift) landed in this round, but the bulk of the Sortable + NestedSet refactor (D.3 / D.4) is deferred — see "Deferred work" below.

The reviewer lenses below are scoped to what landed.

## Architecture (CodeEmitter design)

**Verdict: PASS.**

CodeEmitter (`src/Propel/Generator/Builder/Util/CodeEmitter.php`) shipped per D.1.1–D.1.4 with:

- core indent + line emission (D.1.1),
- method-signature + expression-escape helpers (D.1.2),
- PBT for indentation invariant (D.1.3),
- two real consumers proving the API: Timestampable `objectMethods()` (D.1.4 / D.5.1 POC) and Sortable `addIsFirst()` (D.3.1 incremental — byte-identical verified against the bookstore golden tree).

The RAII `block()` scope works as designed; `unset($body)` is the de-facto closing convention. No API redesign needed mid-flight. The `lines()` heredoc helper proved unused on the two real consumers — an indicator that line-by-line emission is more useful in practice than bulk heredoc dumps.

**Sharp edges flagged for Round 2 attention (none MUST-FIX):**

- The naming `block()` returns a RAII scope object — code reviewers may at first read it as "open a block (will auto-close)", which is correct semantically but syntactically subtle (the `unset($body);` is the close signal). A method called `withBlock()` accepting a closure was considered and rejected as uglier in practice; document the convention in `docs/CODEEMITTER.md` (deferred — doc not authored in this round).

## BC (NestedSet deprecation runway)

**Verdict: PASS.**

- Class-level `@deprecated` PHPDoc on `NestedSetBehavior` (D.2.1).
- `trigger_deprecation('propel/propel', '3.0', ...)` fires once per affected schema (placement: top of `modifyTable()`).
- Generated AR/Query class `@deprecated` headers (D.2.2): pragmatic scope chosen — single header comment block prepended via `objectMethods()` / `queryMethods()` rather than per-method docblock injection. Trade-off: lower per-method specificity but byte-cheap to implement and consumer-visible in IDE-rendered class outline.
- Migration cookbook (D.2.3) covers schema migration (parent_id FK), data conversion SQL, query migration for the 5 most-common operations, performance comparison numbers, ecosystem coordination notes.
- CHANGELOG entry under `[Unreleased]` — `## [Unreleased] — Phase D: Behaviors cleanup + CodeEmitter`, with Added / Changed / Deprecated subsections.

`phpstan.neon` adds path-scoped `ignoreErrors` for the cascading deprecation noise (the modifier classes call into deprecated `NestedSetBehavior`; the entire subsystem is deprecated together; suppression is scoped to `src/Propel/Generator/Behavior/NestedSet/*` and `src/Propel/Runtime/ActiveRecord/NestedSet*` — removed in 4.0 along with the subsystem).

## Code generator specialist (refactor verification)

**Verdict: PASS for what landed; INCOMPLETE for D.3 / D.4 bulk refactor.**

What landed and was verified byte-identical:

- D.1.4 / D.5.1 POC: Timestampable `objectMethods()` ported to CodeEmitter — byte-identical golden contract verified.
- D.3.1 incremental: Sortable `addIsFirst()` ported to CodeEmitter — `git diff tests/snapshots/bookstore-golden/` is empty after regen.

The pattern is proven. The remaining Sortable methods (~25 `add*` methods on `SortableBehaviorObjectBuilderModifier`, ~15 on `SortableBehaviorQueryBuilderModifier`) and the entire NestedSet refactor (~2,900 LOC across two modifiers) are mechanical port work following the proven pattern. They are deferred — see "Deferred work" below.

## Quality gates (per-task verification)

Every commit in this round was verified against:

| Gate | Status |
|---|---|
| `composer test:agnostic` | GREEN (2532 → 2553 tests; +21 new agnostic tests across D.6.3 / D.6.5 / D.6.6 / D.6.4) |
| `composer stan` | clean (0 errors; 403 baseline lines, monotonic) |
| `composer psalm` | 16 errors (unchanged from Phase C end; baseline-tracked, all PossiblyUnused on Phase C newly-introduced methods) |
| `composer cs-check` (touched files) | clean |
| `vendor/bin/deptrac` | 0 violations |
| `php tools/xsd-additivity-check.php` | 12 fixtures green (D.6.6 added optional `deferrable` / `initiallyDeferred` attributes — additive) |
| `php tools/regen-golden.php` + `git diff tests/snapshots/bookstore-golden/` | empty after every commit |

## Deferred work (carry to follow-up cycle)

The following plan tasks are NOT landed in Phase D:

| Task | Reason | Suggested follow-up shape |
|---|---|---|
| D.3.1–D.3.5 remainder (Sortable bulk port) | Per-method byte-identical work; pattern proven on `addIsFirst()`; remaining ~40 methods are mechanical | Subagent-driven, fresh context, one method per iteration |
| D.4.1–D.4.5 (NestedSet bulk port) | Same as D.3, on a deprecated subsystem (lower-value) | Could be skipped entirely — NestedSet is removed in 4.0; refactoring deprecated code competes for attention with new work. Recommendation: skip D.4, accept the 3,036 LOC of deprecated string-concat as terminal, kill it in 4.0 |
| D.6.1 (MysqlSchemaParser INFORMATION_SCHEMA migration) | Per plan §"Critical caveat": testcontainers harness needed for round-trip PBT; not in scope | Phase D' / stretch with testcontainers |
| D.6.2 (PgsqlSchemaParser INFORMATION_SCHEMA migration) | Same | Same |

The `--reverse-format=legacy-show-create` flag (D.6.4) DID land — as forward-compatible scaffolding. The flag and deprecation are wired so consumers adopting them ahead of time get a clean upgrade path; the actual "new" path's full implementation is the deferred work above.

## Plan-vs-actual table

| Task | Status | Commit |
|---|---|---|
| D.1.1 CodeEmitter core | landed | 546d90a13 (pre-Round-1) |
| D.1.2 method/escape helpers | landed | 032c54238 |
| D.1.3 PBT | landed | 4deab2939 |
| D.1.4 POC (swapped to Timestampable) | landed | c23e3336d |
| D.2.1 NestedSet @deprecated + trigger | landed | 5ad10058b |
| D.2.2 NestedSet generated @deprecated header | landed | dcf6dd3e7 |
| D.2.3 NestedSet migration cookbook | landed | 6af293e40 |
| D.2.4 BC tier-2 commitment + CHANGELOG | landed (folded into D.2.3) | 6af293e40 |
| D.3.1 Sortable port | partial (POC: addIsFirst) | bf0b048b3 |
| D.3.2 Sortable QueryBuilder port | DEFERRED | — |
| D.3.3 Sortable TableMap port | N/A — already template-based, no port needed | — |
| D.3.4 Sortable shared helpers extraction | DEFERRED | — |
| D.3.5 Sortable end-state verification | DEFERRED | — |
| D.4.* NestedSet refactor | DEFERRED (recommend skip; deprecated subsystem) | — |
| D.5.1 Timestampable native ON UPDATE | landed | 394cfb214 |
| D.5.2 Timestampable migration cookbook | landed | 961f643ff |
| D.5.3 AggregateColumn future-bridge | landed | 961f643ff |
| D.6.1 MysqlSchemaParser INFORMATION_SCHEMA | DEFERRED (plan §"Critical caveat") | — |
| D.6.2 PgsqlSchemaParser INFORMATION_SCHEMA | DEFERRED (plan §"Critical caveat") | — |
| D.6.3 UUID heuristic | landed | 681435a90 |
| D.6.4 --reverse-format flag | landed | 5dac4e841 |
| D.6.5 IndexComparator extensions | landed | ed13a61a0 |
| D.6.6 ForeignKeyComparator DEFERRABLE | landed | 24228e450 |

## Iteration budget

This round consumed 0 of 3 cycles (no MUST-FIX surfaced; one course correction on the Timestampable test fixtures was a single-commit fix-forward).

## Verdict

**PASS-WITH-DEFERRALS.**

Phase D delivered the CodeEmitter foundation, the NestedSet deprecation, the Timestampable native ON UPDATE switchover, the AggregateColumn future-bridge documentation, and 4 of 6 reverse-parser / Diff-comparator items. The byte-identical refactor pattern is proven on two real consumers. The Sortable bulk port and the reverse-parser INFORMATION_SCHEMA migration are deferred to a follow-up cycle as documented.
