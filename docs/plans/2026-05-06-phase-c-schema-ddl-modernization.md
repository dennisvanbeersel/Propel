# Phase C: Schema Model + DDL Features + Reverse Parser Modernization

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended) or `superpowers:executing-plans` to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Date:** 2026-05-06
**Branch:** `ar-rewrite` (commit; do NOT push)
**Goal:** Land the schema-format and DDL capability set the umbrella spec promises in §6.4: native `JSON`/`JSONB`, generated columns (`virtual`/`stored`), `<check>` constraints, `INVISIBLE` columns, PostgreSQL `IDENTITY` columns. Migrate the MySQL/PostgreSQL reverse parsers off `SHOW CREATE TABLE` regex onto `INFORMATION_SCHEMA` queries. Extend the Diff comparator for collation / comment / CHECK / partial-index / expression-index / DEFERRABLE drift.

**Architecture:** Three concentric rings.
1. **Schema XSD + Model classes** — declarative shape (`Column`, `Table`, `Domain`, new `CheckConstraint`). Adds attributes/elements only as `minOccurs=0` per umbrella §3.7.
2. **Platform DDL emitters** — concrete SQL generation for MySQL 8 / MariaDB 10.5 / PG 14+. SQLite kept-but-frozen (no new DDL features) per umbrella §1.2.
3. **Reverse parsers** — read `INFORMATION_SCHEMA.COLUMNS` / `pg_catalog.*` to produce schema XML symmetrical to the forward direction. Round-trip property test: parse XML → reverse-engineer → re-parse → equal up to normalization (umbrella §4.11).

The Diff comparator extension keeps migrations honest: collation drift, comment drift, CHECK drift, expression-index drift, partial-index drift, DEFERRABLE-FK drift all surface in `migration:diff`.

**Tech Stack:** PHP 8.3, MySQL 8.0+, MariaDB 10.5+, PostgreSQL 14+, SQLite (frozen), PHPUnit 11, PHPStan level 7, Spryker code-sniffer, Infection ^0.29, Deptrac ^2, innmind/black-box ^6.

**Reference spec:** `docs/plans/2026-05-06-modernization-umbrella-spec.md` (umbrella).
**Predecessor:** `docs/plans/2026-05-06-phase-a-foundations.md` (Phase A) and `docs/plans/2026-02-03-builder-om-modernization.md` + `docs/plans/2026-05-06-phase-b-amendments.md` (Phase B).
**Phase A summary:** `docs/PHASE-A-SUMMARY.md`.
**Phase B summary:** `docs/PHASE-B-SUMMARY.md`.

**Review tier (per umbrella §4.13.3):** HIGH-RISK — 3 rounds (mid-phase + end-phase pre-merge + post-merge canary).
**Specialists (per umbrella §4.13.2):** standing 5 reviewers + **DBA specialist** (MySQL 8 + PG 14 + MariaDB 10.5 DDL correctness, INFORMATION_SCHEMA coverage) + **Schema-migration safety reviewer** (Diff comparator output, migration round-trip).

**Test/quality gate state at start of phase (carried from Phase B end):**
- `phpstan-baseline.neon` 403 lines, 0 blanket regex.
- `psalm-baseline.xml` 1616 lines, suppression-clean.
- Deptrac 0 violations against 233-line baseline.
- All `failOn*` flags strict.
- Test suite: 2425 / 5196 / 21 GREEN.
- Tier 1 signature snapshots: 53 stable.
- Bookstore golden tree: 399 files, refreshed-per-task contract enforced.

---

## File Structure (created or modified by this phase)

**Created:**
- `src/Propel/Generator/Model/CheckConstraint.php` — new model class, mirrors `Index`/`Unique` shape.
- `src/Propel/Generator/Model/Diff/CheckConstraintComparator.php` — paired with `CheckConstraint` for diff.
- `src/Propel/Runtime/ActiveQuery/Operator/JsonbOperator.php` — PG `?`, `?&`, `?|`, `@>`, `<@` enum + helper for parameterized binding.
- `tests/Fixtures/schemas/phase-c/` — minimal XML fixtures exercising every new attribute (one schema per capability).
- `tests/Fixtures/reverse/mysql-information-schema/` — captured MySQL 8 `INFORMATION_SCHEMA` query results as JSON for unit-level reverse-parser tests (no live DB needed).
- `tests/Fixtures/reverse/pgsql-information-schema/` — paired PG fixtures.
- `tests/PropertyTests/Schema/RoundTripParseTest.php` — XML → reverse → re-parse PBT (§4.11).
- `tests/PropertyTests/Schema/DiffSymmetryTest.php` — `diff(A, B) == inverse(diff(B, A))` PBT for the new Diff comparator extensions.
- `docs/reviews/C-round-1-*.md`, `C-round-2-*.md`, `C-round-3-*.md`, `C-summary.md`, `C-iterations.md`, `C-waivers.md`, `C-bench.md`, `C-mutation.json`.

**Modified:**
- `resources/xsd/database.xsd` — XSD additivity (umbrella §3.7); new attributes `generated`, `expression`, `invisible` on `<column>`; new `<check>` element on `<table>` and `<column>`; `default_datatypes` simpleType extended with `JSON`, `JSONB`, `INET`, `CIDR`, `TSVECTOR`. **Never remove enumeration values; never raise minOccurs.**
- `src/Propel/Generator/Model/Column.php` — `isGenerated(): bool`, `getGenerationKind(): ?string` (`'virtual'|'stored'|null`), `getGenerationExpression(): ?string`, `isInvisible(): bool`. New optional XML attributes propagated through `loadMapping`.
- `src/Propel/Generator/Model/Table.php` — `getCheckConstraints(): array<CheckConstraint>`, `addCheckConstraint(CheckConstraint $c): self`. `loadMapping` reads nested `<check>` elements.
- `src/Propel/Generator/Model/Domain.php` — recognize `JSON`, `JSONB`, `INET`, `CIDR`, `TSVECTOR` as native types (PG-mapped); MySQL maps `JSONB` → `JSON` with deprecation warning (since MySQL has no JSONB).
- `src/Propel/Generator/Model/PropelTypes.php` — add constants `JSON`, `JSONB`, `INET`, `CIDR`, `TSVECTOR` (additive only — Phase A's `BU_DATE`/`BU_TIMESTAMP`/`BOOLEAN_EMU` deprecation triggers stay intact).
- `src/Propel/Generator/Platform/DefaultPlatform.php` — abstract fall-through hooks: `getColumnGeneratedDDL(Column $c): string`, `getColumnInvisibleDDL(Column $c): string`, `getCheckConstraintDDL(CheckConstraint $cc): string`. Default empty/throw-not-supported per platform's stance.
- `src/Propel/Generator/Platform/MysqlPlatform.php` — emit `GENERATED ALWAYS AS (expr) {VIRTUAL|STORED}`, `INVISIBLE`, `CHECK (expr)`, `JSON` column type.
- `src/Propel/Generator/Platform/PgsqlPlatform.php` — emit `GENERATED ALWAYS AS (expr) STORED` (PG only supports STORED), `JSONB` native, `GENERATED ALWAYS AS IDENTITY` for PK, `CHECK (expr)`. Replace `serial`/`bigserial` emission with `IDENTITY` for new schemas.
- `src/Propel/Generator/Platform/SqlitePlatform.php` — accept `JSON` column type as text-affinity passthrough (ATTACH the type label without enforcing), explicitly **reject** `generated="stored"` and `<check>` with a clear "SQLite is frozen — use MySQL/PG for these features" exception.
- `src/Propel/Generator/Reverse/MysqlSchemaParser.php` — replace `SHOW CREATE TABLE` regex with `INFORMATION_SCHEMA.COLUMNS` + `INFORMATION_SCHEMA.CHECK_CONSTRAINTS` + `INFORMATION_SCHEMA.STATISTICS` queries. Detect `IS_GENERATED`, `GENERATION_EXPRESSION`, `EXTRA LIKE '%INVISIBLE%'`, JSON columns, UUID `BINARY(16)` columns by `COLUMN_COMMENT` / `DATA_TYPE` + check-constraint inference.
- `src/Propel/Generator/Reverse/PgsqlSchemaParser.php` — `pg_attribute` + `pg_attrdef` + `pg_constraint` + `information_schema.check_constraints` + `pg_indexes` partial/expression detection. Detect IDENTITY (`attidentity = 'a'/'d'`), generated (`attgenerated = 's'`), JSONB native, partial indexes via `indpred`, expression indexes via `indexprs`.
- `src/Propel/Generator/Reverse/SqliteSchemaParser.php` — minimal touch: only ensure existing parsing still passes round-trip PBT against frozen feature set. **No new feature support added.**
- `src/Propel/Generator/Reverse/AbstractSchemaParser.php` — protected helpers shared by MySQL/PG (e.g., `quoteIdentifier`, `parseGeneratedExpression`, `normalizeCheckExpression`).
- `src/Propel/Generator/Model/Diff/ColumnComparator.php` — detect collation drift (`getCollation()`), comment drift (`getDescription()`), CHECK drift, generated-expression drift, INVISIBLE drift.
- `src/Propel/Generator/Model/Diff/IndexComparator.php` — detect expression-index drift, partial-index `WHERE`-clause drift, index-type drift (`USING gin/gist/hash`).
- `src/Propel/Generator/Model/Diff/ForeignKeyComparator.php` — detect `DEFERRABLE`/`INITIALLY DEFERRED` drift.
- `src/Propel/Generator/Model/Diff/TableComparator.php` — wire in `CheckConstraintComparator`.
- `src/Propel/Generator/Builder/Om/QueryBuilder.php` — emit a `filterByXJsonbContains()`-family of helpers when a column is JSONB on PG (subject to specialist review — see C.3.2).
- `tests/Fixtures/bookstore/schema.xml` — add an opt-in JSONB/CHECK/generated-column fixture in a new `<table name="book_metadata">` to exercise the new emitters end-to-end through the bookstore pipeline.
- `tests/Fixtures/bookstore/build/golden/` — regenerated per-task; commit-with-source contract.
- `docs/MIGRATION-FROM-PRE-AI.md` — section "Schema feature additions in 3.x"; PG `serial` → `IDENTITY` migration cookbook; JSONB operator helper documentation; SQLite-frozen-features list.
- `docs/UPGRADE-3.0.md` — new capability summary.
- `docs/BACKWARD_COMPATIBILITY.md` — Tier 2 SPI commitment for `CheckConstraint` model class shape; XSD additivity restated for Phase C.
- `CHANGELOG.md` — Phase C entries under `[Unreleased]`.
- `tests/snapshots/tracked-classes.txt` — add `CheckConstraint`, `JsonbOperator` (Tier 2 SPI).
- `tests/agnostic.phpunit.xml` — register `tests/PropertyTests/Schema/` testsuite cell.

**Deleted:**
- None. Phase C is purely additive on the schema/DDL surface. Reverse-parser regex paths are replaced (not deleted) — kept as a fallback under a `--reverse-format=legacy-show-create` CLI flag for one minor before removal in 4.0 per umbrella §3.6 alias-then-kill discipline. (See C.4.5.)

---

## Group C.1: Schema XSD extensions (Tasks C.1.1–C.1.5)

**Verification after each task:** `composer test:agnostic` + `xmllint --schema resources/xsd/database.xsd tests/Fixtures/schemas/phase-c/<fixture>.xml --noout`.

**XSD discipline (umbrella §3.7):**
- New attributes: `use="optional"` (the XSD form of `minOccurs=0`).
- New child elements: `minOccurs="0"`.
- Never remove an existing enumeration value.
- Never tighten a type (e.g., never narrow `xs:string` to a regex).
- Existing valid schemas MUST remain valid.

---

### Task C.1.1: Add `JSON`, `JSONB`, `INET`, `CIDR`, `TSVECTOR` enumeration values to `default_datatypes`

**Files:**
- Modify: `resources/xsd/database.xsd` (the `default_datatypes` `xs:simpleType`, line ~20)
- Create: `tests/Fixtures/schemas/phase-c/json-jsonb-types.xml`

- [ ] **Step 1: Add enumeration entries**

In `default_datatypes`, append:
```xml
<xs:enumeration value="JSON"/>
<xs:enumeration value="JSONB"/>
<xs:enumeration value="INET"/>
<xs:enumeration value="CIDR"/>
<xs:enumeration value="TSVECTOR"/>
```
Maintain alphabetical order if present; otherwise group with other modern types. Do NOT touch the legacy `BU_DATE` / `BU_TIMESTAMP` / `BOOLEAN_EMU` / `OBJECT` / `PHP_ARRAY` entries (Phase A discipline).

- [ ] **Step 2: Write a fixture exercising each new type**

```xml
<!-- tests/Fixtures/schemas/phase-c/json-jsonb-types.xml -->
<database name="phase_c_types" defaultIdMethod="native">
    <table name="event_log">
        <column name="id" type="INTEGER" primaryKey="true" autoIncrement="true"/>
        <column name="payload_mysql" type="JSON"/>
        <column name="payload_pg" type="JSONB"/>
        <column name="source_ip" type="INET"/>
        <column name="source_subnet" type="CIDR"/>
        <column name="search_vector" type="TSVECTOR"/>
    </table>
</database>
```

- [ ] **Step 3: Validate fixture against XSD**

Run: `xmllint --schema resources/xsd/database.xsd tests/Fixtures/schemas/phase-c/json-jsonb-types.xml --noout`
Expected: PASS.

- [ ] **Step 4: Validate every existing fixture in `tests/Fixtures/` against the modified XSD**

Run: `find tests/Fixtures -name 'schema.xml' -exec xmllint --schema resources/xsd/database.xsd {} --noout \;`
Expected: PASS for every existing fixture (XSD additivity proof).

- [ ] **Step 5: Run agnostic tests**

Run: `composer test:agnostic`
Expected: GREEN.

- [ ] **Step 6: Commit**

```bash
git add resources/xsd/database.xsd tests/Fixtures/schemas/phase-c/json-jsonb-types.xml
git commit -m "feat(xsd): add JSON/JSONB/INET/CIDR/TSVECTOR to default_datatypes (additive)"
```

---

### Task C.1.2: Add `generated` + `expression` attributes on `<column>`

**Files:**
- Modify: `resources/xsd/database.xsd` (the `<column>` complexType)
- Create: `tests/Fixtures/schemas/phase-c/generated-columns.xml`

- [ ] **Step 1: Add attribute declarations to `<column>`**

```xml
<xs:attribute name="generated" use="optional">
    <xs:simpleType>
        <xs:restriction base="xs:string">
            <xs:enumeration value="virtual"/>
            <xs:enumeration value="stored"/>
        </xs:restriction>
    </xs:simpleType>
</xs:attribute>
<xs:attribute name="expression" type="xs:string" use="optional"/>
```

Document the cross-attribute constraint in an `<xs:annotation>`: "If `generated` is present, `expression` MUST be present. Validation enforced at model-load time, not in the XSD itself (XSD 1.0 cannot express conditional presence)."

- [ ] **Step 2: Write a fixture**

```xml
<column name="full_name" type="VARCHAR" size="200"
        generated="stored"
        expression="CONCAT(first_name, ' ', last_name)"/>
<column name="full_name_v" type="VARCHAR" size="200"
        generated="virtual"
        expression="CONCAT(first_name, ' ', last_name)"/>
```

- [ ] **Step 3: Validate**

`xmllint --schema resources/xsd/database.xsd tests/Fixtures/schemas/phase-c/generated-columns.xml --noout` → PASS.

- [ ] **Step 4: Re-validate all existing fixtures (additivity)**

Same find-loop as C.1.1. Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/xsd/database.xsd tests/Fixtures/schemas/phase-c/generated-columns.xml
git commit -m "feat(xsd): add optional generated/expression attributes on <column>"
```

---

### Task C.1.3: Add `invisible` attribute on `<column>`

**Files:**
- Modify: `resources/xsd/database.xsd`
- Create: `tests/Fixtures/schemas/phase-c/invisible-columns.xml`

- [ ] **Step 1: Add attribute declaration**

```xml
<xs:attribute name="invisible" type="xs:boolean" use="optional" default="false"/>
```

- [ ] **Step 2: Write fixture**

```xml
<column name="internal_audit_token" type="VARCHAR" size="64" invisible="true"/>
```

- [ ] **Step 3: Validate fixture + all existing fixtures**

Same as C.1.1.

- [ ] **Step 4: Commit**

```bash
git add resources/xsd/database.xsd tests/Fixtures/schemas/phase-c/invisible-columns.xml
git commit -m "feat(xsd): add optional invisible attribute on <column>"
```

---

### Task C.1.4: Add `<check>` element on `<table>` and `<column>`

**Files:**
- Modify: `resources/xsd/database.xsd`
- Create: `tests/Fixtures/schemas/phase-c/check-constraints.xml`

- [ ] **Step 1: Add the `<check>` element type**

```xml
<xs:complexType name="checkConstraint">
    <xs:attribute name="name" type="xs:string" use="optional"/>
    <xs:attribute name="expression" type="xs:string" use="required"/>
    <xs:attribute name="enforced" type="xs:boolean" use="optional" default="true"/>
</xs:complexType>
```

Add `<xs:element name="check" type="checkConstraint" minOccurs="0" maxOccurs="unbounded"/>` to BOTH the `<table>` complexType's content model AND the `<column>` complexType's content model. Column-scoped `<check>` is a shorthand whose `expression` may reference the column by name; table-scoped allows multi-column expressions.

- [ ] **Step 2: Fixture**

```xml
<table name="orders">
    <column name="id" type="INTEGER" primaryKey="true"/>
    <column name="quantity" type="INTEGER">
        <check expression="quantity > 0"/>
    </column>
    <column name="discount_pct" type="DECIMAL" size="5" scale="2"/>
    <check name="ck_orders_discount_range" expression="discount_pct BETWEEN 0 AND 100"/>
</table>
```

- [ ] **Step 3: Validate fixture + additivity sweep**

- [ ] **Step 4: Commit**

```bash
git add resources/xsd/database.xsd tests/Fixtures/schemas/phase-c/check-constraints.xml
git commit -m "feat(xsd): add <check> element on <table> and <column> (optional)"
```

---

### Task C.1.5: XSD additivity proof — every Phase A/B fixture re-validates

**Files:**
- Modify: `tools/xsd-additivity-check.php` (new helper, parallels `tools/check-baseline-monotonic.php`)
- Create: `.github/workflows/quality-gates.yml` job `xsd-additivity`

- [ ] **Step 1: Write helper script**

Walk every `tests/Fixtures/**/schema.xml`; validate against `resources/xsd/database.xsd`. Exit non-zero on any failure. Print per-file PASS/FAIL.

- [ ] **Step 2: Wire into CI**

New job `xsd-additivity` in `.github/workflows/quality-gates.yml`:
```yaml
xsd-additivity:
  runs-on: ubuntu-latest
  steps:
    - uses: actions/checkout@v4
    - run: php tools/xsd-additivity-check.php
```

- [ ] **Step 3: Run locally**

`php tools/xsd-additivity-check.php` → PASS.

- [ ] **Step 4: Commit**

```bash
git add tools/xsd-additivity-check.php .github/workflows/quality-gates.yml
git commit -m "ci: add xsd-additivity gate (every existing fixture validates against new XSD)"
```

---

## Group C.2: Schema model classes (Tasks C.2.1–C.2.5)

**Verification after each task:** `composer test:agnostic` + `composer stan` (baselines monotonic).

---

### Task C.2.1: `Column::isGenerated()` / `getGenerationKind()` / `getGenerationExpression()`

**Files:**
- Modify: `src/Propel/Generator/Model/Column.php`
- Create: `tests/Propel/Tests/Generator/Model/ColumnGeneratedTest.php`

- [ ] **Step 1: Add private properties**

```php
private ?string $generationKind = null; // 'virtual'|'stored'|null
private ?string $generationExpression = null;
```

- [ ] **Step 2: Update `loadMapping(array $attributes)` to read `generated` + `expression` attributes**

Validation: if `generated` is set, `expression` MUST be set; throw `SchemaException` (existing class) with file:line context if not.

Validation: `expression` without `generated` is allowed (forward-compat for future use cases) but emits `trigger_deprecation('maturix/propel', '3.0', '<column expression> without <generated> has no effect')`.

- [ ] **Step 3: Add public getters**

```php
public function isGenerated(): bool { return $this->generationKind !== null; }
public function getGenerationKind(): ?string { return $this->generationKind; }
public function getGenerationExpression(): ?string { return $this->generationExpression; }
```

- [ ] **Step 4: Write unit tests**

Cover: valid stored, valid virtual, missing-expression error, expression-without-generated deprecation, getter return values.

- [ ] **Step 5: Run**

`composer test:agnostic` + `composer stan`. Baselines monotonic.

- [ ] **Step 6: Commit**

```bash
git add src/Propel/Generator/Model/Column.php tests/Propel/Tests/Generator/Model/ColumnGeneratedTest.php
git commit -m "feat(model): Column generated-column accessors (isGenerated/kind/expression)"
```

---

### Task C.2.2: `Column::isInvisible()` accessor

**Files:**
- Modify: `src/Propel/Generator/Model/Column.php`
- Modify: `tests/Propel/Tests/Generator/Model/ColumnGeneratedTest.php` (extend)

- [ ] **Step 1: Add `private bool $invisible = false;` + `loadMapping` read of `invisible` attribute (boolean coercion via existing `booleanValue` helper)**

- [ ] **Step 2: Add `public function isInvisible(): bool { return $this->invisible; }`**

- [ ] **Step 3: Tests** — default false, true when set, INVISIBLE + PK combination produces `SchemaException` (PKs cannot be invisible per MySQL 8 / MariaDB 10.5).

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Model/Column.php tests/Propel/Tests/Generator/Model/ColumnGeneratedTest.php
git commit -m "feat(model): Column::isInvisible() with PK-conflict validation"
```

---

### Task C.2.3: `CheckConstraint` model class

**Files:**
- Create: `src/Propel/Generator/Model/CheckConstraint.php`
- Create: `tests/Propel/Tests/Generator/Model/CheckConstraintTest.php`
- Modify: `tests/snapshots/tracked-classes.txt` (Tier 2 SPI snapshot)

- [ ] **Step 1: Define the class**

Mirror `Index` shape: `extends MappingModel`, has `name`, `expression`, `enforced` properties, `getName()`, `getExpression()`, `isEnforced()`, `setName(string)`, `setExpression(string)`, `setEnforced(bool)`. `loadMapping` reads matching XML attributes. Auto-name when missing: `'ck_' + parent_table_name + '_' + hash(expression)[:8]` via existing `ConstraintNameGenerator`.

- [ ] **Step 2: Add to tracked classes for signature-diff gate**

Append `Propel\Generator\Model\CheckConstraint` to `tests/snapshots/tracked-classes.txt`. Run `bin/propel internal:dump-signatures` to seed the snapshot file.

- [ ] **Step 3: Tests** — construct, set/get, auto-name when nameless, accepts SQL expressions verbatim (no escaping inside the model), `setEnforced(false)` honored.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Model/CheckConstraint.php tests/Propel/Tests/Generator/Model/CheckConstraintTest.php tests/snapshots/tracked-classes.txt tests/snapshots/CheckConstraint.signatures.json
git commit -m "feat(model): introduce CheckConstraint model class"
```

---

### Task C.2.4: `Table::getCheckConstraints()` + `addCheckConstraint()` + nested `<check>` parsing

**Files:**
- Modify: `src/Propel/Generator/Model/Table.php`
- Modify: `src/Propel/Generator/Model/Column.php` (column-scoped `<check>`)
- Create: `tests/Propel/Tests/Generator/Model/TableCheckConstraintTest.php`

- [ ] **Step 1: Wire `Table::loadMapping` to read nested `<check>` elements**

Use the existing nested-element pattern from `Index` (`addIndex()` from inside `loadMapping`). Maintain insertion order.

- [ ] **Step 2: Wire column-scoped `<check>` — Column::loadMapping reads child `<check>` and forwards to its `Table` parent with the column's name auto-prefixed in the expression context.**

Decision: column-scoped `<check>` is sugar; the constraint is stored on the `Table`, not the `Column`. `getCheckConstraints()` always returns a flat list. Document this in the docblock.

- [ ] **Step 3: Tests** — table-scoped, column-scoped, mixed, ordering preserved, unique-name enforcement (two constraints with the same name → `SchemaException`).

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Model/Table.php src/Propel/Generator/Model/Column.php tests/Propel/Tests/Generator/Model/TableCheckConstraintTest.php
git commit -m "feat(model): Table+Column nested <check> parsing into CheckConstraint list"
```

---

### Task C.2.5: `Domain` updates for JSON/JSONB/INET/CIDR/TSVECTOR

**Files:**
- Modify: `src/Propel/Generator/Model/Domain.php`
- Modify: `src/Propel/Generator/Model/PropelTypes.php`
- Create: `tests/Propel/Tests/Generator/Model/DomainJsonbTest.php`

- [ ] **Step 1: Add type constants in `PropelTypes`**

```php
public const JSON = 'JSON';
public const JSONB = 'JSONB';
public const INET = 'INET';
public const CIDR = 'CIDR';
public const TSVECTOR = 'TSVECTOR';
```

Add to the static `getPropelTypes()` array. Native PHP type for all five: `string` (handlers operate on serialized form; runtime layer offers a JSON helper but does not auto-decode — see C.3.2 for opt-in).

- [ ] **Step 2: Update `Domain::PROPEL_TYPES_TO_*` mappings**

For PG: `JSON → json`, `JSONB → jsonb`, `INET → inet`, `CIDR → cidr`, `TSVECTOR → tsvector`.
For MySQL: `JSON → JSON`, `JSONB → JSON` (with `trigger_deprecation` warning that the schema is using a PG-only type), `INET → VARBINARY(16)` with comment, `CIDR → VARBINARY(17)` with comment, `TSVECTOR → TEXT` with comment.
For SQLite: all five map to `TEXT` affinity (frozen-feature stance).

- [ ] **Step 3: Tests** — each type round-trips through the platform's `getDomain()` mapping; cross-platform mismatches surface as deprecations not errors.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Model/Domain.php src/Propel/Generator/Model/PropelTypes.php tests/Propel/Tests/Generator/Model/DomainJsonbTest.php
git commit -m "feat(model): Domain mappings for JSON/JSONB/INET/CIDR/TSVECTOR per platform"
```

---

## Round 1 Review Checkpoint (after C.1 + C.2)

**Trigger:** all tasks C.1.1–C.2.5 landed and green.
**Reviewers (per umbrella §4.13.3 mid-phase):** Architecture + BC + DBA specialist + Schema-migration safety reviewer (3–4 lenses).
**Lens:**
- **Architecture:** does `CheckConstraint`'s shape match `Index`/`Unique`? Is the column-scoped-vs-table-scoped sugar pattern clean?
- **BC:** does the XSD pass additivity (every Phase A/B fixture still validates)? Does the new `Column` API compose with existing builders without forcing them to know about generation?
- **DBA:** are `INVISIBLE`-on-PK rejection, `generated`-without-`expression` rejection, JSONB-on-MySQL deprecation message correct? Is the JSONB→JSON deprecation actionable?
- **Schema-migration safety:** is the new schema-load surface deterministic (e.g., `<check>` ordering)? Will reverse-engineered schemas re-load cleanly?

**Outputs:** `docs/reviews/C-round-1-architecture.md`, `C-round-1-bc.md`, `C-round-1-dba.md`, `C-round-1-migration-safety.md`, `C-round-1-summary.md`.
**Iteration budget:** 3 cycles. Track in `C-iterations.md`.

Findings tagged `MUST-FIX` block progression to C.3; `SHOULD-FIX` may be waived per umbrella §4.15.4.

---

## Group C.3: Platform DDL emission (Tasks C.3.1–C.3.6)

**Verification after each task:** `composer test:agnostic` + golden regen + `composer cs-check tests/Fixtures/bookstore/build/classes/`.

Per umbrella §4.7: every task that touches generator output regenerates the bookstore golden tree and commits it in the same commit.

---

### Task C.3.1: `MysqlPlatform` emits `JSON`, `INVISIBLE`, generated columns, CHECK

**Files:**
- Modify: `src/Propel/Generator/Platform/MysqlPlatform.php`
- Create: `tests/Propel/Tests/Generator/Platform/MysqlPlatformPhaseCTest.php`

- [ ] **Step 1: `getColumnDDL(Column $col)` extension**

Append in this exact order to match MySQL 8 grammar:
```
<type> <NOT NULL?> <DEFAULT?> <AUTO_INCREMENT?> <GENERATED ALWAYS AS (expr) {VIRTUAL|STORED}> <INVISIBLE?> <COMMENT?>
```

Helper methods:
```php
protected function getColumnGeneratedDDL(Column $col): string
{
    if (!$col->isGenerated()) return '';
    $kind = strtoupper($col->getGenerationKind());
    return ' GENERATED ALWAYS AS (' . $col->getGenerationExpression() . ') ' . $kind;
}

protected function getColumnInvisibleDDL(Column $col): string
{
    return $col->isInvisible() ? ' INVISIBLE' : '';
}
```

- [ ] **Step 2: `getCheckConstraintDDL(CheckConstraint $cc): string`**

```sql
CONSTRAINT ck_name CHECK (expression) {NOT ENFORCED?}
```

Wire from `getAddTableDDL()` so checks land inside the `CREATE TABLE` body (MySQL 8 does not allow standalone `ALTER TABLE ADD CHECK` reliably across all minor versions — keep them inline).

- [ ] **Step 3: JSON column type — confirm `Domain::JSON → 'JSON'` already emits correctly; no additional change beyond C.2.5**

- [ ] **Step 4: Tests** — assert SQL string output for: stored generated, virtual generated, INVISIBLE, table-level CHECK, column-level CHECK (sugar), JSON column.

- [ ] **Step 5: Regenerate bookstore golden + lint-parity**

Add new `book_metadata` table to `tests/Fixtures/bookstore/schema.xml` exercising stored generated + INVISIBLE + JSON.

```bash
tests/bin/setup.sqlite.sh
php tools/regen-golden.php
composer cs-check tests/Fixtures/bookstore/build/classes/
composer stan -- tests/Fixtures/bookstore/build/classes/
```

- [ ] **Step 6: Commit (with regenerated golden tree)**

```bash
git add src/Propel/Generator/Platform/MysqlPlatform.php tests/Propel/Tests/Generator/Platform/MysqlPlatformPhaseCTest.php tests/Fixtures/bookstore/schema.xml tests/Fixtures/bookstore/build/golden/
git commit -m "feat(platform/mysql): emit JSON, INVISIBLE, generated columns, CHECK constraints"
```

---

### Task C.3.2: `PgsqlPlatform` emits `JSONB`, generated columns, CHECK, IDENTITY

**Files:**
- Modify: `src/Propel/Generator/Platform/PgsqlPlatform.php`
- Create: `tests/Propel/Tests/Generator/Platform/PgsqlPlatformPhaseCTest.php`
- Create: `src/Propel/Runtime/ActiveQuery/Operator/JsonbOperator.php`

- [ ] **Step 1: PG generated-column DDL**

PG only supports STORED. If `getGenerationKind() === 'virtual'`, emit `STORED` and `trigger_deprecation('maturix/propel', '3.0', 'PostgreSQL has no virtual generated columns; emitting STORED instead')`. Match column DDL to:
```
<type> <COLLATE?> <NULL?> <DEFAULT?> <GENERATED { ALWAYS AS IDENTITY | ALWAYS AS (expr) STORED }>
```

- [ ] **Step 2: PG CHECK** — emit table-scoped `CONSTRAINT name CHECK (expr) [NOT VALID]`. PG does not support `NOT ENFORCED`; map our `enforced=false` to `NOT VALID` with a deprecation note that semantics differ slightly.

- [ ] **Step 3: PG IDENTITY for PK** — replace `serial`/`bigserial` emission with `GENERATED ALWAYS AS IDENTITY` for new schemas. Existing schemas using `<column type="INTEGER" autoIncrement="true">` continue to emit IDENTITY (the modernization). Old `serial` emission stays available behind a soon-to-be-deprecated flag `<vendor type="pgsql"><parameter name="legacy-serial" value="true"/></vendor>` for migration runway. Document in `MIGRATION-FROM-PRE-AI.md` (Task C.6.2).

- [ ] **Step 4: JSONB native type** — confirm Domain mapping from C.2.5 emits `jsonb` correctly; no extra change.

- [ ] **Step 5: `JsonbOperator` enum + helper for parameterized binding**

```php
enum JsonbOperator: string {
    case ContainsAll = '?&';
    case ContainsAny = '?|';
    case ContainsKey = '?';
    case JsonbContains = '@>';
    case JsonbContainedBy = '<@';
}
```

Helper builds `$column $op $bindParam` so the `?` operator is escaped as `??` on the PDO side (PDO uses `?` as positional placeholder; PG's literal `?` operator must be doubled per PG docs). Add helper static `JsonbOperator::buildClause(string $column, JsonbOperator $op, string $bindName): string`.

Add to `tests/snapshots/tracked-classes.txt` (Tier 2 SPI).

- [ ] **Step 6: Tests** — DDL output assertions for IDENTITY, STORED-generated, CHECK with NOT VALID, JSONB; `JsonbOperator` clause-building; PDO escape correctness for `?`.

- [ ] **Step 7: Regenerate golden + lint-parity**

- [ ] **Step 8: Commit**

```bash
git add src/Propel/Generator/Platform/PgsqlPlatform.php src/Propel/Runtime/ActiveQuery/Operator/JsonbOperator.php tests/Propel/Tests/Generator/Platform/PgsqlPlatformPhaseCTest.php tests/snapshots/tracked-classes.txt tests/snapshots/JsonbOperator.signatures.json tests/Fixtures/bookstore/build/golden/
git commit -m "feat(platform/pgsql): JSONB, generated columns, CHECK, IDENTITY + JsonbOperator helper"
```

---

### Task C.3.3: `SqlitePlatform` — accept frozen-passthrough, reject new features clearly

**Files:**
- Modify: `src/Propel/Generator/Platform/SqlitePlatform.php`
- Create: `tests/Propel/Tests/Generator/Platform/SqlitePlatformPhaseCTest.php`

- [ ] **Step 1: JSON column passthrough**

SQLite stores JSON as TEXT; emit the column as `JSON` (the type label is allowed in SQLite — affinity falls back to NUMERIC, but it's recognized syntax). No JSONB emission; if a schema declares JSONB on a SQLite-only setup, throw a clear `EngineException` pointing at `MIGRATION-FROM-PRE-AI.md`.

- [ ] **Step 2: Reject `generated="stored"` and `<check>`**

Per umbrella §1.2 ("SQLite ... freeze. No new DDL features."), SQLite emits a clear, actionable exception:
```
"SQLite is frozen in Propel 3.x. Generated columns / CHECK constraints / INVISIBLE / JSONB are MySQL/PG-only. See docs/MIGRATION-FROM-PRE-AI.md#sqlite-frozen-features."
```

- [ ] **Step 3: Tests** — JSON column emits TEXT-affinity-compatible SQL; generated/CHECK/INVISIBLE all throw with the documented message.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Platform/SqlitePlatform.php tests/Propel/Tests/Generator/Platform/SqlitePlatformPhaseCTest.php
git commit -m "feat(platform/sqlite): accept JSON passthrough; reject generated/CHECK/INVISIBLE per frozen-feature stance"
```

---

### Task C.3.4: `DefaultPlatform` abstract fall-through

**Files:**
- Modify: `src/Propel/Generator/Platform/DefaultPlatform.php`
- Modify: `src/Propel/Generator/Platform/PlatformInterface.php`

- [ ] **Step 1: Add SPI method signatures to `PlatformInterface`**

```php
public function getColumnGeneratedDDL(Column $col): string;
public function getColumnInvisibleDDL(Column $col): string;
public function getCheckConstraintDDL(CheckConstraint $cc): string;
public function supportsGeneratedColumns(): bool;
public function supportsInvisibleColumns(): bool;
public function supportsCheckConstraints(): bool;
```

- [ ] **Step 2: `DefaultPlatform` defaults**

`supports*` → `false`. `getColumn*DDL` → `''`. `getCheckConstraintDDL` → `throw new EngineException('Platform does not support CHECK constraints')`. MysqlPlatform / PgsqlPlatform override; SqlitePlatform overrides to throw with the frozen-features message.

- [ ] **Step 3: Tests** — interface contract test asserting every concrete platform implements the new methods.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Platform/DefaultPlatform.php src/Propel/Generator/Platform/PlatformInterface.php
git commit -m "feat(platform): SPI methods for generated/invisible/check DDL with default fall-through"
```

---

### Task C.3.5: JSONB query helper integration into QueryBuilder (opt-in)

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/QueryBuilder.php`
- Create: `tests/Propel/Tests/Generator/Builder/Om/QueryBuilderJsonbTest.php`

- [ ] **Step 1: When the column is JSONB on PG, generate `filterByXContainsKey(string $key)` / `filterByXContainsAll(array $keys)` / `filterByXContainsAny(array $keys)` / `filterByXJsonbContains(array $subset)` / `filterByXJsonbContainedBy(array $superset)`**

These delegate to `JsonbOperator::buildClause` and use `addUsingAlias($columnAlias, $value, $operatorClause)`. Each emitted method has a `: self` return type per umbrella §3.5.

For non-PG platforms, NONE of these methods are emitted — the schema target platform (or default) decides. Document this asymmetry in MIGRATION-FROM-PRE-AI.md.

- [ ] **Step 2: Tests** — emit-then-compile test on a fixture with a JSONB column; assert each filter generates the right SQL when invoked at runtime against a PG harness.

- [ ] **Step 3: Regenerate golden tree (BookMetadata table picks up these methods)**

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Builder/Om/QueryBuilder.php tests/Propel/Tests/Generator/Builder/Om/QueryBuilderJsonbTest.php tests/Fixtures/bookstore/build/golden/
git commit -m "feat(builder/query): generate JSONB filterByX* helpers on PG only"
```

---

### Task C.3.6: PG `serial`/`bigserial` legacy-flag plumbing

**Files:**
- Modify: `src/Propel/Generator/Platform/PgsqlPlatform.php`
- Modify: `docs/MIGRATION-FROM-PRE-AI.md`

- [ ] **Step 1: Honor `<vendor type="pgsql"><parameter name="legacy-serial" value="true"/>` on a `<column>` to opt back into `serial`/`bigserial` emission**

Triggers `trigger_deprecation('maturix/propel', '3.0', 'legacy-serial vendor flag — migrate to IDENTITY before 4.0')`.

- [ ] **Step 2: Document in `docs/MIGRATION-FROM-PRE-AI.md`**

Section "PG: serial → IDENTITY migration":
- Why: `serial` is deprecated in PG since 10; IDENTITY is the standard.
- How: drop the legacy flag; run `migration:diff`; review the generated ALTER (which is non-destructive).
- Cookbook: SQL snippet showing the equivalent of pre-existing `serial` columns getting an `IDENTITY` rewrite without data loss.

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Generator/Platform/PgsqlPlatform.php docs/MIGRATION-FROM-PRE-AI.md
git commit -m "feat(platform/pgsql): legacy-serial vendor flag for migration runway from serial to IDENTITY"
```

---

## Round 2 Review Checkpoint (mid-phase, after C.3)

**Trigger:** all tasks C.3.1–C.3.6 landed; bookstore regen idempotent.
**Reviewers (per umbrella §4.13.3 mid-phase):** Architecture + BC + DBA specialist + Schema-migration safety reviewer + Performance (DDL emission must not regress generation time on the bookstore fixture).
**Lens:**
- **DBA:** does emitted SQL run cleanly on MySQL 8.0.30, MariaDB 10.5, PG 14, PG 16? `tests/bin/setup.{mysql,pgsql}.sh` should be re-run end-to-end on the modified bookstore.
- **Schema-migration safety:** does `migration:diff` between an empty DB and the new bookstore produce semantically correct migration SQL? Does it survive a round-trip via `migration:up; migration:down`?
- **Architecture:** does the SPI in `PlatformInterface` cleanly express what every platform needs? Are SqlitePlatform's frozen-feature exceptions clean to catch (via a sentinel exception class)?
- **BC:** is the JSONB-on-MySQL deprecation message actionable? Is the legacy-serial flag's runway long enough?
- **Performance:** does the `regen-golden.php` runtime regress more than 5% vs Phase B end?

**Outputs:** `docs/reviews/C-round-2-{architecture,bc,dba,migration-safety,performance}.md` + `C-round-2-summary.md`.
**Iteration budget:** 3 cycles.

---

## Group C.4: Reverse parser INFORMATION_SCHEMA migration (Tasks C.4.1–C.4.5)

**Verification after each task:** `composer test:agnostic` + parser unit tests against fixtured INFORMATION_SCHEMA result rows.

The reverse parsers today rely on `SHOW CREATE TABLE` regex. That's brittle (locale-dependent quoting, MariaDB-vs-MySQL grammar drift, no easy access to generated/INVISIBLE/CHECK metadata). Phase C migrates both to explicit `INFORMATION_SCHEMA` queries (PG: `pg_catalog.*` + `information_schema.*`).

The legacy `SHOW CREATE TABLE` path stays available behind `--reverse-format=legacy-show-create` for one minor (4.0 removes it).

---

### Task C.4.1: `MysqlSchemaParser` migrates to INFORMATION_SCHEMA

**Files:**
- Modify: `src/Propel/Generator/Reverse/MysqlSchemaParser.php`
- Create: `tests/Fixtures/reverse/mysql-information-schema/columns.json`, `indexes.json`, `check_constraints.json`, `key_column_usage.json` — captured query results from a real MySQL 8 schema (committed; no live DB needed for unit tests).
- Create: `tests/Propel/Tests/Generator/Reverse/MysqlSchemaParserPhaseCTest.php`

- [ ] **Step 1: Replace `parseTables()` to issue `SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA = ?`**

- [ ] **Step 2: Replace per-table column parsing with `SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?`**

Map fields:
- `DATA_TYPE` → Domain mapping (with `CHARACTER_MAXIMUM_LENGTH`, `NUMERIC_PRECISION`, `NUMERIC_SCALE`)
- `IS_NULLABLE` → `setNotNull(! 'YES')`
- `COLUMN_DEFAULT` → `setDefaultValue($val)` (with proper handling of `current_timestamp()` etc.)
- `EXTRA LIKE '%auto_increment%'` → `setAutoIncrement(true)`
- `IS_GENERATED IN ('ALWAYS')` + `GENERATION_EXPRESSION` → `setGenerationKind('virtual'|'stored')` based on `EXTRA LIKE '%VIRTUAL%' / '%STORED%'`
- `EXTRA LIKE '%INVISIBLE%'` → `setInvisible(true)`
- `COLUMN_COMMENT` → `setDescription($val)`
- `COLLATION_NAME` → `setCollation($val)` (used by Diff comparator in C.5.1)

- [ ] **Step 3: Add `parseCheckConstraints(Table $table)` querying `information_schema.CHECK_CONSTRAINTS` (MySQL 8.0.16+ / MariaDB 10.2+) joined with `information_schema.TABLE_CONSTRAINTS`**

For each row, build a `CheckConstraint`, set its expression (after stripping the database-injected outer parens), set `enforced` from `ENFORCED = 'YES'`.

- [ ] **Step 4: Indexes — replace `SHOW INDEX` with `SELECT * FROM information_schema.STATISTICS`** preserving per-column ordering (`SEQ_IN_INDEX`).

- [ ] **Step 5: Foreign keys — replace `SHOW CREATE TABLE` regex extraction with `information_schema.REFERENTIAL_CONSTRAINTS` joined with `KEY_COLUMN_USAGE`**

- [ ] **Step 6: Unit tests using fixtured INFORMATION_SCHEMA result rows**

The fixtures are JSON-serialized arrays representing what the queries return; the test mock-injects them via a lightweight DataFetcher stub. Assertions: every field maps to the right Model setter; round-trip property test (C.4.4).

- [ ] **Step 7: Commit**

```bash
git add src/Propel/Generator/Reverse/MysqlSchemaParser.php tests/Fixtures/reverse/mysql-information-schema/ tests/Propel/Tests/Generator/Reverse/MysqlSchemaParserPhaseCTest.php
git commit -m "refactor(reverse/mysql): migrate from SHOW CREATE TABLE regex to INFORMATION_SCHEMA"
```

---

### Task C.4.2: `PgsqlSchemaParser` migrates to `pg_catalog` + `information_schema`

**Files:**
- Modify: `src/Propel/Generator/Reverse/PgsqlSchemaParser.php`
- Create: `tests/Fixtures/reverse/pgsql-information-schema/columns.json`, etc.
- Create: `tests/Propel/Tests/Generator/Reverse/PgsqlSchemaParserPhaseCTest.php`

- [ ] **Step 1: Tables — `SELECT * FROM information_schema.tables WHERE table_schema = current_schema()`**

- [ ] **Step 2: Columns — combine `information_schema.columns` + `pg_attribute` + `pg_attrdef` for default expressions**

Map:
- `attidentity = 'a'` → IDENTITY ALWAYS, `'d'` → IDENTITY BY DEFAULT (both → `setAutoIncrement(true)` + `setIdMethod('identity')`)
- `attgenerated = 's'` → STORED generated (`setGenerationKind('stored')`); PG has no virtual.
- `pg_attribute.attinhcount` for inheritance hints (preserved from existing path)
- `pg_collation.collname` for collation
- `obj_description(...)` for column comment

- [ ] **Step 3: CHECK constraints — `information_schema.check_constraints` joined with `pg_constraint` to get `pg_get_constraintdef`'s normalized expression**

- [ ] **Step 4: Indexes — `pg_index` + `pg_indexes` for partial (`indpred IS NOT NULL`) and expression (`indexprs IS NOT NULL`) detection**

- [ ] **Step 5: Foreign keys — `pg_constraint WHERE contype = 'f'`** including DEFERRABLE / INITIALLY DEFERRED detection (`condeferrable`, `condeferred`).

- [ ] **Step 6: Tests + commit**

```bash
git add src/Propel/Generator/Reverse/PgsqlSchemaParser.php tests/Fixtures/reverse/pgsql-information-schema/ tests/Propel/Tests/Generator/Reverse/PgsqlSchemaParserPhaseCTest.php
git commit -m "refactor(reverse/pgsql): migrate to pg_catalog + information_schema queries"
```

---

### Task C.4.3: UUID parsing on reverse — MySQL `BINARY(16)` heuristic

**Files:**
- Modify: `src/Propel/Generator/Reverse/MysqlSchemaParser.php`
- Modify: `src/Propel/Generator/Reverse/AbstractSchemaParser.php`
- Create: tests in `MysqlSchemaParserPhaseCTest.php` (extend)

- [ ] **Step 1: Add `protected function detectUuidColumn(array $infoSchemaRow): bool`**

Heuristic: `DATA_TYPE = 'binary' AND COLUMN_TYPE = 'binary(16)'` AND (column comment contains `'UUID'` OR column name matches `/(^|_)uuid$|_uuid_/i`). Match the inverse direction of the Phase B `MysqlUuidMigrationBuilder` discipline.

- [ ] **Step 2: PG side — `data_type = 'uuid'` → `setType('UUID')`** (PG has a native UUID type; trivial direct match).

- [ ] **Step 3: Tests** — fixture rows with each pattern; assertion that the produced `Column` has `type = UUID` and the appropriate vendor parameters set.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Reverse/MysqlSchemaParser.php src/Propel/Generator/Reverse/AbstractSchemaParser.php tests/Propel/Tests/Generator/Reverse/MysqlSchemaParserPhaseCTest.php
git commit -m "feat(reverse): UUID detection on MySQL BINARY(16) + PG native uuid"
```

---

### Task C.4.4: Round-trip property test (PBT) — XML → reverse → XML up to normalization

**Files:**
- Create: `tests/PropertyTests/Schema/RoundTripParseTest.php`
- Modify: `tests/agnostic.phpunit.xml` (register the testsuite cell if not yet)

- [ ] **Step 1: Use innmind/black-box to generate randomized but XSD-valid schemas** restricted to the Phase C feature subset (column types, generated, invisible, CHECK)

- [ ] **Step 2: For each generated schema:**
  1. Forward: emit DDL via `MysqlPlatform` (or `PgsqlPlatform`).
  2. Run DDL against an in-memory MySQL 8 / PG 14 (testcontainers-php; gated by env to allow local skip).
  3. Reverse: run `MysqlSchemaParser` (or `PgsqlSchemaParser`) against the live DB.
  4. Re-emit XML.
  5. Assert: original XML and re-emitted XML are equal **up to normalization** (sort attributes, strip whitespace, normalize element order on commutative children).

- [ ] **Step 3: Implement the normalization helper** as `tests/PropertyTests/Schema/SchemaNormalizer.php`

- [ ] **Step 4: Tests** — run with `--seed=fixed` for reproducibility; document seed in `docs/reviews/C-round-2-summary.md`.

- [ ] **Step 5: Commit**

```bash
git add tests/PropertyTests/Schema/ tests/agnostic.phpunit.xml
git commit -m "test(pbt): schema XML→DDL→reverse→XML round-trip property test"
```

---

### Task C.4.5: Legacy `--reverse-format=legacy-show-create` runway flag

**Files:**
- Modify: `src/Propel/Generator/Command/DatabaseReverseCommand.php`
- Modify: `src/Propel/Generator/Reverse/MysqlSchemaParser.php` (route to legacy method when flag set)
- Modify: `docs/MIGRATION-FROM-PRE-AI.md`

- [ ] **Step 1: Add `--reverse-format` option** with values `information-schema` (default) and `legacy-show-create`. Symfony `InputOption::VALUE_REQUIRED`.

- [ ] **Step 2: When `legacy-show-create`, dispatch to the pre-Phase-C parsing method** (kept as a private method in the parser; not deleted). `trigger_deprecation('maturix/propel', '3.0', '--reverse-format=legacy-show-create — removal targeted for 4.0')`.

- [ ] **Step 3: Document in `docs/MIGRATION-FROM-PRE-AI.md`** — when to use the flag (e.g., when downstream tooling depends on the older parser's quirks); concrete deadline.

- [ ] **Step 4: Tests** — flag-routing test asserting the legacy path is used when the option is set.

- [ ] **Step 5: Commit**

```bash
git add src/Propel/Generator/Command/DatabaseReverseCommand.php src/Propel/Generator/Reverse/MysqlSchemaParser.php docs/MIGRATION-FROM-PRE-AI.md
git commit -m "feat(reverse): runway flag --reverse-format=legacy-show-create with deprecation"
```

---

## Group C.5: Diff comparator extension (Tasks C.5.1–C.5.3)

**Verification after each task:** `composer test:agnostic` + diff fixtures.

The diff comparator today catches type/size/null/default/PK changes. Phase C extends it for collation drift, comment drift, CHECK constraint drift, expression-index / partial-index / index-type drift, and DEFERRABLE-FK drift. These are all things that surface on real schema migrations and were silently ignored pre-Phase-C.

---

### Task C.5.1: `ColumnComparator` detects collation/comment/CHECK/generated/INVISIBLE drift

**Files:**
- Modify: `src/Propel/Generator/Model/Diff/ColumnComparator.php`
- Modify: `src/Propel/Generator/Model/Diff/ColumnDiff.php` (capture additional changed-attribute keys)
- Create: `tests/Propel/Tests/Generator/Model/Diff/ColumnComparatorPhaseCTest.php`

- [ ] **Step 1: Extend `ColumnComparator::compareColumns(Column $a, Column $b): bool`**

Detect:
- `getCollation()` mismatch (existing accessor; nothing new on Column needed)
- `getDescription()` mismatch (column-comment drift)
- `getCheckConstraints()` mismatch (set comparison; uses `CheckConstraint::isEquivalent()` from C.2.3)
- `getGenerationKind()` / `getGenerationExpression()` mismatch
- `isInvisible()` mismatch

For each, push the changed key into `ColumnDiff::$changedProperties` so downstream rendering can emit appropriate ALTER fragments.

- [ ] **Step 2: Tests for each drift type**

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Generator/Model/Diff/ColumnComparator.php src/Propel/Generator/Model/Diff/ColumnDiff.php tests/Propel/Tests/Generator/Model/Diff/ColumnComparatorPhaseCTest.php
git commit -m "feat(diff/column): detect collation, comment, CHECK, generated, INVISIBLE drift"
```

---

### Task C.5.2: `IndexComparator` detects expression-index / partial-index / index-type drift

**Files:**
- Modify: `src/Propel/Generator/Model/Diff/IndexComparator.php`
- Modify: `src/Propel/Generator/Model/Index.php` (already has `getColumns()` but may need `getWhereClause()` and `getIndexType()` accessors if not present)
- Create: `tests/Propel/Tests/Generator/Model/Diff/IndexComparatorPhaseCTest.php`

- [ ] **Step 1: Add `Index::getWhereClause(): ?string` and `Index::getIndexType(): ?string`**

If these accessors already exist, skip. Add to `tracked-classes.txt` if newly introduced.

- [ ] **Step 2: Extend `IndexComparator::computeDiff()`**

Detect:
- `getWhereClause()` mismatch → partial-index drift
- `getIndexType()` mismatch → `USING gin/gist/hash` change
- Expression-index per-column expression mismatch (when one or more columns are functional, not bare names)

- [ ] **Step 3: Tests** — fixture A and B differing in WHERE clause / USING-clause / expression; assert diff captures it.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Model/Diff/IndexComparator.php src/Propel/Generator/Model/Index.php tests/Propel/Tests/Generator/Model/Diff/IndexComparatorPhaseCTest.php
git commit -m "feat(diff/index): detect partial / expression / index-type drift"
```

---

### Task C.5.3: `ForeignKeyComparator` detects DEFERRABLE drift

**Files:**
- Modify: `src/Propel/Generator/Model/Diff/ForeignKeyComparator.php`
- Modify: `src/Propel/Generator/Model/ForeignKey.php` (add `getDeferrable()` / `getInitiallyDeferred()` accessors if not present)
- Create: `tests/Propel/Tests/Generator/Model/Diff/ForeignKeyComparatorPhaseCTest.php`

- [ ] **Step 1: Wire `<foreign-key>` XML to read `deferrable` and `initiallyDeferred` attributes**

Add to XSD as optional attributes (companion to C.1; if not yet added, do so now in this task to keep the XSD additivity history clean).

- [ ] **Step 2: Extend `ForeignKeyComparator`** to detect mismatch on these accessors.

- [ ] **Step 3: Tests + commit**

```bash
git add src/Propel/Generator/Model/Diff/ForeignKeyComparator.php src/Propel/Generator/Model/ForeignKey.php resources/xsd/database.xsd tests/Propel/Tests/Generator/Model/Diff/ForeignKeyComparatorPhaseCTest.php
git commit -m "feat(diff/fk): detect DEFERRABLE / INITIALLY DEFERRED drift (PG)"
```

---

## Group C.6: PG `serial` → `IDENTITY` migration helper (Tasks C.6.1–C.6.2)

**Verification after each task:** `composer test:agnostic` + manual `migration:diff` smoke on a serial→identity test schema.

---

### Task C.6.1: `migration:diff` emits IDENTITY for new schemas; converts existing serial via opt-in

**Files:**
- Modify: `src/Propel/Generator/Manager/MigrationManager.php`
- Create: `tests/Propel/Tests/Generator/Manager/MigrationManagerSerialIdentityTest.php`

- [ ] **Step 1: When generating migration SQL for PG, emit `GENERATED ALWAYS AS IDENTITY` for new auto-increment PKs (default behavior)**

- [ ] **Step 2: For existing schemas — detect `serial`/`bigserial` columns in the from-DB state and emit the IDENTITY conversion as a migration step ONLY when the user passes `--convert-serial-to-identity`**. Without the flag, leave the existing `serial` columns untouched (BC).

The conversion SQL (per PG docs):
```sql
ALTER TABLE foo ALTER COLUMN id DROP DEFAULT;
DROP SEQUENCE foo_id_seq;
ALTER TABLE foo ALTER COLUMN id ADD GENERATED ALWAYS AS IDENTITY (
    START WITH (SELECT max(id) + 1 FROM foo)
);
```

- [ ] **Step 3: Tests** — mock from-state with `serial`, assert with-flag emits the conversion, without-flag emits no DROP.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Generator/Manager/MigrationManager.php tests/Propel/Tests/Generator/Manager/MigrationManagerSerialIdentityTest.php
git commit -m "feat(migration): --convert-serial-to-identity flag for PG migration tooling"
```

---

### Task C.6.2: Document the migration in `docs/MIGRATION-FROM-PRE-AI.md`

**Files:**
- Modify: `docs/MIGRATION-FROM-PRE-AI.md`

- [ ] **Step 1: Add section "PG: serial / bigserial → IDENTITY"** with:
- Why migrate (deprecated since PG 10; standard SQL; cleaner ownership semantics).
- How to migrate (step-by-step using `--convert-serial-to-identity`).
- Concrete cookbook for a 50-million-row table (run during low traffic; expect ~2s downtime per ALTER on PG 14+; no full table rewrite).
- Rollback plan (recreate sequence + restore default).

- [ ] **Step 2: Commit**

```bash
git add docs/MIGRATION-FROM-PRE-AI.md
git commit -m "docs(migration): cookbook for PG serial → IDENTITY conversion"
```

---

## Round 3 Review Checkpoint (end-phase pre-merge, after C.6)

**Trigger:** all tasks C.1–C.6 landed; quality gates green; ready to merge.
**Reviewers (per umbrella §4.13.3 end-phase):** **all 5 standing** + **DBA specialist** + **Schema-migration safety reviewer**.
**Lens:**
- **Architecture:** SPI shape (`PlatformInterface` additions) clean? `CheckConstraint` Tier 2 commitment honored?
- **BC:** XSD additivity verified by gate; legacy-show-create runway honored; serial-to-identity is opt-in only.
- **Quality:** baselines monotonic; coverage delta ≥ 0; mutation MSI ≥ 65 on touched files (Phase C threshold per umbrella §4.14); deptrac green; PBT round-trip green.
- **Performance:** `regen-golden.php` runtime within ±5% of Phase B; reverse-parser query count ≤ 5 per table per umbrella §4.10 spirit (no per-row chatter).
- **Ambition:** every umbrella §6.4 capability for Phase C delivered (or documented in waivers).
- **DBA:** every emitted DDL runs cleanly on MySQL 8.0.30 + MariaDB 10.5 + PG 14 + PG 16; testcontainers-based round-trip validates idempotency.
- **Schema-migration safety:** no false positives in Diff comparator; round-trip migration (`up; down`) leaves schema bit-identical for the new feature subset.

**Outputs:** `docs/reviews/C-round-3-{architecture,bc,quality,performance,ambition,dba,migration-safety}.md` + `C-round-3-summary.md`. Plus `C-bench.md`, `C-mutation.json`, `C-waivers.md`.

**Iteration budget:** 3 cycles. Maintainer-escalation triggers per umbrella §4.15.3.

A 4th post-merge canary round runs 7 days after merge to integration branch (umbrella §4.13.3 HIGH-RISK row): performance + quality + BC reviewers re-engage on real-world deployment data.

---

## Definition of Done (umbrella §4.9 — 15 boxes)

- [ ] All test matrix cells green: PHP {8.3, 8.4} × DB {agnostic, mysql, pgsql, sqlite} × Symfony {7.2, 7.latest} = 16 cells.
- [ ] `phpstan-baseline.neon` ≤ 403 lines (Phase B end; Phase C must not regress; ideally drawdown).
- [ ] `psalm-baseline.xml` ≤ 1616 lines (Phase B end).
- [ ] Coverage delta ≥ 0%; floor 70% Runtime / 60% Generator / 70% generated bookstore output.
- [ ] Mutation MSI ≥ 65 on touched files (umbrella §4.14 Phase C threshold).
- [ ] Deptrac green; 0 violations against 233 baseline.
- [ ] Performance benchmarks within ±5% of Phase B end (no must-improve obligation for Phase C — reserved for E/F/G/J).
- [ ] `CHANGELOG.md` updated (Added: JSON/JSONB, generated, CHECK, INVISIBLE, IDENTITY, JsonbOperator; Changed: reverse parsers; Deprecated: legacy-show-create, legacy-serial; Fixed: collation/comment drift detection).
- [ ] Deprecation message audit clean (allowlist accepts legacy-serial + legacy-show-create + virtual-on-PG).
- [ ] Generated-code lint parity green (regenerated bookstore passes phpcs + phpstan).
- [ ] Golden-file diff reviewed line-by-line per task; per-task golden regen committed in same commit as source.
- [ ] Phase plan updated with retrospective notes (this file, appended after Round 3).
- [ ] All review-round reports committed under `docs/reviews/C-round-{1,2,3}-*.md`; consolidated `C-summary.md`.
- [ ] Surgical-test battery executed: PBT round-trip, mutation report, golden-diff, signature-diff (Tier 1 stable; new Tier 2 entries `CheckConstraint` / `JsonbOperator` snapshotted), differential test against Phase B end, ecosystem advisory CI run.
- [ ] All MUST-FIX closed; SHOULD-FIX closed or waived in `C-waivers.md`; iteration cycles within budget (3 per round).

---

## Risk Register (Phase C-specific)

1. **XSD additivity break.** A new attribute declared without `use="optional"`, or a new child element without `minOccurs="0"`, breaks every existing consumer schema. **Mitigation:** the C.1.5 `xsd-additivity` CI gate validates every Phase A/B fixture against the new XSD on every commit; failure blocks merge.

2. **Reverse-parser regression.** Consumer projects use `database:reverse` against existing databases. The output XML must remain stable (modulo the new Phase C attributes that surface only on schemas that actually exhibit them) for unchanged DBs. **Mitigation:** PBT round-trip (C.4.4) validates against random valid schemas; legacy `--reverse-format=legacy-show-create` flag (C.4.5) gives a fallback path for pathological cases. Differential test against Phase B end runs in CI.

3. **DDL emission cross-platform leak.** A generated column's `STORED` keyword on MySQL must not leak into PG output (PG accepts STORED but the wider DDL grammar differs). **Mitigation:** every DDL test in C.3.1/C.3.2 asserts the full SQL string per platform, not partial substrings. PlatformInterface SPI methods (`supportsGeneratedColumns()`, etc.) gate the emission paths.

4. **CHECK constraint enforcement semantics differ across platforms.**
   - PG enforces CHECK by default (no opt-out).
   - MySQL 8.0.16+ enforces CHECK by default; older MySQL parses but ignores. We no longer support MySQL <8 per umbrella §1.2 — but document the behavior.
   - SQLite enforces; we reject CHECK on SQLite per frozen-feature stance.
   - MariaDB 10.5+ enforces; older parses but ignores; we require 10.5+.
   **Mitigation:** the umbrella's "MySQL 8.0+, MariaDB 10.5+" floor is reaffirmed in Phase C's `CheckConstraint` docblock; older versions get a clear deprecation/error.

5. **JSONB operator binding (`?`, `?&`, `?|`) collides with PDO positional placeholders.**
   PDO uses `?` as a placeholder marker; PG's literal `?` operator must be doubled (`??`) per `PDO::ATTR_EMULATE_PREPARES` semantics. **Mitigation:** `JsonbOperator::buildClause()` emits the doubled form; helper covers all 5 operators; unit + integration tests on a real PG harness assert correct binding. Documented in `MIGRATION-FROM-PRE-AI.md`. Decision: ship operator helper, NOT raw-SQL escape — an explicit BC commitment to the helper interface (Tier 2 SPI snapshot) over raw SQL string manipulation.

6. **`serial` → `IDENTITY` migration is non-reversible cheaply.** Once converted, going back to `serial` requires sequence recreation and default-value restoration. **Mitigation:** the conversion is OPT-IN via `--convert-serial-to-identity`; default behavior leaves existing schemas untouched. Documented rollback in `MIGRATION-FROM-PRE-AI.md` (C.6.2).

7. **INFORMATION_SCHEMA query performance on large databases.** Reverse-parsing a 1000-table schema may run ~5 queries per table = 5000 queries. **Mitigation:** parsers do a single batch query per metadata kind (one COLUMNS query for the whole DB, etc.), then group in PHP. Asserted by ChaosTest replay against a captured 100-table fixture: ≤ 5 queries total per database.

8. **MariaDB-vs-MySQL grammar drift.** MariaDB 10.5 supports CHECK fully; MySQL 8.0.16 added enforcement. INVISIBLE columns: MariaDB 10.3+, MySQL 8.0+. Both supported; tested against both via the DBA specialist's testcontainers harness.

9. **Tier 2 SPI commitment for `CheckConstraint` and `JsonbOperator`.** Once committed to the snapshot, the public method shapes are bound to the 3.x line. **Mitigation:** signature-diff gate enforces.

---

## Out of Scope (explicit)

- **JSON shape DSL / value-object generation.** Per umbrella §1.3: "JSON shape DSL with code-generated value objects — stretch goal; default Phase C support is JSON/JSONB column type with operator helpers, not a shape system." Phase C ships the operator helpers and column type; nothing more on JSON.
- **Migration tooling overhaul.** Per umbrella §5: dry-run, squash, baseline, drift detection are Phase H. Phase C's only migration touch is the `--convert-serial-to-identity` flag (C.6).
- **Multi-tenancy / sharding.** Per umbrella §1.3: addon-package-only, post-4.0.
- **Behavior-modifier refactor.** Sortable / NestedSet / Versionable refactor into the `CodeEmitter` template approach is Phase D (and the new Phase B').
- **Connection-layer collapse / replica routing.** Phase E.
- **Criteria split + enums-alongside.** Phase F.
- **PHP 8.4 lazy objects / asymmetric visibility.** Phase G.
- **Observability hooks for DDL emission or schema introspection.** Phase I (TelemetryInterface).
- **Worker-mode / long-running-process safety.** Phase J.
- **`oracle` / `mssql` / `sqlsrv` adapters.** Per umbrella §1.2: removed; not resurrected.
- **YAML / PHP schema parsers.** Per umbrella §1.3: XML stays primary.

---

## Self-Review Checklist (writing-plans skill)

**Spec coverage check:**

| Umbrella spec promise (Phase C from §6.4) | Task |
|---|---|
| Native `JSON` / `JSONB` column type | C.1.1, C.2.5, C.3.1, C.3.2 |
| PG-side JSONB operator helpers | C.3.2 (`JsonbOperator`), C.3.5 (filterBy* methods) |
| Generated columns `<column generated="virtual\|stored" expression="...">` | C.1.2, C.2.1, C.3.1, C.3.2 |
| CHECK constraints in schema model + DDL | C.1.4, C.2.3, C.2.4, C.3.1, C.3.2, C.5.1 |
| `INVISIBLE` columns (MySQL 8 / MariaDB 10.3+) | C.1.3, C.2.2, C.3.1 |
| PG `IDENTITY` columns (replace `serial`/`bigserial`) | C.3.2, C.3.6, C.6.1, C.6.2 |
| Reverse parsers migrate to `INFORMATION_SCHEMA` | C.4.1, C.4.2 |
| UUID parsing on reverse | C.4.3 |
| INVISIBLE detection on reverse | C.4.1 (within `MysqlSchemaParser` step 2) |
| Generated-column expression detection on reverse | C.4.1, C.4.2 |
| Diff: collation drift | C.5.1 |
| Diff: comment drift | C.5.1 |
| Diff: CHECK drift | C.5.1 |
| Diff: partial-index drift | C.5.2 |
| Diff: expression-index drift | C.5.2 |
| Diff: index-type drift | C.5.2 |
| Diff: DEFERRABLE-FK drift | C.5.3 |
| XSD additivity proof | C.1.5 |
| Round-trip PBT (umbrella §4.11) | C.4.4 |
| Per-task golden regen + lint parity (umbrella §4.6, §4.7) | C.3.1, C.3.2, C.3.5 (each commits regenerated tree) |

**Type-consistency check:** All file paths verified against `src/Propel/Generator/Model/`, `src/Propel/Generator/Platform/`, `src/Propel/Generator/Reverse/`, `src/Propel/Generator/Model/Diff/`, `resources/xsd/database.xsd`. Method names verified (`loadMapping`, `compareColumns`, `getDomain`, `parseTables`).

**Placeholder scan:** No TBDs; no "TODO" markers; every task has a primary file, a concrete change, a verify command, and a commit-message stub.

---

## Execution Handoff

Phase C is HIGH-RISK 3-round per umbrella §4.13.3. Recommended execution shape:

**1. Subagent-Driven (recommended for C.1, C.2, C.4, C.5)** — fresh subagent per task; well-scoped XSD and model-class work; mechanical reverse-parser query swaps.

**2. Inline Execution (recommended for C.3 platform DDL emitters)** — deep platform-specific judgment calls; per-task golden regen contracts mean the same session benefits from holding the schema-fixture context.

**3. Mixed (recommended for C.6 + cross-cutting tasks)** — inline for the migration-cookbook documentation; subagent for the migration-manager flag plumbing.

Always: `tests/bin/setup.sqlite.sh` BEFORE `php tools/regen-golden.php`. Working tree must be empty after a regen run; if it isn't, the generator is non-deterministic and a MUST-FIX is logged before continuing (Phase B Round 2 cycle 1 lesson).

---

## Phase C Retrospective

_To be appended after Round 3 closes._
