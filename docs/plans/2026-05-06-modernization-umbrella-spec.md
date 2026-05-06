# Propel2 Modernization — Umbrella Spec (v2)

**Date:** 2026-05-06 (v2 revised after critical-review pass)
**Branch:** `ar-rewrite`
**Status:** Strategic spec. Phase plans drafted just-in-time.
**Composer package name:** `maturix/propel` (used in `trigger_deprecation()` calls — NOT `propel/propel`)
**Branch alias today:** `3.0-dev` per `composer.json:65` — this rewrite ships as **3.0.0**.

> **For Claude:** This is the strategic umbrella. Each phase below has (or will have) its own `docs/plans/YYYY-MM-DD-<phase-letter>-<topic>.md` with task-level steps. When executing a phase, use `superpowers:writing-plans` first to draft that phase's plan, then `superpowers:executing-plans` to run it.

---

## 1. Vision & Scope

A refresh of Propel2 onto modern PHP and a focused two-database (MySQL 8 / MariaDB 10.5+, PostgreSQL 14+) reality, with aggressive removal of dead code, broken behaviors, and Java-era abstractions — while keeping pre-rewrite consumer projects on a deprecation runway, never a wall, and shipping enterprise-grade observability, testing, and quality machinery the project lacks today.

### 1.1 Version map

| Propel version | PHP floor | Status | Purpose |
|---|---|---|---|
| **2.x** | 8.3 | LTS branch (security only) | Existing consumers stay here through 4.0 release |
| **3.0** | 8.3 | This rewrite (Phases A–F + I) | All deprecations introduced; nothing removed yet |
| **3.x minors** | 8.3 | Iterate Phase F/G/H | Continue runway |
| **4.0** | **8.4** | Phase G + removals of all 3.x deprecations | Lazy objects, asymmetric visibility, property hooks become defaults |

This is non-negotiable for the spec to be coherent. Without a version map, "remove in next major" is unactionable.

### 1.2 In scope

| Dimension | Decision |
|-----------|----------|
| PHP | 8.3 baseline through Propel 3.x; 8.4 minimum at Propel 4.0 |
| MySQL/MariaDB | MySQL 8.0+, MariaDB 10.5+ (both have recursive CTEs, JSON, generated cols, INVISIBLE columns, `IF NOT EXISTS`) |
| PostgreSQL | PG 14+ — full IDENTITY, JSONB, generated columns, partial indexes assumed |
| SQLite | Keep but freeze. No new DDL features. Used by tests + small projects. SQLite restored to CI matrix in Phase A. |
| MSSQL / Oracle | Already absent; do not resurrect. Existing config referencing them already fails Symfony Config validation; replace generic message with migration-guide pointer. |
| Symfony | 7.2+ |
| Generated AR public surface | Tier 1 frozen (see §3); free reign on builder internals (Tier 3). |

### 1.3 Out of scope (explicit, so we don't drift)

- **Not writing a new ORM.** Active Record API stays. `find()`, `filterByX()`, `save()`, `delete()` semantics unchanged.
- **Not switching schema format.** XML stays primary. YAML/PHP schema parsers explicitly out.
- **Not building a new query DSL alongside Criteria.** `Criteria` gets enums + a typed Criterion-class generator (Phase F stretch); not a "fluent v2" sibling API.
- **Not promising byte-stable generated code** unless explicitly noted (Phase D refactors). Default contract is method-name + signature compatibility (§3).
- **Not maintaining MyISAM, MySQL ≤5.7, PG ≤13, PHP <8.3** in any form.
- **Multi-tenancy / sharding** — application-layer concern. Out of scope for 3.0–4.0. May ship as a separate addon package (`propel/multitenancy`) post-4.0; requires its own spec.
- **Contributor experience / governance / docs-site rebuild / RFC process** — non-code workstream. Out of this spec; needs its own plan.
- **JSON shape DSL with code-generated value objects** — stretch goal; default Phase C support is JSON/JSONB column type with operator helpers, not a shape system.

### 1.4 Core principles

1. **Remove dead, modernize live.** Anything broken on supported PHP/Symfony today is a kill candidate.
2. **Deprecation runway over instant breaks.** Tier 1/2 changes get `trigger_deprecation('maturix/propel', '3.X', ...)` for the entire 3.x line before removal in 4.0.
3. **Target architecture before refactor.** No "split X" / "collapse Y" task lands without a documented post-state architecture (§2).
4. **YAGNI on abstractions, ambition on capabilities.** Multi-platform abstractions designed for Oracle/MSSQL collapse to direct strategies for the two databases we keep — but capability gaps vs. Doctrine/Cycle (observability, worker-mode, typed DSL) get closed.
5. **Generator output IS the public API.** Treat generated method signatures as binding. CI signature-diff gate enforces this (§4).
6. **Quality is machine-checked monotonic gates, not aspirational prose.** Baselines only ever shrink; coverage floors enforced; mutation tests run; architecture tests via Deptrac (§4).
7. **Performance via type pinning, not micro-opt.** Numerical targets in §5.

---

## 2. Target Architecture

This section defines the post-modernization shape of the three load-bearing subsystems. Without it, "collapse" / "split" tasks are a wishlist.

### 2.1 Connection layer (post-Phase E)

Today: `PdoConnection` → `ConnectionWrapper` (722 LOC, 5 responsibilities) → `ProfilerConnectionWrapper`, with parallel `StatementWrapper` chain.

**Target:**

```
ConnectionInterface (Tier 1)
    ↑ implemented by
PdoConnection (final, ~200 LOC) — bare PDO bridge, no logic
    ↑ wrapped by chain of (each opt-in, each implements ConnectionDecoratorInterface SPI)
    ├── TransactionalConnection — nested-tx accounting
    ├── LoggingConnection — PSR-3 + telemetry hooks (consumes TelemetryInterface from §I)
    ├── CachingConnection — bounded-LRU prepared-statement cache
    ├── ProfilingConnection — query duration histograms
    └── ReplicaRoutingConnection — primary/replica routing intelligence (Phase E.2)
```

**SPI (internal contract — new third axis, not Tier 1, not Tier 3 free):**

```php
interface ConnectionDecoratorInterface extends ConnectionInterface {
    public function getInner(): ConnectionInterface;
}
```

Decorators compose via `ConnectionFactory` registered with the `ServiceContainerInterface`. Decorator order is deterministic (configuration-driven). Third-party profilers (today's `ProfilerConnectionWrapper`) migrate to implement this SPI; deprecation runway covers the swap.

### 2.2 Criteria / ActiveQuery (post-Phase F)

Today: `Criteria` 2524 LOC, mixing 9 responsibilities (constants, hashtable API, criterion building, join planning, name-replacement SQL parser, WHERE/HAVING trees, comparison operators, raw-SQL escape hatches, configuration storage).

**Target:**

```
ActiveQuery/
├── Criteria.php                       — public Tier 1 facade, ~600 LOC
├── Compiler/
│   ├── NameResolver.php               — replaces hand-rolled replaceNames; tokenizer-based
│   └── PreparedStatementKey.php       — shared with Phase E's CachingConnection
├── Plan/
│   ├── JoinPlan.php                   — split out of Criteria
│   ├── WhereTree.php                  — split out of Criteria
│   └── OrderClause.php
├── Operator/                           — enums alongside string consts
│   ├── Comparison.php                 — enum: Equal, NotEqual, GreaterThan, ...
│   ├── JoinType.php
│   ├── SortOrder.php
│   └── LogicalOperator.php
└── Criterion/                          — Tier 2 extension surface (today: 19 classes)
    ├── ...                            — existing, modernized
```

**Phase E↔F coupling acknowledged:** `PreparedStatementKey` lives in `Compiler/`; `CachingConnection` (Phase E) consumes it. Both phases must coordinate on cache-key shape; Phase F drafts the SPI signature; Phase E adopts it.

### 2.3 Builder (post-Phase B + D)

Today: ~14k LOC of string concatenation across `ObjectBuilder` (3,664), `QueryBuilder` (2,255), `TableMapBuilder` (1,601), 4 builder traits (~3,766), and behavior modifiers (Sortable 2,011 LOC, NestedSet 3,036 LOC across 3 files).

**Target architecture decision (forces honesty about Phase B scope):**

The companion plan `2026-02-03-builder-om-modernization.md` is **bug fixes + generated-code modernization inside the existing string-concat architecture**. It does NOT change the builder architecture. Renaming acknowledged:

- **Phase B (existing plan):** generated-code modernization. 37 tasks. Stays as-is.
- **Phase B' (NEW):** Builder architecture refactor — introduce a thin template layer using PHP heredoc + a shared `CodeEmitter` helper (NOT a third-party templating engine; YAGNI). Behavior modifiers (Phase D's targets) consume the same emitter. Sortable + NestedSet refactor lands as the validation of B'.

**Behavior facade (closes BC hole flagged in review):**

Behaviors (Tier 2) consume `ObjectBuilder` (Tier 3 free-reign), creating silent breaks at codegen. Fix: introduce `Generator/Builder/Om/ObjectBuilderApi` interface — narrow facade exposing only the methods Behaviors actually call (`declareClass`, `getStubObjectBuilder`, `addClassOpen`, `addClassClose`, etc.). Behavior `objectMethods()` etc. take the interface, not the concrete class. Tier 2 freezes the interface; Tier 3 keeps freedom on `ObjectBuilder` internals.

### 2.4 Service container / DI strategy

**Decision:** `Propel::` static facade is a **permanent Tier 1 commitment**. Internals delegate to a PSR-11 `ContainerInterface`-compatible `StandardServiceContainer` (Tier 3). New code in 3.0+ accesses services via injected `ServiceContainerInterface` (Tier 2); legacy `Propel::` calls remain forever as the convenience entrypoint.

This avoids the "is the static facade going away?" ambiguity and lets phases I/J introduce DI-friendly hooks without churning the consumer-facing facade.

### 2.5 Observability contract (Phase I)

```php
interface TelemetryInterface {
    public function startQuerySpan(string $sql, array $params): SpanInterface;
    public function recordPreparedCacheHit(bool $hit): void;
    public function recordTransactionDepth(int $depth): void;
    public function recordHydrationDuration(string $class, float $microseconds): void;
}

final class NoOpTelemetry implements TelemetryInterface { /* default */ }
```

Adapter packages (`propel/telemetry-otel`, `propel/telemetry-prometheus`) ship separately; default Propel ships only the no-op.

---

## 3. BC Tiers, Deprecation Discipline, and the SPI Axis

Three axes, not two:

- **Public API (Tier 1, Tier 2):** what consumer code calls.
- **Internal SPI:** contracts internal Tier 3 components speak to each other (e.g., `ConnectionDecoratorInterface`, `ObjectBuilderApi`, `PreparedStatementKey`). Frozen at the SPI signature; implementations free.
- **Implementation (Tier 3):** free reign within SPI signatures.

### 3.1 Tier 1 — Frozen public API

No removals, no signature narrowing. Enums and modern alternatives may be added **alongside**, never replacing. This list is enumerative, not exhaustive — but the signature-diff gate (§4) catches anything not listed.

**Generated ActiveRecord per-instance API** (emitted by `ObjectBuilder`):
- Lifecycle: `save()`, `delete()`, `reload()`, `hydrate()`, `isNew()`/`setNew()`, `isModified()`/`setModified()`, `isDeleted()`/`setDeleted()`, `isPrimaryKeyNull()`.
- Accessors: per-column `getX()`/`setX()`, per-relation `getXs()`/`addX()`, `getPrimaryKey()`/`setPrimaryKey()`, `getByName()`/`setByName()`, `getByPosition()`/`setByPosition()`.
- Serialization: `toArray()`, `fromArray()`, `importFrom()`, `exportTo()`, `__serialize()`/`__unserialize()`.

**Generated query-class API** (emitted by `QueryBuilder` — the BIGGEST consumer surface):
- Factory: `XxxQuery::create()` per query class.
- Per-column: `filterByX()`, `findByX()`, `findOneByX()`, `requireOneByX()`, magic `findByXAndY()` / `filterByXAndY()` via `__call`.
- Per-relation: `useXxxQuery()`, `endUse()`, `joinXxx()`, `leftJoinXxx()`, `rightJoinXxx()`, `innerJoinXxx()`, `joinWithXxx()`, magic `joinWithRelationName` via `__call`.
- Per-PK: `findPk()`, `findPks()`, `filterByPrimaryKey()`, `filterByPrimaryKeys()`.
- Aggregate: `find()`, `findOne()`, `count()`, `exists()`, `paginate()`, `delete()`, `deleteAll()`, `update()`.

**`ModelCriteria` inherited methods:** `where()`, `orderBy()`, `groupBy()`, `limit()`, `offset()`, `select()`, `distinct()`, `join()`, `joinWith()`, `with()`, `useQuery()`, `endUse()`, `condition()`, `having()`, `whereExists()`, `whereNotExists()`, `findBy()`, `findOneBy()`, `findByArray()`, `findOneByArray()`, `findOneOrCreate()`, `requirePk()`, `requireOne()`, `requireOneBy()`.

**`Criteria` constants — string values immutable:** all current public consts in `Criteria.php`. Phase A task: enumerate them in `tests/snapshots/criteria-constants.txt` and gate with signature-diff. Constants identified during review: `EQUAL`, `NOT_EQUAL`, `ALT_NOT_EQUAL`, `GREATER_THAN`, `LESS_THAN`, `GREATER_EQUAL`, `LESS_EQUAL`, `LIKE`, `NOT_LIKE`, `ILIKE`, `NOT_ILIKE`, `IN`, `NOT_IN`, `ISNULL`, `ISNOTNULL`, `LEFT_JOIN`, `RIGHT_JOIN`, `INNER_JOIN`, `JOIN`, `ASC`, `DESC`, `LOGICAL_OR`, `LOGICAL_AND`, `DISTINCT`, `CUSTOM`, `RAW`, `CUSTOM_EQUAL`, `BINARY_AND`, `BINARY_OR`, `BINARY_ALL`, `BINARY_NONE`, `CONTAINS_ALL`, `CONTAINS_SOME`, `CONTAINS_NONE`, `ALL`, `CURRENT_DATE`, `CURRENT_TIME`, `CURRENT_TIMESTAMP`. (~38 actual; replaces the previous bogus "31" claim.)

**`TableMap::TYPE_*` constants:** `TYPE_PHPNAME`, `TYPE_CAMELNAME`, `TYPE_COLNAME`, `TYPE_FIELDNAME`, `TYPE_NUM`. Plus the static API: `getFieldnamesForClass()`, `translateFieldnameForClass()` (called from generated code).

**`Propel::` static facade** — 18 static methods enumerated in Phase A snapshot. Includes: `init()`, `getServiceContainer()`, `setServiceContainer()`, `getConnection()`, `getReadConnection()`, `getWriteConnection()`, `getAdapter()`, `getDatabaseMap()`, `enableInstancePooling()`, `disableInstancePooling()`, `isInstancePoolingEnabled()`, `getDefaultLogger()`, `getLogger()`, `setLogger()`, `getConfiguration()`, `setConfiguration()`, `log()`. Internals delegate to `StandardServiceContainer` (Tier 3, free).

**`ActiveRecordInterface`:** declared method `isPrimaryKeyNull()`; declared `@method toArray()` PHPDoc contract. No additions in 3.x without a deprecation cycle. (The "single method, sacred" prior wording was inaccurate.)

**`ConnectionInterface`** (mirrors PDO) — keep parameter looseness. No tightening in 3.x.

### 3.2 Tier 2 — Deprecation runway required

One full minor in 3.x with `trigger_deprecation('maturix/propel', '3.X', '...')` before removal in 4.0.

- `AdapterInterface`, `SqlAdapterInterface` (custom adapters in user codebases).
- **`Behavior` abstract class hooks taking `ObjectBuilderApi` (NEW interface, §2.3) instead of concrete `ObjectBuilder`** — closes the silent-break-at-codegen hole. `objectFilter`, `objectAttributes`, `objectMethods`, `queryMethods`, `staticMethods`, `tableMapFilter`, `preSave`/`postSave`/`preUpdate`/`postUpdate`/`preDelete`/`postDelete`. Third-party Behaviors update once during 3.x runway.
- **All 19 `Criterion` classes** in `Runtime/ActiveQuery/Criterion/`: `BasicCriterion`, `RawCriterion`, `CustomCriterion`, `LikeCriterion`, `InCriterion`, `ExistsCriterion`, `BinaryCriterion`, `CriterionFactory`, `AbstractCriterion`, etc. (User-extension surface flagged by review.)
- **`PropelException` hierarchy** (`Runtime/Exception/`) and 8 typed exception subclasses — caught by user code.
- **Util classes:** `PropelDateTime`, `PropelModelPager` (returned by `paginate()` which is Tier 1), `Profiler`, `UuidConverter`.
- `AbstractFormatter::format()`/`formatOne()`. `Collection`, `ObjectCollection`, `ArrayCollection`, `OnDemandCollection`.
- `ConnectionManagerInterface`, `ConnectionManagerSingle`, `ConnectionManagerPrimaryReplica`.
- `StatementWrapper`, `ConnectionWrapper` (wrapped by profiling extensions).
- `ServiceContainerInterface`.
- `Map\TableMap` public methods called from generated `*TableMap::initialize()`.

### 3.3 Tier 3 — Internal, free reign within SPI

- All of `src/Propel/Generator/Builder/Om/` (codegen-time only) — but `ObjectBuilderApi` interface is Tier 2.
- `src/Propel/Generator/Platform/`, `Generator/Reverse/`, `Generator/Manager/`, `Generator/Command/`.
- Runtime `ActiveQuery/SqlBuilder/` and `ActiveQuery/QueryExecutor/` — consume `ConnectionDecoratorInterface` SPI (Tier 2 axis).
- `Common/Config/Loader/*`, `Common/Config/XmlToArrayConverter`.
- `DebugPDO`, `PropelPDO` — alias-then-remove (see §3.5).
- `Generator/Util/QuickBuilder` — used by user tests; Tier 3 with documented stability commitment to its public methods.

### 3.4 Deprecation tooling — concrete

1. **`composer require symfony/deprecation-contracts`** as a direct dep (Phase A foundational task). `trigger_deprecation('maturix/propel', '3.X', ...)` as the calling convention.
2. **`composer require --dev symfony/phpunit-bridge`** + set `SYMFONY_DEPRECATIONS_HELPER=max[self]=0` in CI. Tests fail if any code change triggers a self-emitted deprecation that wasn't there before. Existing 6 deprecations form the baseline.
3. **`#[\Deprecated]` PHP attribute** is **PHP 8.4 only**. NOT used in 3.x. At 4.0 (Phase G PHP-8.4 bump), PHPDoc `@deprecated` migrates to the attribute via Rector rule shipped in this repo.
4. **CI signature-diff gate — concretely defined (Phase A foundational task):**
   - **Snapshot location:** `tests/snapshots/{Base_Book.php,Base_BookQuery.php,Base_BookTableMap.php,...}.signatures.json`.
   - **Format:** JSON. Per class: array of `{name, visibility, isStatic, parameters: [{name, type, default, byRef, variadic}], returnType, phpDocReturn}`. Order: alphabetical by `(visibility, name)` for determinism.
   - **Generation:** `bin/propel internal:dump-signatures` walks `ReflectionClass` of regenerated bookstore fixture, emits the JSON. Reproducibility achieved by fixed traversal order + locale-independent output.
   - **What it diffs:** methods (signature + return type + visibility + nullability) + class-level public properties + class constants. Does NOT diff method bodies, PHPDoc text, comment blocks.
   - **BC-safe widening allowlist:** return-type narrowing (LSP-safe) is allowed; parameter-type widening is allowed; adding parameters with defaults is allowed; everything else fails CI unless paired with a `@deprecated` annotation in the same diff.
   - **Update process:** signature changes land in the same commit as the snapshot regeneration. PR template requires reviewer to confirm reviewed.
5. **Migration guide:** `docs/MIGRATION-FROM-PRE-AI.md` — Phase A foundational deliverable (today doesn't exist; spec previously referenced it).
6. **Rector ruleset:** `propel/rector-rules` package — ships at 4.0 with mechanical fixes for every Tier 1/2 deprecation introduced in 3.x. Examples: `Criteria::EQUAL` → `Comparison::Equal`, `slaves` → `replicas` config keys, `IDMethod::*` constants → enum, `DebugPDO` → `ConnectionWrapper`.

### 3.5 `: static` vs `: self` decision (closes review M7)

Generated setters today have no return type; companion plan adds `: static`. **`: static` IS an LSP break** for user subclasses overriding `setTitle($v)` without `: static`. Decision:

- **Use `: self` (not `: static`)** in generated setters. Existing user-subclass `setTitle($v)` overrides remain valid (they implicitly return `static`-compatible). This costs us return-type covariance in subclasses but is BC-safe.
- Document the reason in `2026-02-03-builder-om-modernization.md` (companion plan task 2.6 needs amending).

### 3.6 `DebugPDO` / `PropelPDO` — alias, don't delete (closes review M4)

Phase A v1 said "delete". Real-world deployments have `connection.classname = '\Propel\Runtime\Connection\DebugPDO'` in their `propel.yaml`. Deletion fatals boot. Revised:

- **Phase A:** keep both classes as `class_alias` thin wrappers that emit `trigger_deprecation`. Existing config keeps booting.
- **4.0:** delete the aliases. Rector rule (above) rewrites consumer config + classnames.

### 3.7 Schema XSD additive promise — concrete wording

"Schema instances valid against today's XSD remain valid against the new XSD. New attributes/elements may be added as `minOccurs=0` / optional. New enumeration values may be added to existing types. Enumeration values are never removed; column types added only as additions." Phase C will add: `<column generated="virtual|stored" expression="...">`, `<column invisible="true">`, `<check>` element on `<table>`/`<column>`, new column types (`json`, `jsonb`, `inet`, `cidr`, `tsvector`).

XSD hosting: `resources/xsd/database.xsd` shipped in repo + published to `propelorm.org/xsd/database.xsd` via release pipeline. URL stability part of the BC contract.

### 3.8 Configuration alias-don't-rename

- Keep `slave`/`slaves` keys parseable; deprecate via `trigger_deprecation` forwarding to `replica`/`replicas`.
- Keep `master` keys parseable; deprecate forwarding to `primary`.
- Keep both `connection` (singular) and `connections` (plural).
- `paths.phpDir`, `paths.sqlDir`, `paths.migrationDir` defaults locked.
- Adapter enum stays `mysql|pgsql|sqlite`. `oracle`/`mssql`/`sqlsrv` still produce Symfony Config validation error, but with custom message pointing at `docs/MIGRATION-FROM-PRE-AI.md`.
- `connection.classname` defaulting to `DebugPDO` keeps working via the alias (§3.6).

---

## 4. Quality Gates (foundational, machine-checked)

This section is the rigor backbone. Every gate is in CI, every threshold is numerical, every threshold is monotonic (only allowed to improve).

### 4.1 Static-analysis baseline drawdown

| Tool | Today | Phase A target | Phase G target |
|---|---|---|---|
| `phpstan-baseline.neon` | 548 lines, 91 ignored blocks | ≤438 (-20%) | 0 |
| `psalm-baseline.xml` | 2598 lines, 207 baselined files | ≤2078 (-20%) | 0 |
| `phpstan.neon` blanket regex `ignoreErrors` | 4 entries | 0 | 0 |
| `psalm.xml` global `<issueHandlers>` suppressions | 9 types | 6 (`ImplementedReturnTypeMismatch` removed first) | 0 |

**CI gate:** `tools/check-baseline-monotonic.php` runs on every PR, fails if any file's line count increased vs `master`.

### 4.2 PHPUnit rigor restoration (Phase A exit)

All three of `tests/{agnostic,mysql,pgsql}.phpunit.xml` flip to:
```xml
failOnDeprecation="true"
failOnPhpunitDeprecation="true"
failOnWarning="true"
failOnRisky="true"
failOnIncomplete="true"
failOnNotice="true"
failOnEmptyTestSuite="true"
```
The `4e964c16b` commit that turned these off lands as Phase A exit reversal.

### 4.3 Coverage

CI restores `coverage: pcov` (was `coverage: none` per `ef02d9483`). Floor in `composer.json` script:

| Path | Phase A floor | Phase G floor |
|---|---|---|
| `src/Propel/Runtime/` | 70% | 85% |
| `src/Propel/Generator/` | 60% | 75% |
| Generated bookstore output | 70% line | 85% line |

Coverage delta tracked per phase. Codecov restored or self-hosted summary committed per release.

### 4.4 Mutation testing (Infection)

`composer require --dev infection/infection`. Configuration `infection.json5` targets `Runtime/{Connection,ActiveQuery,ActiveRecord,Map,Adapter}`. **MSI (Mutation Score Indicator) ≥ 75** required for Phase E and F exit on touched files.

### 4.5 Architecture tests (Deptrac)

`composer require --dev qossmic/deptrac`. `deptrac.yaml` encodes:

- `Generator/*` MUST NOT import `Runtime/*` (codegen does not depend on runtime).
- `Common/*` MUST NOT import `Generator/*` or `Runtime/*` (sink layer).
- `Runtime/Internal/*` (new namespace introduced in Phase E for non-SPI internals) MUST NOT be imported by anything outside `Runtime/`.
- Tier 2 contracts: `Behavior` MUST consume only `ObjectBuilderApi`, never `ObjectBuilder` concrete.

Phase A: capture today's structure as Deptrac baseline. Subsequent phases enforce no regressions.

### 4.6 Generated-code lint parity

CI job `lint-generated`: regenerate bookstore fixture, run **the same** `composer cs-check && composer stan && composer psalm` against `tests/Fixtures/bookstore/build/classes/`. Generated code must pass the same bar as hand-written code. Gate added Phase A.

### 4.7 Golden-file regression for generator

`tests/Fixtures/bookstore/build/golden/` committed; Phase A introduces `tools/regen-golden.php` and a CI check that diffs new generation against golden. Required artifact: every builder change includes the regenerated golden tree in the same commit; reviewers approve diff line-by-line.

### 4.8 phpcs configuration freshness

`phpcs.xml` `phpVersion=7.4` → `8.3`. Phase A.

### 4.9 Per-phase Definition of Done

Every phase's exit checklist:

- [ ] All test matrix cells green: PHP {8.3, 8.4} × DB {agnostic, mysql, pgsql, sqlite} × Symfony {7.2, 7.latest} = 16 cells.
- [ ] Baselines decreased by phase target (§4.1).
- [ ] Coverage delta ≥ 0%; coverage floor met.
- [ ] Mutation score ≥ threshold on touched files.
- [ ] Deptrac green; no new layer violations.
- [ ] Performance benchmarks within ±5% of pre-phase numbers (or improved per §5).
- [ ] `CHANGELOG.md` updated (Keep-a-Changelog format).
- [ ] Deprecation message audit clean (no new self-triggered deprecations).
- [ ] Generated-code lint parity green.
- [ ] Golden-file diff reviewed.
- [ ] Phase plan updated with retrospective notes.
- [ ] **Review rounds completed per §4.13; all reviewer reports committed under `docs/reviews/`.**
- [ ] **Surgical-test battery (§4.14) executed; every test type's report committed.**
- [ ] **All MUST-FIX review findings closed; all SHOULD-FIX either closed or waived with documented reasoning in `docs/reviews/<phase>-waivers.md`.**
- [ ] **Iteration cycles consumed within budget (§4.15); any over-budget escalations resolved.**

A phase isn't "done" until all 15 boxes are checked.

### 4.10 Performance targets (numerical)

Set in Phase A; tracked in `tests/Benchmark/`.

| Metric | Today (baseline TBD in Phase A) | 4.0 target |
|---|---|---|
| Hydrate 100k-row `Book` collection | TBD ms | ≥30% faster |
| Memory peak for 100k-row hydrate | TBD MB | ≤TBD MB (no regression) |
| Query overhead per call (vs raw PDO) | TBD µs | ≤2× raw PDO |
| Prepared-statement cache hit rate (typical workload) | TBD% | ≥90% |
| Doctrine 3 hydration parity (same workload) | n/a | within ±10% |

Baselines captured in Phase A. Phase E + F + G must each show a measurable improvement; Phase B/C/D must not regress.

### 4.11 Property-based testing infrastructure

`composer require --dev giorgiosironi/eris`. Standing tool used in:
- Phase C: SchemaParser XML round-trip; Migration apply/inverse identity.
- Phase F: `replaceNames` rewrite (token-equivalence); Criterion compose/decompose; `Comparison::X->value === Criteria::X` contract test.

### 4.12 Failure-injection / chaos (Phase E)

PDO connection drop mid-transaction, statement-cache eviction during prepared call, deadlock retry boundedness. Tests live under `tests/ChaosTests/`. Gate: Phase E exit.

### 4.13 Review Team & Protocol (mandatory deliverable per phase)

Every phase ships through a **multi-reviewer protocol**, not a self-merge. Reviews are dispatched as critical-review subagents using the `superpowers:requesting-code-review` skill; reports are markdown files committed alongside the code.

#### 4.13.1 Standing review team (5 reviewers — every phase, no exception)

| Reviewer role | Lens / charter |
|---|---|
| **Architecture & SOLID reviewer** | Target architecture honesty, interface integrity, SPI contract correctness, DRY/SOLID/SRP violations, module boundary leaks, dependency direction. |
| **BC & migration realism reviewer** | Tier 1/2 surface drift, deprecation runway correctness, signature-diff gate output, schema XSD additivity, config alias preservation, real-consumer impact assessment. |
| **Quality & rigor reviewer** | Baseline drawdown actually achieved (not just promised), PHPUnit fail-flags green, coverage delta verified, mutation score on touched files, Deptrac green, generated-code lint parity, golden-file diff sanity. |
| **Performance & PHP runtime reviewer** | Benchmark numbers vs targets (§4.10), JIT-friendliness, opcache compatibility, memory profile, hot-path overhead, PHP 8.4 feature usage correctness. |
| **Ambition & capability reviewer** | What did the phase claim to deliver vs what landed; capability gaps vs Doctrine 3 / Cycle ORM; out-of-scope creep into in-scope; under-delivered features. |

#### 4.13.2 Phase-specific specialist reviewers (in addition to standing 5)

| Phase | Specialist(s) |
|---|---|
| **A** | Tooling & CI specialist (Infection/Deptrac/coverage wiring correctness) |
| **B** | Code generator specialist (template safety, generated-code idiom correctness) |
| **B'** | Code generator specialist + Architecture reviewer second pass |
| **C** | DBA specialist (MySQL 8 + PG 14 + MariaDB 10.5 DDL correctness, INFORMATION_SCHEMA coverage), Schema-migration safety reviewer |
| **D** | Behavior author / third-party-ecosystem reviewer (would real Behaviors break?), Code generator specialist |
| **E** | SQL & concurrency specialist (transaction nesting, prepared-statement cache invariants, replica routing correctness, lag handling), Security reviewer (injection/DoS audit) |
| **F** | Compiler/parser specialist (`replaceNames` tokenizer correctness, fuzzing coverage), Security reviewer |
| **G** | PHP 8.4 internals specialist (lazy object semantics, asymmetric visibility BC implications, property hook ordering), Performance reviewer second pass |
| **H** | Database operations / SRE reviewer (migration rollback safety, drift detection, dry-run preview correctness) |
| **I** | Observability specialist (telemetry interface stability, OpenTelemetry conventions, span semantics) |
| **J** | Long-running PHP runtime specialist (RoadRunner/FrankenPHP/Swoole semantics, fiber-safety, leak detection methodology) |

#### 4.13.3 Review rounds — risk-tiered cadence

**LOW-RISK phases (A, D, H, I):** 2 rounds.
**HIGH-RISK phases (B, B', C, E, F, G, J):** 3 rounds.

| Round | Timing | Purpose | Reviewers engaged |
|---|---|---|---|
| **1 — Mid-phase architecture review** | After ~30% of phase tasks complete; before any large refactor lands | Validate target architecture decisions, SPI shapes, BC tier classifications, naming. Catches direction-wrong work before it propagates. | Architecture + BC + relevant specialist(s) — 3–4 reviewers |
| **2 — End-phase pre-merge review** | All tasks complete; quality gates green; ready to merge | Full review pass on the integrated change. MUST-FIX findings block merge. | All 5 standing + all phase specialists |
| **3 — Post-merge canary review (HIGH-RISK only)** | 7 days after merge to integration branch; ecosystem advisory CI has run; consumer-smoke ran for a week | Validate no real-world regressions surfaced. If severity-1 regressions appear, trigger rollback procedure. | Performance + Quality + BC reviewers — 3 reviewers |

#### 4.13.4 Reviewer charter

Every reviewer report:

1. **Tagged findings:** each finding labeled `MUST-FIX` (blocks merge), `SHOULD-FIX` (response required: fix or waiver), or `NICE` (advisory).
2. **Cited:** every finding references file:line in the codebase.
3. **Verifiable:** every finding states how to reproduce / observe it.
4. **Committed:** report saved to `docs/reviews/<phase-letter>-round-<n>-<reviewer-role>.md`. Filename convention is part of the contract.
5. **Adversarial by construction:** reviewers are dispatched with explicit "find weaknesses, don't be diplomatic" framing. Performative agreement is itself a review failure.

The maintainer (or designated phase owner) writes a single `docs/reviews/<phase>-summary.md` consolidating all reviewer reports, MUST-FIX status, and waivers.

### 4.14 Surgical Test Battery (mandatory deliverable per phase)

"Tests pass" is the floor. Every phase ships **deep, targeted tests** scoped to what the phase touched. Phase plans enumerate which test types apply; the umbrella requires ALL applicable types are exercised.

| Test type | What it does | Phases requiring it |
|---|---|---|
| **Property-based tests (PBT)** via `eris` | Generate randomized inputs satisfying invariants; assert round-trip / equivalence properties. Tests added in `tests/PropertyTests/`. | All phases that change a transformation: B, B', C, F, G |
| **Mutation testing** via Infection on touched files | MSI threshold ≥75 (Phase E/F/G/J) or ≥65 (others). Mutation report committed to `docs/reviews/<phase>-mutation.json`. | All phases |
| **Differential test against pre-phase baseline** | Run current and pre-phase build side-by-side on identical inputs; assert behavior parity for unchanged surface. Catches accidental BC drift. | All phases (smaller scope for low-risk) |
| **Failure-injection / chaos** | PDO connection drop mid-tx, statement-cache evict during prepare, deadlock retry, fiber cancellation, replica-lag spike. Lives in `tests/ChaosTests/`. | E, J (mandatory); G (recommended) |
| **Performance benchmarks vs §4.10 targets** | Numerical comparison; report in `docs/reviews/<phase>-bench.md`. Regressions ≥5% block merge unless waived. | All phases (regression check); E, F, G, J (must-improve) |
| **Consumer smoke** | Run `tests/integration/consumer-smoke/` mini-project against the phase build. Tier 1 surface exercised end-to-end. | All phases |
| **Ecosystem advisory CI** | Run 2–3 Packagist downstream packages' test suites against the phase build. Advisory (non-blocking) but required to RUN. Reports committed. | All phases (advisory); E/F/G (escalates to blocking if any project's MUST-FIX findings indicate Tier 1 break) |
| **Fuzzing** (Phase F-specific) | Randomized SQL fragment generator feeding `replaceNames`; token-equivalence assertion. | F (mandatory); E (statement-cache key fuzzing) |
| **Architecture conformance** via Deptrac | Layer rules from §4.5 enforced; diff reported. | All phases |
| **Signature-diff gate** | §3.4 JSON snapshot compared; per-class diff report. | All phases that touch generated code (B, B', C, D, F, G) |
| **Golden-file diff** | `tests/Fixtures/bookstore/build/golden/` line-by-line diff reviewed. | Same as signature-diff |

Every applicable test type produces a committed artifact. "Ran the tests" is not a deliverable; **the report is the deliverable**.

### 4.15 Iteration Loop

Reviews and surgical tests will surface findings. Phases iterate until findings close.

#### 4.15.1 Iteration triggers

A finding triggers iteration if:

- Tagged `MUST-FIX` by any reviewer.
- Surgical-test report shows a regression (perf, mutation, consumer-smoke, etc.) that exceeds the phase's tolerance.
- Signature-diff gate fails without an accompanying `@deprecated`.
- Definition-of-Done checkbox cannot be checked.

#### 4.15.2 Iteration mechanics

Each iteration cycle:

1. Phase owner authors a focused fix addressing the finding(s).
2. Affected surgical tests re-run.
3. Reviewer who flagged the finding is re-engaged in **verify mode** (single-reviewer, single-question: "is your finding now closed?"). Other reviewers do not re-review unless the fix touched their lens.
4. Verify-mode reviewer either signs off (finding closed) or escalates (finding becomes a blocker).

#### 4.15.3 Iteration budget

Per phase: **3 iteration cycles per round**. Tracking lives in `docs/reviews/<phase>-iterations.md`.

If a round exceeds 3 cycles without convergence:

- **Maintainer escalation triggered.** Phase owner + maintainer hold a synchronous review.
- Outcome is one of: (a) extend budget (with reasoning recorded), (b) waive specific findings (with reasoning), (c) split phase scope (some work deferred to a follow-up phase plan), or (d) abort phase and re-plan.

#### 4.15.4 SHOULD-FIX waivers

Findings tagged `SHOULD-FIX` may be waived rather than fixed, but only with:

- A documented reason in `docs/reviews/<phase>-waivers.md` (cited by line, with rationale).
- Confirmation from the reviewer who raised the finding (or maintainer override on dispute).
- An issue filed for follow-up if the deferred work is bounded; or a permanent disposition note if it's a wontfix.

Waiver patterns to refuse:
- "Out of scope" without naming where the work IS in scope.
- "Will fix later" without a tracking issue.
- "Reviewer is wrong" without technical refutation cited to file:line.

---

## 5. Phases

Each has its own implementation plan drafted just-in-time. Phase B has one committed (`2026-02-03-builder-om-modernization.md`).

| # | Phase | PHP | Risk | Effort | Sequencing rationale |
|---|-------|-----|------|--------|----------------------|
| **A** | **Foundations: dead code + critical bugs + ALL quality gates** | 8.3 | Medium | M | Pure cleanup + foundational tooling. Includes: kill broken behaviors, fix `MysqlPlatform::getMajorServerVersionNumber` off-by-one (`:1107`), fix `PgsqlAdapter::getId` sequence quoting (`:111`), install symfony/deprecation-contracts + phpunit-bridge + Infection + Deptrac + phpcs 8.3, restore CI coverage, draw down baselines 20%, restore PHPUnit fail-flags, define signature-diff gate JSON format, write `MIGRATION-FROM-PRE-AI.md`, capture all perf baselines, alias `DebugPDO`/`PropelPDO`, add SQLite back to CI matrix. Larger than v1 indicated. Risk bumped to Medium. |
| **B** | **Generated-code modernization (existing 37-task plan)** | 8.3 | Medium | L | `2026-02-03-builder-om-modernization.md`. **Amend task 2.6**: `: self` instead of `: static` (§3.5). Add: every task regenerates golden; lint parity passes. |
| **B'** | **Builder architecture refactor (template-emitter)** | 8.3 | Medium | M | New phase forced by review. Introduce `CodeEmitter` helper + thin templates for `ObjectBuilder` / `QueryBuilder`. Validates by Phase D's behavior-modifier refactor consuming the same emitter. **Sequenced after B** so the existing plan's bug fixes land before architectural churn. |
| **C** | **Schema model & DDL features (Generator/Platform/Reverse)** | 8.3 | Medium | L | JSON/JSONB native, generated columns, CHECK constraints, INVISIBLE, PG IDENTITY. Reverse parsers move to `INFORMATION_SCHEMA`. Diff comparator extended. Schema XSD adds new optional attributes (§3.7). |
| **D** | **Behaviors cleanup** | 8.3 | Medium | M | Kill Validate + QueryCache (broken). Deprecate NestedSet (CTEs replace it). Refactor Sortable (2,011 LOC) and NestedSet (3,036 LOC) string-concat generators into `CodeEmitter` templates from B'. Independent — can parallelize with C. |
| **E** | **Runtime: Connection collapse + decorators + replica routing** | 8.3 | High | L | Implements the architecture in §2.1. `PdoConnection` shrinks to ~200 LOC; decorator chain (TransactionalConnection, LoggingConnection, CachingConnection, ProfilingConnection, ReplicaRoutingConnection). Replace `debug_backtrace()` in log path. Bounded-LRU prepared-statement cache. Replica routing intelligence: `forcePrimary()`/`allowReplica()` query hints, session-consistency window, replica-lag awareness, primary-fallback. Tier 2 deprecations cover ConnectionWrapper migration. |
| **F** | **Runtime: ActiveQuery/Criteria split + enums alongside + typed Criterion DSL** | 8.3 | High | L | Implements §2.2. `Comparison`/`JoinType`/`SortOrder`/`LogicalOperator` enums alongside `Criteria::*` constants. Split `Criteria` into `Compiler/`, `Plan/`. Deprecate Java-Hashtable methods (`put`/`putAll`/`get`/`keys`/`size`/`equals`). Rewrite `replaceNames` SQL parser (token-based). **Stretch:** generate per-column typed Criterion classes (`BookCriterion::title()->equals(...)`) — opt-in via `<table generate-typed-criterion="true">`; default off in 3.x. Coordinate with E on `PreparedStatementKey` SPI (§2.2). |
| **G** | **PHP 8.4 bump + lazy objects + asymmetric visibility + property hooks (4.0)** | 8.4 | Medium-High | L | Lazy objects for relation collections (replaces `coll*Partial` boilerplate). Asymmetric visibility for typed entity properties — note: this DOES change AR semantics (read-anywhere, write via setter); explicit BC break documented in `UPGRADE-4.0.md`. Property hooks for dirty-tracking. Streaming `Generator`-based formatter. `WeakMap`-based instance pool. Lazy-object adoption gated behind `<table use-lazy-objects="true">` for one minor before 4.1 default flip. Numerical rollback criterion: lazy hydration must be ≤+5% latency vs eager on a 100k-row `with()` query, else flag stays opt-in another minor. Decision authority: maintainer + benchmark CI report. |
| **H** | **CLI/Manager polish + migration tooling overhaul** | 8.3 | Medium | M | `#[AsCommand]` everywhere. `SymfonyStyle` for output. MigrationManager: `migration_name`, `batch`, `checksum` columns; dry-run with SQL preview; squashing; baselines; drift detection (runtime checksum compare); separate data vs structural migrations. Fix silent-table-create-on-error (`MigrationManager.php:166-194`). Bigger than v1 framing — risk bumped to Medium. |
| **I** | **Observability (TelemetryInterface + adapters)** | 8.3 | Low | M | New phase forced by review. Defines `TelemetryInterface` (§2.5). Default `NoOpTelemetry` ships in core. `propel/telemetry-otel` and `propel/telemetry-prometheus` adapter packages. Hook points: query span lifecycle, prepared-cache hit/miss, transaction depth, hydration duration. Phase E's `LoggingConnection` consumes the interface. |
| **J** | **Worker-mode / long-running-process safety (RoadRunner/FrankenPHP/Swoole)** | 8.4 | Medium | M | New phase forced by review. Request-scoped instance-pool reset hook, fiber-safe transaction context binding, connection lifecycle hooks (`onWorkerStart`, `onRequestStart`, `onRequestEnd`), assert-no-leaked-state in test mode. Sequenced post-G because PHP 8.4 Fibers + lazy objects are the cleanest substrate. |

### 5.0 Review-and-test protocol per phase

Every phase is governed by §4.13 (review team), §4.14 (surgical tests), §4.15 (iteration loop). Cadence is risk-tiered:

- **2-round cadence (LOW-RISK):** Phases A, D, H, I.
- **3-round cadence (HIGH-RISK):** Phases B, B', C, E, F, G, J.

Specialists (in addition to the 5 standing reviewers) per phase are enumerated in §4.13.2. Surgical-test types per phase per §4.14.

A phase merge is conditional on:
1. All review-round reports committed under `docs/reviews/<phase-letter>-round-<n>-<role>.md`.
2. All surgical-test reports committed and within tolerance.
3. All MUST-FIX closed; SHOULD-FIX closed or waived per §4.15.4.
4. Iteration cycles within budget per §4.15.3.
5. Definition-of-Done (§4.9) all 15 boxes checked.

### 5.1 Sequencing rules

- **A always first.** Tooling foundation; nothing else has machine-checked quality without it.
- **B → B' → C → D.** B's bug fixes settle the existing builder; B' refactors architecture; C extends schema model; D's behavior refactor validates B'.
- **D, H independent.** Slot opportunistically.
- **E ↔ F coupled** via `PreparedStatementKey` SPI (§2.2). E first (collapses internals), F second (touches Criteria public API). F may overlap E's tail.
- **I parallel with E.** Telemetry interface defined alongside connection collapse so `LoggingConnection` is born telemetry-aware.
- **G last in 3.x cycle; ships as 4.0.** Lazy objects + asymmetric visibility need B/B'/E in place.
- **J post-G.** Worker-mode features rely on 8.4 substrate.

---

## 6. Concrete kill / deprecate / keep / add lists

(Numbers re-verified post-review; corrections noted.)

### 6.1 Kill in Phase A (with deprecation runway where consumer-visible)

| Item | Action | Notes |
|---|---|---|
| `src/Propel/Runtime/Validator/Constraints/*` | Kill | Symfony 6+ supports `DateTimeInterface` natively |
| `src/Propel/Generator/Behavior/Validate/` | Kill | Imports Symfony 3.0-removed classes; generates dead code |
| `src/Propel/Generator/Behavior/QueryCache/` | Kill | Uses `apc_*` (removed PHP 5.5) |
| `src/Propel/Runtime/Connection/ConnectionManagerMasterSlave.php` | Deprecate (already `@deprecated`); kill in 4.0 | Existing deprecation |
| `src/Propel/Runtime/Connection/PropelPDO.php` | **Alias-then-kill at 4.0** (was: kill in A) | M4 in review: deletion fatals boot |
| `src/Propel/Runtime/Connection/DebugPDO.php` | **Alias-then-kill at 4.0** (was: kill in A) | Same |
| `Propel::initConfiguration()` | Already `@deprecated`; emit `trigger_deprecation`; kill 4.0 | |
| `BU_DATE`, `BU_TIMESTAMP`, `BOOLEAN_EMU` types in `PropelTypes.php` | Deprecate; kill 4.0 | Schema XSD additive: keep parsing forever per §3.7 (just emit deprecation when used) |
| `PropelTypes::OBJECT`, `PropelTypes::PHP_ARRAY` | Deprecate; kill 4.0 | Same |
| MyISAM plumbing in `MysqlPlatform`: `tableEngineKeyword`, `defaultTableEngine`, MyISAM-only `getTableOptions` | Kill | InnoDB only |
| `MysqlPlatform::getColumnBindingPHP` PECL #9919 hack (`:1036`) | Kill | Bug fixed in PHP 5.x |
| `MysqlPlatform::getBeginDDL` "MySQL >= 4.1.x" comment + logic (`:236`) | Kill | |
| `SqlitePlatform::initialize` `version_compare($v, '3.6.19')` | Kill | SQLite 3.6.19 from 2009 |
| `SqliteAdapter::__construct` `mb_regex_encoding` per-call hack (`:51`) | Move to one-time init | |
| HHVM strict-issue comments in `PdoConnection` (`:174,187`) | Kill | HHVM dead |
| `IdMethod.php` vs `IdMethodType.php` duplicates | Consolidate | Both reference Oracle |
| `tests/Fixtures/etc/xsl/` + XSLT in `AbstractManager::loadDataModels:321` | Kill | |
| `tests/Fixtures/bookstore/build/.../ValidateTriggerBook.php` | Kill | Validate-behavior artifact |
| `Serializable` interface use on `Collection.php:48` | Kill | Soft-deprecated PHP 8.1 |
| `spl_object_hash` calls in `ObjectCollection.php` (verified per Phase A audit) | Replace with `spl_object_id` | Note: cross-process hash stability lost (uncommon use) |
| `Criteria::put`/`putAll`/`get`/`keys`/`containsKey`/`keyContainsValue`/`size`/`equals` | Deprecate; kill 4.0 | Java-Hashtable rump |

### 6.2 Critical bugs to fix in Phase A

| Bug | Location |
|---|---|
| `MysqlPlatform::getMajorServerVersionNumber` off-by-one — never picks MySQL-8 NOACTION | `MysqlPlatform.php:1107` |
| `PgsqlAdapter::getId` quotes sequence as string-literal, not identifier | `PgsqlAdapter.php:111` |
| `Collection::offsetGet` returns `null` by reference (PHP 8 warns) | `Collection.php:117` |
| `cachedPreparedStatements` ignores `$driverOptions` in cache key | `ConnectionWrapper.php:389-406` |
| `ReflectionClass` per-row in formatter STI hydration | `AbstractFormatterWithHydration.php:85`, `OnDemandFormatter.php:128` |
| `PropelDateTime::__wakeup` calls parent ctor that throws on bad TZ | `PropelDateTime.php:221` |
| `MigrationManager::getAllDatabaseVersions` silently creates table on `PDOException` | `MigrationManager.php:166-194` |

### 6.3 Behavior keep-list

| Behavior | LOC (verified) | Status |
|---|---|---|
| Timestampable | TBD verified in Phase A audit | Core. Switch default to native `ON UPDATE CURRENT_TIMESTAMP`. |
| Sluggable | TBD | Core. |
| Sortable | **2,011 LOC across `SortableBehavior.php` (239) + Modifier (965) + others** | Core. Refactored to `CodeEmitter` templates in Phase D. |
| I18n | TBD | Core. |
| AutoAddPk | 62 | Core. |
| Versionable | ~1,500 | Optional. |
| Archivable | TBD | Optional. |
| AggregateColumn / AggregateMultipleColumns | TBD | Optional. Future bridge to native generated columns. |
| ConcreteInheritance | ~530 | Optional. |
| Delegate | TBD | Optional. |
| **NestedSet** | **3,036 LOC across 3 files (1,783 + 1,117 + 136)** | **Deprecate.** Recursive CTE pattern documented as replacement. Kill in 4.0. |
| **Validate** | n/a | **Kill in A.** |
| **QueryCache** | n/a | **Kill in A.** |

### 6.4 Capability additions

| Capability | Phase |
|---|---|
| Native `JSON` / `JSONB` column type with PG operator helpers | C |
| Generated columns: `<column generated="virtual\|stored" expression="...">` | C |
| CHECK constraints in schema model + DDL | C |
| `INVISIBLE` columns (MySQL 8, MariaDB 10.3+) | C |
| PG `IDENTITY` columns (replaces deprecated `serial`/`bigserial`) | C |
| Migration table fields: `migration_name`, `batch`, `checksum` | H |
| Migration tooling: dry-run, squash, baseline, drift detection | H |
| Backed enum classes for ENUM columns | B |
| `KeyType` enum alongside `TableMap::TYPE_*` | F |
| `Comparison` / `JoinType` / `SortOrder` / `LogicalOperator` enums alongside `Criteria::*` | F |
| Per-column typed Criterion classes (opt-in) | F (stretch) |
| `TelemetryInterface` + no-op default | I |
| `propel/telemetry-otel`, `propel/telemetry-prometheus` adapter packages | I |
| Replica routing: `forcePrimary()`, `allowReplica()`, session consistency, lag awareness | E |
| Worker-mode hooks: `onWorkerStart`, `onRequestStart`/`End`, fiber-safe tx | J |
| Streaming `Generator`-based formatter | G |
| `WeakMap` instance pool variant | G |
| PHP 8.4 lazy-object relation collections (opt-in 4.0; default 4.1) | G |
| `#[\Override]` mechanical sweep (~92 sites) | A |
| `declare(strict_types=1)` in generated base classes | B |
| Typed properties in generated entity attributes | B |
| `trigger_deprecation()` infrastructure | A |
| `symfony/phpunit-bridge` deprecation telemetry in CI | A |
| CI signature-diff gate (concrete spec in §3.4) | A |
| Coverage restoration (PCOV) | A |
| Infection mutation testing | A |
| Deptrac architecture testing | A |
| Golden-file generator regression | A |
| Generated-code lint parity | A |
| `propel/rector-rules` package (4.0 mechanical upgrade) | G/release |
| `docs/MIGRATION-FROM-PRE-AI.md` | A |
| `UPGRADE-3.0.md` + `UPGRADE-4.0.md` checklists | A (3.0 doc), G (4.0 doc) |
| `Propel\Testing\Factory` + `RefreshDatabaseTrait` (test primitives) | G |
| PHPStan extension: typed `findOneByX()` returns, `ObjectCollection<T>` generics | B' |

---

## 7. Risk Register & Validation Strategy

### 7.1 Top risks (post-review)

1. **Generated method signature drift** — concrete signature-diff gate (§3.4) is the load-bearing mitigation. Must ship in Phase A or §3.4 promise is theatre.
2. **`Criteria::CUSTOM` raw-SQL injection** — Phase A audits, F replaces with parameterized alternative.
3. **`PgsqlAdapter::getId` mis-quoting** — Phase A fix.
4. **Unbounded prepared-statement cache** — Phase E LRU.
5. **`Validate`/`QueryCache` deletions** — XML schema parser throws clear "removed in 3.0, see migration guide" exception (Phase A).
6. **PHP 8.4 lazy objects untested at scale in ORMs** — opt-in via `<table use-lazy-objects>`; numerical rollback criterion (≤+5% latency on 100k-row `with()`) gates default flip in 4.1.
7. **Behavior third-party Packagist packages break** — `ObjectBuilderApi` interface (§2.3) closes the silent-break-at-codegen hole; runway plus Rector rules ease migration.
8. **Generated `: static` LSP break** — closed by §3.5 (use `: self`).
9. **`DebugPDO`/`PropelPDO` deletion fatals existing deployments** — closed by §3.6 (alias-then-kill).
10. **Baseline backsliding** — monotonic CI gate (§4.1).
11. **Worker-mode state leaks** — Phase J + assert-no-leaked-state test mode.

### 7.2 Per-phase validation (additive to §4.9 Definition of Done)

- **Phase A:** all quality gates installed and reporting numbers; baselines drawn down 20%; `MIGRATION-FROM-PRE-AI.md` published; signature-diff gate working on bookstore fixture; perf baselines captured.
- **Phase B:** existing plan + golden-file diff + lint parity green per task.
- **Phase B':** Sortable/NestedSet refactors produce byte-identical output via `CodeEmitter` (validates the refactor).
- **Phase C:** Containerized MySQL 8 + MariaDB 10.5 + PG 14 + PG 16 reverse fixtures (testcontainers-php). PBT round-trip: DDL → reverse → forward → equal-DDL up to normalization. Corner-case tests: INVISIBLE, generated, partial-index, collation.
- **Phase D:** Behavior tests pass; killed-behavior schemas throw clear errors; refactored generators byte-identical (gate from B').
- **Phase E:** Decorator interface contract test. PBT for LRU eviction. Failure-injection: PDO drop mid-tx, cache-evict-during-prepare, deadlock retry. Connection benchmark in CI (≤±5% query overhead vs pre-collapse). Replica routing tested with two-DB harness.
- **Phase F:** `Comparison::Equal->value === Criteria::EQUAL` contract test. `replaceNames` rewrite: token-equivalence PBT against legacy implementation; randomized SQL fragment fuzzer.
- **Phase G:** Hydration benchmark on 100k rows (≥30% faster vs 3.0 baseline). Memory profile of `with()` query. Lazy-object opt-in/opt-out matrix tested. Mutation score ≥75 on `Runtime/`.
- **Phase H:** Migration roundtrip test; checksum drift test; dry-run preview test; squash test.
- **Phase I:** Telemetry interface contract test; OTEL adapter integration test (in adapter-package CI, not core).
- **Phase J:** Long-running-process leak detection: 10k-request worker run, instance-pool size bounded, no unclosed transactions.

### 7.3 External validation

- **Consumer smoke test:** `tests/integration/consumer-smoke/` mini-project — Phase A deliverable — exercises Tier 1 surface (find/filterBy/save/delete/with/paginate). Run on every PR.
- **Ecosystem coverage:** advisory CI job runs the test suites of 2–3 Packagist projects depending on `maturix/propel` against each phase merge (e.g., `propel/propel-bundle`, real Symfony app). Failures surface as advisory warnings — do not block merge but require maintainer note.
- **Version compatibility matrix in README.md**: which Propel version supports which PHP / MySQL / MariaDB / PG / Symfony combinations. Updated per phase.

---

## 8. Release Engineering & Versioning

### 8.1 Version map (canonical)

See §1.1 above. Restated:
- **2.x** = LTS, security only
- **3.0** = this rewrite end-state at completion of Phases A–F + I
- **3.x** = Phases F (stretch parts) + G ramp + H + J groundwork
- **4.0** = PHP 8.4 minimum + Phase G removal of all 3.x deprecations
- **4.1** = lazy-object default flip per §G rollback criterion

### 8.2 SemVer policy

- 3.x: NO removals; no signature-narrowing; only additions + deprecations.
- 4.0: removals of all `@deprecated`-since-3.x items; PHP 8.4 floor.
- 4.x: same SemVer guarantee as 3.x relative to 4.0.

### 8.3 Documentation

- `CHANGELOG.md` — Keep-a-Changelog format. PR template requires entry.
- `UPGRADE-3.0.md` — Phase A deliverable. Tier-by-tier migration guide.
- `UPGRADE-4.0.md` — Phase G deliverable. Lists all 3.x deprecations + their Rector rules.
- `MIGRATION-FROM-PRE-AI.md` — Phase A deliverable. For users of pre-rewrite Propel landing on 3.0.
- `BACKWARD_COMPATIBILITY.md` — Phase A deliverable. Pins the §3 tier definitions outside this spec.
- README.md compatibility matrix — updated per phase.

### 8.4 Deprecation telemetry

- `symfony/phpunit-bridge` installed; `SYMFONY_DEPRECATIONS_HELPER=max[self]=0` in CI.
- Allowlist file `tests/deprecations.allowlist` for known deprecations; only manual additions allowed via PR.
- Phase A: capture today's 6 `@deprecated` markers as the allowlist baseline.

### 8.5 Rector ruleset (`propel/rector-rules`)

Mechanical upgrades for 4.0:
- `Criteria::EQUAL` → `Comparison::Equal` (and analogues)
- `slaves` → `replicas` config keys; `master` → `primary`
- `IDMethod::*` → enum cases
- `DebugPDO`/`PropelPDO` → `ConnectionWrapper`
- `BU_DATE`/`BU_TIMESTAMP`/`BOOLEAN_EMU` schema types → modern equivalents
- `<behavior name="validate">` / `<behavior name="query_cache">` → schema parser error guidance

Ships as a separate Composer package; tagged in lockstep with 4.0 release.

---

## 9. Cross-references

- **Companion plan (Phase B):** `docs/plans/2026-02-03-builder-om-modernization.md` — 37 tasks. **Must amend task 2.6** (`: self` not `: static`).
- **Source of agent analyses:** four critical-review agents on 2026-05-06 (initial pass) + four critical-review agents on 2026-05-06 (review-of-review pass synthesizing v1→v2). Reports ephemeral; findings folded into this spec.

---

## 10. Open questions deliberately deferred

- **Phase E:** exact `ConnectionDecoratorInterface` contract (added → resolved in §2.1).
- **Phase F:** whether `Criteria` becomes `final` post-deprecation cycle. **Decision deferred to F plan; constraint: cannot break Tier 2 Behavior subclasses.**
- **Phase G:** streaming formatter as default vs. additive `findStream()`. **Decision: ADDITIVE in 3.x via `findStream()`/`findOnDemand()`. Default flip considered for 4.x post-data.** Closes review N3.
- **Phase H:** migration `checksum` algorithm. **Default proposed: SHA-256 over normalized SQL output of the up migration. Confirmed at H plan time.**

---

## 11. Critical changes from v1 (for reviewers)

### v2 (post first critical-review pass)

1. **§1.1 Version map** added. 2.x LTS, 3.0 rewrite, 4.0 PHP-8.4 + removals.
2. **§2 Target Architecture** added. Connection chain post-E, Criteria split post-F, Builder template strategy, DI/facade decision, TelemetryInterface.
3. **§3 BC tiers** corrected: enumerated generated-code surface (`useXxxQuery`, `XxxQuery::create`, magic dispatch); fixed Criteria constant count (~38, not 31); fixed `ActiveRecordInterface` "single method, sacred" wording; introduced `ObjectBuilderApi` Tier 2 facade; flipped `: static` to `: self`; alias-then-kill `DebugPDO`/`PropelPDO`; concretized signature-diff gate (snapshot format, allowlist, update process); fixed `trigger_deprecation` package name to `maturix/propel`; deferred `#[\Deprecated]` to 4.0/PHP-8.4; pinned XSD hosting; added Tier 2 entries for `Criterion`/`PropelException`/`Util` classes.
4. **§4 Quality Gates** added (entire section). Baseline drawdown contract, PHPUnit fail-flag restoration, coverage floors, mutation testing, Deptrac, generated-code lint parity, golden-file regression, Definition of Done, numerical perf targets, PBT infrastructure, chaos tests.
5. **§5 Phases** revised: added B' (builder architecture refactor), I (observability), J (worker mode); expanded E (replica routing), F (typed Criterion DSL stretch), H (migration tooling). Risk levels recalibrated (A bumped to Medium; H bumped to Medium).
6. **§6 Lists** corrected: Sortable 2,011 LOC (not 965); NestedSet 3,036 LOC (not 2,900); `DebugPDO`/`PropelPDO` moved from kill-in-A to alias-deprecate-kill-4.0.
7. **§8 Release Engineering** added. SemVer policy, CHANGELOG, UPGRADE docs, deprecation telemetry, Rector ruleset.
8. **§1.3 Out of scope** expanded: explicit scope-outs for multi-tenancy, governance, JSON shape DSL.

### v2.1 (review/test/iteration framework as first-class deliverables)

9. **§4.13 Review Team & Protocol** added. 5 standing reviewers (architecture, BC realism, quality/rigor, performance, ambition) + per-phase specialists (DBA, security, SQL/concurrency, PHP 8.4 internals, observability, etc.). 2-round cadence for low-risk phases; 3-round for high-risk. Reviewer reports are a phase deliverable, committed under `docs/reviews/`.
10. **§4.14 Surgical Test Battery** added. 11 test types enumerated (PBT, mutation, differential, chaos, benchmarks, consumer-smoke, ecosystem advisory, fuzzing, architecture conformance, signature-diff, golden-file). Every applicable type produces a committed report; the report IS the deliverable.
11. **§4.15 Iteration Loop** added. MUST-FIX vs SHOULD-FIX vs NICE finding tags. 3-cycle iteration budget per round; over-budget escalates to maintainer review. Waiver discipline (no "out of scope" without where, no "fix later" without tracking issue).
12. **§4.9 Definition of Done** extended from 11 to 15 checkboxes; review and surgical-test deliverables are now exit-blocking.
13. **§5.0** added: cross-reference establishing the review-and-test protocol applies to every phase.
