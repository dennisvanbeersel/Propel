# Phase H: CLI Polish + Migration Tooling Overhaul

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:executing-plans` (recommended for the H.1 attribute sweep + H.2 schema upgrade) or `superpowers:subagent-driven-development` (recommended for H.3/H.4/H.5 capability adds — each is independently testable). Steps use checkbox (`- [ ]`) syntax for tracking.

**Date:** 2026-05-07
**Branch:** `ar-rewrite` (commit; do NOT push)
**Goal:** Implement umbrella §5 phase-H row, §6.4 capability additions ("migration tooling: dry-run, squash, baseline, drift detection; migration table fields: migration_name, batch, checksum"), and §7.1 risk #5 (migration safety) in full. Modernize the 17 Symfony console commands to the `#[AsCommand]` attribute pattern + `SymfonyStyle` output. Add a `migration_name` + `batch` + `checksum` columns to the migration table (with on-first-run upgrade-in-place against existing consumer databases). Add `--dry-run` to `migration:migrate`. Add runtime checksum drift detection. Add `migration:squash` (combine N old migrations into one) and `migration:baseline` (declare a known-good schema state without re-running history). Split data vs structural migration templates (`migration:make:data` and `migration:make:schema` Laravel-style aliases).

**Architecture:** Three concentric pieces.

1. **`#[AsCommand]` attribute migration (H.1).** Symfony 5.3+ encourages `#[AsCommand(name: 'foo:bar', description: '...', aliases: ['foo'])]` on the command class. Our 17 commands today use the legacy `setName()` + `setDescription()` + `setAliases()` calls inside `configure()`. Phase H moves that metadata to the attribute, leaves `configure()` for option/argument declarations only. `SymfonyStyle` replaces raw `<info>` / `<error>` / `<comment>` writeln tags — uses the higher-level `success()` / `warning()` / `error()` / `note()` / `table()` / `progressBar()` API. No public CLI surface change: command names, aliases, options, arguments stay byte-identical.

2. **MigrationManager schema redesign (H.2 + H.3).** The migration table today has two columns: `version` (int) + `execution_datetime` (datetime). Phase H adds three:
   - `migration_name` VARCHAR(255) — derived from the migration filename suffix (e.g. `PropelMigration_1234567890_add_users.php` → `add_users`); enables human-readable status reports.
   - `batch` INTEGER — groups migrations applied in the same `migration:migrate` invocation; enables batch-aware rollback (`migrate:rollback --batch=N`); Laravel pattern.
   - `checksum` CHAR(64) — SHA-256 hex digest over the migration file's full PHP body normalized (whitespace-collapsed, comment-stripped); recorded at apply time, verified at next-run time. **Hash algorithm decision (closes umbrella §10 open question for Phase H):** SHA-256 over `php_strip_whitespace($migrationFilePath)` output. PHP_strip_whitespace removes comments + unifies whitespace; the file-level hash catches all hand-edits short of cosmetic-comment-only changes. Algorithm name + format pinned in `MigrationCheckSummer::ALGORITHM` const so a future bump (BLAKE3, etc.) can be swapped without breaking the column shape (CHAR(64) covers SHA-256 hex; if future algorithm needs more, that's a column-widen migration on the migration table itself). The on-disk migration files are immutable-by-discipline; consumers who hand-edit get a runtime warning at next migrate.

   **Backward-compatibility for existing consumer databases:** `MigrationManager::modifyMigrationTableIfOutdated()` already exists for `execution_datetime` (Phase A precedent). Phase H extends it: detects missing `migration_name`/`batch`/`checksum` columns; backfills via `ALTER TABLE ADD COLUMN` per platform; backfills `migration_name` from existing `version` column joined against on-disk migration filenames; sets `batch = 1` for all pre-existing rows; leaves `checksum` NULL initially (verified-on-next-apply, not retroactively). Existing rows + execution-datetime ordering preserved exactly.

3. **Capability additions (H.3 + H.4 + H.5).**
   - `--dry-run` flag on `migration:migrate` (and `migration:up`, `migration:down`): collects the SQL the migration WOULD execute via `getUpSQL()` / `getDownSQL()`, prints it, does NOT run `$conn->exec()`, does NOT insert into the migration table. Output goes through SymfonyStyle's `block()` + `text()` so it's grep-friendly. Useful for code review + production change-window prep.
   - `migration:squash <fromTimestamp> <toTimestamp>`: combines the up-SQL of all migrations in `[fromTimestamp..toTimestamp]` into a single squashed `PropelMigration_<toTimestamp>_squashed.php`; archives the source files into a `squashed/` subdirectory; updates the in-database migration table by replacing the squashed range with a single row whose checksum matches the new squashed file. Squashing requires that the range be fully-applied on every connected datasource (we refuse to squash partially-applied ranges to avoid inconsistent state).
   - `migration:baseline <timestamp>`: declares the migration table to contain every timestamp ≤ `<timestamp>`, without running them. Used when adopting Propel migrations on an existing database. Emits a clear warning that this is a one-shot operation; idempotent (re-running with the same timestamp is a no-op and reports "already at baseline N").
   - **Runtime checksum drift detection** (the H.3 cornerstone): on every `migration:migrate` and `migration:status`, MigrationManager loads the recorded checksums from the table and compares against the on-disk file's current SHA-256. Any mismatch emits a `<comment>` warning per migration ("checksum drift on PropelMigration_X — file changed since apply") and (with `--strict-drift`) returns non-zero exit. Default behavior is warn-don't-fail to preserve workflow; the `--strict-drift` flag lets CI/CD enforce. Verified-on-status: `migration:status` reports a `(drift)` tag next to drifted rows.
   - **Data vs structural migration split (H.5):** `migration:create` already exists; H.5 adds two convenience aliases:
     - `migration:make:schema` — alias to `migration:create --kind=schema`. Generated stub instructs "DDL/DML migrations only; reversible via getDownSQL()".
     - `migration:make:data` — alias to `migration:create --kind=data`. Generated stub omits the DDL helper sections; provides a `preUp()`/`postUp()` PHP-only template for data-only operations (no SQL); designed for reference-data seeding + normalization.
     The distinction is documented; the runtime treats both kinds identically.

**Tech Stack:** PHP 8.3, MySQL 8.0+, MariaDB 10.5+, PostgreSQL 14+, SQLite (frozen), PHPUnit 11, PHPStan level 7, Spryker code-sniffer, Symfony Console 7.2+ (which drives the `#[AsCommand]` attribute support), Deptrac ^2 (architecture-test enforcement).

**Reference spec:** `docs/plans/2026-05-06-modernization-umbrella-spec.md` (umbrella §5 Phase H row, §6.4 capability additions, §7.1 risk #5 migration safety, §10 open question on checksum algorithm).
**Predecessors:** Phases A–F (`docs/plans/2026-05-06-phase-a-foundations.md` through `docs/plans/2026-05-07-phase-f-criteria-split.md`). Specifically:
- Phase A established the `MigrationManager::getAllDatabaseVersions` SQLState-only auto-create-on-PDOException fix (umbrella §A.20). Phase H must NOT regress this — the table-schema upgrade-on-first-run code path catches a separate failure mode (column-missing) and goes through `modifyMigrationTableIfOutdated()`, not the table-create path.
- Phase A also added `MigrationManager::modifyMigrationTableIfOutdated()` for `execution_datetime`; H.2 follows the same pattern for the three new columns.

**Phase summaries:** `docs/PHASE-A-SUMMARY.md`, `docs/PHASE-B-SUMMARY.md`, `docs/PHASE-C-SUMMARY.md`, `docs/PHASE-D-SUMMARY.md`, `docs/PHASE-E-SUMMARY.md`, `docs/PHASE-F-SUMMARY.md`.

**Review tier (per umbrella §4.13.3):** **MEDIUM-RISK 2-round cadence** (per umbrella §5 phase-H row + the umbrella's classification "MEDIUM" in the phase table; H is grouped LOW-RISK in §4.13.3 but the user directive bumps cadence to MEDIUM 2-round given the BC implications of the migration-table change). Round 1 mid-phase after H.3 (attribute sweep + table-schema redesign + dry-run shipped). Round 2 end-phase pre-merge after H.5 (squash + baseline + data/schema split landed).

**Specialists (per umbrella §4.13.2):** standing 5 reviewers (Architecture, BC realism, Quality/rigor, Performance, Ambition) + **Database operations / SRE reviewer** (migration table schema upgrade safety, drift detection semantics, dry-run preview correctness, squash partial-state handling, baseline-after-existing-rows safety) + **CLI-UX reviewer** (`#[AsCommand]` attribute correctness on every command, SymfonyStyle output legibility, command-name + alias preservation, `--dry-run` output format usability, `--help` text quality post-attribute-move).

**Test/quality gate state at start of phase (carried from Phase F end per `docs/PHASE-F-SUMMARY.md`):**
- `phpstan-baseline.neon` 403 lines, 0 blanket regex.
- `psalm-baseline.xml` 1621 lines, suppression-clean.
- Deptrac 0 violations against 233-line baseline.
- All `failOn*` flags strict.
- Test suite: **2815 / 14995 / 21 GREEN** (counts confirmed by re-run at Phase H plan time).
- cs-check clean.
- Tier 1 signature snapshots stable.
- 17 commands all use legacy `setName()` (no `#[AsCommand]` attributes on any).
- 26 raw `<info>` / `<error>` / `<comment>` style tags across `Generator/Command/*.php`.
- `MigrationManager` 784 LOC, two-column migration table schema (`version` + `execution_datetime`), no checksum / batch / migration_name columns.

---

## File Structure (created or modified by this phase)

**Created:**

- `src/Propel/Generator/Manager/MigrationCheckSummer.php` — Tier 3 helper. Public methods: `compute(string $migrationFilePath): string` (returns SHA-256 hex of normalized file), `verify(string $expectedHex, string $migrationFilePath): bool`. Const: `ALGORITHM = 'sha256'`, `HEX_LENGTH = 64`.
- `src/Propel/Generator/Command/MigrationSquashCommand.php` — Tier 3 (CLI-only). Combines a range of migrations into one squashed file + reconciles the migration table. `#[AsCommand(name: 'migration:squash')]`. Required args: `from-version`, `to-version`. Options: `output-dir`, `migration-table`, `connection`, `dry-run`.
- `src/Propel/Generator/Command/MigrationBaselineCommand.php` — Tier 3 (CLI-only). Declares all migrations ≤ given timestamp as already-applied without running them. `#[AsCommand(name: 'migration:baseline')]`. Required arg: `version`. Options: `output-dir`, `migration-table`, `connection`, `dry-run`.
- `src/Propel/Generator/Command/MigrationMakeSchemaCommand.php` — Tier 3 (CLI-only). Schema-DDL migration template. `#[AsCommand(name: 'migration:make:schema')]`. Wraps existing `migration:create` with `kind=schema` template selection.
- `src/Propel/Generator/Command/MigrationMakeDataCommand.php` — Tier 3 (CLI-only). Data-only migration template. `#[AsCommand(name: 'migration:make:data')]`. Wraps existing `migration:create` with `kind=data` template selection (PHP-only `preUp()`/`postUp()` body).
- `src/Propel/Generator/Manager/templates/migration_template_schema.php` — schema migration stub (DDL-focused; sections for getUpSQL / getDownSQL).
- `src/Propel/Generator/Manager/templates/migration_template_data.php` — data migration stub (preUp / postUp PHP body for seeding/normalizing).
- `tests/Propel/Tests/Generator/Manager/MigrationCheckSummerTest.php` — unit tests: (a) compute is deterministic across calls, (b) compute is whitespace-stable (cosmetic-only edits don't change hex), (c) compute differs after a real code change, (d) verify returns false on hex/file mismatch, (e) hex length is exactly 64 characters.
- `tests/Propel/Tests/Generator/Manager/MigrationSchemaUpgradeTest.php` — integration test using SQLite in-memory: creates the legacy two-column migration table; runs `MigrationManager::modifyMigrationTableIfOutdated()`; asserts the three new columns exist; asserts pre-existing rows backfill correctly (`migration_name` populated from on-disk filenames, `batch=1`, `checksum` NULL).
- `tests/Propel/Tests/Generator/Manager/MigrationDriftDetectionTest.php` — unit + integration test: simulates a recorded checksum, edits a migration file, asserts drift detection fires.
- `tests/Propel/Tests/Generator/Command/MigrationSquashCommandTest.php` — integration test using the `bookstore-migration` fixture: creates 3 migrations, applies them, runs squash, asserts the squashed file contains all 3 ups, asserts the migration table has only 1 row at the squash timestamp.
- `tests/Propel/Tests/Generator/Command/MigrationBaselineCommandTest.php` — integration test: declares baseline at a given timestamp, asserts all earlier timestamps are recorded as applied, asserts later ones still pending.
- `tests/Propel/Tests/Generator/Command/MigrationMakeSchemaCommandTest.php` — integration test: invokes the command, asserts a schema-template file is generated.
- `tests/Propel/Tests/Generator/Command/MigrationMakeDataCommandTest.php` — integration test: invokes the command, asserts a data-template file is generated.
- `tests/Propel/Tests/Generator/Command/AsCommandAttributeTest.php` — unit test asserting every command class in `Propel\Generator\Command\` has an `#[AsCommand]` attribute and the attribute's `name` matches the previously-used `setName()` value (snapshotted in `tests/snapshots/cli-commands.txt`).
- `tests/snapshots/cli-commands.txt` — alphabetized list of all CLI command names + their aliases. Used by AsCommandAttributeTest to enforce no-rename-by-accident.
- `docs/reviews/H-round-1-summary.md` — mid-phase review consolidation.
- `docs/reviews/H-round-2-summary.md` — end-phase review consolidation.
- `docs/reviews/H-round-1-architecture.md`, `H-round-1-bc.md`, `H-round-1-cli-ux.md`, `H-round-1-sre.md` — Round-1 specialist reports.
- `docs/reviews/H-round-2-architecture.md`, `H-round-2-bc.md`, `H-round-2-quality.md`, `H-round-2-perf.md`, `H-round-2-ambition.md`, `H-round-2-cli-ux.md`, `H-round-2-sre.md` — Round-2 standing-5 + 2 specialists.
- `docs/reviews/H-iterations.md` — iteration cycle log.
- `docs/reviews/H-waivers.md` — SHOULD-FIX waivers (if any).
- `docs/reviews/H-summary.md` — phase summary used to feed PHASE-H-SUMMARY.md.
- `docs/PHASE-H-SUMMARY.md` — phase summary mirror (written at phase exit).
- `docs/MIGRATION-TABLE-UPGRADE.md` — short cookbook documenting the on-first-run migration-table schema upgrade path for existing consumer projects.

**Modified:**

- `src/Propel/Generator/Manager/MigrationManager.php` — extended:
  - `COL_MIGRATION_NAME`, `COL_BATCH`, `COL_CHECKSUM` constants added.
  - `createMigrationTable()` adds the three new columns to the new-table DDL.
  - `modifyMigrationTableIfOutdated()` adds the three new columns to existing tables; backfills `migration_name` from on-disk filenames; sets `batch=1` on legacy rows; leaves `checksum` NULL.
  - `updateLatestMigrationTimestamp()` is renamed to `recordMigrationApplication(int $timestamp, string $migrationName, int $batch, ?string $checksum)` while keeping the original method as a thin BC forwarder that calls the new method with backfilled defaults.
  - New: `verifyChecksums(): array<int, string>` returns timestamps with checksum drift; called by `migration:migrate` (warn) and `migration:status` (report).
  - New: `getCurrentBatch(): int` returns max(batch) + 1 for the next migrate run.
  - New: `recordSquash(int $fromTimestamp, int $toTimestamp, string $newChecksum): void` collapses migration-table rows in the range to one row.
  - New: `recordBaseline(array $timestamps): void` inserts faked rows for baseline migrations (`checksum=NULL`, `batch=0` to mark "baseline").
  - The Phase A `getAllDatabaseVersions()` SQLState-only auto-create logic is **not** changed; the new column-missing path goes through `modifyMigrationTableIfOutdated()` instead.
- `src/Propel/Generator/Command/AbstractCommand.php` — adds `protected function io(InputInterface $input, OutputInterface $output): SymfonyStyle` helper (memoized per call); subclasses use `$this->io($input, $output)->success(...)` etc.
- `src/Propel/Generator/Command/ConfigConvertCommand.php` — gains `#[AsCommand(name: 'config:convert', description: '...', aliases: ['convert-conf'])]`; `setName()` / `setDescription()` / `setAliases()` removed from `configure()`; SymfonyStyle output used.
- `src/Propel/Generator/Command/DataDictionaryExportCommand.php` — same treatment.
- `src/Propel/Generator/Command/DatabaseReverseCommand.php` — same treatment.
- `src/Propel/Generator/Command/GraphvizGenerateCommand.php` — same treatment.
- `src/Propel/Generator/Command/InitCommand.php` — same treatment.
- `src/Propel/Generator/Command/MigrationCreateCommand.php` — same treatment + adds `--kind=schema|data` option (default `schema`, drives template selection).
- `src/Propel/Generator/Command/MigrationDiffCommand.php` — same treatment.
- `src/Propel/Generator/Command/MigrationDownCommand.php` — same treatment + `--dry-run` flag.
- `src/Propel/Generator/Command/MigrationMigrateCommand.php` — same treatment + `--dry-run` flag + `--strict-drift` flag + records `migration_name` + `batch` + `checksum` per migration.
- `src/Propel/Generator/Command/MigrationStatusCommand.php` — same treatment + reports `(drift)` tag next to each drifted timestamp.
- `src/Propel/Generator/Command/MigrationUpCommand.php` — same treatment + `--dry-run` flag.
- `src/Propel/Generator/Command/ModelBuildCommand.php` — same treatment.
- `src/Propel/Generator/Command/SqlBuildCommand.php` — same treatment.
- `src/Propel/Generator/Command/SqlInsertCommand.php` — same treatment.
- `src/Propel/Generator/Command/TestPrepareCommand.php` — same treatment.
- `src/Propel/Generator/Command/Executor/RollbackExecutor.php` — switches its raw `<info>` / `<error>` writelns to SymfonyStyle.
- `src/Propel/Generator/Manager/templates/migration_template.php` — kept as the default fallback template; H.5 adds two siblings.
- `bin/propel` — registers the four new commands (`migration:squash`, `migration:baseline`, `migration:make:schema`, `migration:make:data`) on the application.
- `tests/snapshots/tracked-classes.txt` — adds `Propel\Generator\Manager\MigrationCheckSummer` (Tier 3 with stability commitment to `compute()` / `verify()` / `ALGORITHM` const), four new command classes (Tier 3).
- `docs/MIGRATION-FROM-PRE-AI.md` — three new sections: "Migration table schema upgrade (3.0)", "Checksum drift detection (3.0)", "Migration squashing + baselines (3.0)".
- `docs/UPGRADE-3.0.md` — capability summary updated.
- `docs/BACKWARD_COMPATIBILITY.md` — Tier 3 with stability commitment: `MigrationCheckSummer`. Tier 2 additive: extended `MigrationManager` public methods (`recordMigrationApplication`, `verifyChecksums`, `getCurrentBatch`, `recordSquash`, `recordBaseline`).
- `CHANGELOG.md` — Phase H entries under `[Unreleased]`.

**Deleted:**

- None in Phase H. Per umbrella §3.6 alias-don't-delete and §3.2 deprecation-runway disciplines, `setName()` etc. patterns and the legacy `updateLatestMigrationTimestamp()` keep working as BC forwarders. The two-column migration table schema is upgraded in-place; no data is destroyed.

---

## Group H.1: `#[AsCommand]` attribute migration + SymfonyStyle (Tasks H.1.1–H.1.4)

**Verification after each task:** `composer test:agnostic` GREEN. `composer cs-check` clean. Snapshot in `tests/snapshots/cli-commands.txt` stable (no command name / alias renames). PHPStan + Psalm baselines unchanged. Deptrac unchanged.

Group H.1 is the lowest-risk group of Phase H. It validates the new conventions across all 17 commands without touching any business logic. After H.1, every command has `#[AsCommand]`; no command uses raw `<info>`/`<error>` writeln tags.

### Task H.1.1: Snapshot CLI command names + aliases (gate against accidental rename)

**Files:**
- Create: `tests/snapshots/cli-commands.txt` — alphabetized list, format `name|alias1,alias2`.
- Create: `tests/Propel/Tests/Generator/Command/AsCommandAttributeTest.php` — passes today against `setName()`-defined names; will continue passing after H.1.2.

- [ ] **Step 1: Capture today's command name + alias list.** Run `bin/propel list --raw | grep '^[a-z]'` and pipe through `sort -u` and a small awk to extract just `name|aliases`. Commit as `tests/snapshots/cli-commands.txt`. The 16 currently-callable commands (excluding `AbstractCommand`) appear; format example:
  ```
  config:convert|convert-conf
  database:reverse|reverse
  datadictionary:export|datadictionary,md
  graphviz:generate|graphviz
  init|
  migration:create|
  migration:diff|diff
  migration:down|down
  migration:migrate|migrate
  migration:status|status
  migration:up|up
  model:build|build
  sql:build|build-sql
  sql:insert|insert-sql
  test:prepare|
  ```
  (The list is concrete, not invented.)
- [ ] **Step 2: Write `AsCommandAttributeTest`.** For each PHP file in `src/Propel/Generator/Command/` (excluding `AbstractCommand.php`, `Helper/`, `Executor/`):
  - Reflect the class.
  - Read the `#[AsCommand]` attribute via `ReflectionClass::getAttributes(AsCommand::class)`.
  - Assert exactly one attribute exists.
  - Assert `$attribute->newInstance()->name` equals the snapshot's `name` for that class.
  - Assert `$attribute->newInstance()->aliases` (as comma-joined string) equals the snapshot's `aliases` for that class.
  At task H.1.1 commit time the test ASSERTS-FALSE — it's a placeholder. H.1.2 makes it green.
- [ ] **Step 3: Add a CI snapshot check in `composer.json` `scripts` block: `tools/snapshot-cli-commands.php` re-runs `bin/propel list` against the codebase and diffs against the snapshot. Fails CI on diff. Out-of-scope: writing `tools/snapshot-cli-commands.php` itself (deferred to Phase A's signature-diff infra extension; for H.1.1 we just commit the .txt file and the test).
- [ ] **Step 4: Run `composer test:agnostic`** — confirm new test fails (the test exists; the attribute doesn't yet). Commit with message: `feat(cli): snapshot CLI command names ahead of #[AsCommand] sweep`.

### Task H.1.2: Add `#[AsCommand]` attribute to every command

**Files:**
- Modify: 16 command files in `src/Propel/Generator/Command/*Command.php`.
- Modify: `tests/Propel/Tests/Generator/Command/AsCommandAttributeTest.php` — flips from FALSE to GREEN.

- [ ] **Step 1: Add the attribute on each command class.** Concrete pattern:
  ```php
  use Symfony\Component\Console\Attribute\AsCommand;

  #[AsCommand(name: 'migration:migrate', description: 'Execute all pending migrations', aliases: ['migrate'])]
  class MigrationMigrateCommand extends AbstractCommand
  ```
- [ ] **Step 2: Remove the corresponding `->setName()` / `->setDescription()` / `->setAliases()` lines from `configure()`.** The `configure()` method retains `parent::configure();` plus the `addOption()` / `addArgument()` calls.
- [ ] **Step 3: Run `composer test:agnostic`** — `AsCommandAttributeTest` flips green. Other tests unaffected.
- [ ] **Step 4: Run `composer cs-check`** — clean. Run `composer stan` + `composer psalm` — baselines unchanged.
- [ ] **Step 5: Run `bin/propel list`** — manual verification that every command's name + aliases unchanged.
- [ ] **Step 6: Commit.** Message: `feat(cli): adopt #[AsCommand] attribute on all 16 commands`.

### Task H.1.3: Adopt SymfonyStyle for output

**Files:**
- Modify: `src/Propel/Generator/Command/AbstractCommand.php` — add `protected function io(InputInterface $input, OutputInterface $output): SymfonyStyle` memoized helper.
- Modify: every command's `execute()` method — replace raw `$output->writeln('<info>...</info>')` with `$this->io($input, $output)->success('...')`; replace `<error>` with `error()`; replace `<comment>` with `note()` or `warning()` as semantically appropriate.
- Modify: `src/Propel/Generator/Command/Executor/RollbackExecutor.php` — same treatment.

- [ ] **Step 1: Helper in AbstractCommand.**
  ```php
  private ?SymfonyStyle $io = null;

  protected function io(InputInterface $input, OutputInterface $output): SymfonyStyle
  {
      return $this->io ??= new SymfonyStyle($input, $output);
  }
  ```
- [ ] **Step 2: Sweep replace raw style tags.** The 26 raw-tag occurrences across `Generator/Command/*.php` map to:
  - `<info>X</info>` → `$io->note('X')` or `$io->success('X')` (per semantics).
  - `<error>X</error>` → `$io->error('X')`.
  - `<comment>X</comment>` → `$io->warning('X')` or `$io->note('X')` per semantics.
  - Plain `writeln('Y')` (without tags) stays as `$io->text('Y')` for consistency.
- [ ] **Step 3: For multi-line table output (`migration:status` lists migrations):** use `$io->table($headers, $rows)` instead of manual `writeln(sprintf(...))`. Headers: `Timestamp`, `Name`, `Batch`, `Status` (the latter populated post-H.2).
- [ ] **Step 4: Re-run `composer test:agnostic`.** Existing CommandTester-based tests continue to pass (SymfonyStyle wraps the OutputInterface; it doesn't replace it). Any test asserting exact string content (`<info>...</info>` markers in CommandTester output) needs adjustment — update the test to assert the human-readable form.
- [ ] **Step 5: Run `composer cs-check` + `composer stan` + `composer psalm`** — baselines unchanged.
- [ ] **Step 6: Commit.** Message: `feat(cli): SymfonyStyle output across console commands`.

### Task H.1.4: Drop `setName()` / `setDescription()` / `setAliases()` from configure() entirely

**Files:**
- Modify: every command's `configure()` method — assert no `setName()` / `setDescription()` / `setAliases()` calls remain.
- Add: a static-analysis rule (PHPStan custom rule or simple grep CI check) asserting `setName(` / `setDescription(` / `setAliases(` never appear in `Generator/Command/*Command.php`.

- [ ] **Step 1: Audit + delete dangling calls.** `grep -rn '->setName(\|->setDescription(\|->setAliases(' src/Propel/Generator/Command/` should match zero hits after H.1.2; H.1.4 confirms.
- [ ] **Step 2: Add a `tools/check-no-legacy-setname.sh` script (one-line grep + exit 1 on match).** Wire it into the `composer testsuite` script.
- [ ] **Step 3: Run `composer testsuite`.** All quality gates green.
- [ ] **Step 4: Commit.** Message: `chore(cli): enforce no-legacy-setName grep gate`.

---

## Group H.2: Migration table schema redesign (Tasks H.2.1–H.2.4)

**Verification after each task:** `composer test:agnostic` GREEN; `MigrationManagerTest` + `MigrationManagerTableNotFoundTest` continue to pass; new `MigrationSchemaUpgradeTest` GREEN; PHPStan + Psalm baselines unchanged.

Group H.2 is the highest-risk group: it changes a runtime-shaped table schema for existing consumer databases. The `modifyMigrationTableIfOutdated()` precedent from Phase A makes the upgrade path safe; H.2 follows that precedent verbatim.

### Task H.2.1: Add `migration_name` + `batch` + `checksum` column constants and DDL

**Files:**
- Modify: `src/Propel/Generator/Manager/MigrationManager.php`.

- [ ] **Step 1: Add three new const declarations:**
  ```php
  protected const COL_MIGRATION_NAME = 'migration_name';
  protected const COL_BATCH = 'batch';
  protected const COL_CHECKSUM = 'checksum';
  ```
- [ ] **Step 2: Add three new column-builder methods** parallel to `createVersionColumn()` and `createExecutionDatetimeColumn()`:
  ```php
  protected function createMigrationNameColumn(PlatformInterface $platform): Column
  {
      $column = new Column(static::COL_MIGRATION_NAME);
      $column->getDomain()->copy($platform->getDomainForType('VARCHAR'));
      $column->getDomain()->setSize(255);
      $column->setDefaultValue('');
      $column->setNotNull(true);
      return $column;
  }

  protected function createBatchColumn(PlatformInterface $platform): Column
  {
      $column = new Column(static::COL_BATCH);
      $column->getDomain()->copy($platform->getDomainForType('INTEGER'));
      $column->setDefaultValue('1');
      $column->setNotNull(true);
      return $column;
  }

  protected function createChecksumColumn(PlatformInterface $platform): Column
  {
      $column = new Column(static::COL_CHECKSUM);
      $column->getDomain()->copy($platform->getDomainForType('CHAR'));
      $column->getDomain()->setSize(64);
      // checksum is NULLABLE — legacy rows have no recorded checksum
      return $column;
  }
  ```
- [ ] **Step 3: Update `createMigrationTable()` to include the three new columns.**
- [ ] **Step 4: Run `composer test:agnostic`** — `MigrationManagerTest` continues to pass (SQLite test fixture creates a fresh table; the new columns appear automatically). PHPStan + Psalm baselines unchanged.
- [ ] **Step 5: Commit.** Message: `feat(migration): add migration_name + batch + checksum columns to migration table DDL`.

### Task H.2.2: Extend `modifyMigrationTableIfOutdated()` for the three new columns

**Files:**
- Modify: `src/Propel/Generator/Manager/MigrationManager.php` — extend the existing `modifyMigrationTableIfOutdated()` method.
- Create: `tests/Propel/Tests/Generator/Manager/MigrationSchemaUpgradeTest.php`.

- [ ] **Step 1: Extend `modifyMigrationTableIfOutdated()` to detect each missing column independently:**
  ```php
  public function modifyMigrationTableIfOutdated(string $datasource): void
  {
      $connection = $this->getAdapterConnection($datasource);
      $platform = $this->getPlatform($datasource);
      $table = new Table($this->getMigrationTable());

      $columnSpecs = [
          static::COL_EXECUTION_DATETIME => fn() => $this->createExecutionDatetimeColumn($platform),
          static::COL_MIGRATION_NAME => fn() => $this->createMigrationNameColumn($platform),
          static::COL_BATCH => fn() => $this->createBatchColumn($platform),
          static::COL_CHECKSUM => fn() => $this->createChecksumColumn($platform),
      ];

      foreach ($columnSpecs as $name => $factory) {
          if ($this->columnExists($connection, $name)) {
              continue;
          }
          $column = $factory();
          $column->setTable($table);
          $sql = $platform->getAddColumnDDL($column);
          $stmt = $connection->prepare($sql);
          if ($stmt === false) {
              throw new RuntimeException(...);
          }
          $stmt->execute();
      }

      $this->backfillMigrationNamesIfNeeded($datasource);
  }
  ```
- [ ] **Step 2: Implement `backfillMigrationNamesIfNeeded()`** — walks rows where `migration_name = ''`, looks up the on-disk filename for each `version` timestamp, sets `migration_name` to the suffix. Uses `findMigrationClassNameSuffix($timestamp)` (already exists).
- [ ] **Step 3: Write `MigrationSchemaUpgradeTest`:**
  - Set up an SQLite in-memory connection via the test harness.
  - Manually create a legacy two-column migration table (`version`, `execution_datetime`).
  - Insert a few legacy rows (timestamp + datetime).
  - Place corresponding `PropelMigration_<timestamp>_<suffix>.php` stub files.
  - Call `MigrationManager::modifyMigrationTableIfOutdated('default')`.
  - Assert: `migration_name`, `batch`, `checksum` columns now exist.
  - Assert: pre-existing rows have `migration_name` populated from the on-disk suffix.
  - Assert: pre-existing rows have `batch = 1`.
  - Assert: pre-existing rows have `checksum IS NULL`.
- [ ] **Step 4: Run `composer test:agnostic`.** New test green; existing tests still green; `MigrationManagerTableNotFoundTest` (Phase A regression) still green.
- [ ] **Step 5: Run `composer cs-check` + `composer stan` + `composer psalm` + `composer deptrac:check`.** All clean.
- [ ] **Step 6: Commit.** Message: `feat(migration): upgrade migration table schema in-place for existing consumer databases`.

### Task H.2.3: Replace `updateLatestMigrationTimestamp()` with `recordMigrationApplication()`

**Files:**
- Modify: `src/Propel/Generator/Manager/MigrationManager.php`.
- Modify: `src/Propel/Generator/Command/MigrationMigrateCommand.php`.
- Modify: `src/Propel/Generator/Command/MigrationUpCommand.php`.

- [ ] **Step 1: Add `recordMigrationApplication(string $datasource, int $timestamp, string $migrationName, int $batch, ?string $checksum): void` method.** Inserts into the migration table with all 5 columns populated. Calls `modifyMigrationTableIfOutdated()` first.
- [ ] **Step 2: Mark `updateLatestMigrationTimestamp()` as `@deprecated` and turn it into a thin BC forwarder:**
  ```php
  /**
   * @deprecated since 3.0; use recordMigrationApplication() instead.
   */
  public function updateLatestMigrationTimestamp(string $datasource, int $timestamp): void
  {
      trigger_deprecation('dennisvanbeersel/propel', '3.0', '...');
      $this->recordMigrationApplication($datasource, $timestamp, '', 1, null);
  }
  ```
- [ ] **Step 3: Update `MigrationMigrateCommand::execute()` to call `recordMigrationApplication(...)` with the migration's name, current batch number (from `getCurrentBatch()`), and checksum (from `MigrationCheckSummer::compute()`).**
- [ ] **Step 4: Same for `MigrationUpCommand::execute()`.**
- [ ] **Step 5: Run `composer test:agnostic`** — confirm green.
- [ ] **Step 6: Commit.** Message: `feat(migration): record migration_name + batch + checksum on apply`.

### Task H.2.4: Add `getCurrentBatch()` helper

**Files:**
- Modify: `src/Propel/Generator/Manager/MigrationManager.php`.

- [ ] **Step 1: `getCurrentBatch(string $datasource): int`** — runs `SELECT MAX(batch) FROM migration_table`, returns max+1 (or 1 if NULL).
- [ ] **Step 2: Plumb into `MigrationMigrateCommand` so each `migration:migrate` invocation gets a single shared batch number.** All migrations applied in the same invocation share the batch; the next invocation's batch is one higher.
- [ ] **Step 3: Test added to `MigrationSchemaUpgradeTest`:** apply 2 migrations in one invocation → both `batch=2` (since legacy-rows are `batch=1`); apply 1 more in next invocation → `batch=3`.
- [ ] **Step 4: Run `composer test:agnostic`.**
- [ ] **Step 5: Commit.** Message: `feat(migration): batch grouping per migrate invocation`.

---

## Group H.3: Dry-run + checksum drift detection (Tasks H.3.1–H.3.4)

**Verification after each task:** `composer test:agnostic` GREEN; PHPStan + Psalm baselines unchanged; new dry-run + drift tests GREEN.

This is the user-facing safety capability set. After H.3, operators can preview SQL pre-production and detect hand-edits to migration files.

### Task H.3.1: Implement `MigrationCheckSummer`

**Files:**
- Create: `src/Propel/Generator/Manager/MigrationCheckSummer.php`.
- Create: `tests/Propel/Tests/Generator/Manager/MigrationCheckSummerTest.php`.

- [ ] **Step 1: Implementation.**
  ```php
  final class MigrationCheckSummer
  {
      public const ALGORITHM = 'sha256';
      public const HEX_LENGTH = 64;

      public function compute(string $migrationFilePath): string
      {
          if (!is_file($migrationFilePath) || !is_readable($migrationFilePath)) {
              throw new RuntimeException(...);
          }
          $normalized = php_strip_whitespace($migrationFilePath);
          return hash(self::ALGORITHM, $normalized);
      }

      public function verify(string $expectedHex, string $migrationFilePath): bool
      {
          if ($expectedHex === '' || strlen($expectedHex) !== self::HEX_LENGTH) {
              return true; // pre-checksum-era row; treat as not-yet-recorded; not a drift
          }
          return hash_equals($expectedHex, $this->compute($migrationFilePath));
      }
  }
  ```
- [ ] **Step 2: Tests as enumerated in the file structure above (5 cases).**
- [ ] **Step 3: Run `composer test:agnostic`** — GREEN.
- [ ] **Step 4: Commit.** Message: `feat(migration): MigrationCheckSummer for SHA-256 file hashing`.

### Task H.3.2: Wire checksum recording into apply path

**Files:**
- Modify: `src/Propel/Generator/Command/MigrationMigrateCommand.php`.
- Modify: `src/Propel/Generator/Command/MigrationUpCommand.php`.

- [ ] **Step 1: Each apply path computes the checksum** via `(new MigrationCheckSummer())->compute($migrationFilePath)` and passes it to `MigrationManager::recordMigrationApplication(...)`.
- [ ] **Step 2: Test in `MigrationSchemaUpgradeTest`:** apply a migration, query the table, assert `checksum` is a 64-char hex string equal to `compute(filename)`.
- [ ] **Step 3: Run `composer test:agnostic`.**
- [ ] **Step 4: Commit.** Message: `feat(migration): record checksum on apply`.

### Task H.3.3: Implement `verifyChecksums()` + drift warning

**Files:**
- Modify: `src/Propel/Generator/Manager/MigrationManager.php` — `verifyChecksums(string $datasource): array<int, string>` returns `[timestamp => recordedHex]` for drifted rows.
- Modify: `src/Propel/Generator/Command/MigrationStatusCommand.php` — calls `verifyChecksums()` and reports `(drift)` next to drifted rows in the status table.
- Modify: `src/Propel/Generator/Command/MigrationMigrateCommand.php` — calls `verifyChecksums()` at start; warns per drifted row; with `--strict-drift` returns non-zero exit if any drift found.
- Create: `tests/Propel/Tests/Generator/Manager/MigrationDriftDetectionTest.php`.

- [ ] **Step 1: Implement `verifyChecksums()`:**
  ```php
  public function verifyChecksums(string $datasource): array
  {
      $rows = $this->getMigrationData($datasource);
      $checkSummer = new MigrationCheckSummer();
      $drifted = [];
      foreach ($rows as $row) {
          $timestamp = (int)$row[static::COL_VERSION];
          $checksum = $row[static::COL_CHECKSUM] ?? null;
          if ($checksum === null) {
              continue; // legacy / baseline row; no recorded checksum
          }
          $filename = sprintf('%s/%s.php', $this->getWorkingDirectory(), $this->getMigrationClassName($timestamp));
          if (!is_file($filename)) {
              continue; // file was archived (squashed?); not drift
          }
          if (!$checkSummer->verify($checksum, $filename)) {
              $drifted[$timestamp] = $checksum;
          }
      }
      return $drifted;
  }
  ```
- [ ] **Step 2: Wire into `migration:status` table output** — drifted timestamps get a `(drift)` suffix in the Name column.
- [ ] **Step 3: Wire into `migration:migrate`** — at start, run `verifyChecksums()`; for each drifted timestamp emit `$io->warning('Migration X has drifted; the file changed since the recorded apply.')`. With `--strict-drift`, return non-zero exit.
- [ ] **Step 4: Tests in `MigrationDriftDetectionTest`:**
  - Apply a migration, store its checksum.
  - Edit the migration file (introduce a real-code change).
  - Call `verifyChecksums()` → returns the timestamp.
  - Run `migration:migrate --strict-drift` → non-zero exit.
  - Run `migration:migrate` (no flag) → zero exit but warning emitted.
- [ ] **Step 5: Run `composer test:agnostic`** — GREEN.
- [ ] **Step 6: Commit.** Message: `feat(migration): runtime checksum drift detection`.

### Task H.3.4: Implement `--dry-run` for `migration:migrate` / `migration:up` / `migration:down`

**Files:**
- Modify: `src/Propel/Generator/Command/MigrationMigrateCommand.php`, `MigrationUpCommand.php`, `MigrationDownCommand.php`.

- [ ] **Step 1: Add `--dry-run` `InputOption::VALUE_NONE` flag** to each command's `configure()`.
- [ ] **Step 2: At the SQL-execution site:** if `dry-run` is set, replace `$conn->exec($statement)` with `$io->block($statement, 'SQL', 'fg=cyan', '> ')` and skip the increment of `$res`. After the loop, replace the `recordMigrationApplication(...)` call with `$io->note('Dry run: would have applied <name>; no changes committed.')`.
- [ ] **Step 3: Verbose output reads "DRY-RUN:" in front.**
- [ ] **Step 4: Test:** apply a migration with `--dry-run`; query the table; assert no rows changed; assert SQL appears in the output.
- [ ] **Step 5: Run `composer test:agnostic`.**
- [ ] **Step 6: Commit.** Message: `feat(migration): --dry-run flag emits SQL preview without applying`.

---

## Group H.4: Squashing + baselines (Tasks H.4.1–H.4.4)

**Verification after each task:** `composer test:agnostic` GREEN; new squash + baseline tests GREEN.

### Task H.4.1: `migration:squash` command

**Files:**
- Create: `src/Propel/Generator/Command/MigrationSquashCommand.php`.
- Create: `tests/Propel/Tests/Generator/Command/MigrationSquashCommandTest.php`.
- Modify: `src/Propel/Generator/Manager/MigrationManager.php` — add `recordSquash()`.
- Modify: `bin/propel` — register the command.

- [ ] **Step 1: Command skeleton with `#[AsCommand(name: 'migration:squash')]`.** Args: `from-version`, `to-version`. Options inherited from `migration:migrate`. Plus `--dry-run`.
- [ ] **Step 2: Validation:**
  - Both versions exist in the database (already-applied).
  - Range is contiguous (no gaps in the on-disk migration files).
  - All datasources are at-or-past the to-version.
  - Refuse if any datasource has the range partially-applied.
- [ ] **Step 3: Execution path:**
  - Load each migration in `[from..to]`, concatenate `getUpSQL()` per datasource into a combined squashed up-SQL.
  - Same for `getDownSQL()` (the squashed down is the concatenation of inverted-order downs).
  - Generate a new `PropelMigration_<toVersion>_squashed.php` via `getMigrationClassBody()`.
  - Move the source migration files into a `squashed/` subdirectory.
  - Compute the new checksum for the squashed file.
  - Call `MigrationManager::recordSquash(<from>, <to>, <newChecksum>)` on each datasource.
- [ ] **Step 4: `recordSquash()` implementation:**
  ```php
  public function recordSquash(string $datasource, int $fromTimestamp, int $toTimestamp, string $newChecksum): void
  {
      $platform = $this->getPlatform($datasource);
      $conn = $this->getAdapterConnection($datasource);
      $conn->transaction(function () use ($conn, $platform, $fromTimestamp, $toTimestamp, $newChecksum): void {
          // Delete all rows in the range
          $deleteSql = sprintf('DELETE FROM %s WHERE %s >= ? AND %s <= ?', ...);
          $stmt = $conn->prepare($deleteSql);
          $stmt->bindValue(1, $fromTimestamp, PDO::PARAM_INT);
          $stmt->bindValue(2, $toTimestamp, PDO::PARAM_INT);
          $stmt->execute();
          // Insert the squashed row
          $insertSql = sprintf('INSERT INTO %s (...) VALUES (?, ?, ?, ?, ?)', ...);
          // ...
      });
  }
  ```
- [ ] **Step 5: Test in `MigrationSquashCommandTest`:** create 3 migrations, apply them, run squash, assert squashed file exists, assert `squashed/` archive contains the originals, assert migration table has only 1 row at `to-version`.
- [ ] **Step 6: Run `composer test:agnostic`.**
- [ ] **Step 7: Commit.** Message: `feat(migration): migration:squash combines applied range into one migration`.

### Task H.4.2: `migration:baseline` command

**Files:**
- Create: `src/Propel/Generator/Command/MigrationBaselineCommand.php`.
- Create: `tests/Propel/Tests/Generator/Command/MigrationBaselineCommandTest.php`.
- Modify: `src/Propel/Generator/Manager/MigrationManager.php` — add `recordBaseline()`.
- Modify: `bin/propel` — register the command.

- [ ] **Step 1: Command skeleton with `#[AsCommand(name: 'migration:baseline')]`.** Required arg: `version`. Options: standard set + `--dry-run`.
- [ ] **Step 2: Idempotence guard:** if `version` already in migration table with `batch=0` (baseline marker), report "already at baseline N" and exit 0.
- [ ] **Step 3: Validation:** every on-disk timestamp ≤ `version` is unfaked (otherwise the baseline would clobber real history); refuse with a clear error if any are present.
- [ ] **Step 4: Execution:** call `MigrationManager::recordBaseline(<datasource>, <listOfTimestamps>)` which inserts faked rows with `batch=0`, `checksum=NULL`, `migration_name` from the on-disk filename.
- [ ] **Step 5: Test in `MigrationBaselineCommandTest`:** invoke baseline at timestamp T; assert all timestamps ≤ T are now in the migration table; assert later ones still pending.
- [ ] **Step 6: Run `composer test:agnostic`.**
- [ ] **Step 7: Commit.** Message: `feat(migration): migration:baseline declares known-good schema state`.

### Task H.4.3: Wire dry-run into squash + baseline

**Files:**
- Modify: `src/Propel/Generator/Command/MigrationSquashCommand.php`, `MigrationBaselineCommand.php`.

- [ ] **Step 1: With `--dry-run`:** print what would happen; do not file-system-mutate; do not DB-mutate.
- [ ] **Step 2: Add tests:** assert dry-run leaves both filesystem + DB unchanged.
- [ ] **Step 3: Commit.** Message: `feat(migration): --dry-run for squash + baseline`.

### Task H.4.4: Documentation cookbook

**Files:**
- Create: `docs/MIGRATION-TABLE-UPGRADE.md`.
- Modify: `docs/MIGRATION-FROM-PRE-AI.md` — three new sections.
- Modify: `docs/UPGRADE-3.0.md` — capability summary.
- Modify: `docs/BACKWARD_COMPATIBILITY.md` — Tier 2 + Tier 3 entries.
- Modify: `CHANGELOG.md` — Phase H entries.

- [ ] **Step 1: Cookbook page with concrete shell sessions:** `migration:status` shows old + new table; `migration:squash` example; `migration:baseline` example; troubleshooting drift detection.
- [ ] **Step 2: Commit.** Message: `docs(migration): cookbook + migration-table upgrade path`.

---

## Group H.5: Data vs structural migrations (Tasks H.5.1–H.5.3)

**Verification after each task:** `composer test:agnostic` GREEN.

### Task H.5.1: Add `--kind=schema|data` to `migration:create` + two template files

**Files:**
- Modify: `src/Propel/Generator/Command/MigrationCreateCommand.php` — adds `--kind` option.
- Modify: `src/Propel/Generator/Manager/MigrationManager.php` — `getMigrationClassBody()` accepts a `$kind` parameter; selects the corresponding template file.
- Create: `src/Propel/Generator/Manager/templates/migration_template_schema.php` — DDL-focused template (current `migration_template.php` content with a comment block adjustment).
- Create: `src/Propel/Generator/Manager/templates/migration_template_data.php` — data-only template (preUp/postUp PHP body, no SQL helper sections).

- [ ] **Step 1: Implement `--kind` option.** Default `schema` (preserves today's behavior).
- [ ] **Step 2: Tests:** `migration:create --kind=data` produces a file using `migration_template_data.php`; assert specific markers appear/disappear (`getUpSQL` present in schema; absent or commented in data).
- [ ] **Step 3: Commit.** Message: `feat(migration): --kind=schema|data option on migration:create`.

### Task H.5.2: `migration:make:schema` + `migration:make:data` aliases

**Files:**
- Create: `src/Propel/Generator/Command/MigrationMakeSchemaCommand.php`.
- Create: `src/Propel/Generator/Command/MigrationMakeDataCommand.php`.
- Modify: `bin/propel` — register both.

- [ ] **Step 1: Each command extends `MigrationCreateCommand` and overrides `configure()` to set the kind default.** Both use `#[AsCommand]`.
- [ ] **Step 2: Tests in `MigrationMakeSchemaCommandTest` + `MigrationMakeDataCommandTest`.**
- [ ] **Step 3: Commit.** Message: `feat(migration): migration:make:schema + migration:make:data aliases`.

### Task H.5.3: Documentation

**Files:**
- Modify: `docs/MIGRATION-FROM-PRE-AI.md` — explanation of the kind distinction.
- Modify: `docs/UPGRADE-3.0.md` — capability mention.

- [ ] **Step 1: Doc the distinction; reference Laravel's pattern; reference the runtime is identical.**
- [ ] **Step 2: Commit.** Message: `docs(migration): data vs schema migration distinction`.

---

## Group H.6: Round 1 mid-phase review (Task H.6.1)

**Verification:** Round 1 review summary committed; iteration log up to date; any MUST-FIX findings closed before progressing.

### Task H.6.1: Round 1 mid-phase review

**Files:**
- Create: `docs/reviews/H-round-1-architecture.md`, `H-round-1-bc.md`, `H-round-1-cli-ux.md`, `H-round-1-sre.md`, `H-round-1-summary.md`.

- [ ] **Step 1: Trigger `superpowers:requesting-code-review`** with focus on: H.1 attribute sweep correctness, H.2 schema-upgrade safety, H.3 drift detection, BC for the migration-table change.
- [ ] **Step 2: Apply MUST-FIX findings.** SHOULD-FIX may be deferred with documented reasoning into `H-waivers.md`.
- [ ] **Step 3: Commit.** Message: `docs(reviews): Phase H round 1 mid-phase review`.

---

## Group H.7: Round 2 end-phase review + DoD (Tasks H.7.1–H.7.2)

### Task H.7.1: Round 2 end-phase review

**Files:**
- Create: `docs/reviews/H-round-2-{architecture,bc,quality,perf,ambition,cli-ux,sre}.md`, `H-round-2-summary.md`.

- [ ] **Step 1: Trigger end-phase review** across all 5 standing reviewers + 2 specialists.
- [ ] **Step 2: Apply MUST-FIX findings.** SHOULD-FIX waived only with documented reasoning per umbrella §4.15.4.
- [ ] **Step 3: Commit.** Message: `docs(reviews): Phase H round 2 end-phase review`.

### Task H.7.2: DoD check + Phase H summary

**Files:**
- Create: `docs/PHASE-H-SUMMARY.md`.

- [ ] **Step 1: Walk the umbrella §4.9 15-checkbox DoD list.** Mark each box.
- [ ] **Step 2: Write the phase summary** mirroring A/B/C/D/E/F summaries.
- [ ] **Step 3: Commit.** Message: `docs(phase-h): summary + DoD`.

---

## Definition of Done (umbrella §4.9, 15 boxes)

- [ ] All test matrix cells green (agnostic at minimum; mysql/pgsql/sqlite cells deferred to CI consistent with prior phases).
- [ ] Baselines decreased OR unchanged (Phase H is not a drawdown phase per umbrella §4.1; unchanged is acceptable).
- [ ] Coverage delta ≥ 0%.
- [ ] Mutation score ≥ threshold on touched files (Infection on `Generator/Manager/MigrationManager.php` + `Generator/Manager/MigrationCheckSummer.php`; ≥65 per umbrella §4.14 row "all phases").
- [ ] Deptrac green; no new layer violations.
- [ ] Performance benchmarks within ±5% of pre-phase numbers (Phase H is not perf-critical; checksum compute on apply is once per apply).
- [ ] CHANGELOG.md updated.
- [ ] Deprecation message audit clean (only the `updateLatestMigrationTimestamp()` BC forwarder emits one new deprecation).
- [ ] Generated-code lint parity green (Phase H does not touch generated code).
- [ ] Golden-file diff: no change (Phase H does not touch generated code).
- [ ] Phase plan updated with retrospective notes (Phase H summary covers this).
- [ ] Review rounds 1 + 2 completed; reports committed.
- [ ] Surgical-test battery executed; reports committed.
- [ ] All MUST-FIX review findings closed; SHOULD-FIX closed or waived.
- [ ] Iteration cycles consumed within budget.

---

## Risk Register

| # | Risk | Mitigation |
|---|---|---|
| H1 | Migration-table schema upgrade fails on a real-world consumer database (e.g. read-only DBA, missing ALTER privilege) | `modifyMigrationTableIfOutdated()` is run inside a transaction; ALTER failure rolls back; consumer gets a clear error pointing at `MIGRATION-TABLE-UPGRADE.md`. |
| H2 | Dry-run output is inaccurate (the SQL printed differs from what would actually run) | Dry-run uses the exact same `getUpSQL()` / `getDownSQL()` paths the apply uses; the only difference is `$conn->exec()` is skipped. Tests assert the printed output equals the SQL collected at apply time. |
| H3 | Squash semantics are wrong (e.g. preserves an out-of-order down-SQL) | Squash refuses partially-applied ranges; the squashed file's `getDownSQL()` is the concatenation of source files' downs in REVERSE order; tests cover this. |
| H4 | Baseline overwrites real applied rows | Baseline rejects with a clear error if any timestamp ≤ baseline target is present in the migration table with `batch != 0`. |
| H5 | SHA-256 checksum is too sensitive (whitespace edits trigger false drift) | `php_strip_whitespace()` normalization removes comments + collapses whitespace; cosmetic edits don't change the hash. Dedicated test asserts whitespace-only + comment-only edits leave the hex stable. |
| H6 | `#[AsCommand]` attribute migration accidentally renames a command (BC break) | `tests/snapshots/cli-commands.txt` snapshot + `AsCommandAttributeTest` enforces parity per command. CI fails on rename. |
| H7 | SymfonyStyle changes test assertions in CommandTester-based tests | We update tests as part of H.1.3; the change is mechanical and visible in `git diff`. |
| H8 | Phase A's SQLState-only auto-create-on-PDOException regression | The H.2 column-missing path goes through `modifyMigrationTableIfOutdated()`, NOT the table-create path; Phase A's `getAllDatabaseVersions()` SQLState-handling code is unchanged; explicit regression test in `MigrationManagerTableNotFoundTest` continues to pass. |
| H9 | Existing third-party Behaviors that subclass `MigrationManager` break on the new column constants | Constants are `protected`, additive; no removal of existing constants; method signatures preserved with `updateLatestMigrationTimestamp()` as BC forwarder. |
| H10 | The `php_strip_whitespace()` fingerprint changes between PHP minor versions | Mitigation: this is acknowledged. The checksum is a "best effort" detector; if PHP changes the strip output between 8.3 and 8.4, EVERY recorded checksum would drift after the PHP upgrade. The mitigation is the warn-don't-fail default; consumers can re-record post-PHP-upgrade by running a single `migration:status --refresh-checksums` (planned for a follow-up). For Phase H, the warning is acceptable; the strict-drift mode requires explicit opt-in. |

---

## Out of scope

- **Tier 1 surface changes.** Phase H adds Tier 3 commands + Tier 2 additive `MigrationManager` methods. No Tier 1 generator-output / runtime-AR changes.
- **New SQL dialects.** Phase H uses the existing platform DDL paths.
- **`migration:status --refresh-checksums`** — recompute every recorded checksum from the on-disk file. Useful but defer to follow-up; not in Phase H scope.
- **Migration-table archive.** When squashing, the archived migration files live under a `squashed/` directory; we don't ship a "purge old archives" command in Phase H.
- **Multi-version baseline.** Baseline at one timestamp only per H.4.2; multi-baseline is out-of-scope.
- **Auto-detection of safe-to-squash ranges.** Squashing is operator-driven; we don't auto-suggest ranges.
- **GUI / TUI for migration management.** CLI only.
- **PHPStan rule that migration files don't reference deprecated APIs.** Out-of-scope; covered by the project-wide deprecation telemetry.
