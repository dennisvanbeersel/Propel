# Phase A — Summary

**Status:** Complete (PASS).
**Branch:** `ar-rewrite` at `6601a0347`.
**Effort:** 44 tasks across 11 groups + Round 1 + Round 2 reviews + 1 iteration cycle.
**Test suite:** 2413 / 5176 / 21 (GREEN; up from 2386 / 5126 / 21 at phase start — +27 tests of regression coverage).

## What Phase A delivered

The foundational tooling, BC machinery, and review framework that every subsequent phase depends on. Nothing visible to consumers; everything needed for the next phases to land safely.

### Quality gates (machine-checked, monotonic)

| Gate | Pre-Phase A | Phase A end |
|---|---|---|
| `phpstan-baseline.neon` | 548 lines + 4 blanket regex `ignoreErrors` | **403 lines, 0 blanket regex** (-27%) |
| `psalm-baseline.xml` | 2598 lines + `ImplementedReturnTypeMismatch` global suppress | **1621 lines, suppression dropped** (-38%) |
| `deptrac` violations | none tracked | **0 violations** against 233-line baseline; 4 layers (Common/Generator/Runtime/RuntimeInternal) |
| PHPUnit `failOn*` flags | all `false` (TODO comment in configs) | all `true` (Deprecation, PhpunitDeprecation, Warning, Risky, Incomplete, Notice, EmptyTestSuite) |
| Coverage in CI | `coverage: none` | `coverage: pcov`, clover artifact uploaded |
| Mutation testing | not installed | Infection 0.29 wired; runs cleanly (MSI 83% / Covered-MSI 91% on filtered Map run) |
| Architecture testing | not installed | Deptrac 2.0 with layer rules + baseline |
| Property-based testing | not installed | innmind/black-box 6.x scaffold (replaced eris which is PHP-7-only) |
| Generator-output linting | not enforced | `lint-generated` CI job runs phpcs + phpstan against regenerated bookstore — same bar as `src/` |
| BC signature contract | not enforced | 53 Tier 1/2 classes snapshotted; `signature-diff` CI gate |
| Generated-code regression | not enforced | 399-file golden tree committed; `golden-diff` CI gate (character-by-character `diff -ru`) |
| Baseline drawdown | not enforced | `baseline-monotonic` CI gate (substr_count-consistent, ref-validating) |

### Critical bug fixes (all with TDD regression tests)

7 production bugs found and fixed during Phase A — none introduced by the rewrite, all latent:

1. **`MysqlPlatform::getMajorServerVersionNumber`** — off-by-one returned `0` for MySQL "8.0.30"; the MySQL-8 NOACTION default-FK-action branch had never executed.
2. **`PgsqlAdapter::getId`** — sequence name string-quoted instead of identifier-quoted; broken for case-sensitive schema-qualified sequences. Latent injection vector.
3. **`Collection::offsetGet`** — returned `null` from a by-reference function; PHP 8 notice silently failed under `failOnNotice`.
4. **`ConnectionWrapper`** — prepared-statement cache ignored `$driverOptions`; silent statement reuse with wrong cursor type / fetch mode.
5. **Formatters STI hydration** — `new ReflectionClass($class)` per row × per `with()`-relation; O(N×M) wasted allocations in the hot path.
6. **`PropelDateTime::__sleep`/`__wakeup`** — shadowed by parent `DateTime::__serialize` on PHP 8+ (dead serialization path) + threw on invalid stored timezone. Migrated to `__serialize`/`__unserialize` API with UTC fallback.
7. **`MigrationManager::getAllDatabaseVersions`** — caught EVERY `PDOException` and silently created the migration table; masked typo'd table names, permission errors, network failures, schema drift. Now only auto-creates on table-not-found SQLState/message patterns.

### Dead code removed

- `Propel\Generator\Behavior\Validate` — imported Symfony 3.0-removed classes; broken on Symfony 4+.
- `Propel\Generator\Behavior\QueryCache` — used `apc_*` (removed PHP 5.5).
- `Propel\Runtime\Validator\Constraints\*` — Symfony 6+ supports `DateTimeInterface` natively.
- MyISAM plumbing in `MysqlPlatform` — InnoDB only.
- MySQL 4.1.x conditional in `getBeginDDL`.
- PECL bug #9919 `getColumnBindingPHP` workaround.
- HHVM strict-issue comments.
- XSLT pipeline in `AbstractManager::loadDataModels`.
- `tests/Fixtures/etc/xsl/` directory.
- `Serializable` interface from `Collection` (PHP 8.1 soft-deprecated).
- 30+ `ValidateTrigger*` generated fixture artifacts.

### BC alias-deprecations (kill in 4.0)

Deprecation runway preserves consumer code. Triggers `trigger_deprecation('maturix/propel', '3.0', ...)` with `@deprecated` PHPDoc:

- `DebugPDO`, `PropelPDO` — empty subclasses kept as alias to `ConnectionWrapper`.
- `ConnectionManagerMasterSlave` — `trigger_deprecation` added to the existing `@deprecated`.
- Config keys `slaves:` / `master:` — forward to `replicas:` / `primary:` with deprecation notice.
- `PropelTypes::BU_DATE` / `BU_TIMESTAMP` / `BOOLEAN_EMU` / `OBJECT` / `PHP_ARRAY` — deprecation triggers on resolution; XSD additivity preserved (4 enumeration values restored after Round 2 caught the inconsistency).
- Schema `<behavior name="validate">` / `query_cache` — schema parser throws clear migration-guide-pointer error.

### Test cleanup

Two large mechanical sweeps via Rector + sed:
- 679 PHPDoc metadata deprecations → PHP attributes (`@dataProvider` → `#[DataProvider]`, etc.) — 177 test files.
- 189 mock-API deprecations → `willReturn`-family methods (`->will($this->returnValue(X))` → `->willReturn(X)`; `getMockForTrait` → `getMockBuilder` pattern) — 7 test class rewrites.

After both sweeps + a few static-state fixes (`StandardServiceContainerTest`, `DatabaseMapTest`), the suite passes with **zero PHPUnit deprecations under all 7 strict failOn flags**.

### `#[\Override]` mechanical sweep

~92+ method override sites across `src/` got the `#[\Override]` attribute via Rector + manual cleanup. Plus 16 missing-docblock cs-check violations on Iterator/DataFetcher methods that surfaced after the attribute landed.

## Documentation deliverables

- `docs/MIGRATION-FROM-PRE-AI.md` — for users coming from any pre-rewrite Propel branch. Covers every removal/deprecation with concrete migration code.
- `docs/UPGRADE-3.0.md` — Propel 2.x → 3.0 upgrade guide. Required env changes; new dependencies; Tier 1 frozen surface; Tier 2 deprecation runway; capability additions.
- `docs/BACKWARD_COMPATIBILITY.md` — durable Tier 1/2/3 contract reference. Independent of the umbrella spec which is strategic.
- `CHANGELOG.md` — Keep-a-Changelog format with full Phase A entries: Added / Changed / Deprecated / Removed / Fixed / Security.
- `README.md` — compatibility matrix added (Propel × PHP × MySQL × MariaDB × PostgreSQL × Symfony).
- `docs/PHASE-A-SUMMARY.md` (this file).
- `docs/reviews/` — Round 1 reports (3 reviewer + summary + waivers + iteration log) + Round 2 reports (summary + bench + waivers W6 + iteration log) + perf-baseline.json + infection.json.

## Review process

Phase A used the umbrella spec §4.13-§4.15 review-and-test protocol:

- **Round 1** (mid-phase, after 13/44 tasks): 3 reviewers (architecture, BC realism, tooling/CI specialist) ran in parallel. Surfaced 8 MUST-FIX + 5 SHOULD-FIX. Cycle 1 closed 8/8 MUST-FIX + 1/5 SHOULD-FIX; 4/5 SHOULD-FIX waived with documented reasoning + named future-phase confirmation (W1–W5).
- **Round 2** (end-phase, after 42/44 tasks): consolidated single-pass adversarial review across all 6 lenses. Surfaced 4 MUST-FIX + 3 SHOULD-FIX. Cycle 1 closed all 4 MUST-FIX + DoD #11 retrospective + SHOULD-FIX-B1 (1 added waiver: W6 — generated AR enforced via golden-diff not signature-diff).

Total iteration budget consumed: 1 of 3 cycles per round. Remaining budget reserved.

## What Phase A explicitly did NOT do (correctly scoped out)

- Did not change generated AR signatures (Phase B handles `: self` return-type and typed-property work).
- Did not refactor builder-architecture (Phase B' introduces `CodeEmitter` + template approach).
- Did not collapse the triple-wrapper Connection chain (Phase E).
- Did not split `Criteria` (Phase F).
- Did not adopt PHP 8.4 lazy objects / asymmetric visibility (Phase G).
- Did not add observability hooks (Phase I) or worker-mode lifecycle (Phase J).

These are first-class topics in their own phase plans, drafted just-in-time per umbrella §3 and §5.

## Surprises and signals for future phases

1. **Eris is PHP-7-only.** Switched to `innmind/black-box ^6.0` (also a different runner model). Phase F's `replaceNames` PBT plan needs to be re-checked when drafted; black-box's RNG seeding is a Phase F concern (W2).
2. **PHPUnit doc-comment-metadata sweep was 679 deprecations**, not the ~200 estimated in the original Phase A plan. Took two subagent runs to clear cleanly.
3. **Static-state pollution in `StandardServiceContainerTest` + `DatabaseMapTest`** broke Infection's randomized ordering. Surgical setUp/tearDown fixes for Phase A; the architectural cleanup (eliminating the `ConnectionFactory::$useProfilerConnection` static via a service-container decorator) lands in Phase E.
4. **Baseline drawdown forced source-level fixes**, not workaround baselining. The user's "no batch stuff, no ignoring 2000 errors" directive turned A.32–A.33 from a config tweak into a 9-commit substantive cleanup. Net: ~155 phpstan errors and ~700 psalm errors fixed at source.
5. **Versionable test pollution** (Round 1 W5) is real and deeper than Phase A scope — properly tracked for Phase D's behavior-modifier refactor.
6. **`composer testsuite` should include Infection** (Round 2 SHOULD-FIX-Q1) — deferred until Phase E sets a real MSI threshold.

## What stayed clean throughout

- BC tier discipline: zero Tier 1 surface drift across the whole phase.
- Test suite green per commit (except where flagged in iteration logs).
- Deprecation telemetry wiring: every `trigger_deprecation` call uses the correct package name (`maturix/propel`).
- One-purpose commits: 80 commits on `ar-rewrite` ahead of `master`, each with a clear scope and full commit-message rationale.

## Next: Phase B

Companion plan: `docs/plans/2026-02-03-builder-om-modernization.md` (37 tasks, generator-output modernization).

Phase B amendments required before execution:
- Task 2.6 (`: static` return type on setters) → use `: self` per umbrella §3.5 (Round 1 BC reviewer fix).
- Each task must regenerate the golden bookstore tree and commit the diff.
- Lint-parity gate must pass on the regenerated tree per task.
- Signature-diff gate covers handwritten classes only; golden-diff is the contract for generated code (W6).

Phase B is HIGH-RISK per umbrella §4.13.3 — 3-round review cadence, all 5 standing reviewers + Code Generator specialist.
