# Backward Compatibility Contract

Propel uses a three-tier BC contract. This document is the durable reference; the umbrella spec at `docs/plans/2026-05-06-modernization-umbrella-spec.md` §3 is the strategic definition.

## Tier 1 — Frozen public API

No removals. No signature narrowing. New parameters allowed only with defaults. Enums and modern alternatives may be added **alongside**, never replacing.

Includes:
- All generated `XxxQuery::create()`, `useXxxQuery()`, `findByX()`, `filterByX()`, `joinWithX()` per-relation methods.
- Generated AR `save()`, `delete()`, `reload()`, `hydrate()`, `isNew()`/`setNew()`, `isModified()`/`setModified()`, `isDeleted()`/`setDeleted()`, `isPrimaryKeyNull()`, `toArray()`, `fromArray()`, `importFrom()`, `exportTo()`, per-column `getX()`/`setX()`, per-relation `getXs()`/`addX()`, `getPrimaryKey()`/`setPrimaryKey()`, `getByName()`/`setByName()`, `getByPosition()`/`setByPosition()`.
- `ModelCriteria::where()`, `orderBy()`, `groupBy()`, `limit()`, `offset()`, `select()`, `distinct()`, `join()`, `joinWith()`, `with()`, `useQuery()`, `endUse()`, `condition()`, `having()`, `whereExists()`, `whereNotExists()`, `findBy()`, `findOneBy()`, `findByArray()`, `findOneByArray()`, `findOneOrCreate()`, `requirePk()`, `requireOne()`, `requireOneBy()`, `find()`, `findOne()`, `findPk()`, `findPks()`, `count()`, `exists()`, `paginate()`, `delete()`, `deleteAll()`, `update()`.
- All `Criteria::*` constants (~38 entries; full list in `tests/snapshots/Propel_Runtime_ActiveQuery_Criteria.signatures.json`).
- `TableMap::TYPE_*` constants + `getFieldnamesForClass()` / `translateFieldnameForClass()`.
- `Propel::` static facade (18 methods).
- `ActiveRecordInterface` (`isPrimaryKeyNull()` + `@method toArray()` PHPDoc).
- `ConnectionInterface` (mirrors PDO; parameter looseness preserved).

The CI `signature-diff` gate enforces this tier at every PR via the snapshots in `tests/snapshots/*.signatures.json`. Drift requires a paired `@deprecated` annotation.

## Tier 2 — Deprecation runway required

One full minor in the current major with `trigger_deprecation('maturix/propel', '3.X', ...)` before removal in the next major.

Includes:
- `AdapterInterface`, `SqlAdapterInterface`.
- `Behavior` abstract class hooks: `objectFilter`, `objectAttributes`, `objectMethods`, `queryMethods`, `staticMethods`, `tableMapFilter`, `preSave`/`postSave`/`preUpdate`/`postUpdate`/`preDelete`/`postDelete`.
- All 19 `Criterion` classes (`BasicCriterion`, `RawCriterion`, `CustomCriterion`, `LikeCriterion`, `InCriterion`, `ExistsCriterion`, `BinaryCriterion`, `CriterionFactory`, `AbstractCriterion`, etc.).
- `PropelException` hierarchy (9 typed exceptions).
- Util classes returned by Tier 1 methods: `PropelDateTime`, `PropelModelPager`, `Profiler`, `UuidConverter`.
- `AbstractFormatter::format()`/`formatOne()`. `Collection`, `ObjectCollection`, `ArrayCollection`, `OnDemandCollection`.
- `ConnectionManagerInterface`, `ConnectionManagerSingle`, `ConnectionManagerPrimaryReplica`.
- `StatementWrapper`, `ConnectionWrapper`.
- `ServiceContainerInterface`.
- `Map\TableMap` public methods called from generated `*TableMap::initialize()`.

## Tier 3 — Internal, free reign within SPI

These are NOT BC. Free to refactor as long as their interactions with Tier 2 remain coherent.

Includes:
- All of `src/Propel/Generator/Builder/Om/` (codegen-time only). The `ObjectBuilderApi` interface (introduced in Phase B') is the Tier 2 facade Behaviors consume.
- `src/Propel/Generator/Platform/`, `Generator/Reverse/`, `Generator/Manager/`, `Generator/Command/`.
- Runtime `ActiveQuery/SqlBuilder/` and `ActiveQuery/QueryExecutor/`.
- `Common/Config/Loader/*`, `Common/Config/XmlToArrayConverter`.
- `DebugPDO`, `PropelPDO` (alias-deprecated; removal at 4.0).
- `Generator/Util/QuickBuilder` (used in user tests; documented stability commitment to its public methods).

## Schema XSD — additive only

Schema instances valid against today's XSD remain valid against the new XSD. New attributes/elements may be added as `minOccurs=0` / optional. New enumeration values may be added to existing types. **Enumeration values are never removed**; column types are added only as additions.

## Configuration — alias, don't rename

`slaves`/`master` config keys remain parseable in 3.x with deprecation forwarding to `replicas`/`primary`. Both `connection` (singular) and `connections` (plural) accepted. Adapter enum stays `mysql|pgsql|sqlite`. Historical entries (`oracle`/`mssql`/`sqlsrv`) produce a clear migration-guide error.

## Deprecation tooling

- `trigger_deprecation('maturix/propel', '3.X', ...)` for runtime emission.
- `@deprecated` PHPDoc for static-analysis tools.
- `#[\Deprecated]` attribute reserved for 4.0+ (PHP 8.4).
- `tests/deprecations.allowlist.json` baselines known deprecations; `SYMFONY_DEPRECATIONS_HELPER=max[self]=0` fails CI on any new self-emitted deprecation that isn't allowlisted.
- `propel/rector-rules` package (lands at 4.0) automates migration of every Tier 1/2 deprecation introduced in 3.x.

## Version map

| Propel | PHP floor | Status |
|---|---|---|
| 2.x | 8.3 | LTS branch (security only) |
| 3.0 | 8.3 | Current rewrite |
| 3.x | 8.3 | Iterate; no removals |
| 4.0 | 8.4 | All 3.x deprecations removed; lazy objects + asymmetric visibility default |
| 4.x | 8.4 | Same SemVer guarantee as 3.x relative to 4.0 |
