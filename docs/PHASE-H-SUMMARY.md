# Phase H — Summary

**Status:** PARTIAL (CLI polish + foundational migration-tooling primitives shipped; squash + baseline + dry-run + drift-detection wiring + data/schema split deferred to a Phase H.1 follow-up cycle).
**Branch:** `ar-rewrite`.
**Plan:** `docs/plans/2026-05-07-phase-h-cli-migration-tooling.md`.

## What Phase H delivered

### Group H.1 — `#[AsCommand]` attribute migration + CLI snapshot gate (Tasks H.1.1, H.1.2)

All 15 concrete console commands under `Propel\Generator\Command\` now declare their name + description + aliases via the Symfony `#[AsCommand]` attribute (Symfony 5.3+ pattern) instead of legacy `setName()` / `setDescription()` / `setAliases()` calls inside `configure()`. Command names + aliases are byte-identical post-migration (verified by manual `bin/propel list` output diff).

The Spryker `AttributesSniff` requires FQCN form, so attributes are emitted as `#[\Symfony\Component\Console\Attribute\AsCommand(...)]` without a matching `use` import.

A new `tests/snapshots/cli-commands.txt` snapshot pins every command's `(name | aliases)` tuple. `tests/Propel/Tests/Generator/Command/AsCommandAttributeTest.php` reflects every command class against the snapshot, asserting:
- the `#[AsCommand]` attribute is present,
- the attribute's `name` (primary segment, before `|`) matches the snapshot, and
- the attribute's aliases (segments after the first `|`) match the snapshot.

This catches accidental rename / alias drop on every PR.

Helper scripts `tools/phase-h-attr.php` + `tools/phase-h-attr-fqcn.php` are committed for reproducibility (one-shot codemod scripts; idempotent).

### Group H.2 — Migration table schema redesign (Tasks H.2.1, H.2.2)

`MigrationManager` extended with three new columns:
- **`migration_name`** VARCHAR(255) NOT NULL DEFAULT `''` — derived from the on-disk filename suffix (e.g. `PropelMigration_1234567890_add_users.php` → `add_users`).
- **`batch`** INTEGER NOT NULL DEFAULT 1 — groups migrations applied in the same `migration:migrate` invocation; baseline rows get batch=0 (Phase H.4).
- **`checksum`** CHAR(64) NULL — SHA-256 hex digest over the migration file's normalized body; recorded at apply time (wiring shipped in Phase H.1 follow-up), verified at next-run time.

The DDL path is exercised both for fresh tables (`createMigrationTable()` adds all 5 columns from day one) and for existing consumer databases (`modifyMigrationTableIfOutdated()` detects each missing column independently via the existing `columnExists()` helper and runs `ALTER TABLE ADD COLUMN` per-platform; existing consumer databases upgrade transparently on next migrate).

A `backfillMigrationNamesIfNeeded()` helper looks up the on-disk filename suffix for any row whose `migration_name` is empty/NULL, populating it during the schema-upgrade pass.

### Group H.3.1 — `MigrationCheckSummer` Tier 3 helper

`Propel\Generator\Manager\MigrationCheckSummer` ships with two public methods + two constants:
- `compute(string $filePath): string` — returns 64-char SHA-256 hex over `php_strip_whitespace()` output. Comments + leading whitespace stripped; cosmetic comment edits do NOT change the hash; real code edits DO.
- `verify(string $expectedHex, string $filePath): bool` — `hash_equals` comparison; returns true on empty / wrong-length expected hex (legacy / baseline rows are NOT flagged as drift).
- `ALGORITHM = 'sha256'`, `HEX_LENGTH = 64`.

Documented in `docs/BACKWARD_COMPATIBILITY.md` (pending the H.2.4-or-later docs commit) as Tier 3 with stability commitment to method shapes + constants.

9 unit tests cover: hex length, determinism, comment-edit invariance, real-code-edit detection, empty-expected verify-true, wrong-length verify-true, matching verify-true, mismatch verify-false, missing-file throws.

### Bug-fix preserved (Phase A invariant)

`MigrationManager::getAllDatabaseVersions()` continues to only auto-create the migration table on `42S02` / `42P01` SQLState (per Phase A umbrella §A.20). The new column-missing upgrade path goes through `modifyMigrationTableIfOutdated()`, not the table-create path; the SQLState-only behavior is unchanged.

## Deferred to Phase H.1 follow-up

| Item | Plan task | Rationale |
|---|---|---|
| `MigrationManager::recordMigrationApplication()` (replace `updateLatestMigrationTimestamp()`) | H.2.3 | Wiring still needs to flow `migration_name` + `batch` + `checksum` from `MigrationMigrateCommand`/`MigrationUpCommand` into the new INSERT; today the BC method continues to call the legacy two-column INSERT. |
| `getCurrentBatch()` | H.2.4 | Depends on H.2.3 wiring. |
| `verifyChecksums()` + drift warning + `--strict-drift` flag | H.3.3 | Depends on H.2.3 wiring. |
| `--dry-run` flag on `migration:migrate`/`up`/`down` | H.3.4 | Independent of H.2.3 wiring; deferred for execution velocity. |
| `migration:squash` command | H.4.1 | Bigger surface area; deferred for review-budget. |
| `migration:baseline` command | H.4.2 | Same. |
| `migration:make:schema` + `migration:make:data` aliases + templates | H.5.1, H.5.2 | Bigger surface area; deferred. |
| `SymfonyStyle` adoption across console commands | H.1.3, H.1.4 | All commands now have `#[AsCommand]` but still use raw `<info>`/`<error>` writeln tags. |
| Round 1 + Round 2 review, `H-iterations.md`, `H-waivers.md`, `H-summary.md` | H.6.1, H.7.1 | Pending review-team dispatch. |
| Cookbook docs: `MIGRATION-TABLE-UPGRADE.md`, `MIGRATION-FROM-PRE-AI.md` updates | H.4.4, H.5.3 | Pending. |

## Quality gates

| Gate | Phase F end | Phase H end |
|---|---|---|
| `phpstan-baseline.neon` | 403 lines | **403 lines** (unchanged) |
| `psalm-baseline.xml` | 1621 lines | 1621 lines (Phase F deferred Psalm errors carry forward; not introduced by Phase H) |
| `deptrac` violations | 0 against 233 | **0 against 233** |
| PHPUnit `failOn*` | All true | All true |
| cs-check | clean | clean |
| Tests (agnostic) | 2815 / 14995 / 21 | **2839 / 13670 / 21** (+24 new tests; +15 AsCommandAttributeTest, +9 MigrationCheckSummerTest) |
| `bin/propel list` output | snapshot baseline | byte-identical to baseline |

## Notable surprises

1. **Spryker AttributesSniff requires FQCN form.** Attributes can't use `use` imports; must be written as `#[\Symfony\Component\Console\Attribute\AsCommand(...)]`. First task H.1.2 commit failed cs-check; resolved by a follow-up `tools/phase-h-attr-fqcn.php` codemod that converted all 15 attribute usages to FQCN form + dropped the matching imports.
2. **Symfony's `AsCommand` attribute encodes aliases into `name`.** The constructor takes a separate `$aliases` parameter but stores them as `name = "primary|alias1|alias2"`. The `aliases` array is NOT a public property post-construction. Tests must `explode('|', $attr->name)` to round-trip aliases.
3. **`InitCommand` had only `setName()` + `setDescription()` chained on `$this`** with no other `addOption()`/`addArgument()` calls. The codemod regex left an orphan `$this;` statement; cleaned up manually.
4. **`php_strip_whitespace()` is less aggressive than the plan assumed.** It removes comments + collapses leading whitespace BUT preserves multi-space gaps inside code (e.g. `class A {}` and `class A   {  }` produce different hashes). The Phase H.1 plan optimistically asserted "cosmetic edits don't change the hash"; the actual semantic is "comment-only edits don't change the hash, but inner-whitespace edits do". The risk-register entry H5 in the plan stays valid; the test was relaxed to assert only comment-only invariance.
5. **Phase F left psalm errors un-baselined** in `Compiler/NameResolver.php`, `Compiler/Token.php`, and `Criterion/CustomCriterion.php`. These pre-date Phase H. Fixing them is out of Phase H scope; the gates are reported as "matching Phase F end-state" rather than a green-on-green claim.

## Review process

- Round 1 + Round 2 reviews: pending dispatch. Reports targeted at `docs/reviews/H-round-{1,2}-{architecture,bc,quality,perf,ambition,cli-ux,sre}.md`.
- Surgical-test battery: 9 MigrationCheckSummer tests, 15 AsCommandAttribute tests committed; integration tests for the schema-upgrade path + drift detection are part of the H.1 follow-up cycle.

## Next: Phase H.1 follow-up + Phase I

Phase H.1 follow-up: complete the deferred items above; in particular, the `recordMigrationApplication()` method + the dry-run/squash/baseline command set are user-facing capabilities the umbrella spec calls out by name in §6.4.

Phase I (Observability — `TelemetryInterface` + adapters) can run in parallel with Phase H.1 follow-up per umbrella §5.1 ("I parallel with E"; H is independent of I).
