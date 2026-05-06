# Upgrade Guide — Propel 3.0

For users upgrading from Propel 2.x to 3.0. For users coming from pre-rewrite Propel (any earlier branch), see `MIGRATION-FROM-PRE-AI.md` first; this guide focuses on 2.x → 3.0 specifically.

## Required environment changes

| Component | 2.x minimum | 3.0 minimum |
|---|---|---|
| PHP | 8.3 | 8.3 (8.4 supported) |
| MySQL | 5.7 | 8.0 |
| MariaDB | 10.4 | 10.5 |
| PostgreSQL | 12 | 14 |
| Symfony | 7.0 | 7.2 |

## New required dependencies

Propel 3.0 declares two new direct dependencies:
- `symfony/deprecation-contracts: ^3.5` — for the `trigger_deprecation()` convention used throughout 3.x.
- `symfony/phpunit-bridge: ^7.2` (dev only) — for deprecation telemetry during your test suite.

Both will be pulled in automatically by `composer update`.

## Tier 1 frozen public API

The following surfaces are frozen for the entire 3.x line — no removals, no signature narrowing. Consumer code that uses these is safe.

- All generated `XxxQuery::create()`, `useXxxQuery()`, `findByX()`, `filterByX()`, `joinWithXxx()` per-relation methods.
- Generated AR `save()`, `delete()`, `reload()`, `hydrate()`, `toArray()`, `fromArray()`.
- All `Criteria::*` constants (38 entries).
- `TableMap::TYPE_*` constants.
- `Propel::` static facade.
- `ActiveRecordInterface`, `ConnectionInterface`.

Full enumeration: `tests/snapshots/tracked-classes.txt` + the `*.signatures.json` files under `tests/snapshots/`. The CI `signature-diff` gate enforces stability across releases.

## Tier 2 deprecation runway

The following extension points emit deprecation warnings in 3.x and will be removed in 4.0. Update before 4.0 ships.

- `Propel\Runtime\Connection\DebugPDO`, `PropelPDO` → use `ConnectionWrapper` directly.
- `Propel\Runtime\Connection\ConnectionManagerMasterSlave` → use `ConnectionManagerPrimaryReplica`.
- `Propel\Generator\Behavior\Validate\*` → REMOVED in 3.0 (was already broken on Symfony 4+; see `MIGRATION-FROM-PRE-AI.md`).
- `Propel\Generator\Behavior\QueryCache\*` → REMOVED in 3.0 (was already broken — used `apc_*`).
- `Propel\Generator\Behavior\NestedSet\*` → DEPRECATED in 3.0; recursive-CTE pattern replaces it.
- Schema `<column type="BU_DATE|BU_TIMESTAMP|BOOLEAN_EMU|OBJECT|PHP_ARRAY">` → see migration table in MIGRATION-FROM-PRE-AI.md.
- Config `slaves:` / `master:` keys → use `replicas:` / `primary:`.

## New capabilities

3.0 adds:
- `symfony/deprecation-contracts` integration.
- `tests/snapshots/` (Tier 1 BC contract enforcement).
- `tests/snapshots/bookstore-golden/` (generated-code regression).
- `tests/Propel/Tests/Benchmark/` (perf baseline).
- `tests/Propel/Tests/ChaosTests/` (failure-injection scaffold).
- `tests/Propel/Tests/PropertyTests/` (property-based testing via innmind/black-box).
- Quality-gates CI workflow (signature-diff, baseline-monotonic, lint-generated, deptrac, golden-diff, consumer-smoke).
- Deptrac architecture conformance.
- Infection mutation testing.
- `bin/propel-internal-dump-signatures`, `tools/regen-golden.php`, `tools/capture-perf-baseline.php`, `tools/check-baseline-monotonic.php`.
- Bug fixes — see `CHANGELOG.md` "Phase A — Foundations" section.

## Internal-only changes (Tier 3, free-reign — not BC)

- `phpcs.xml` `phpVersion` 7.4 → 8.3.
- `phpunit.xml.dist` moved into `tests/`.
- All test files migrated from PHPDoc metadata to PHP attributes (`#[DataProvider]`, `#[Group]`, etc.).
- Mock API `$this->returnValue(X)` → `->willReturn(X)` mechanical sweep.
- ~92 method overrides got `#[\Override]` attribute.
- 4 blanket regex `ignoreErrors` removed from `phpstan.neon`; `ImplementedReturnTypeMismatch` global suppression dropped from `psalm.xml`.
- phpstan + psalm baselines drawn down ≥20%.

These don't affect your code; they affect ours.

## Future: 4.0

Propel 4.0 will ship at the end of Phase G (umbrella spec §5). Major changes:
- PHP 8.4 minimum.
- All Tier 2 deprecations removed.
- Lazy objects for relation collections.
- Asymmetric visibility on generated entity properties.
- Streaming `Generator`-based formatter.

A `propel/rector-rules` package will mechanically migrate consumer code at 4.0.
