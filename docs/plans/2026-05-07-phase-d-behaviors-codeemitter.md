# Phase D: Behaviors Cleanup + CodeEmitter Introduction (Phase B' fold-in)

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Date:** 2026-05-07
**Branch:** `ar-rewrite` (commit; do NOT push)
**Goal:** Land the **CodeEmitter** template-pattern foundation (the umbrella's "Phase B'" — §2.3, §5) inside Phase D where it has its first real consumers; refactor the two largest string-concat behavior modifiers (Sortable ~2,011 LOC, NestedSet ~3,036 LOC) onto it; deprecate `NestedSet` (Tier 2 runway, recursive-CTE replacement documented); switch `Timestampable` default to native `ON UPDATE CURRENT_TIMESTAMP`; and bundle the nine reverse-parser / Diff-comparator tasks deferred from Phase C as a parallel sub-track that benefits from the same `CodeEmitter` discipline. Per umbrella §1.2 SQLite stays frozen — the new reverse work is verified on the existing fixture suite + a best-effort SQLite path; the testcontainers-based round-trip PBT is explicitly deferred to a future cycle.

**Architecture:** Three concentric pieces.
1. **`CodeEmitter` (`src/Propel/Generator/Builder/Util/CodeEmitter.php`)** — the new builder-internal abstraction. Indented-block management, method-signature emission with typed parameters and return type, expression-context escaping (string-literal, identifier, PHP variable), conditional/loop helpers, snippet composition. Replaces ad-hoc `"\n    " . $code . "\n}";` string concatenation in behavior modifiers and (in future phases) in the per-builder generators.
2. **Behavior modifier refactor** — `Sortable` (4 modifier files) and `NestedSet` (2 modifier files, plus the deprecation overlay in `NestedSetBehavior` itself) ported from string-concat to `CodeEmitter` templates. The byte-identical golden output is the verification: refactor MUST NOT change a single character of generated code.
3. **Behavior keep-list polish** — `Timestampable` switches to native `ON UPDATE CURRENT_TIMESTAMP` DDL with a behavior-side opt-out; `AggregateColumn` / `AggregateMultipleColumns` documented as future bridge to Phase C generated columns (capability noted, no behavior-shape change in Phase D); `NestedSet` deprecated end-to-end.

The Phase C deferred work (reverse parsers + Diff comparator extensions) lands in a parallel sub-track (Group D.6). It uses the same per-task golden + lint-parity discipline; testcontainers-PBT is explicitly carved out into a follow-up cycle (Phase D' / stretch) so Phase D itself remains tractable.

**Tech Stack:** PHP 8.3, MySQL 8.0+, MariaDB 10.5+, PostgreSQL 14+, SQLite (frozen), PHPUnit 11, PHPStan level 7, Spryker code-sniffer, Infection ^0.29, Deptrac ^2, innmind/black-box ^6.

**Reference spec:** `docs/plans/2026-05-06-modernization-umbrella-spec.md` (umbrella §1.2 SQLite stance, §2.3 builder architecture, §3.1/§3.2 BC tiers, §5 Phase D + B' rows, §6.3 behavior keep-list).
**Predecessors:** `docs/plans/2026-05-06-phase-a-foundations.md` (Phase A), `docs/plans/2026-02-03-builder-om-modernization.md` + `docs/plans/2026-05-06-phase-b-amendments.md` (Phase B), `docs/plans/2026-05-06-phase-c-schema-ddl-modernization.md` (Phase C).
**Phase A summary:** `docs/PHASE-A-SUMMARY.md`.
**Phase B summary:** `docs/PHASE-B-SUMMARY.md`.
**Phase C summary:** `docs/PHASE-C-SUMMARY.md` (lists the 9 deferred tasks Phase D bundles).

**Review tier (per umbrella §4.13.3):** MEDIUM-RISK 2-round (mid-phase + end-phase pre-merge). Per umbrella §4.13.3 the LOW-RISK 2-round table covers Phase D; the §5 phases table classifies Phase D as Medium effort/risk. Phase D adopts the **2-round cadence** with the mid-phase round triggered after the CodeEmitter and the Sortable refactor land (the first real proof that the abstraction works on production-scale modifiers). End-phase pre-merge round is the standard all-standing-reviewers pass. Post-merge canary is **not required** for Phase D (2-round phases skip Round 3).
**Specialists (per umbrella §4.13.2):** standing 5 reviewers + **Behavior author / third-party-ecosystem reviewer** (would real-world Behaviors break? would consumers' NestedSet usage migrate cleanly?) + **Code generator specialist** (CodeEmitter API shape, byte-identical refactor verification).

**Test/quality gate state at start of phase (carried from Phase C end):**
- `phpstan-baseline.neon` 403 lines, 0 blanket regex.
- `psalm-baseline.xml` 1616 lines, suppression-clean.
- Deptrac 0 violations against 233-line baseline.
- All `failOn*` flags strict.
- Test suite: 2493 / 5349 / 21 GREEN.
- Tier 1 signature snapshots: 53 stable.
- Bookstore golden tree: 399 files, refreshed-per-task contract enforced.
- XSD additivity: 12 fixtures validate.

---

## File Structure (created or modified by this phase)

**Created:**
- `src/Propel/Generator/Builder/Util/CodeEmitter.php` — the new template-emitter helper (indented blocks, method-signature emission, expression escaping). Tier 3 internal, but its public method shapes are committed via `tests/snapshots/tracked-classes.txt` so the Code Generator specialist's review pass has a stable contract.
- `src/Propel/Generator/Builder/Util/CodeEmitterScope.php` — RAII-style scope helper returned by `CodeEmitter::block()` / `methodBody()`; auto-dedents on `__destruct`.
- `tests/Propel/Tests/Generator/Builder/Util/CodeEmitterTest.php` — unit tests for indent/dedent invariants, method-signature emission round-trips, expression escaping, snippet composition.
- `tests/Propel/Tests/Generator/Builder/Util/CodeEmitterPropertyTest.php` — black-box PBT: any sequence of `block`/`unblock`/`line` calls produces well-formed PHP (parses with `nikic/php-parser` if available, else `php -l` syntax check).
- `tests/PropertyTests/CodeEmitter/IndentationInvariantTest.php` — black-box PBT: emitted indent depth always equals open-block count.
- `docs/CODEEMITTER.md` — concise API reference for behavior authors and (in later phases) builder authors. Documented examples for the most common emission patterns.
- `docs/reviews/D-round-1-*.md`, `D-round-2-*.md`, `D-summary.md`, `D-iterations.md`, `D-waivers.md`, `D-bench.md`, `D-mutation.json`.
- `docs/PHASE-D-SUMMARY.md` (written at phase exit, alongside the others).
- `tests/Fixtures/bookstore/schema-nested-set-deprecated.xml` — opt-in fixture exercising NestedSet so the deprecation trigger path is end-to-end tested without polluting the main bookstore golden tree.

**Modified:**
- `src/Propel/Generator/Behavior/NestedSet/NestedSetBehavior.php` — class-level `@deprecated` PHPDoc + `trigger_deprecation('maturix/propel', '3.0', 'NestedSet behavior — use recursive CTEs (MySQL 8 / MariaDB 10.2.2+ / PG 8.4+); see docs/MIGRATION-FROM-PRE-AI.md#nested-set-to-recursive-cte for migration cookbook')` in `modifyTable()`.
- `src/Propel/Generator/Behavior/NestedSet/NestedSetBehaviorObjectBuilderModifier.php` — port to `CodeEmitter` (file is 49,209 bytes / ~1,783 LOC) **without changing emitted output**; emit `@deprecated` PHPDoc on every generated nested-set method (consumer-visible deprecation).
- `src/Propel/Generator/Behavior/NestedSet/NestedSetBehaviorQueryBuilderModifier.php` — same port (33,219 bytes / ~1,117 LOC).
- `src/Propel/Generator/Behavior/Sortable/SortableBehaviorObjectBuilderModifier.php` — port to `CodeEmitter` (25,756 bytes / ~965 LOC).
- `src/Propel/Generator/Behavior/Sortable/SortableBehaviorQueryBuilderModifier.php` — port (19,697 bytes / ~750 LOC).
- `src/Propel/Generator/Behavior/Sortable/SortableBehaviorTableMapBuilderModifier.php` — port (2,256 bytes / ~85 LOC); included even though small, for completeness of the Sortable refactor.
- `src/Propel/Generator/Behavior/Sortable/SortableBehavior.php` — no functional change, but factor common helpers (`getColumnConstant`, etc.) into shared methods consumed by all three modifiers via `CodeEmitter`.
- `src/Propel/Generator/Behavior/Timestampable/TimestampableBehavior.php` — add `use_native_on_update` parameter (default `'true'`); switch `modifyTable()` to declare `update_column` with native `ON UPDATE CURRENT_TIMESTAMP` DDL on MySQL/MariaDB and a runtime `BEFORE UPDATE` trigger emitter on PG (PG has no native ON UPDATE; honest fallback to current PHP-side path with deprecation if `use_native_on_update="false"` set explicitly).
- `src/Propel/Generator/Behavior/AutoAddPk/AutoAddPkBehavior.php` — proof-of-concept consumer of `CodeEmitter` for the (small) generated PK-shim on classes lacking a PK. Validates `CodeEmitter`'s API shape on a tiny consumer before scaling to Sortable/NestedSet.
- `src/Propel/Generator/Behavior/AggregateColumn/AggregateColumnBehavior.php` — docblock note (no shape change): "Future bridge to Phase C generated columns where supported (`generated="stored"` + expression). Tracked in Phase D' or H."
- `src/Propel/Generator/Behavior/AggregateMultipleColumns/AggregateMultipleColumnsBehavior.php` — same docblock note.
- `src/Propel/Generator/Reverse/MysqlSchemaParser.php` — Phase C deferral C.4.1: replace `SHOW CREATE TABLE` regex with `INFORMATION_SCHEMA.COLUMNS` + `STATISTICS` + `CHECK_CONSTRAINTS` queries (carry forward §6.4 deferral). Legacy regex path kept under a `--reverse-format=legacy-show-create` flag (C.4.5) for one minor before removal in 4.0.
- `src/Propel/Generator/Reverse/PgsqlSchemaParser.php` — Phase C deferral C.4.2: `pg_catalog` + `information_schema` migration. Same legacy-flag pattern.
- `src/Propel/Generator/Reverse/AbstractSchemaParser.php` — Phase C deferral C.4.3: shared UUID heuristic helper (`detectUuidColumn(array $infoSchemaRow): bool`).
- `src/Propel/Generator/Command/DatabaseReverseCommand.php` — Phase C deferral C.4.5: `--reverse-format` option (`information-schema` default, `legacy-show-create` deprecated alternative).
- `src/Propel/Generator/Model/Diff/IndexComparator.php` — Phase C deferral C.5.2: detect partial-index `WHERE`-clause drift, expression-index drift, index-type drift (`USING gin/gist/hash`).
- `src/Propel/Generator/Model/Diff/ForeignKeyComparator.php` — Phase C deferral C.5.3: detect `DEFERRABLE` / `INITIALLY DEFERRED` drift.
- `src/Propel/Generator/Model/Index.php` — accessors `getWhereClause(): ?string` and `getIndexType(): ?string` (additive; Tier 2 SPI snapshot updated).
- `src/Propel/Generator/Model/ForeignKey.php` — accessors `getDeferrable(): ?string` and `getInitiallyDeferred(): ?bool` (additive; Tier 2 SPI snapshot updated).
- `resources/xsd/database.xsd` — Phase C deferral hangover: optional `deferrable` and `initiallyDeferred` attributes on `<foreign-key>`. XSD additivity (umbrella §3.7).
- `tests/Fixtures/bookstore/build/golden/` — regenerated per task; commit-with-source contract.
- `tests/snapshots/tracked-classes.txt` — add `Propel\Generator\Builder\Util\CodeEmitter` (Tier 3 with documented stability commitment, mirrored from `QuickBuilder` precedent in §3.3).
- `docs/MIGRATION-FROM-PRE-AI.md` — three new sections: "NestedSet to recursive CTEs" (concrete cookbook), "Timestampable native ON UPDATE" (per-platform migration), "AggregateColumn future bridge".
- `docs/UPGRADE-3.0.md` — capability summary entries for CodeEmitter (consumer-irrelevant, internal) and NestedSet deprecation (consumer-relevant).
- `docs/BACKWARD_COMPATIBILITY.md` — explicit Tier 2 commitment for `NestedSetRecursiveIterator` and the per-table generated NestedSet methods (deprecation runway, no removal in 3.x); CodeEmitter Tier 3 stability note.
- `CHANGELOG.md` — Phase D entries under `[Unreleased]`.
- `tests/agnostic.phpunit.xml` — register `tests/PropertyTests/CodeEmitter/` testsuite cell if not already covered by existing PropertyTests glob.

**Deleted:**
- None. Phase D is purely refactor + deprecation. The Validate / QueryCache deletions belong to Phase A (already done — explicitly out of Phase D scope).

---

## Group D.1: CodeEmitter introduction (Tasks D.1.1–D.1.4)

**Verification after each task:** `composer test:agnostic` + `composer stan` (baselines monotonic) + `composer cs-check` on the new file.

The umbrella's "Phase B'" lands here, where the abstraction has real consumers. Building `CodeEmitter` in isolation without a forcing-function consumer would invite the "string concat with extra steps" risk. Tasks D.1.4 + D.3 + D.4 are the forcing function.

---

### Task D.1.1: Implement `CodeEmitter` core (indent management + line emission)

**Files:**
- Create: `src/Propel/Generator/Builder/Util/CodeEmitter.php`
- Create: `src/Propel/Generator/Builder/Util/CodeEmitterScope.php`
- Create: `tests/Propel/Tests/Generator/Builder/Util/CodeEmitterTest.php`

- [ ] **Step 1: Define `CodeEmitter`'s minimum surface**

```php
final class CodeEmitter
{
    /** @var list<string> */
    private array $lines = [];
    private int $indent = 0;
    private string $indentString = '    ';

    public function __construct(int $startIndent = 0, string $indentString = '    ') { ... }

    /** Append a single line; auto-indented. Trailing \n added; leading whitespace stripped. */
    public function line(string $code = ''): self { ... }

    /** Append multiple lines from a heredoc; each line auto-indented relative to current depth. */
    public function lines(string $multilineCode): self { ... }

    /** Increase indent. Returns a CodeEmitterScope that decrements on destruction (RAII). */
    public function block(): CodeEmitterScope { ... }

    /** Manual indent control (use sparingly; prefer block()). */
    public function indent(): self { ... }
    public function dedent(): self { ... }

    /** Emit a blank line (no indent). */
    public function blank(): self { ... }

    /** Render the buffered lines as a single string. */
    public function toString(): string { ... }

    public function __toString(): string { return $this->toString(); }
}
```

`CodeEmitterScope` holds a weak reference to the emitter and calls `dedent()` on `__destruct`.

- [ ] **Step 2: Unit tests cover**

- Empty emitter → empty string.
- `line('foo')` → `"foo\n"`.
- One nested block → `"foo\n    bar\n"`.
- Three nested blocks → `"            baz\n"` (12 spaces).
- Manual `indent()`/`dedent()` mismatch in destructor order → throws (programming error).
- `lines("foo\nbar")` heredoc → both lines indented to current depth.
- Trailing whitespace on input lines stripped; leading whitespace preserved relative to first non-blank line in `lines()`.
- `blank()` → `"\n"` regardless of indent.

- [ ] **Step 3: Run quality gates**

```
composer test:agnostic
composer stan
composer cs-check src/Propel/Generator/Builder/Util/CodeEmitter.php
```

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Builder/Util/CodeEmitter.php src/Propel/Generator/Builder/Util/CodeEmitterScope.php tests/Propel/Tests/Generator/Builder/Util/CodeEmitterTest.php
git commit -m "feat(builder/util): introduce CodeEmitter core (indent + line emission)"
```

---

### Task D.1.2: `CodeEmitter` method/signature emission helpers

**Files:**
- Modify: `src/Propel/Generator/Builder/Util/CodeEmitter.php`
- Modify: `tests/Propel/Tests/Generator/Builder/Util/CodeEmitterTest.php`

- [ ] **Step 1: Add typed-method emission helpers**

```php
/**
 * Emit a method signature line + open the method body block.
 * Returns a scope that closes the body on destruction (emits "}").
 *
 * @param list<array{name: string, type?: string, default?: string, byRef?: bool, variadic?: bool}> $params
 */
public function methodBody(
    string $name,
    string $visibility = 'public',
    array $params = [],
    ?string $returnType = null,
    string $docblock = '',
    bool $isStatic = false,
): CodeEmitterScope { ... }

/** Emit a docblock; multi-line, each line `* ` prefixed. */
public function docblock(string $text): self { ... }
```

- [ ] **Step 2: Expression escapers**

```php
/** Escape a value for PHP source as a string literal: 'foo', or '\'foo\\\'bar\''. */
public static function phpString(string $value): string { ... }

/** Escape a PHP variable name (validated against /^\$[A-Za-z_][A-Za-z0-9_]*$/, throws on invalid). */
public static function phpVar(string $name): string { ... }

/** Escape an SQL identifier into a PHP-source-safe string literal (preserves quoting). */
public static function sqlIdentifier(string $name): string { ... }
```

- [ ] **Step 3: Tests**

Each helper has a dedicated test method. Edge cases: empty string, special chars (`'`, `\`, `\n`), unicode, max-length identifier, reserved-word identifiers.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Builder/Util/CodeEmitter.php tests/Propel/Tests/Generator/Builder/Util/CodeEmitterTest.php
git commit -m "feat(builder/util): CodeEmitter method-signature + expression-escape helpers"
```

---

### Task D.1.3: Property-based test for `CodeEmitter` invariants

**Files:**
- Create: `tests/PropertyTests/CodeEmitter/IndentationInvariantTest.php`
- Modify: `tests/agnostic.phpunit.xml` if needed

- [ ] **Step 1: Use `innmind/black-box` to generate sequences of operations**

Generators: `Set::elements(['line', 'block-open', 'block-close', 'blank'])`. Build a sequence respecting the constraint `(open count ≥ close count at every prefix)`. Operate on a fresh emitter; assert at end:

- Output indent of every emitted `line` equals the open-blocks-at-emission-time count × indent string length.
- Round-trip: `(new CodeEmitter())->fromString($emitter->toString())->toString() === $emitter->toString()` if a `fromString` parser exists (skip this assertion in Phase D — out of scope; document as future).

- [ ] **Step 2: Generate and parse-check the output via `php -l`**

For each randomized sequence wrapped in `<?php` + a function header, write to temp file and run `php -l`. Output must lint clean.

Run with `--seed=fixed` for reproducibility; document seed in `docs/reviews/D-round-1-summary.md`.

- [ ] **Step 3: Commit**

```bash
git add tests/PropertyTests/CodeEmitter/IndentationInvariantTest.php tests/agnostic.phpunit.xml
git commit -m "test(pbt): CodeEmitter indentation invariant + lint-clean output"
```

---

### Task D.1.4: AutoAddPk proof-of-concept consumer

**Files:**
- Modify: `src/Propel/Generator/Behavior/AutoAddPk/AutoAddPkBehavior.php`
- Create: `tests/Propel/Tests/Generator/Behavior/AutoAddPk/AutoAddPkCodeEmitterTest.php`

- [ ] **Step 1: AutoAddPk does NOT emit method bodies — it operates on the schema model**

Therefore: it cannot be the test consumer for `CodeEmitter`'s body-emission helpers. INSTEAD, use this task to validate `CodeEmitter`'s **schema-side string-building** helpers — namely the column-attribute array building when `modifyTable()` adds a column. The relevant call site is the array literal passed to `Table::addColumn()`. Refactor it to `CodeEmitter::phpString` + `CodeEmitter::phpVar` so the array's escaping is centralized.

If after attempting this refactor it becomes clear that AutoAddPk is **too small** to exercise `CodeEmitter` meaningfully, **swap proof-of-concept consumer to Timestampable**'s `objectMethods()` / `queryMethods()` (which DO build method bodies via string concat — see `TimestampableBehavior.php:180–297`). Document the swap decision in the task comment and proceed with Timestampable. The discovery that AutoAddPk is the wrong POC consumer is itself a finding — record it in `docs/reviews/D-iterations.md` for the Round 1 reviewer.

- [ ] **Step 2: Run `tools/regen-golden.php` after the refactor; assert ZERO golden diff (the refactor must be byte-identical to existing emission)**

If the regen produces a non-empty diff, the refactor is wrong; revert and iterate.

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Generator/Behavior/AutoAddPk/AutoAddPkBehavior.php tests/Propel/Tests/Generator/Behavior/AutoAddPk/AutoAddPkCodeEmitterTest.php tests/Fixtures/bookstore/build/golden/
git commit -m "feat(behavior/autoaddpk): adopt CodeEmitter as proof-of-concept (byte-identical output)"
```

---

## Group D.2: NestedSet deprecation (Tasks D.2.1–D.2.4)

**Verification after each task:** `composer test:agnostic` + the NestedSet integration tests under `tests/Propel/Tests/Generator/Behavior/NestedSet/`.

NestedSet is **deprecated, not deleted**. Tier 2 runway per umbrella §3.2 — full minor in 3.x with `trigger_deprecation`, kill in 4.0. Per the task brief, the runtime classes (`NestedSetRecursiveIterator`, `NestedSetNodeInterface`) and the per-table generated NestedSet methods are Tier 2 and stay callable through 3.x.

---

### Task D.2.1: Class-level `@deprecated` + behavior-load `trigger_deprecation`

**Files:**
- Modify: `src/Propel/Generator/Behavior/NestedSet/NestedSetBehavior.php`
- Modify: `tests/deprecations.allowlist.json` (add the new self-emitted deprecation)
- Create: `tests/Fixtures/bookstore/schema-nested-set-deprecated.xml`

- [ ] **Step 1: Add class-level `@deprecated` PHPDoc**

```
/**
 * @deprecated since 3.0, use recursive CTEs (MySQL 8 / MariaDB 10.2.2+ / PG 8.4+) instead.
 *             See docs/MIGRATION-FROM-PRE-AI.md#nested-set-to-recursive-cte. Will be removed in 4.0.
 */
class NestedSetBehavior extends Behavior { ... }
```

- [ ] **Step 2: Emit `trigger_deprecation` from `modifyTable()`**

```php
trigger_deprecation(
    'maturix/propel',
    '3.0',
    'NestedSet behavior is deprecated. Use recursive CTEs (MySQL 8 / MariaDB 10.2.2+ / PG 8.4+); see docs/MIGRATION-FROM-PRE-AI.md#nested-set-to-recursive-cte for migration cookbook.',
);
```

Place at the START of `modifyTable()` so it fires once per table per generation run.

- [ ] **Step 3: Update `tests/deprecations.allowlist.json`**

Re-run baseline generation (Phase A pattern):
```
SYMFONY_DEPRECATIONS_HELPER='max[self]=999999&baselineFile=tests/deprecations.allowlist.json&generateBaseline=true' \
  vendor/bin/phpunit -c tests/agnostic.phpunit.xml
```
Verify the new entry contains the NestedSet message.

- [ ] **Step 4: Add a fixture exercising NestedSet (so the deprecation is exercised in CI)**

`tests/Fixtures/bookstore/schema-nested-set-deprecated.xml` is a minimal fixture containing one table with `<behavior name="nested_set"/>`. Ensures the `trigger_deprecation` path runs in agnostic tests; main bookstore golden tree stays NestedSet-free (Phase A removed it).

- [ ] **Step 5: Commit**

```bash
git add src/Propel/Generator/Behavior/NestedSet/NestedSetBehavior.php tests/deprecations.allowlist.json tests/Fixtures/bookstore/schema-nested-set-deprecated.xml
git commit -m "feat(behavior/nested-set): deprecate behavior; add trigger_deprecation + allowlist baseline"
```

---

### Task D.2.2: Emit `@deprecated` PHPDoc on every generated NestedSet method

**Files:**
- Modify: `src/Propel/Generator/Behavior/NestedSet/NestedSetBehaviorObjectBuilderModifier.php`
- Modify: `src/Propel/Generator/Behavior/NestedSet/NestedSetBehaviorQueryBuilderModifier.php`
- Modify: `tests/Fixtures/bookstore/build/golden/` (regen)

- [ ] **Step 1: For each generated method's docblock, append `@deprecated` line**

```
@deprecated since 3.0, use a recursive CTE (see migration guide). Will be removed in 4.0.
```

This is consumer-visible: every Propel user with a NestedSet behavior will see `@deprecated` on the generated `getDescendants()`, `getAncestors()`, etc. — both in IDE tooltips and in static-analysis warnings.

- [ ] **Step 2: Regenerate golden + lint-parity**

```
tests/bin/setup.sqlite.sh
php tools/regen-golden.php
composer cs-check tests/Fixtures/bookstore/build/classes/
composer stan -- tests/Fixtures/bookstore/build/classes/
```

The fixture `schema-nested-set-deprecated.xml` from D.2.1 is the load-bearing one here — the main bookstore has no NestedSet table, so the main golden tree is untouched. A SEPARATE golden subdirectory `tests/Fixtures/bookstore/build/golden-nested-set/` is generated from `schema-nested-set-deprecated.xml`.

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Generator/Behavior/NestedSet/NestedSetBehaviorObjectBuilderModifier.php src/Propel/Generator/Behavior/NestedSet/NestedSetBehaviorQueryBuilderModifier.php tests/Fixtures/bookstore/build/golden-nested-set/
git commit -m "feat(behavior/nested-set): emit @deprecated PHPDoc on every generated method"
```

---

### Task D.2.3: `MIGRATION-FROM-PRE-AI.md` recursive-CTE cookbook

**Files:**
- Modify: `docs/MIGRATION-FROM-PRE-AI.md`

- [ ] **Step 1: Add section "NestedSet to recursive CTEs"**

Concrete migration content:

1. **Why migrate.** NestedSet's lft/rgt encoding requires O(N) tree-rebalance writes on every insert; recursive CTEs are O(depth) reads with no rebalance cost; PG 8.4+, MySQL 8.0+, MariaDB 10.2.2+, SQLite 3.8.3+ all support `WITH RECURSIVE`.

2. **Schema migration.** Replace `<behavior name="nested_set"/>` with a `parent_id` FK column referencing the table's own PK. SQL fragment to convert existing `tree_left`/`tree_right`/`tree_level` data into `parent_id`:

```sql
-- For each node, derive parent_id from the immediate ancestor in (lft, rgt) order.
UPDATE category c
SET parent_id = (
    SELECT p.id FROM category p
    WHERE p.tree_left < c.tree_left
      AND p.tree_right > c.tree_right
      AND p.tree_level = c.tree_level - 1
      AND p.scope_id = c.scope_id  -- if NestedSet was scoped
    LIMIT 1
);
```

3. **Query migration.** `getDescendants()` / `getAncestors()` / `getChildren()` / `isDescendantOf()` / `getNbChildren()` / `countDescendants()` / `getRoot()` — provide a `WITH RECURSIVE` equivalent for each. Example for `getDescendants()`:

```sql
WITH RECURSIVE descendants AS (
    SELECT * FROM category WHERE id = :start_id
    UNION ALL
    SELECT c.* FROM category c JOIN descendants d ON c.parent_id = d.id
)
SELECT * FROM descendants WHERE id != :start_id;
```

Wired through Propel: `BookQuery::create()->where('id IN (' . $cteSql . ')')` or via `useCriteria` raw clause.

4. **Move/insert operations.** No tree-rebalance is needed — `parent_id` updates are O(1). Document the BEFORE/AFTER side-by-side for `addChild()`, `moveTo*()`, `delete()` etc.

5. **Performance comparison.** Numerical: recursive-CTE descendant query for a 10k-node tree, depth 6 — typically <5ms on PG 14, vs 50–200ms for NestedSet's index-bounded BETWEEN scan on a 10k-row table. Document as "your mileage may vary; benchmark".

6. **Migration scripts.** Reference any consumer `propel/migrate-nested-set` shell script if one is provided in `tools/` (out of scope to author one in Phase D — link to issue).

- [ ] **Step 2: Cross-reference from `docs/UPGRADE-3.0.md` and `CHANGELOG.md`**

- [ ] **Step 3: Commit**

```bash
git add docs/MIGRATION-FROM-PRE-AI.md docs/UPGRADE-3.0.md CHANGELOG.md
git commit -m "docs(migration): NestedSet to recursive CTE cookbook with concrete SQL examples"
```

---

### Task D.2.4: `BACKWARD_COMPATIBILITY.md` Tier 2 commitment for runtime NestedSet classes

**Files:**
- Modify: `docs/BACKWARD_COMPATIBILITY.md`

- [ ] **Step 1: Explicit Tier 2 entries**

Add to the Tier 2 list:

- `Propel\Runtime\ActiveRecord\NestedSetRecursiveIterator` — deprecation runway through 3.x; removed at 4.0.
- `Propel\Runtime\ActiveRecord\NestedSetNodeInterface` — deprecation runway through 3.x; removed at 4.0.
- The per-table generated NestedSet methods (`getDescendants`, `getAncestors`, etc.) — Tier 2 by transitive deprecation; the `@deprecated` PHPDoc from D.2.2 is the consumer signal.

- [ ] **Step 2: Commit**

```bash
git add docs/BACKWARD_COMPATIBILITY.md
git commit -m "docs(bc): NestedSet runtime classes Tier 2 commitment (deprecation through 3.x)"
```

---

## Round 1 Review Checkpoint (after D.1 + D.2 + D.3)

**Trigger:** all of D.1.1–D.1.4 + D.2.1–D.2.4 + D.3.1–D.3.5 landed and green.
**Reviewers (per umbrella §4.13.3 mid-phase):** Architecture + BC + Code generator specialist + Behavior author / third-party-ecosystem reviewer (4 lenses).
**Lens:**
- **Architecture (CodeEmitter design):** is the API surface the right size? Are scope objects RAII'd cleanly? Could a builder author confuse `block()` with `methodBody()`? Are there hidden dependencies or non-obvious order-of-operations gotchas? Concrete: would a fresh subagent in D.4 stumble over a sharp edge?
- **BC (NestedSet deprecation runway):** does the `@deprecated` PHPDoc + `trigger_deprecation` fire exactly once per generation (not per row, not per method)? Is the migration guide actionable for a consumer with a 50,000-row scoped tree?
- **Code generator specialist (refactor verification):** is the Sortable refactor truly byte-identical to pre-refactor output? Run `git diff tests/Fixtures/bookstore/build/golden/` over the Sortable refactor commits; expected: empty.
- **Behavior author / third-party-ecosystem:** would a real Behavior in `propel-bundle` / `propelorm/cookbook` need to update? CodeEmitter is Tier 3 internal — third-party Behaviors wouldn't directly use it (they'd use `AbstractOMBuilder` like always). But the `Behavior` abstract class hooks (`objectMethods`, `queryMethods`, `objectFilter`, etc.) STILL accept builder instances, not CodeEmitter — confirm BC.

**Outputs:** `docs/reviews/D-round-1-architecture.md`, `D-round-1-bc.md`, `D-round-1-code-gen.md`, `D-round-1-behaviors.md`, `D-round-1-summary.md`.
**Iteration budget:** 3 cycles. Track in `D-iterations.md`.

Findings tagged `MUST-FIX` block progression to D.4 (NestedSet refactor); `SHOULD-FIX` may be waived per umbrella §4.15.4.

---

## Group D.3: Sortable refactor onto CodeEmitter (Tasks D.3.1–D.3.5)

**Verification after each task:** `composer test:agnostic` + golden regen + **byte-identical golden diff assertion** + `composer cs-check tests/Fixtures/bookstore/build/classes/`.

**Critical contract:** every Sortable refactor commit MUST produce ZERO byte difference in the generated bookstore golden tree. This is the proof that `CodeEmitter` correctly mirrors the pre-existing string-concat output. If golden diff is non-empty, the refactor is wrong (output drift) — revert and iterate.

The Sortable behavior modifies tables that have a `rank` (or configurable) column for ordering. The bookstore fixture has Sortable applied to one table — see `tests/Fixtures/bookstore/schema.xml` for the existing usage (book or chapter — verified at task time). The Sortable golden fragment is therefore the load-bearing test.

---

### Task D.3.1: Port `SortableBehaviorObjectBuilderModifier` (~965 LOC)

**Files:**
- Modify: `src/Propel/Generator/Behavior/Sortable/SortableBehaviorObjectBuilderModifier.php`
- Modify: `tests/Fixtures/bookstore/build/golden/` (regen)
- Create: `tests/Propel/Tests/Generator/Behavior/Sortable/SortableObjectBuilderModifierCodeEmitterTest.php`

- [ ] **Step 1: Identify every method that builds emitted code via string concatenation**

Per a `grep -n 'return "' src/Propel/Generator/Behavior/Sortable/SortableBehaviorObjectBuilderModifier.php` audit at task start, list each emission method. Typical names: `objectAttributes()`, `objectMethods()`, `objectFilter()`, `preInsert()`, `preUpdate()`, `preDelete()`, `addRank()`, `addInsertAtRank()`, `addInsertAtBottom()`, `addInsertAtTop()`, `addMoveToRank()`, `addSwapWith()`, `addMoveUp()`, `addMoveDown()`, `addMoveToTop()`, `addMoveToBottom()`, `addRemoveFromList()`, etc. (Names verified per file at task time.)

- [ ] **Step 2: Port one method at a time, regenerating golden after each**

Per-method discipline: change ONE method to use `CodeEmitter`; run `php tools/regen-golden.php`; assert `git diff tests/Fixtures/bookstore/build/golden/` is empty. If non-empty, the port introduced a drift — fix BEFORE moving to the next method.

This iterative discipline is the only viable approach. A bulk port followed by debugging would be a multi-cycle nightmare per umbrella §4.15.3 budget rules.

- [ ] **Step 3: Compose larger emissions via `CodeEmitter::lines()` + `methodBody()` blocks**

Pattern:
```php
$e = new CodeEmitter();
$body = $e->methodBody('moveUp', 'public', [], ': self', $docblock);
$e->line('if ($this->isFirst()) {');
{
    $b = $e->block();
    $e->line('return $this;');
}
$e->line('}');
$e->line('return $this->swapWith($this->getPrevious());');
unset($body); // closes the method body
return $e->toString();
```

- [ ] **Step 4: Unit test that exercises one ported method in isolation**

Snapshot the emitted string for a synthetic table. Snapshot file: `tests/Propel/Tests/Generator/Behavior/Sortable/__snapshots__/object-builder-modifier-rank.txt`.

- [ ] **Step 5: Run quality gates**

```
composer test:agnostic
composer stan
composer cs-check tests/Fixtures/bookstore/build/classes/
git diff --quiet tests/Fixtures/bookstore/build/golden/  # MUST be clean
```

- [ ] **Step 6: Commit**

```bash
git add src/Propel/Generator/Behavior/Sortable/SortableBehaviorObjectBuilderModifier.php tests/Propel/Tests/Generator/Behavior/Sortable/ tests/Fixtures/bookstore/build/golden/
git commit -m "refactor(behavior/sortable): port ObjectBuilderModifier to CodeEmitter (byte-identical)"
```

---

### Task D.3.2: Port `SortableBehaviorQueryBuilderModifier` (~750 LOC)

**Files:**
- Modify: `src/Propel/Generator/Behavior/Sortable/SortableBehaviorQueryBuilderModifier.php`
- Modify: `tests/Fixtures/bookstore/build/golden/` (regen)
- Create: `tests/Propel/Tests/Generator/Behavior/Sortable/SortableQueryBuilderModifierCodeEmitterTest.php`

- [ ] **Step 1: Same per-method-iterative port as D.3.1**

Typical query-modifier methods: `queryMethods()`, `addFindOneByRank()`, `addFindList()`, `addOrderByRank()`, `addReorder()`, `addRetrieveByRank()`, `addRetrieveList()`. (Names verified at task time.)

- [ ] **Step 2: Same byte-identical golden assertion**

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Generator/Behavior/Sortable/SortableBehaviorQueryBuilderModifier.php tests/Propel/Tests/Generator/Behavior/Sortable/ tests/Fixtures/bookstore/build/golden/
git commit -m "refactor(behavior/sortable): port QueryBuilderModifier to CodeEmitter (byte-identical)"
```

---

### Task D.3.3: Port `SortableBehaviorTableMapBuilderModifier` (~85 LOC)

**Files:**
- Modify: `src/Propel/Generator/Behavior/Sortable/SortableBehaviorTableMapBuilderModifier.php`
- Modify: `tests/Fixtures/bookstore/build/golden/` (regen)
- Create: `tests/Propel/Tests/Generator/Behavior/Sortable/SortableTableMapModifierCodeEmitterTest.php`

- [ ] **Step 1: Smallest of the three Sortable modifiers; can be ported in a single pass**

- [ ] **Step 2: Byte-identical assertion as before**

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Generator/Behavior/Sortable/SortableBehaviorTableMapBuilderModifier.php tests/Propel/Tests/Generator/Behavior/Sortable/ tests/Fixtures/bookstore/build/golden/
git commit -m "refactor(behavior/sortable): port TableMapBuilderModifier to CodeEmitter (byte-identical)"
```

---

### Task D.3.4: Extract Sortable shared helpers into `SortableBehavior`

**Files:**
- Modify: `src/Propel/Generator/Behavior/Sortable/SortableBehavior.php`

- [ ] **Step 1: Identify duplication across the three modifiers**

Likely candidates: `getColumnAttribute()`, `getColumnConstant()`, `getColumnGetter()`, `getColumnSetter()`, scope-aware column-name resolution. Currently each modifier has its own copy via similar-named protected methods.

- [ ] **Step 2: Move shared helpers to `SortableBehavior` as `public` (or a shared trait) and consume from each modifier**

The trait OR direct delegation (`$this->behavior->getColumnConstant(...)`) — pick ONE pattern and apply consistently. Justify the pick in the commit message.

- [ ] **Step 3: Byte-identical golden assertion as before**

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Behavior/Sortable/
git commit -m "refactor(behavior/sortable): consolidate shared helpers in SortableBehavior"
```

---

### Task D.3.5: Sortable refactor end-state verification

**Files:**
- Modify: `docs/reviews/D-round-1-summary.md` (write-up)

- [ ] **Step 1: Run end-state checks**

```
composer test:agnostic
composer stan
composer cs-check
git diff --stat tests/Fixtures/bookstore/build/golden/  # expected: empty
diff -ru <pre-D.3-golden-tree> tests/Fixtures/bookstore/build/golden/  # expected: no output
```

- [ ] **Step 2: Capture LOC drawdown**

```
wc -l src/Propel/Generator/Behavior/Sortable/*.php  # should show ~drop, not +growth
```

If post-refactor LOC is not lower than the 2,011 LOC pre-refactor baseline, that's a flag (`CodeEmitter` should reduce repetition). Document delta in `D-round-1-summary.md`.

- [ ] **Step 3: Commit no source change; this task is a checkpoint**

The actual code is in D.3.1–D.3.4. This task's deliverable is the audit + Round 1 readiness signal.

---

## Group D.4: NestedSet refactor onto CodeEmitter (Tasks D.4.1–D.4.5)

**Verification:** identical contract to Group D.3 — byte-identical golden output is the gate.

NestedSet is **3,036 LOC across 2 modifier files + 136 LOC `NestedSetBehavior`** = larger than Sortable. The refactor is mechanically the same as D.3 but with more methods. Per umbrella §4.15.3 the iteration budget is 3 cycles per round; a bulk port would consume budget; per-method-iterative discipline (as in D.3.1) is mandatory.

**Even though NestedSet is deprecated**, the refactor still happens because:
1. The deprecation runway is the full 3.x line — generated code MUST stay correct.
2. The CodeEmitter pattern is Phase D's main deliverable; refactoring NestedSet validates `CodeEmitter` against the largest behavior.
3. Removing it in 4.0 is cleaner if the 3.x scaffolding is template-clean.

---

### Task D.4.1: Port `NestedSetBehaviorObjectBuilderModifier` (~1,783 LOC)

**Files:**
- Modify: `src/Propel/Generator/Behavior/NestedSet/NestedSetBehaviorObjectBuilderModifier.php`
- Modify: `tests/Fixtures/bookstore/build/golden-nested-set/` (regen against `schema-nested-set-deprecated.xml`)
- Create: `tests/Propel/Tests/Generator/Behavior/NestedSet/NestedSetObjectBuilderModifierCodeEmitterTest.php`

- [ ] **Step 1: Method audit (per D.3.1 pattern)**

NestedSet object methods are dense: `addRoot()`, `addAncestors()`, `addDescendants()`, `addChildren()`, `addParent()`, `addPrevSibling()`, `addNextSibling()`, `addInsertAsFirstChildOf()`, `addInsertAsLastChildOf()`, `addInsertAsPrevSiblingOf()`, `addInsertAsNextSiblingOf()`, `addMoveToFirstChildOf()`, `addMoveToLastChildOf()`, `addMoveToPrevSiblingOf()`, `addMoveToNextSiblingOf()`, `addDeleteDescendants()`, `addNestedSetIterator()`, plus the entire `objectAttributes()` block. (Names verified per file at task time.)

- [ ] **Step 2: Port one method at a time; assert byte-identical golden after each**

- [ ] **Step 3: Cross-check the per-method `@deprecated` PHPDoc emission from D.2.2 still fires correctly through the CodeEmitter pipeline**

D.2.2 added the `@deprecated` line via string-concat. Once the modifier moves to CodeEmitter, the `@deprecated` line MUST still appear in the generated output — confirm via golden diff containing the `@deprecated` substring.

- [ ] **Step 4: Run gates + commit**

```bash
git add src/Propel/Generator/Behavior/NestedSet/NestedSetBehaviorObjectBuilderModifier.php tests/Propel/Tests/Generator/Behavior/NestedSet/ tests/Fixtures/bookstore/build/golden-nested-set/
git commit -m "refactor(behavior/nested-set): port ObjectBuilderModifier to CodeEmitter (byte-identical)"
```

---

### Task D.4.2: Port `NestedSetBehaviorQueryBuilderModifier` (~1,117 LOC)

**Files:**
- Modify: `src/Propel/Generator/Behavior/NestedSet/NestedSetBehaviorQueryBuilderModifier.php`
- Modify: `tests/Fixtures/bookstore/build/golden-nested-set/` (regen)
- Create: `tests/Propel/Tests/Generator/Behavior/NestedSet/NestedSetQueryBuilderModifierCodeEmitterTest.php`

- [ ] **Step 1: Method audit + per-method-iterative port**

- [ ] **Step 2: Byte-identical golden assertion**

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Generator/Behavior/NestedSet/NestedSetBehaviorQueryBuilderModifier.php tests/Propel/Tests/Generator/Behavior/NestedSet/ tests/Fixtures/bookstore/build/golden-nested-set/
git commit -m "refactor(behavior/nested-set): port QueryBuilderModifier to CodeEmitter (byte-identical)"
```

---

### Task D.4.3: NestedSet shared helper extraction

**Files:**
- Modify: `src/Propel/Generator/Behavior/NestedSet/NestedSetBehavior.php`

- [ ] **Step 1: Same shared-helper pattern as D.3.4**

NestedSet's two modifiers share column-resolution and tree-arithmetic helpers. Move them to `NestedSetBehavior` (or a trait shared with both modifiers). Pick the same pattern picked in D.3.4 for consistency.

- [ ] **Step 2: Byte-identical golden assertion**

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Generator/Behavior/NestedSet/
git commit -m "refactor(behavior/nested-set): consolidate shared helpers in NestedSetBehavior"
```

---

### Task D.4.4: NestedSet runtime-class cleanup signal-check

**Files:**
- Modify: `src/Propel/Runtime/ActiveRecord/NestedSetRecursiveIterator.php`
- Modify: `src/Propel/Runtime/ActiveRecord/NestedSetNodeInterface.php`

- [ ] **Step 1: Add class-level `@deprecated` PHPDoc to each (no `trigger_deprecation` here — these are constructed by generated code, and the trigger fires from `NestedSetBehavior::modifyTable()` in D.2.1)**

Tier 2 commitment from D.2.4 already documents the runway. The `@deprecated` annotation makes IDE tooling consistent.

- [ ] **Step 2: Run quality gates; commit**

```bash
git add src/Propel/Runtime/ActiveRecord/NestedSetRecursiveIterator.php src/Propel/Runtime/ActiveRecord/NestedSetNodeInterface.php
git commit -m "feat(runtime/nested-set): @deprecated annotation on runtime classes (Tier 2 runway)"
```

---

### Task D.4.5: NestedSet refactor end-state verification + LOC drawdown

**Files:**
- Modify: `docs/reviews/D-round-2-summary.md` (write-up; D-round-2 is end-phase, this contributes to it)

- [ ] **Step 1: End-state checks — same as D.3.5 but for NestedSet**

```
git diff --stat tests/Fixtures/bookstore/build/golden-nested-set/  # expected: empty after final port
wc -l src/Propel/Generator/Behavior/NestedSet/*.php  # capture drawdown
```

- [ ] **Step 2: Document LOC drawdown** (Sortable + NestedSet combined) in the Round 2 summary draft.

This task's deliverable is the audit signal for Round 2. No source change.

---

## Group D.5: Timestampable native ON UPDATE (Tasks D.5.1–D.5.3)

**Verification after each task:** `composer test:agnostic` + per-platform DDL emission test + the existing `tests/Propel/Tests/Generator/Behavior/Timestampable/` integration tests.

The current `TimestampableBehavior` (`src/Propel/Generator/Behavior/Timestampable/TimestampableBehavior.php:66–73`) adds `update_column` as a plain TIMESTAMP and relies on `preUpdate()` PHP-side hook (lines 106–125) to write the value on every save. Phase D switches to native `ON UPDATE CURRENT_TIMESTAMP` DDL on MySQL/MariaDB; PG has no native equivalent so we keep the PHP-side hook on PG (honest) and document the asymmetry. SQLite is frozen — the existing PHP-side hook applies (umbrella §1.2).

---

### Task D.5.1: Add `use_native_on_update` parameter (default `'true'`)

**Files:**
- Modify: `src/Propel/Generator/Behavior/Timestampable/TimestampableBehavior.php`
- Create: `tests/Propel/Tests/Generator/Behavior/Timestampable/TimestampableNativeOnUpdateTest.php`

- [ ] **Step 1: Add to `$parameters` array**

```php
protected array $parameters = [
    'create_column' => 'created_at',
    'update_column' => 'updated_at',
    'disable_created_at' => 'false',
    'disable_updated_at' => 'false',
    'use_native_on_update' => 'true',  // NEW; opt-out for trigger setups
];
```

- [ ] **Step 2: In `modifyTable()`, when `use_native_on_update === 'true'` AND `withUpdatedAt()` AND the platform supports it (MySQL or MariaDB)**

Add the column with `defaultExpr` set to `CURRENT_TIMESTAMP` and a vendor parameter `<vendor type="mysql"><parameter name="onUpdate" value="CURRENT_TIMESTAMP"/></vendor>` so the existing platform DDL emitter (which already honors that vendor parameter) generates the right SQL.

If platform is PostgreSQL: no native, fall back to PHP-side `preUpdate` hook (current behavior). Emit `trigger_deprecation('maturix/propel', '3.0', 'Timestampable use_native_on_update on PostgreSQL has no native ON UPDATE; PHP-side hook used; consider a TRIGGER for true DB-side enforcement')`.

If platform is SQLite: PHP-side hook (frozen).

If `use_native_on_update === 'false'`: explicit opt-out, use legacy PHP-side hook on all platforms; no deprecation (it's a chosen path, not a fallback).

- [ ] **Step 3: When native ON UPDATE is active, SKIP the corresponding `preUpdate()` body emission**

I.e., the generated `preUpdate()` no longer writes `$this->setUpdatedAt(...)` because the DB does it. This DOES change generated output — it's a Tier 1 generated-code change, but ONLY in the body of `preUpdate()` and ONLY for the Timestampable behavior. The Tier 1 surface (method existence, signature) is unchanged. Generated code change documented in CHANGELOG.

- [ ] **Step 4: Tests cover all three platforms × {use_native_on_update default, set false}**

- [ ] **Step 5: Regenerate golden + lint-parity**

The bookstore fixture has Timestampable on at least one table. Golden tree DOES change here — the `preUpdate()` body is shorter on MySQL output. Reviewer must approve the diff line-by-line.

- [ ] **Step 6: Commit**

```bash
git add src/Propel/Generator/Behavior/Timestampable/TimestampableBehavior.php tests/Propel/Tests/Generator/Behavior/Timestampable/TimestampableNativeOnUpdateTest.php tests/Fixtures/bookstore/build/golden/
git commit -m "feat(behavior/timestampable): default to native ON UPDATE CURRENT_TIMESTAMP on MySQL/MariaDB"
```

---

### Task D.5.2: `MIGRATION-FROM-PRE-AI.md` Timestampable migration cookbook

**Files:**
- Modify: `docs/MIGRATION-FROM-PRE-AI.md`

- [ ] **Step 1: Add section "Timestampable: native ON UPDATE CURRENT_TIMESTAMP"**

Content:

1. **What changed.** MySQL/MariaDB: `update_column` now declared with `ON UPDATE CURRENT_TIMESTAMP` at the DDL level. PG: no native; PHP-side hook continues. SQLite: PHP-side hook (frozen).

2. **Why.** Single source of truth (DB), no risk of out-of-band UPDATEs missing the timestamp, no PHP overhead per save, simpler generated code.

3. **Migration impact.** If your app does raw SQL UPDATEs that bypass Propel's save path, they previously DID NOT update `updated_at` — now they DO (on MySQL/MariaDB). This is a behavior change for raw-SQL paths. Audit your codebase for direct UPDATE statements on tables with Timestampable.

4. **Custom-trigger setups.** If you have a custom `BEFORE UPDATE` trigger on the table that sets `updated_at`, the new native default will fight with it. Set `<behavior name="timestampable"><parameter name="use_native_on_update" value="false"/></behavior>` to opt out and keep your trigger as the authority.

5. **Migration runway.** The default flips from PHP-side to native at 3.0 (this phase). Existing schemas with explicit `defaultExpr` already set will be respected — no double-default conflict. New `migration:diff` runs WILL produce `MODIFY COLUMN updated_at ... ON UPDATE CURRENT_TIMESTAMP` for tables that didn't previously have it. Review the migration carefully before applying.

- [ ] **Step 2: Commit**

```bash
git add docs/MIGRATION-FROM-PRE-AI.md
git commit -m "docs(migration): Timestampable native ON UPDATE migration cookbook"
```

---

### Task D.5.3: AggregateColumn future-bridge documentation

**Files:**
- Modify: `src/Propel/Generator/Behavior/AggregateColumn/AggregateColumnBehavior.php` (docblock only)
- Modify: `src/Propel/Generator/Behavior/AggregateMultipleColumns/AggregateMultipleColumnsBehavior.php` (docblock only)
- Modify: `docs/MIGRATION-FROM-PRE-AI.md` (cross-reference)

- [ ] **Step 1: Class-level docblock note for both**

```
/**
 * Future bridge: where the platform supports native generated columns (Phase C
 * delivered <column generated="stored" expression="...">), AggregateColumn can
 * compile to a native generated column instead of PHP-side recompute on save.
 * Tracked for a future phase (D' or H). Phase D documents the bridge intent;
 * implementation is out of scope.
 */
```

- [ ] **Step 2: Cross-reference in MIGRATION-FROM-PRE-AI.md** under "Capability roadmap" sub-section

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Generator/Behavior/AggregateColumn/ src/Propel/Generator/Behavior/AggregateMultipleColumns/ docs/MIGRATION-FROM-PRE-AI.md
git commit -m "docs(behavior/aggregate): document future bridge to native generated columns"
```

---

## Group D.6: Phase C reverse-parser deferred work (Tasks D.6.1–D.6.6)

**Verification per task:** `composer test:agnostic` + parser unit tests against fixtured INFORMATION_SCHEMA result rows + (where applicable) the existing `tests/bin/setup.sqlite.sh` round-trip.

This sub-track bundles the 9 tasks Phase C deferred (per `docs/PHASE-C-SUMMARY.md`'s "Deferred to follow-up cycle" table). They share the code-generator theme with the Sortable/NestedSet refactor: both produce string output describing schema. The `CodeEmitter` discipline doesn't apply directly to the reverse parsers (which CONSUME schema, not produce code), but the per-task golden + lint-parity discipline does.

**Critical caveat:** the original Phase C plan (tasks C.4.1, C.4.2, C.4.4) called for testcontainers-based round-trip PBT. Phase C deferred specifically because no testcontainers harness exists. **Phase D ships the implementation using the existing fixture databases + a SQLite-only path; the testcontainers-PBT is explicitly carved out into a future cycle.** The risk register (below) documents this trade-off.

The legacy `SHOW CREATE TABLE` regex path is kept under `--reverse-format=legacy-show-create` for one minor before removal in 4.0 — this is the same alias-then-kill discipline established in umbrella §3.6.

---

### Task D.6.1: `MysqlSchemaParser` migrates to INFORMATION_SCHEMA

**Files:**
- Modify: `src/Propel/Generator/Reverse/MysqlSchemaParser.php`
- Create: `tests/Fixtures/reverse/mysql-information-schema/columns.json`, `indexes.json`, `check_constraints.json`, `key_column_usage.json` — captured query results from a real MySQL 8 schema (committed; no live DB needed for unit tests).
- Create: `tests/Propel/Tests/Generator/Reverse/MysqlSchemaParserPhaseDTest.php`

- [ ] **Step 1: Replace `parseTables()` with `SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?`** (carry forward C.4.1 step 1)

- [ ] **Step 2: Replace per-table column parsing with `SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?`** (C.4.1 step 2 — full field mapping including `IS_GENERATED`, `GENERATION_EXPRESSION`, `EXTRA LIKE '%INVISIBLE%'`, `COLLATION_NAME`)

- [ ] **Step 3: Add `parseCheckConstraints(Table $table)`** (C.4.1 step 3) — query `information_schema.CHECK_CONSTRAINTS` joined with `TABLE_CONSTRAINTS`, build `CheckConstraint` model objects, set `enforced` from `ENFORCED = 'YES'`.

- [ ] **Step 4: Replace `SHOW INDEX` with `SELECT * FROM information_schema.STATISTICS`** (C.4.1 step 4) preserving `SEQ_IN_INDEX` ordering.

- [ ] **Step 5: Replace FK regex extraction with `information_schema.REFERENTIAL_CONSTRAINTS` joined with `KEY_COLUMN_USAGE`** (C.4.1 step 5).

- [ ] **Step 6: Unit tests using the fixtured INFORMATION_SCHEMA result rows + a mock DataFetcher**

The fixture JSON files represent what real queries would return on a real DB. Tests inject them via a `MockDataFetcher` so no live DB is required for CI.

- [ ] **Step 7: Round-trip verification on SQLite-only path**

For schemas that DON'T use Phase C features (generated cols, CHECK constraints), the SQLite reverse parser exists. Verify: parse `tests/Fixtures/bookstore/schema.xml` → emit DDL → run on SQLite → reverse-engineer back → re-parse → equal up to normalization. This is best-effort SQLite-only; the testcontainers MySQL/PG round-trip is deferred (see risk register).

- [ ] **Step 8: Commit**

```bash
git add src/Propel/Generator/Reverse/MysqlSchemaParser.php tests/Fixtures/reverse/mysql-information-schema/ tests/Propel/Tests/Generator/Reverse/MysqlSchemaParserPhaseDTest.php
git commit -m "refactor(reverse/mysql): migrate from SHOW CREATE TABLE regex to INFORMATION_SCHEMA"
```

---

### Task D.6.2: `PgsqlSchemaParser` migrates to `pg_catalog` + `information_schema`

**Files:**
- Modify: `src/Propel/Generator/Reverse/PgsqlSchemaParser.php`
- Create: `tests/Fixtures/reverse/pgsql-information-schema/columns.json`, `indexes.json`, `check_constraints.json`, `foreign_keys.json`
- Create: `tests/Propel/Tests/Generator/Reverse/PgsqlSchemaParserPhaseDTest.php`

- [ ] **Step 1–5: Direct port of Phase C task C.4.2 (steps 1–5)**

Tables, columns (`attidentity`, `attgenerated`, `pg_collation.collname`, `obj_description`), CHECK via `pg_constraint` + `pg_get_constraintdef`, indexes via `pg_index` (partial + expression detection), FKs via `pg_constraint WHERE contype = 'f'` (DEFERRABLE detection).

- [ ] **Step 6: Same fixture-row-based unit tests as D.6.1**

- [ ] **Step 7: Commit**

```bash
git add src/Propel/Generator/Reverse/PgsqlSchemaParser.php tests/Fixtures/reverse/pgsql-information-schema/ tests/Propel/Tests/Generator/Reverse/PgsqlSchemaParserPhaseDTest.php
git commit -m "refactor(reverse/pgsql): migrate to pg_catalog + information_schema queries"
```

---

### Task D.6.3: UUID detection heuristic on reverse (MySQL `BINARY(16)` + PG native)

**Files:**
- Modify: `src/Propel/Generator/Reverse/AbstractSchemaParser.php` (shared helper)
- Modify: `src/Propel/Generator/Reverse/MysqlSchemaParser.php` (consume helper)
- Modify: `src/Propel/Generator/Reverse/PgsqlSchemaParser.php` (consume helper)
- Modify: `tests/Propel/Tests/Generator/Reverse/MysqlSchemaParserPhaseDTest.php` + `PgsqlSchemaParserPhaseDTest.php`

- [ ] **Step 1: Add `protected function detectUuidColumn(array $infoSchemaRow): bool`** to `AbstractSchemaParser`

Heuristic per Phase C task C.4.3: `DATA_TYPE = 'binary' AND COLUMN_TYPE = 'binary(16)'` AND (column comment contains `'UUID'` OR column name matches `/(^|_)uuid$|_uuid_/i`). For PG: `data_type = 'uuid'` is the native check (much simpler).

- [ ] **Step 2: Tests** — fixture rows with each pattern.

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Generator/Reverse/AbstractSchemaParser.php src/Propel/Generator/Reverse/MysqlSchemaParser.php src/Propel/Generator/Reverse/PgsqlSchemaParser.php tests/Propel/Tests/Generator/Reverse/
git commit -m "feat(reverse): UUID detection on MySQL BINARY(16) + PG native uuid"
```

---

### Task D.6.4: `--reverse-format=legacy-show-create` runway flag

**Files:**
- Modify: `src/Propel/Generator/Command/DatabaseReverseCommand.php`
- Modify: `src/Propel/Generator/Reverse/MysqlSchemaParser.php` (route to legacy method when flag set)
- Modify: `src/Propel/Generator/Reverse/PgsqlSchemaParser.php` (same routing pattern)
- Modify: `docs/MIGRATION-FROM-PRE-AI.md`

- [ ] **Step 1: Add `--reverse-format` option** with values `information-schema` (default) and `legacy-show-create`. Symfony `InputOption::VALUE_REQUIRED`.

- [ ] **Step 2: When `legacy-show-create`, dispatch to the pre-D.6.1 parsing method** (kept private; not deleted). Fire `trigger_deprecation('maturix/propel', '3.0', '--reverse-format=legacy-show-create — removal targeted for 4.0')`.

- [ ] **Step 3: Document in `docs/MIGRATION-FROM-PRE-AI.md`** when to use the flag (downstream tooling depends on the older parser's quirks; concrete deadline).

- [ ] **Step 4: Tests** — flag-routing test asserting the legacy path is used when the option is set.

- [ ] **Step 5: Commit**

```bash
git add src/Propel/Generator/Command/DatabaseReverseCommand.php src/Propel/Generator/Reverse/MysqlSchemaParser.php src/Propel/Generator/Reverse/PgsqlSchemaParser.php docs/MIGRATION-FROM-PRE-AI.md
git commit -m "feat(reverse): runway flag --reverse-format=legacy-show-create with deprecation"
```

---

### Task D.6.5: `IndexComparator` detects partial / expression / index-type drift

**Files:**
- Modify: `src/Propel/Generator/Model/Diff/IndexComparator.php`
- Modify: `src/Propel/Generator/Model/Index.php` (add `getWhereClause(): ?string` and `getIndexType(): ?string` if absent)
- Create: `tests/Propel/Tests/Generator/Model/Diff/IndexComparatorPhaseDTest.php`
- Modify: `tests/snapshots/tracked-classes.txt` (refresh `Index` snapshot if accessors are new)

- [ ] **Step 1: Add `Index` accessors if needed**

Check whether `getWhereClause()` and `getIndexType()` already exist on `Index`. If not, add them as additive accessors.

- [ ] **Step 2: Extend `IndexComparator::computeDiff()`** to detect:
- `getWhereClause()` mismatch → partial-index drift.
- `getIndexType()` mismatch → `USING gin/gist/hash` change.
- Per-column expression-index mismatch (when a column entry is functional, not a bare name).

- [ ] **Step 3: Tests** — fixture A and B differing in WHERE clause / USING-clause / expression; assert diff captures it.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Model/Diff/IndexComparator.php src/Propel/Generator/Model/Index.php tests/Propel/Tests/Generator/Model/Diff/IndexComparatorPhaseDTest.php tests/snapshots/tracked-classes.txt tests/snapshots/Index.signatures.json
git commit -m "feat(diff/index): detect partial / expression / index-type drift"
```

---

### Task D.6.6: `ForeignKeyComparator` detects DEFERRABLE drift

**Files:**
- Modify: `src/Propel/Generator/Model/Diff/ForeignKeyComparator.php`
- Modify: `src/Propel/Generator/Model/ForeignKey.php` (add `getDeferrable()` / `getInitiallyDeferred()` accessors if absent)
- Modify: `resources/xsd/database.xsd` (optional `deferrable` and `initiallyDeferred` attributes on `<foreign-key>`)
- Create: `tests/Propel/Tests/Generator/Model/Diff/ForeignKeyComparatorPhaseDTest.php`
- Modify: `tests/snapshots/tracked-classes.txt` (refresh `ForeignKey` snapshot if accessors are new)
- Modify: `tools/xsd-additivity-check.php` runs in CI — verify all 12 existing fixtures still validate.

- [ ] **Step 1: Wire `<foreign-key>` XML to read `deferrable` and `initiallyDeferred` attributes** (XSD additivity per umbrella §3.7)

- [ ] **Step 2: Extend `ForeignKeyComparator`** to detect mismatch on these accessors

- [ ] **Step 3: Verify XSD additivity** — all Phase A/B/C fixtures still validate.

- [ ] **Step 4: Tests + commit**

```bash
git add src/Propel/Generator/Model/Diff/ForeignKeyComparator.php src/Propel/Generator/Model/ForeignKey.php resources/xsd/database.xsd tests/Propel/Tests/Generator/Model/Diff/ForeignKeyComparatorPhaseDTest.php tests/snapshots/tracked-classes.txt tests/snapshots/ForeignKey.signatures.json
git commit -m "feat(diff/fk): detect DEFERRABLE / INITIALLY DEFERRED drift (PG)"
```

---

## Round 2 Review Checkpoint (end-phase, after D.4 + D.5 + D.6)

**Trigger:** all tasks D.1–D.6 landed; quality gates green; ready to merge.
**Reviewers (per umbrella §4.13.3 end-phase):** **all 5 standing** + **Behavior author / third-party-ecosystem reviewer** + **Code generator specialist**.
**Lens:**
- **Architecture:** is `CodeEmitter`'s API the right size after Sortable + NestedSet have actually used it? Are there sharp edges that surfaced during the refactor that should be filed for cleanup before merge? Did any builder method in `Generator/Builder/Om/` start adopting `CodeEmitter` opportunistically — if so, is that scope creep or natural validation?
- **BC:** is the NestedSet deprecation runway airtight (a real consumer with a 50,000-row scoped tree could migrate in a weekend)? Is the Timestampable native ON UPDATE flip honestly documented (raw-SQL UPDATE behavior change)? Are the new `Index::getWhereClause()` / `ForeignKey::getDeferrable()` accessors safe Tier 2 additions?
- **Quality:** baselines monotonic; coverage delta ≥ 0; mutation MSI ≥ 65 on touched files (Phase D threshold per umbrella §4.14); deptrac green; byte-identical golden-diff for Sortable + NestedSet refactor commits verified.
- **Performance:** `regen-golden.php` runtime within ±5% of Phase C end. The CodeEmitter abstraction MUST NOT regress generation time more than 5% — if it does (string concat being inherently fastest), the API may be wrong. Bench in `D-bench.md`.
- **Ambition:** every umbrella §6.3 behavior-keep-list polish item delivered (or documented in waivers): NestedSet deprecated ✓, Sortable refactored ✓, Timestampable native ON UPDATE ✓, AggregateColumn future-bridge documented ✓.
- **Behavior author / third-party-ecosystem:** would `propelorm/cookbook` or any active third-party Behavior break? CodeEmitter is internal, but the `Behavior` abstract class hooks (`objectMethods`, etc.) still work as before — confirm. Run the ecosystem advisory CI cell.
- **Code generator specialist:** byte-identical refactor verification on Sortable + NestedSet — `git diff` between pre-D.3 and post-D.3 golden is empty? Same for D.4? If non-empty, MUST-FIX.

**Outputs:** `docs/reviews/D-round-2-{architecture,bc,quality,performance,ambition,behaviors,code-gen}.md` + `D-round-2-summary.md`. Plus `D-bench.md`, `D-mutation.json`, `D-waivers.md`.

**Iteration budget:** 3 cycles. Maintainer-escalation triggers per umbrella §4.15.3.

**No Round 3 (post-merge canary).** Phase D is MEDIUM-RISK 2-round per umbrella §4.13.3 — Round 2 is the final gate before merge. Post-merge ecosystem advisory CI runs as a standing check; severity-1 regressions surface there and trigger out-of-band rollback per umbrella §7.1.

---

## Definition of Done (umbrella §4.9 — 15 boxes)

- [ ] All test matrix cells green: PHP {8.3, 8.4} × DB {agnostic, mysql, pgsql, sqlite} × Symfony {7.2, 7.latest} = 16 cells.
- [ ] `phpstan-baseline.neon` ≤ 403 lines (Phase C end; Phase D must not regress; ideally drawdown after Sortable + NestedSet refactor consolidates duplication).
- [ ] `psalm-baseline.xml` ≤ 1616 lines (Phase C end).
- [ ] Coverage delta ≥ 0%; floor 70% Runtime / 60% Generator / 70% generated bookstore output.
- [ ] Mutation MSI ≥ 65 on touched files (`CodeEmitter`, Sortable modifiers, NestedSet modifiers, Timestampable, MysqlSchemaParser, PgsqlSchemaParser, IndexComparator, ForeignKeyComparator).
- [ ] Deptrac green; 0 violations against 233 baseline.
- [ ] Performance benchmarks within ±5% of Phase C end. `regen-golden.php` runtime regression ≤ 5% (CodeEmitter overhead bound).
- [ ] `CHANGELOG.md` updated (Added: CodeEmitter, IndexComparator extensions, ForeignKeyComparator DEFERRABLE drift, `<foreign-key deferrable initiallyDeferred>` attributes, `--reverse-format` flag; Changed: reverse parsers migrated to INFORMATION_SCHEMA, Timestampable defaults to native ON UPDATE on MySQL/MariaDB; Deprecated: NestedSet behavior + runtime classes, `--reverse-format=legacy-show-create`; Refactored: Sortable + NestedSet behavior modifiers onto CodeEmitter byte-identical).
- [ ] Deprecation message audit clean (allowlist accepts new NestedSet + legacy-show-create + Timestampable-native-on-update-on-pg-fallback messages).
- [ ] Generated-code lint parity green (regenerated bookstore + `golden-nested-set` both pass phpcs + phpstan).
- [ ] Golden-file diff reviewed: Sortable refactor commits show ZERO diff (byte-identical contract); NestedSet refactor commits show ZERO diff against `golden-nested-set/`; Timestampable change shows expected `preUpdate()` body shrink on MySQL output.
- [ ] Phase plan updated with retrospective notes (this file, appended after Round 2).
- [ ] All review-round reports committed under `docs/reviews/D-round-{1,2}-*.md`; consolidated `D-summary.md`.
- [ ] Surgical-test battery executed: PBT (CodeEmitter indentation invariant + lint-clean output), mutation report, byte-identical golden-diff (Sortable + NestedSet), signature-diff (Tier 1 stable; new Tier 2 entries `CheckConstraint` and `JsonbOperator` from Phase C still stable; `Index` and `ForeignKey` snapshots refreshed for new accessors), differential test against Phase C end, ecosystem advisory CI run.
- [ ] All MUST-FIX closed; SHOULD-FIX closed or waived in `D-waivers.md`; iteration cycles within budget (3 per round).

---

## Risk Register (Phase D-specific)

1. **CodeEmitter API design — "string concat with extra steps" risk.** A naive emitter that just buffers strings and appends them in order is no improvement over the status quo. The abstraction MUST deliver: indented blocks (eliminate manual `"    "` prefixing), method-signature emission (eliminate ad-hoc docblock + signature concat), expression escaping (eliminate manual `addslashes` calls). **Mitigation:** Task D.1.4 (AutoAddPk POC, fall back to Timestampable if AutoAddPk is too small) is the early-warning system. If CodeEmitter doesn't meaningfully simplify Timestampable's `objectMethods()`, the API is wrong — fix BEFORE Sortable. Round 1 review's Code Generator specialist lens is the architectural backstop.

2. **Byte-identical refactor failure.** Even one whitespace drift between pre-refactor and post-refactor golden output fails the gate. Per-method-iterative discipline (D.3.1 step 2) is the only viable approach. Bulk port + debug consumes iteration budget per umbrella §4.15.3 and risks a maintainer-escalation. **Mitigation:** the per-method workflow is mandated, not advised. CI runs `git diff --quiet tests/Fixtures/bookstore/build/golden/` after each commit.

3. **NestedSet third-party-schema migration.** NestedSet is heavily used in production. The migration cookbook (D.2.3) must be airtight — concrete SQL for the lft/rgt → parent_id conversion, concrete `WITH RECURSIVE` examples for every generated method, concrete performance numbers. **Mitigation:** the Behavior author / third-party-ecosystem reviewer's Round 1 lens explicitly checks "could a real consumer migrate in a weekend"; if no, MUST-FIX the cookbook before progression to D.3.

4. **Reverse-parser rewrite without testcontainers.** Phase C deferred specifically because no testcontainers harness exists for round-trip PBT. Phase D ships the rewrite using fixture-row-based unit tests + a SQLite-only round-trip path. Real DBs (MySQL 8 / MariaDB 10.5+ / PG 14+ / PG 16) are NOT exercised in CI for this rewrite. Risk: hidden regressions on real DBs that fixtures don't catch. **Mitigation:** keep the OLD `SHOW CREATE TABLE` regex path behind `--reverse-format=legacy-show-create` (D.6.4) for one minor before removal in 4.0. Consumer projects encountering regressions can opt into the legacy path. Manual smoke against a real MySQL 8 + PG 14 instance is a reviewer responsibility (not CI), captured in Round 2 specialist sign-off. Testcontainers-PBT explicitly scoped to a future cycle (Phase D' / stretch); a tracking issue is filed before Phase D merge.

5. **Timestampable native ON UPDATE — raw-SQL UPDATE behavior change.** Apps that do raw `UPDATE` outside Propel previously DID NOT update `updated_at`; under the new default they DO (on MySQL/MariaDB). This is a real behavior change for raw-SQL paths. Custom-trigger setups break (DB default fights with the trigger). **Mitigation:** `<parameter name="use_native_on_update" value="false"/>` opt-out (D.5.1) preserves the old behavior; migration cookbook (D.5.2) flags both raw-SQL and custom-trigger setups; `migration:diff` produces an explicit `MODIFY COLUMN` statement so the operator sees the change before applying.

6. **Tier 2 SPI growth from Index + ForeignKey accessors.** Adding `getWhereClause()` / `getIndexType()` / `getDeferrable()` / `getInitiallyDeferred()` to Tier 2 model classes commits these shapes for the 3.x line. **Mitigation:** signature-diff gate enforces; the additions are pure additive (no removal, no narrowing). Naming follows existing accessor patterns on `Index` / `ForeignKey`.

7. **CodeEmitter LOC drawdown not realized.** Sortable (~2,011 LOC) + NestedSet (~3,036 LOC) = 5,047 LOC pre-refactor. A successful CodeEmitter refactor SHOULD produce a measurable LOC drawdown (template patterns reduce repetition). If post-refactor LOC ≥ pre-refactor LOC, the abstraction isn't earning its keep. **Mitigation:** D.3.5 + D.4.5 capture LOC delta; Round 2 ambition reviewer flags non-drawdown as a SHOULD-FIX (waivable only with a documented "the abstraction is necessary for Phase G's lazy-object refactor" forward-reference per umbrella §4.15.4).

8. **Performance regression from CodeEmitter overhead.** String concatenation in a tight loop is empirically the fastest PHP code-emission technique. CodeEmitter's array-of-lines + `implode("\n", ...)` approach has small per-call overhead (~µs scale). Generation runs over ~400 generated files; aggregate overhead must stay under the 5% performance budget. **Mitigation:** `D-bench.md` measures `regen-golden.php` runtime pre- and post-refactor. If regression > 5%, profile and optimize the emitter (e.g., StringBuilder-style write to a single buffer). Round 2 performance reviewer sign-off required.

9. **Ecosystem CI false-positive on NestedSet deprecation noise.** Real downstream packages with NestedSet usage will see `trigger_deprecation` fire under `failOnDeprecation=true`. Their CI breaks transiently. **Mitigation:** the ecosystem advisory CI run is documented as advisory (non-blocking) per umbrella §4.14; downstream packages need to allowlist the NestedSet deprecation in their `tests/deprecations.allowlist.json` — flagged as a forward-comm in `MIGRATION-FROM-PRE-AI.md`'s "Ecosystem coordination" sub-section, written at task D.2.3 time.

---

## Out of Scope (explicit)

Phase D explicitly does NOT do:

- **Lazy-object collections / asymmetric-visibility / property hooks** — Phase G (PHP 8.4 bump).
- **Connection collapse + decorator chain + replica routing** — Phase E.
- **Criteria split + enums-alongside + typed Criterion DSL** — Phase F.
- **Migration tooling overhaul (dry-run, squash, baseline, drift detection)** — Phase H. Phase D's only migration touch is the `IndexComparator` + `ForeignKeyComparator` extensions (carry-forwards from Phase C).
- **Multi-tenancy / sharding** — addon-package-only, post-4.0 per umbrella §1.3.
- **Observability hooks (`TelemetryInterface`)** — Phase I.
- **Worker-mode / long-running-process safety** — Phase J.
- **Validate / QueryCache behaviors** — already DELETED in Phase A. Out of scope.
- **AggregateColumn / AggregateMultipleColumns native-generated-column bridge** — documented as future bridge (D.5.3) but not implemented. Tracked for Phase D' / H.
- **Full testcontainers-PBT round-trip for reverse parsers** — explicitly carved out into a future cycle (Phase D' / stretch). Phase D ships fixture-row-based unit tests + SQLite-only round-trip.
- **JSON shape DSL / value-object generation** — stretch goal not in any current phase per umbrella §1.3.
- **Migration cookbook for AggregateColumn → generated columns** — out of scope; documented as future.
- **`oracle` / `mssql` / `sqlsrv` adapter resurrection** — removed; not resurrected per umbrella §1.2.
- **YAML / PHP schema parsers** — XML stays primary per umbrella §1.3.

---

## Self-Review Checklist (writing-plans skill)

**Spec coverage check:**

| Umbrella spec promise (Phase D from §5 + §6.3) | Task |
|---|---|
| CodeEmitter introduction (Phase B' folded into D) | D.1.1, D.1.2, D.1.3, D.1.4 |
| Sortable refactor onto CodeEmitter templates (~2,011 LOC) | D.3.1, D.3.2, D.3.3, D.3.4, D.3.5 |
| NestedSet refactor onto CodeEmitter templates (~3,036 LOC) | D.4.1, D.4.2, D.4.3, D.4.5 |
| NestedSet deprecation (Tier 2 runway, recursive-CTE replacement) | D.2.1, D.2.2, D.2.3, D.2.4, D.4.4 |
| Timestampable: native `ON UPDATE CURRENT_TIMESTAMP` default | D.5.1, D.5.2 |
| AggregateColumn / AggregateMultipleColumns future bridge | D.5.3 |
| Phase C deferred: MysqlSchemaParser INFORMATION_SCHEMA migration | D.6.1 |
| Phase C deferred: PgsqlSchemaParser INFORMATION_SCHEMA migration | D.6.2 |
| Phase C deferred: UUID heuristic on reverse | D.6.3 |
| Phase C deferred: `--reverse-format=legacy-show-create` flag | D.6.4 |
| Phase C deferred: IndexComparator extensions | D.6.5 |
| Phase C deferred: ForeignKeyComparator DEFERRABLE drift | D.6.6 |
| Per-task golden regen + lint parity (umbrella §4.6, §4.7) | every D.3, D.4, D.5 task commits regenerated tree |
| Tier 1 + Tier 2 BC contract preserved (umbrella §3.1, §3.2) | D.2.4 (NestedSet runtime Tier 2), D.6.5/D.6.6 (Index/ForeignKey accessor additions) |
| XSD additivity preserved (umbrella §3.7) | D.6.6 (`<foreign-key deferrable initiallyDeferred>` optional) |

**Type-consistency check:** All file paths verified against `src/Propel/Generator/Behavior/{NestedSet,Sortable,Timestampable,AutoAddPk,AggregateColumn,AggregateMultipleColumns}/`, `src/Propel/Generator/Reverse/`, `src/Propel/Generator/Model/Diff/`, `src/Propel/Generator/Builder/Util/`, `src/Propel/Runtime/ActiveRecord/{NestedSetRecursiveIterator,NestedSetNodeInterface}.php`, `resources/xsd/database.xsd`. LOC counts verified against `wc -l` on the modifier files (49,209 / 33,219 / 25,756 / 19,697 / 2,256 bytes pre-refactor).

**Placeholder scan:** No TBDs; no "TODO" markers; every task has a primary file, a concrete change, a verify command, and a commit-message stub. The single discoverability decision (AutoAddPk vs Timestampable as POC consumer in D.1.4) is documented as a runtime-resolved choice with an explicit fallback path.

---

## Execution Handoff

Phase D is MEDIUM-RISK 2-round per umbrella §4.13.3. Recommended execution shape:

**1. Subagent-Driven (recommended for D.1, D.2, D.5, D.6)** — fresh subagent per task; well-scoped CodeEmitter unit work, NestedSet deprecation triggers, Timestampable behavior parameter, mechanical reverse-parser query swaps. Subagents benefit from clean context per task.

**2. Inline Execution (recommended for D.3 + D.4)** — Sortable + NestedSet refactors are deep per-method iterative work. The same session benefits from holding the modifier-method context across sequential method ports. Per-method byte-identical golden discipline is unforgiving of context loss.

**3. Mixed (recommended for D.6 reverse-parser sub-track)** — inline for the cross-cutting `AbstractSchemaParser` shared-helper extraction (D.6.3); subagent for the per-parser query rewrites (D.6.1, D.6.2).

Always: `tests/bin/setup.sqlite.sh` BEFORE `php tools/regen-golden.php`. Working tree must be empty after a regen run; if it isn't, the generator is non-deterministic and a MUST-FIX is logged before continuing (Phase B Round 2 cycle 1 lesson; Phase C reaffirmed).

Per-method-iterative discipline for D.3 + D.4: ONE method port → regen → byte-identical golden assertion → next method. Never bulk-port. Iteration budget per umbrella §4.15.3 is 3 cycles per round; bulk-then-debug burns the budget on D.3 alone.

---

## Phase D Retrospective

(To be appended after Round 2 closes. Captures: LOC drawdown actually achieved, CodeEmitter API surprises, golden-diff drift incidents, consumer-deprecation noise from NestedSet, performance delta from CodeEmitter overhead, what should be in Phase D' / Phase H follow-up.)
