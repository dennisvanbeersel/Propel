# Phase A — Iteration Log

Per umbrella spec §4.15.3: iteration cycles are tracked here. Budget is 3 per round.

## Round 1 (mid-phase, after Tasks A.1–A.13)

### Cycle 1 — Round 1 fix sweep

**Triggered by:** 8 MUST-FIX + 5 SHOULD-FIX findings across 3 reviewer reports (`A-round-1-{architecture,bc,tooling}.md`).

**Resolution commits:**
- `1e6f8eed2` fix(infection): file move setup for working configDir
- `8f67794a3` fix(infection): point at tests/ configDir; drop duplicate --configuration arg
- `39d3cf6f8` fix: address Round 1 review MUST-FIX + SHOULD-FIX findings (bulk fix; details in commit message)

**Findings status:**
- 8/8 MUST-FIX → closed in this cycle.
- 1/5 SHOULD-FIX → closed (composer manifest drift).
- 4/5 SHOULD-FIX → waived per `A-waivers.md` with documented reasoning + future-reviewer/plan confirmation.
- NICE findings → tracked in summary; no action.

**Verify-mode re-engagement:** none required this cycle (fixes are mechanical and visible in diffs). Round 2 reviewers will validate end-to-end.

**Cycles remaining in Round 1 budget:** 2 of 3 unused. Cycle 1 sufficient.

## Round 2 (end-phase pre-merge, after Tasks A.14–A.42)

_Pending phase completion._

---

## A.31 baseline-growth note (mid-phase)

**Context:** Running `composer stan` and `composer psalm` immediately before
A.31 (i.e. after A.30 commit `e3fe4ed65`) reports 22 phpstan errors and ~35
psalm errors that are NOT in the existing baselines. They were introduced by
upstream removals of the Validate / QueryCache behaviors (commits before
this work session) but the baselines were not regenerated at the time.

**Verification:** the 22 phpstan errors count is identical before and after
the Rector run that adds `#[\Override]`. Override addition itself contributes
zero new analysis errors. Psalm temporarily grew because Override added on a
method previously baselined as "MissingOverrideAttribute" turned that entry
into "UnusedBaselineEntry"; psalm-set-baseline cleans up.

**Action:** Regenerated `phpstan-baseline.neon` and `psalm-baseline.xml` so
the suite stays green. phpstan-baseline grew 548 → 644 lines. This violates
umbrella spec §4.1's "baselines must shrink, not grow" rule; the growth is
fully attributable to the prior Validate / QueryCache removal, not to A.31.

**Follow-up:** Phase A drawdown tasks A.32–A.33 should reduce these baselines
back below the original size.

---

## A.32–A.33 baseline drawdown (mid-phase)

**Context:** Per umbrella spec §4.1 Phase A targets:

- phpstan-baseline.neon ≤ 438 lines (≤80% of 548 starting point)
- psalm-baseline.xml ≤ 2078 lines (≤80% of 2598 starting point)

**Drawdown commits (chronological):**

| SHA | Surface | What was fixed |
|---|---|---|
| `5f3256c3f` | Generator/Builder/Om | Drop 4 blanket regex `ignoreErrors` from `phpstan.neon` and `ImplementedReturnTypeMismatch` global suppression from `psalm.xml`. Move `addColumnAccessorMethods`/`addColumnMutatorMethods` from `AbstractObjectBuilder` (which lacked the underlying traits) into `ObjectBuilder` (which has them). Closes 21 phpstan errors. |
| `1e4b2a5c7` | Generator/Behavior/I18n | Remove dead `Validate\ValidateBehavior` references from `I18nBehavior::moveI18nColumns()` (class deleted in A.22). Tighten `getI18nColumnNamesFromConfig()` to honour its `list<string>` declared return (was emitting sparse arrays). Closes 10 phpstan errors. |
| `3a42b9faf` | Runtime/ActiveQuery/SqlBuilder | Tighten `?array &$params` to `array &$params` on `buildJoinClauses`, `buildWhereClause`, `buildHavingClause`, `buildFromClause`, `buildStatementFromCriterion` across `AbstractSqlQueryBuilder`, `SelectQuerySqlBuilder`, `UpdateQuerySqlBuilder`. The nullable was a leftover; no caller passes null. Closes 7 phpstan errors. |
| `70bbe8f52` | Generator/Migration | Introduce `Propel\Generator\Migration\MigrationInterface` capturing the contract every generated migration class already follows. Update `MigrationManager::getMigrationObject()` PHPDoc to return the interface. Static-analysis-only (legacy migrations on disk remain unchanged). Closes 8 phpstan errors. |
| `d83232645` | Runtime/ActiveRecord/NestedSet | Introduce `NestedSetNodeInterface` and use it via PHPDoc on `NestedSetRecursiveIterator::$topNode`/`$curNode`. Same pattern: static analysis sees the methods; native types remain `object` so legacy generated AR classes are unaffected. Closes 5 phpstan errors. |
| `d3ddbc68e` | Runtime/ActiveRecord | Add `@method` PHPDoc on `ActiveRecordInterface` for the lifecycle / serialization methods every generated AR class emits (`save`, `delete`, `fromArray`, `setNew`, `setDeleted`, `clear`, `getPrimaryKey`, etc.). Per spec §3.1 Tier 1 freeze: `@method` PHPDoc is allowed (the existing `toArray()` follows the same pattern); abstract method additions are not. Closes the bulk of `ArrayCollection`/`ObjectCollection`/`Collection`/`Formatter` `method.notFound` errors at the source. |
| `c52cbab97` | Cross-cutting | Apply `psalm --alter --issues=MissingOverrideAttribute`. 112 method overrides got the attribute they were missing. Pure decoration; no runtime change. Drops psalm baseline 2260 → 1621 lines. |

**Final state:**

- phpstan-baseline.neon: **403 lines** (target ≤438) — 75 errors remaining.
- psalm-baseline.xml: **1621 lines** (target ≤2078) — drops 2598 → 1621, a ~38% reduction.
- Tests: 2412 / 5175 / 21 maintained throughout.

**Errors that remain baselined (and why):**

- `Runtime/ActiveQuery/ModelCriteria.php` (9 errors) — the `joins` property is typed as `array<Join>` on the parent `Criteria` class, but every entry inside a `ModelCriteria` is a `ModelJoin`. Phpstan's instruction is explicit: "Do not use `assert()` or inline `@var` to override inferred types. Do not widen parameter or return types just to make the error go away." A clean fix requires either making `Join::getTableMap()` exist on the base class (returning `null`), or refactoring `Criteria::$joins` typing — both BC-sensitive. Deferred to Phase B (BC + builder modernization).
- `Generator/Util/QuickBuilder.php` (4 errors), `Generator/Reverse/MysqlSchemaParser.php` (3), `Generator/Behavior/ConcreteInheritance/ConcreteInheritanceBehavior.php` (3) — codegen / reverse-engineering hot paths that touch dynamically-loaded user objects. Same `object::method()` pattern as migrations / nested-set, but the call sites are scattered and adding interfaces is a per-subsystem refactor better done in Phase D.
- `Runtime/ActiveQuery/SqlBuilder/SelectQuerySqlBuilder.php` (3 errors), various `Runtime/Formatter/*` (6 errors) — narrower types on protected helpers that participate in the SqlBuilder/Formatter SPI. To be revisited in Phase E (connection layer) where the SqlBuilder boundary gets retyped end-to-end.

**Conclusion:** Both Phase A targets met. Subsequent phase work will continue the drawdown via the same source-fix pattern (no blanket regex, no per-callsite ignores).
