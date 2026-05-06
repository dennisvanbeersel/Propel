# Propel2 Modernization — Umbrella Spec

**Date:** 2026-05-06
**Branch:** `ar-rewrite`
**Status:** Approved scope. Phase plans drafted just-in-time.

> **For Claude:** This is the strategic umbrella. Each phase below has (or will have) its own `docs/plans/YYYY-MM-DD-<phase-letter>-<topic>.md` with task-level steps. When executing a phase, use `superpowers:writing-plans` first to draft that phase's plan, then `superpowers:executing-plans` to run it.

---

## 1. Vision & Scope

A refresh of Propel2 onto modern PHP and a focused 2-database (MySQL 8 / MariaDB 10.5+, PostgreSQL 14+) reality, with aggressive removal of dead code, broken behaviors, and Java-era abstractions — while keeping consumer projects (using pre-rewrite Propel) on a deprecation runway, never a wall.

### In scope

| Dimension | Decision |
|-----------|----------|
| PHP | 8.3 baseline today (already locked in `composer.json`). Phase G **bumps minimum to 8.4** to adopt lazy objects / asymmetric visibility / property hooks unconditionally. Major version bump alongside that change. |
| MySQL/MariaDB | MySQL 8.0+, MariaDB 10.5+ — no version-compat code below this floor. (MariaDB 10.5+ includes recursive CTEs, JSON, generated cols, INVISIBLE columns, IF NOT EXISTS — all features Phase C relies on.) |
| PostgreSQL | PG 14+ — full IDENTITY, JSONB, generated columns, CTEs assumed |
| SQLite | Keep but freeze. No new DDL features beyond what's there. Used by tests + small projects. |
| MSSQL / Oracle | Already absent; do not resurrect. Config referencing them must hard-error with a migration-guide pointer. |
| Symfony | 7.2+ |
| Generated AR public surface | Frozen to BC contract (Tier 1). Free reign on generator internals (Tier 3). |

### Out of scope (call out loudly so we don't drift)

- **Not writing a new ORM.** Active Record API stays. `find()`, `filterByX()`, `save()`, `delete()` semantics unchanged.
- **Not switching schema format.** XML stays primary. YAML/PHP schema parsers explicitly out.
- **Not building a new query DSL alongside Criteria.** No "fluent v2".
- **Not promising byte-stable generated code.** Generated code WILL change shape (type declarations, return types, lazy patterns). We promise method-name + signature compatibility on the BC tier, not byte equality.
- **Not maintaining MyISAM, ancient MySQL <5.7, PG ≤13, PHP <8.3** in any form.

### Core principles

1. **Remove dead, modernize live.** Anything broken on supported PHP/Symfony today is a kill candidate.
2. **Deprecation runway over instant breaks.** Tier 1/2 changes get `trigger_deprecation()` + `@deprecated` for one minor before removal in next major.
3. **YAGNI applied to abstractions, not features.** Multi-platform abstractions designed for Oracle/MSSQL collapse to direct strategies for the two databases we keep.
4. **Generator output IS the public API.** Treat generated method signatures as binding. CI signature-diff gates Tier 1 stability.
5. **Performance via type pinning, not micro-opt.** Typed properties + readonly DTOs + lazy objects > clever string tricks.

---

## 2. BC Tiers & Deprecation Discipline

### Tier 1 — Frozen public API

No removals, no signature narrowing, ever. Enums and modern alternatives may be added **alongside**, never replacing.

**Generated ActiveRecord per-instance API:** `save()`, `delete()`, `reload()`, `hydrate()`, `isNew()`/`setNew()`, `isModified()`/`setModified()`, `isDeleted()`/`setDeleted()`, `toArray()`, `fromArray()`, `importFrom()`, `exportTo()`, `getPrimaryKey()`/`setPrimaryKey()`, `getByName()`/`setByName()`, `getByPosition()`/`setByPosition()`, all per-column `getX()`/`setX()`, all per-relation `getXs()`/`addX()`.

**Generated query class API:** `find()`, `findOne()`, `findPk()`, `findPks()`, `findBy()`, `findOneBy()`, `findByArray()`, `findOneByArray()`, `findOneOrCreate()`, `requirePk()`, `requireOne()`, `requireOneBy()`, `filterByX()`/`filterByRelation()`, `count()`, `exists()`, `paginate()`, `delete()`, `deleteAll()`, `update()`, `doSelect()`, `where()`, `orderBy()`, `groupBy()`, `limit()`, `offset()`, `select()`, `distinct()`, `join()`, `joinWith()`, `with()`, `useQuery()`, `endUse()`, `condition()`, `having()`, `whereExists()`, `whereNotExists()`.

**`Criteria` constants (string values immutable):** all 31 — `EQUAL`, `NOT_EQUAL`, `GREATER_THAN`, `LESS_THAN`, `GREATER_EQUAL`, `LESS_EQUAL`, `LIKE`, `NOT_LIKE`, `ILIKE`, `IN`, `NOT_IN`, `ISNULL`, `ISNOTNULL`, `LEFT_JOIN`, `RIGHT_JOIN`, `INNER_JOIN`, `JOIN`, `ASC`, `DESC`, `LOGICAL_OR`, `LOGICAL_AND`, `DISTINCT`, `CUSTOM`, `RAW`, `BINARY_AND`/`OR`, `CONTAINS_ALL`/`SOME`/`NONE`.

**`TableMap::TYPE_*` constants:** `TYPE_PHPNAME`, `TYPE_CAMELNAME`, `TYPE_COLNAME`, `TYPE_FIELDNAME`, `TYPE_NUM`.

**`Propel::` static facade** (`Propel.php`) — all 18 static methods. `StandardServiceContainer` internals are free to rewrite.

**`ConnectionInterface`** — mirrors PDO; keep parameter looseness.

**`ActiveRecordInterface`** — single method, sacred. Adding methods is a BC break for manual implementers.

### Tier 2 — Deprecation runway required

One minor release of `trigger_deprecation()` before removal in next major.

- `AdapterInterface`, `SqlAdapterInterface` (custom adapters in user codebases).
- `Behavior` abstract class hook signatures: `objectFilter`, `objectAttributes`, `objectMethods`, `queryMethods`, `staticMethods`, `tableMapFilter`, `preSave`/`postSave`/`preUpdate`/`postUpdate`/`preDelete`/`postDelete`. Third-party Behaviors on Packagist subclass these.
- `AbstractFormatter::format()`/`formatOne()`. `Collection`, `ObjectCollection`, `ArrayCollection`, `OnDemandCollection` extension points.
- `ConnectionManagerInterface`, `ConnectionManagerSingle`, `ConnectionManagerPrimaryReplica`.
- `StatementWrapper`, `ConnectionWrapper` (wrapped by profiling extensions).
- `ServiceContainerInterface` (DI bridge implementations).
- `Map\TableMap` public methods called from generated `*TableMap::initialize()`.

### Tier 3 — Internal, free reign

- All of `src/Propel/Generator/Builder/Om/`
- `src/Propel/Generator/Platform/`, `Generator/Reverse/`, `Generator/Manager/`, `Generator/Command/`
- Runtime `ActiveQuery/SqlBuilder/` and `ActiveQuery/QueryExecutor/`
- `Common/Config/Loader/*`, `Common/Config/XmlToArrayConverter`
- `DebugPDO`, `PropelPDO` (BC shells — already empty subclasses; deprecate, then remove)

### Deprecation tooling

1. Adopt **Symfony's `trigger_deprecation('propel/propel', 'X.Y', '...')`** convention. Already a transitive dependency.
2. Use **PHP 8 `#[\Deprecated]` attribute** for class/method-level (PhpStorm + PHPStan parse this).
3. Today only **6 `@deprecated` markers** exist tree-wide. Phase A establishes the new deprecations introduced by this rewrite.
4. **CI signature-diff gate:** regenerate the bookstore fixture, dump method signatures of generated `Base\Book.php` / `Base\BookQuery.php`, compare against a committed snapshot. Any signature change must include a `@deprecated` annotation OR justify why it's net-new.

### Schema XSD: additive-only promise

- Never remove an enumeration value (column types, ON DELETE actions, `idMethod` values).
- Old XSD URL must remain valid (most schemas in the wild reference a public XSD URL).
- New types added only as additions to `custom_datatypes.xsd`.

### Configuration: alias-don't-rename promise

- Keep `slave`/`slaves` keys parseable (forward to `replica` with deprecation notice).
- Keep `master` keys parseable (forward to `primary` with deprecation notice).
- Keep both `connection` (singular) and `connections` (plural).
- `paths.phpDir`, `paths.sqlDir`, `paths.migrationDir` defaults locked.
- Adapter enum stays `mysql|pgsql|sqlite`. Historical entries (`oracle`, `mssql`, `sqlsrv`) → hard error with migration-guide pointer.

---

## 3. Phases

Each phase has its own implementation plan drafted just-in-time. Phase B already has one committed (`2026-02-03-builder-om-modernization.md`).

| # | Phase | Risk | Effort | Sequencing rationale |
|---|-------|------|--------|----------------------|
| **A** | **Quick wins: dead code + critical bugs** | Low | S | Pure cleanup. Includes `MysqlPlatform::getMajorServerVersionNumber` off-by-one fix (real bug). Run first to isolate the codebase before structural work. |
| **B** | **Builder/Om generated code modernization** | Medium | L | 37 tasks already planned in `2026-02-03-builder-om-modernization.md`. Strict types in generated output, typed properties, return types, backed enums for ENUM columns, JSON_THROW_ON_ERROR everywhere. |
| **C** | **Schema model & DDL modernization (Generator/Platform/Reverse)** | Medium | L | Adds JSON/JSONB native, generated columns, CHECK constraints, INVISIBLE columns, PG IDENTITY. Reverse parsers move to `INFORMATION_SCHEMA`. Diff comparator extended for collation/comments/CHECK/partial-where. After B because schema-model changes ride on B's generator output. |
| **D** | **Behaviors cleanup** | Medium | M | Kill Validate + QueryCache (broken). Deprecate NestedSet (CTEs replace it). Refactor Sortable (965 LOC) and NestedSet (2900 LOC) string-concat generators into template files. Independent — can parallelize with C. |
| **E** | **Runtime: Connection layer collapse + adapter cleanup** | High | L | Merge `PdoConnection` + `ConnectionWrapper` into `final` class with decorator-based logging/profiling/caching. Removes 2 stack frames per query. Replace `debug_backtrace()` in log path. Add LRU bound to `cachedPreparedStatements`. Touches Tier 2 contracts → deprecation runway required. |
| **F** | **Runtime: ActiveQuery/Criteria modernization + enums alongside** | High | L | Add `Comparison`, `JoinType`, `SortOrder`, `LogicalOperator` enums alongside `Criteria::*` constants. Split `Criteria` 2524 LOC. Deprecate Java-Hashtable methods (`put`/`putAll`/`get`/`keys`/`size`/`equals`). Rewrite hand-rolled `replaceNames` SQL parser. Most BC-fragile phase. |
| **G** | **PHP 8.4 forward-looking (PHP minimum bumps to 8.4 here; major version bump)** | Medium-High | L | Lazy objects for relation collections (replaces `coll*Partial` boilerplate). Asymmetric visibility for typed entity properties. Property hooks for dirty-tracking on generated setters. Streaming `Generator`-based formatter. `WeakMap`-based instance pool. Requires B and E in place. Lazy adoption gated behind config flag for one minor before flipping default. |
| **H** | **CLI/Manager polish + migration table redesign** | Low | M | `#[AsCommand]` attributes everywhere. `SymfonyStyle` for output. MigrationManager: add `migration_name`, `batch`, `checksum` columns. Stop silently creating migration table on read failure. Independent. |

### Sequencing rules

- **A always first.** Removes dead code that other phases would otherwise have to special-case.
- **B → C** strictly sequential. C's schema-model changes ride on B's modernized generator output.
- **D, H independent.** Slot in opportunistically.
- **E before F.** E collapses internals; F touches the public Criteria API. Spreading the BC pain.
- **G last.** Lazy-objects + streaming formatters retrofit cleanly only after B and E land.

---

## 4. Concrete kill / deprecate / keep / add lists

### Kill immediately (Phase A)

| File / class / type | Reason |
|---|---|
| `src/Propel/Runtime/Connection/PropelPDO.php` | 5-line empty BC subclass |
| `src/Propel/Runtime/Connection/DebugPDO.php` | 8-line empty BC subclass |
| `src/Propel/Runtime/Connection/ConnectionManagerMasterSlave.php` | Already `@deprecated`; delegates to PrimaryReplica |
| `src/Propel/Runtime/Validator/Constraints/*` | Re-allows `DateTimeInterface`; Symfony 6+ already supports it natively |
| `src/Propel/Generator/Behavior/Validate/` | Imports Symfony 3.0-removed `DefaultTranslator`, `StaticMethodLoader`; generates dead code today |
| `src/Propel/Generator/Behavior/QueryCache/` | Uses `apc_*` (removed PHP 5.5, 2013); cannot run on supported PHP |
| `Propel::initConfiguration()` (`Propel.php:117`) | Already `@deprecated` |
| `BU_DATE`, `BU_TIMESTAMP`, `BOOLEAN_EMU` types in `PropelTypes.php` | Pre-1970 timestamp / pre-PG-bool workarounds; useless on 64-bit PHP 8.3 |
| `PropelTypes::OBJECT`, `PropelTypes::PHP_ARRAY` | Stores serialized PHP / CSV in TEXT — anti-patterns from ~2005 |
| `MysqlPlatform::getColumnBindingPHP` PECL #9919 hack (`:1036`) | Bug fixed in PHP 5.x |
| MyISAM plumbing: `tableEngineKeyword`, `defaultTableEngine`, MyISAM-only options in `MysqlPlatform::getTableOptions` (`:367-393`) | InnoDB is the only modern choice |
| `MysqlPlatform::getBeginDDL` "MySQL >= 4.1.x" comment + logic (`:236`) | Two decades obsolete |
| `SqlitePlatform::initialize` `version_compare($v, '3.6.19')` | SQLite 3.6.19 released 2009 |
| `SqliteAdapter::__construct` `mb_regex_encoding` per-call hack (`:51`) | Move to one-time init |
| HHVM strict-issue comments in `PdoConnection` (`:174,187`) | HHVM dead ~5 years |
| `IdMethod.php` vs `IdMethodType.php` duplicate constants | Both reference Oracle in docblocks |
| `tests/Fixtures/etc/xsl/` + XSLT pipeline in `AbstractManager::loadDataModels:321` | Unused |
| `tests/Fixtures/bookstore/build/classes/Propel/Tests/Bookstore/Behavior/ValidateTriggerBook.php` | Generated artifact for the killed Validate behavior |
| Use of `Serializable` interface on `Collection.php:48` | Soft-deprecated since PHP 8.1; `__serialize`/`__unserialize` already implemented |
| `spl_object_hash` calls in `ObjectCollection.php` (`:419,442,455,494,519,522,527,541,557`) | Replace with `spl_object_id` (cheaper) |

### Critical bugs to fix in Phase A

| Bug | Location |
|---|---|
| `MysqlPlatform::getMajorServerVersionNumber` off-by-one — never picks MySQL-8 NOACTION default | `MysqlPlatform.php:1107` |
| `PgsqlAdapter::getId` quotes sequence as string-literal not identifier | `PgsqlAdapter.php:111` |
| `Collection::offsetGet` returns `null` by reference (PHP 8 warns) | `Collection.php:117` |
| `cachedPreparedStatements` ignores `$driverOptions` in cache key (silent statement reuse) | `ConnectionWrapper.php:389-406` |
| Reflection-per-row in formatter STI hydration | `AbstractFormatterWithHydration.php:85`, `OnDemandFormatter.php:128` |
| `PropelDateTime::__wakeup` calls parent ctor that throws on bad TZ | `PropelDateTime.php:221` |
| `MigrationManager::getAllDatabaseVersions` silently creates table on `PDOException` | `MigrationManager.php:166-194` |

### Deprecate (Phase A; remove in next major)

| Item | Replacement / reason |
|---|---|
| `NestedSetBehavior` (entire 2900 LOC) | Recursive CTEs (`WITH RECURSIVE`) — supported in MySQL 8, MariaDB 10.2.2+, PG 8.4+ |
| `connection.options.MYSQL_ATTR_*` hardcoded list | Free-form `options` array |
| Master/slave config keys (`slaves`, `master`) | Primary/replica (already supported alongside) |
| `IDMethod::*` constants | Enum (`IdMethodEnum`) |
| `Criteria::addMultipleJoin` (already `@deprecated`) | Proper `addJoin` + condition |
| `Criteria::put`/`putAll`/`get`/`keys`/`containsKey`/`keyContainsValue`/`size`/`equals` | Java-Hashtable rump |

### Keep (no removal, modernize internally)

| Behavior | Status |
|---|---|
| Timestampable | Core. Switch to native `ON UPDATE CURRENT_TIMESTAMP` default. |
| Sluggable | Core. |
| Sortable | Core. **Refactor 965-LOC string-concat builder to template files (Phase D).** |
| I18n | Core. |
| AutoAddPk | Core (62 LOC, fine). |
| Versionable | Optional. |
| Archivable | Optional. |
| AggregateColumn / AggregateMultipleColumns | Optional. Future: bridge to native generated columns where supported. |
| ConcreteInheritance | Optional. |
| Delegate | Optional. |

### Add (new capability)

| Capability | Phase |
|---|---|
| Native `JSON` / `JSONB` column type with PG-side operator helpers | C |
| Generated columns (MySQL 5.7+, PG 12+): `<column generated="virtual\|stored" expression="...">` | C |
| CHECK constraints in schema model + DDL | C |
| `INVISIBLE` columns (MySQL 8, MariaDB 10.3+) | C |
| PG `IDENTITY` columns instead of `serial`/`bigserial` | C |
| Migration table fields: `migration_name`, `batch`, `checksum` | H |
| Backed enum classes for ENUM columns | B (existing plan, Phase 3) |
| `KeyType` enum alongside `TableMap::TYPE_*` | F |
| `Comparison`, `JoinType`, `SortOrder`, `LogicalOperator` enums alongside `Criteria::*` | F |
| Streaming `Generator`-based formatter | G |
| `WeakMap`-based instance pool variant | G |
| PHP 8.4 lazy-object relation collections | G |
| `#[\Override]` mechanical sweep (~92 sites) | A |
| `declare(strict_types=1)` in generated base classes | B (existing plan) |
| Typed properties in generated entity attributes | B (existing plan) |
| `trigger_deprecation()` infrastructure | A (foundational) |
| CI signature-diff gate against bookstore fixture | A (foundational) |

---

## 5. Risk Register & Validation Strategy

### Top risks

1. **Generated method signature drift breaks consumer projects silently.** Mitigation: CI signature-diff gate (Phase A).
2. **`Criteria::CUSTOM` raw-SQL injection vector.** Mitigation: Document threat model; introduce parameterized alternative in F; deprecate raw `CUSTOM` after.
3. **`PgsqlAdapter::getId` sequence-name string-quoting bug.** Fix in Phase A as a security-coded commit (BC-positive, no runway needed).
4. **Unbounded `cachedPreparedStatements` is DoS vector for long-running workers.** Mitigation: Phase E adds bounded LRU.
5. **`Validate` and `QueryCache` deletions break user-authored schemas.** Mitigation: schema parser throws clear "removed in this version, see migration guide" exception (Phase A).
6. **PHP 8.4 lazy objects are untested at scale in ORMs.** Mitigation: opt-in via generator config flag for one minor before flipping default; benchmark hydration of 100k-row collection.
7. **Behavior third-party Packagist packages break if hook interfaces tighten.** Mitigation: Tier 2 deprecation runway; add `#[\Override]` to Behavior subclasses in Propel core to prove the contract is stable; deprecate any hook before changing it.

### Validation per phase

- **Phase A:** `composer test:agnostic` + `test:mysql` + `test:pgsql` green. PHPStan level 7. Psalm. New tests asserting Validate/QueryCache schema usage now throws clear errors.
- **Phase B:** Existing plan's per-task verification. CI signature-diff gate green per task (snapshot deliberately updated).
- **Phase C:** New tests: JSON/JSONB roundtrip on PG and MySQL 8. Generated-column integration test. Migration diff produces correct DDL on both DBs for new CHECK constraints.
- **Phase D:** Existing behavior tests pass. New test: schemas referencing killed behaviors throw. Refactored Sortable/NestedSet generators produce byte-identical output (pre/post refactor) on the bookstore fixture.
- **Phase E:** Connection wrapper benchmark before/after — query-count overhead per 10k operations. Existing transaction tests pass. New test: `cachedPreparedStatements` LRU eviction.
- **Phase F:** Criteria enum + string constants both work for one minor. Deprecation messages emitted but tests still green. `replaceNames` rewrite covered by property-based test against existing implementation.
- **Phase G:** Hydration benchmark on 100k rows. Memory profile of `with()` query. Lazy-object opt-in/opt-out matrix tested.
- **Phase H:** Migration roundtrip test: create migration → run → roll back → verify table state with new schema.

### External validation

- Run modernized Propel against a real consumer project (smoke test) before each phase merge.
- Maintain a `tests/integration/consumer-smoke/` mini-project with hand-written queries hitting Tier 1 surface.
- Document version compatibility matrix in README: which Propel version supports which PHP / MySQL / PG / MariaDB versions.

---

## 6. Cross-references

- **Companion plan (Phase B):** `docs/plans/2026-02-03-builder-om-modernization.md` — 37 tasks, builder/Om generated code modernization.
- **Source of agent analysis (this spec was synthesized from 4 critical-review agents on 2026-05-06):** see commit history; agent reports were ephemeral.

---

## 7. Open questions deferred to phase planning

- **Phase E:** Exact decorator interface for the collapsed Connection. Decide when drafting Phase E plan.
- **Phase F:** Whether `Criteria` itself becomes `final` post-deprecation cycle, or stays open for subclass extension.
- **Phase G:** Whether streaming formatter becomes the default for `find()` (BC change) or stays opt-in via `findStream()` (additive).
- **Phase H:** Migration `checksum` algorithm (sha256 of file? of normalized AST?). Decide when drafting Phase H plan.

These are intentionally deferred — locking them now would force decisions before the surrounding code shape is known.
