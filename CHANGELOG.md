# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased] — Phase A: Foundations

### Added

- `symfony/deprecation-contracts` ^3.5 as a direct dependency.
- `symfony/phpunit-bridge` ^7.2 as a dev dependency for deprecation telemetry.
- `infection/infection` ^0.29 (dev) — mutation testing.
- `deptrac/deptrac` ^2.0 (dev) — architecture-layer conformance.
- `innmind/black-box` ^6.0 (dev) — property-based testing.
- `bin/propel-internal-dump-signatures` — JSON snapshot dumper for the BC signature-diff gate.
- `tools/check-baseline-monotonic.php` — CI guard against phpstan/psalm baseline regressions.
- `tools/regen-golden.php` — refreshes the committed golden bookstore tree.
- `tools/capture-perf-baseline.php` — runs benchmarks and emits a stable JSON for perf comparison.
- `tests/snapshots/` — Tier 1 frozen public-API surface snapshots (53 classes covering generated AR, ModelCriteria, Criteria constants, Criterion hierarchy, PropelException hierarchy, Util classes, Connection management).
- `tests/snapshots/bookstore-golden/` — 399 generated PHP files committed for character-by-character regression diff.
- `tests/Propel/Tests/Benchmark/` — perf baseline harness + initial `CriteriaBuildBench`.
- `tests/Propel/Tests/ChaosTests/` — failure-injection scaffold (real tests land in Phase E/J).
- `tests/Propel/Tests/PropertyTests/` — property-based testing scaffold using innmind/black-box.
- `tests/integration/consumer-smoke/` — scaffold for the Tier 1 end-to-end consumer test (full project lands when Tier 1 is e2e-testable).
- `.github/workflows/quality-gates.yml` — new CI workflow with jobs: signature-diff, baseline-monotonic, lint-generated, deptrac, golden-diff.
- `tests/sqlite.phpunit.xml` — strict fail-flags + Symfony PHPUnit bridge alignment.
- `tests/snapshots/tracked-classes.txt` — Tier 1/2 class registry consumed by the snapshot dumper.
- `tests/deprecations.allowlist.json` — Symfony PHPUnit bridge deprecation allowlist (currently empty).
- `infection.json5`, `deptrac.yaml`, `deptrac-baseline.yaml` — tooling configuration.
- `tests/Propel/Tests/Generator/Platform/MysqlPlatformTest::testGetMajorServerVersionNumberPicksMySql8` — regression for off-by-one in version parsing.
- `tests/Propel/Tests/Runtime/Adapter/Pdo/PgsqlAdapterTest::testGetIdQuotesSequenceAsIdentifier` — regression for sequence quoting.
- `tests/Propel/Tests/Runtime/Collection/CollectionOffsetGetTest` — regression for by-reference null return.
- `tests/Propel/Tests/Runtime/Connection/ConnectionWrapperPrepareCacheTest` — regression for driverOptions cache key.
- `tests/Propel/Tests/Runtime/Formatter/AbstractFormatterReflectionCacheTest` — regression for ReflectionClass caching.
- `tests/Propel/Tests/Runtime/Util/PropelDateTimeWakeupTest` — regression for invalid stored timezone fallback.
- `tests/Propel/Tests/Generator/Manager/MigrationManagerTableNotFoundTest` — regression for table-not-found heuristic (data-provided, 7 cases).
- `tests/Propel/Tests/Generator/Model/RemovedBehaviorErrorTest` — regression for Validate / QueryCache schema-parser hard error.
- `MigrationInterface` — typing entry for migration class call sites.
- `NestedSetNodeInterface` — typing entry for nested-set iterator.
- `docs/MIGRATION-FROM-PRE-AI.md`, `docs/UPGRADE-3.0.md`, `docs/BACKWARD_COMPATIBILITY.md`, `CHANGELOG.md`, README compatibility matrix.
- `docs/reviews/` directory + Round 1 reports (architecture, BC realism, tooling/CI specialist) + summary + waivers + iteration log + perf-baseline.json.
- Docblock `@method toArray()` on `ActiveRecordInterface` documenting the generated-AR contract per umbrella §3.1.

### BC Breaks (Phase B)

- **Generated setter return type — LSP requirement on subclass overrides.** Generated `setX()` methods now declare `: self` (previously had no return type). User subclasses that override a generated setter without a matching return type will fatal at class load. See `docs/MIGRATION-FROM-PRE-AI.md` § "API surface changes" → ": self return type on generated setters" for migration code and grep patterns. The same rule applies to FK getters (`: ?Publisher` etc.).
- **Generated AR `__sleep` → `__serialize`/`__unserialize` wire-format change.** Persisted serialized AR objects (sessions, caches, message queues) created on pre-rewrite Propel cannot round-trip through the new methods. PHP-level `serialize()`/`unserialize()` behavior is preserved, but the byte sequence on the wire is not. Invalidate stale caches at deploy time, or use the one-time read-old-write-new pattern documented in `docs/MIGRATION-FROM-PRE-AI.md`.
- **`PropelTypes::*_NATIVE_TYPE` constant value drift.** `REAL_NATIVE_TYPE`, `FLOAT_NATIVE_TYPE`, `DOUBLE_NATIVE_TYPE` shifted from `'double'` to `'float'`; `BOOLEAN_NATIVE_TYPE`, `BOOLEAN_EMU_NATIVE_TYPE` from `'boolean'` to `'bool'` (PHP-canonical names). Consumer code reading these constants and comparing against literal strings (`=== 'double'`, `=== 'boolean'`) will silently see false. Compare against the constant itself instead. `PropelTypes::isPhpPrimitiveType()` accepts both old and new spellings, so call-site behavior through that helper is unchanged.

### Changed

- `composer.json` — `testsuite` script now includes `composer run deptrac`.
- `composer.json` `allow-plugins` — added `infection/extension-installer`.
- `phpcs.xml` `phpVersion` 7.4 → 8.3.
- `phpunit.xml.dist` — moved from repo root to `tests/` (paths were relative to `tests/` cwd; broken at root). `tests/phpunit.xml.dist` is now a symlink to `agnostic.phpunit.xml`.
- `tests/{agnostic,mysql,pgsql,sqlite}.phpunit.xml` — `failOnDeprecation`, `failOnPhpunitDeprecation`, `failOnWarning`, `failOnRisky`, `failOnIncomplete`, `failOnNotice`, `failOnEmptyTestSuite` flipped to `true`. `SYMFONY_DEPRECATIONS_HELPER` env var wired with allowlist baseline. Symfony PHPUnit `<bootstrap>` extension registered.
- `tests/agnostic.phpunit.xml` — added `property` and `chaos` testsuites; `propel2` testsuite excludes `PropertyTests/`, `ChaosTests/`, `Benchmark/`.
- `.github/workflows/ci.yml` — restored `coverage: pcov` (was `none`); added `sqlite` to db-type matrix; added coverage-clover artifact upload.
- `phpstan.neon` — dropped 4 blanket regex `ignoreErrors` entries (only `missingType.iterableValue` identifier exception remains).
- `psalm.xml` — dropped `ImplementedReturnTypeMismatch` global suppression.
- `phpstan-baseline.neon` — drawn down 548 → 403 lines (≤80% Phase A target).
- `psalm-baseline.xml` — drawn down 2598 → 1621 lines (≤80% Phase A target).
- `infection.json5` — fixed `configDir` to `tests/`; dropped duplicate `--configuration` arg.
- 177 test files — migrated PHPUnit doc-comment metadata (`@dataProvider` etc.) to PHP attributes (`#[DataProvider]` etc.) via Rector. Cleared 679 PHPUnit deprecation warnings.
- All test files using mock APIs — `->will($this->returnValue(X))` → `->willReturn(X)` and 7 `getMockForTrait()` rewrites. Cleared 189 additional PHPUnit deprecation warnings.
- ~92 method overrides across `src/` — `#[\Override]` attribute added (Rector + 3 manual).
- `src/Propel/Generator/Builder/Om/AbstractObjectBuilder.php` — column accessor/mutator orchestration relocated into `ObjectBuilder` (where the traits live).
- `src/Propel/Runtime/Formatter/AbstractFormatter::getReflectionClass()` — new shared per-class cache eliminating per-row Reflection allocation in STI hydration.
- `src/Propel/Runtime/ActiveQuery/SqlBuilder/*` — by-ref `?array $params` signatures tightened to `array $params`.
- `src/Propel/Common/Config/PropelConfiguration.php` — `slaves:` / `master:` keys forward to `replicas:` / `primary:` with deprecation; unsupported `oracle`/`mssql`/`sqlsrv` adapters produce migration-guide-pointer error.

### Deprecated

- `Propel\Runtime\Connection\DebugPDO` — alias-deprecated; removal at 4.0.
- `Propel\Runtime\Connection\PropelPDO` — alias-deprecated; removal at 4.0.
- `Propel\Runtime\Connection\ConnectionManagerMasterSlave` — `trigger_deprecation` added to constructor (was `@deprecated` only); removal at 4.0.
- Config keys `slaves:`, `master:` — emit deprecation forwarding to `replicas:`, `primary:`.
- `PropelTypes::BU_DATE`, `BU_TIMESTAMP`, `BOOLEAN_EMU`, `OBJECT`, `PHP_ARRAY` column types — emit deprecation when resolved.

### Removed

- `src/Propel/Generator/Behavior/Validate/` — entire directory (Symfony 3.0-removed imports; broken on Symfony 4+).
- `src/Propel/Generator/Behavior/QueryCache/` — entire directory (used `apc_*`; removed PHP 5.5).
- `src/Propel/Runtime/Validator/Constraints/` — entire directory (Symfony 6+ supports `DateTimeInterface` natively).
- `tests/Fixtures/bookstore/behavior-{validate,validate-triggers,query_cache}-schema.xml`.
- `tests/Propel/Tests/Generator/Behavior/{Validate,QueryCache}/` test classes (3 files).
- 30+ generated `ValidateTrigger*` fixture artifacts.
- MyISAM-only options in `MysqlPlatform::getTableOptions` (DelayKeyWrite, PackKeys, RowFormat, InsertMethod, Union).
- MySQL 4.1.x conditional in `MysqlPlatform::getBeginDDL`.
- PECL #9919 bool-int hack in `MysqlPlatform::getColumnBindingPHP`.
- HHVM strict-issue comments in `PdoConnection`.
- XSLT pipeline in `AbstractManager::loadDataModels`.
- `tests/Fixtures/etc/xsl/` directory.
- `Serializable` interface from `Collection` (PHP 8.1 soft-deprecated).
- `Collection::serialize()` / `unserialize()` legacy methods (kept `__serialize` / `__unserialize`).
- 4 blanket regex `ignoreErrors` in `phpstan.neon`.
- 1 global suppression (`ImplementedReturnTypeMismatch`) in `psalm.xml`.

### Fixed

- `MysqlPlatform::getMajorServerVersionNumber` off-by-one (returned 0 for "8.0.30" — MySQL-8 NOACTION default-FK-action branch was never picked). [#A.14]
- `PgsqlAdapter::getId` mis-quoted sequence name as string-literal instead of identifier (broken for case-sensitive sequence names; latent injection vector). [#A.15]
- `Collection::offsetGet` returned `null` from a by-reference function (PHP 8 notice). [#A.16]
- `ConnectionWrapper` prepared-statement cache ignored `$driverOptions` in cache key (silent statement reuse with wrong cursor type / fetch mode). [#A.17]
- `AbstractFormatterWithHydration` and `OnDemandFormatter` allocated `new ReflectionClass($class)` per-row in STI hydration — O(rows × with-relations) wasted allocations. [#A.18]
- `PropelDateTime::__sleep`/`__wakeup` were shadowed by parent `DateTime::__serialize` on PHP 8+ (dead serialization path). Migrated to `__serialize`/`__unserialize` API; tolerates invalid stored timezones with E_USER_WARNING fallback to UTC. [#A.19]
- `MigrationManager::getAllDatabaseVersions` silently created the migration table on ANY `PDOException` — masked typo'd table names, permission errors, network failures, schema drift. Now only auto-creates on `42S02`/`42P01` SQLState or driver-specific "no such table" / "does not exist" messages. [#A.20]
- `spl_object_hash` calls in `ObjectCollection` replaced with `spl_object_id` (cheaper int hash; cross-process stability lost — uncommon use case).
- Latent dangling references to removed `Validate` / `QueryCache` classes in `I18nBehavior`.
- `tests/agnostic.phpunit.xml` `propel2` testsuite would emit a "duplicate file added to test suite" warning because `Propel/Tests/PropertyTests/` was both included and claimed by the new `property` testsuite — now explicitly excluded.

### Security

- `PgsqlAdapter::getId` sequence-name quoting (see Fixed). Was a latent injection vector if sequence names ever reached the call site from user-controllable config.
