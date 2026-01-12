# Propel2 PHP 8.3+ Modernization Plan

This document outlines the step-by-step plan to modernize Propel2 from PHP 7.4+ to PHP 8.3+ minimum.

## Quick Reference

| Metric | Current | Target |
|--------|---------|--------|
| PHP Version | 7.4+ | 8.3+ |
| Symfony | 5.x/6.x/7.x | 7.x only |
| Platforms | MySQL, PostgreSQL, SQLite, Oracle, MSSQL | MySQL, PostgreSQL, SQLite |
| strict_types | 4 files | All 309 files |

---

## Phase 1: Foundation

**Goal:** Update infrastructure, establish quality gates, prepare for safe refactoring.

**Duration:** 2-3 days

### 1.1 Version Requirements

- [ ] Update `composer.json`:
  ```json
  "php": ">=8.3",
  "symfony/*": "^7.0.0"
  ```
- [ ] Remove `tests/composer/composer-symfony5-*.json` (4 files)
- [ ] Remove `tests/composer/composer-symfony6-*.json` (4 files)
- [ ] Update `phpunit/phpunit` to `^10.0` or `^11.0`
- [ ] Run `composer update` and fix any issues

**Verification:** `composer validate && composer install`

### 1.2 CI Matrix Update

**File:** `.github/workflows/ci.yml`

- [ ] Remove PHP versions from matrix: `7.4`, `8.0`, `8.1`, `8.2`
- [ ] Keep only: `8.3`, `8.4`
- [ ] Remove Symfony version matrix entries for 5.x and 6.x
- [ ] Update code coverage job to PHP 8.3
- [ ] Remove legacy exclusion rules

**Verification:** Push to branch, verify CI runs on 8.3/8.4 only

### 1.3 Static Analysis Baseline

- [ ] Update `phpstan.neon` - set level to 8
- [ ] Run `composer stan-baseline` to regenerate baseline
- [ ] Run `composer psalm-set-baseline` to regenerate baseline
- [ ] Commit new baselines

**Verification:** `composer stan && composer psalm` pass

### 1.4 Test Infrastructure

- [ ] Update PHPUnit configuration for v10/v11 format
- [ ] Fix any deprecated PHPUnit assertions
- [ ] Create git tag `v2.x-pre-modernization` as rollback point

**Verification:** `composer test:agnostic` passes

---

## Phase 2: PHP 8.3+ Syntax Modernization

**Goal:** Modernize all PHP syntax while maintaining API compatibility.

**Duration:** 5-7 days

**Approach:** One comprehensive PR with incremental commits for each sub-step.

### 2.1 strict_types Declaration

Add `declare(strict_types=1);` to all files, by directory:

- [ ] `src/Propel/Common/` (26 files)
- [ ] `src/Propel/Runtime/Exception/` (10 files)
- [ ] `src/Propel/Runtime/Parser/` (5 files)
- [ ] `src/Propel/Runtime/Util/` (6 files)
- [ ] `src/Propel/Runtime/Validator/` (5 files)
- [ ] `src/Propel/Runtime/Map/` (6 files)
- [ ] `src/Propel/Runtime/Formatter/` (6 files)
- [ ] `src/Propel/Runtime/DataFetcher/` (4 files)
- [ ] `src/Propel/Runtime/Connection/` (16 files)
- [ ] `src/Propel/Runtime/Collection/` (9 files)
- [ ] `src/Propel/Runtime/Adapter/` (12 files)
- [ ] `src/Propel/Runtime/ActiveRecord/` (2 files)
- [ ] `src/Propel/Runtime/ActiveQuery/` (30 files)
- [ ] `src/Propel/Runtime/` root files (3 files)
- [ ] `src/Propel/Generator/Exception/` (11 files)
- [ ] `src/Propel/Generator/Config/` (4 files)
- [ ] `src/Propel/Generator/Command/` (18 files)
- [ ] `src/Propel/Generator/Util/` (6 files)
- [ ] `src/Propel/Generator/Schema/` (1 file)
- [ ] `src/Propel/Generator/Reverse/` (8 files)
- [ ] `src/Propel/Generator/Platform/` (9 files)
- [ ] `src/Propel/Generator/Model/` (25 files)
- [ ] `src/Propel/Generator/Manager/` (7 files)
- [ ] `src/Propel/Generator/Builder/` (16 files)
- [ ] `src/Propel/Generator/Behavior/` (56 files)

**After each directory:** Run `composer test:agnostic`

**Verification:** Full test suite passes

### 2.2 Typed Properties

Convert `@var` annotations to native types, by priority:

**High Priority (most annotations):**
- [ ] `src/Propel/Generator/Model/PropelTypes.php` (71 @var)
- [ ] `src/Propel/Runtime/ActiveQuery/Criteria.php` (66 @var)
- [ ] `src/Propel/Generator/Model/Column.php` (36 @var)
- [ ] `src/Propel/Generator/Builder/DataModelBuilder.php` (26 @var)

**Medium Priority:**
- [ ] `src/Propel/Generator/Model/Table.php`
- [ ] `src/Propel/Generator/Model/Database.php`
- [ ] `src/Propel/Generator/Model/ForeignKey.php`
- [ ] `src/Propel/Generator/Model/Index.php`
- [ ] `src/Propel/Runtime/Map/TableMap.php`
- [ ] `src/Propel/Runtime/Map/ColumnMap.php`
- [ ] `src/Propel/Runtime/Map/RelationMap.php`

**Remaining files:** Process remaining ~150 files with @var annotations

**Pattern:**
```php
// Before
/** @var string|null */
protected $name;

// After
protected ?string $name = null;
```

**Verification:** `composer stan` passes after each batch

### 2.3 Constructor Property Promotion

Convert constructors in these files:

- [ ] `src/Propel/Runtime/ActiveQuery/Join.php`
- [ ] `src/Propel/Generator/Model/Domain.php`
- [ ] `src/Propel/Generator/Model/ColumnDefaultValue.php`
- [ ] `src/Propel/Runtime/Map/RelationMap.php`
- [ ] `src/Propel/Runtime/Map/ColumnMap.php`
- [ ] `src/Propel/Runtime/Exception/` classes
- [ ] `src/Propel/Generator/Exception/` classes
- [ ] Remaining ~80 constructor candidates

**Pattern:**
```php
// Before
public function __construct(?string $name = null) {
    $this->name = $name;
}

// After
public function __construct(
    protected ?string $name = null,
) {}
```

**Verification:** Tests pass after each batch

### 2.4 Match Expressions

Convert switch statements to match expressions:

**High-value conversions:**
- [ ] `src/Propel/Generator/Builder/Om/ObjectBuilder.php` - getTemporalFormatter()
- [ ] `src/Propel/Runtime/ActiveQuery/Criterion/CriterionFactory.php` - build()
- [ ] `src/Propel/Generator/Model/PhpNameGenerator.php`
- [ ] `src/Propel/Generator/Command/InitCommand.php`
- [ ] `src/Propel/Generator/Builder/Util/SchemaReader.php`
- [ ] `src/Propel/Generator/Reverse/AbstractSchemaParser.php`

**Remaining:** ~45 other switch statements

**Verification:** Tests pass, PHPStan clean

### 2.5 Enum Conversions (Conservative)

**Safe to convert (low BC risk):**
- [ ] Create `src/Propel/Generator/Model/IdMethodType.php` enum
  - Values: `NATIVE`, `NO_ID_METHOD`
  - Keep `IdMethod` constants with `@deprecated`

- [ ] Create `src/Propel/Generator/Model/ForeignKeyAction.php` enum
  - Values: `NONE`, `NO_ACTION`, `CASCADE`, `RESTRICT`, `SET_DEFAULT`, `SET_NULL`
  - Keep `ForeignKey::*` constants with `@deprecated`

- [ ] Create `src/Propel/Generator/Model/DefaultValueType.php` enum
  - Values: `VALUE`, `EXPRESSION`
  - Keep `ColumnDefaultValue::TYPE_*` with `@deprecated`

**DO NOT convert yet (high BC risk):**
- `PropelTypes` constants - used in SQL generation
- `Criteria::EQUAL` etc. - used extensively in user code

**Verification:** Tests pass, no BC break for existing code

### 2.6 Additional PHP 8.x Features

- [ ] Add `readonly` to truly immutable properties where appropriate
- [ ] Use named arguments in internal calls where it improves clarity
- [ ] Remove PHP version checks (e.g., `PHP_VERSION_ID >= 80000`)

**Verification:** Full test suite passes

---

## Phase 3: ObjectBuilder Refactoring

**Goal:** Break up the 7,376-line ObjectBuilder using traits (maintains BC).

**Duration:** 7-10 days

**Strategy:** Extract methods to traits first, then move traits to separate files.

### 3.1 Add ObjectBuilder Test Coverage

**CRITICAL: Do this before any refactoring**

- [ ] Review existing `tests/Propel/Tests/Generator/Builder/Om/ObjectBuilderTest.php` (87 lines)
- [ ] Add tests for column accessor generation
- [ ] Add tests for column mutator generation
- [ ] Add tests for FK method generation
- [ ] Add tests for persistence method generation
- [ ] Target: 500+ lines of ObjectBuilder tests

**Verification:** New tests pass, establish baseline

### 3.2 Extract ColumnAccessorBuilderMethods Trait

**Methods to extract (~1,200 lines):**
- [ ] `addColumnAccessorMethods()`
- [ ] `addDefaultAccessor()`
- [ ] `addTemporalAccessor()`
- [ ] `addTemporalAccessorComment()`
- [ ] `addObjectAccessor()`
- [ ] `addBooleanAccessor()`
- [ ] `addEnumAccessor()`
- [ ] `addSetAccessor()`
- [ ] `addArrayAccessor()`
- [ ] `addJsonAccessor()`
- [ ] `addLobAccessor()`

**Implementation:**
```php
// In ObjectBuilder.php, add at top:
trait ColumnAccessorBuilderMethods {
    // Move methods here
}

class ObjectBuilder extends AbstractObjectBuilder {
    use ColumnAccessorBuilderMethods;
    // ...
}
```

**Verification:** All tests pass, generated code identical

### 3.3 Extract ColumnMutatorBuilderMethods Trait

**Methods to extract (~1,000 lines):**
- [ ] `addColumnMutatorMethods()`
- [ ] `addMutatorOpen()`
- [ ] `addMutatorOpenBody()`
- [ ] `addMutatorClose()`
- [ ] `addTemporalMutator()`
- [ ] `addEnumMutator()`
- [ ] `addSetMutator()`
- [ ] `addArrayMutator()`
- [ ] `addJsonMutator()`
- [ ] `addLobMutator()`

**Verification:** All tests pass, generated code identical

### 3.4 Extract ForeignKeyBuilderMethods Trait

**Methods to extract (~1,400 lines):**
- [ ] `addFKMethods()`
- [ ] `addFKAttributes()`
- [ ] `addFKAccessor()`
- [ ] `addFKMutator()`
- [ ] All FK-related methods (35 methods total)

**Verification:** All tests pass, FK tests specifically

### 3.5 Extract ReferrerBuilderMethods Trait

**Methods to extract (~800 lines):**
- [ ] `addRefFKMethods()`
- [ ] `addRefFKGet()`
- [ ] `addRefFKAdd()`
- [ ] `addRefFKRemove()`
- [ ] Related referrer methods

**Verification:** All tests pass

### 3.6 Extract CrossForeignKeyBuilderMethods Trait

**Methods to extract (~1,100 lines):**
- [ ] `addCrossFKMethods()`
- [ ] `addCrossFKGet()`
- [ ] `addCrossFKAdd()`
- [ ] `addCrossFKRemove()`
- [ ] Related cross-FK methods

**Verification:** All tests pass

### 3.7 Extract PersistenceBuilderMethods Trait

**Methods to extract (~700 lines):**
- [ ] `addSave()`
- [ ] `addDoSave()`
- [ ] `addDoInsert()`
- [ ] `addDoUpdate()`
- [ ] `addDelete()`
- [ ] `addBuildCriteria()`
- [ ] Related persistence methods

**Verification:** All tests pass, persistence tests specifically

### 3.8 Move Traits to Separate Files

- [ ] Create `src/Propel/Generator/Builder/Om/ObjectBuilder/` directory
- [ ] Move `ColumnAccessorBuilderMethods.php`
- [ ] Move `ColumnMutatorBuilderMethods.php`
- [ ] Move `ForeignKeyBuilderMethods.php`
- [ ] Move `ReferrerBuilderMethods.php`
- [ ] Move `CrossForeignKeyBuilderMethods.php`
- [ ] Move `PersistenceBuilderMethods.php`
- [ ] Update ObjectBuilder imports

**Final structure:**
```
src/Propel/Generator/Builder/Om/
  ObjectBuilder.php (~800 lines, uses traits)
  ObjectBuilder/
    ColumnAccessorBuilderMethods.php
    ColumnMutatorBuilderMethods.php
    ForeignKeyBuilderMethods.php
    ReferrerBuilderMethods.php
    CrossForeignKeyBuilderMethods.php
    PersistenceBuilderMethods.php
```

**Verification:** Full test suite, PHPStan, code generation test

---

## Phase 4: Platform Cleanup

**Goal:** Remove Oracle and MSSQL/SqlSrv support.

**Duration:** 2-3 days

### 4.1 Deprecation Warnings

- [ ] Add `@deprecated` to OraclePlatform class
- [ ] Add `@deprecated` to MssqlPlatform class
- [ ] Add `@deprecated` to SqlsrvPlatform class
- [ ] Add runtime deprecation trigger in platform constructors
- [ ] Document deprecation in CHANGELOG

**Verification:** Code still works, deprecation warnings appear

### 4.2 Remove Platform Classes

**Generator Platform files to delete:**
- [ ] `src/Propel/Generator/Platform/OraclePlatform.php`
- [ ] `src/Propel/Generator/Platform/MssqlPlatform.php`
- [ ] `src/Propel/Generator/Platform/SqlsrvPlatform.php`

**Generator Reverse files to delete:**
- [ ] `src/Propel/Generator/Reverse/OracleSchemaParser.php`
- [ ] `src/Propel/Generator/Reverse/MssqlSchemaParser.php`
- [ ] `src/Propel/Generator/Reverse/SqlsrvSchemaParser.php`

**Verification:** `grep -r "OraclePlatform\|MssqlPlatform\|SqlsrvPlatform" src/`

### 4.3 Remove Runtime Adapters

**Files to delete:**
- [ ] `src/Propel/Runtime/Adapter/Pdo/OracleAdapter.php`
- [ ] `src/Propel/Runtime/Adapter/Pdo/MssqlAdapter.php`
- [ ] `src/Propel/Runtime/Adapter/Pdo/SqlsrvAdapter.php`
- [ ] `src/Propel/Runtime/Adapter/MSSQL/` (entire directory)

**Verification:** No runtime errors

### 4.4 Remove References

- [ ] Remove platform imports from `ObjectBuilder.php`
- [ ] Remove `instanceof` checks for removed platforms
- [ ] Update `InitCommand.php` database type choices
- [ ] Update `resources/xsd/database.xsd`
- [ ] Remove from AdapterFactory mappings

**Verification:** `grep -r "Oracle\|Mssql\|Sqlsrv" src/` returns nothing

### 4.5 Remove Test Files

- [ ] `tests/Propel/Tests/Generator/Platform/OraclePlatform*.php`
- [ ] `tests/Propel/Tests/Generator/Platform/MssqlPlatform*.php`
- [ ] `tests/Propel/Tests/Generator/Reverse/MssqlSchemaParserTest.php`
- [ ] `tests/Propel/Tests/Runtime/Adapter/Pdo/OracleAdapterTest.php`
- [ ] `tests/Propel/Tests/Runtime/Adapter/Pdo/MssqlAdapterTest.php`

**Verification:** Test suite passes

### 4.6 Update Exclusions

- [ ] Clean `phpstan-baseline.neon` of Oracle/MSSQL entries
- [ ] Clean `psalm-baseline.xml` of Oracle/MSSQL entries
- [ ] Update `phpunit.xml` filter exclusions

**Verification:** Static analysis passes

---

## Phase 5: Security and Cleanup

**Goal:** Fix security issues and remove deprecated code.

**Duration:** 3-5 days

### 5.1 Replace eval() in PropelTemplate

**File:** `src/Propel/Generator/Builder/Util/PropelTemplate.php`

- [ ] Replace line 99 `eval()` with temp file approach:
  ```php
  $tempFile = tempnam(sys_get_temp_dir(), 'propel_tpl_');
  file_put_contents($tempFile, '<?php ?>' . $this->template);
  try {
      require $tempFile;
  } finally {
      @unlink($tempFile);
  }
  ```
- [ ] Add unit tests for template rendering
- [ ] Test all behaviors that use templates

**Verification:** All behavior tests pass, no eval() in codebase

### 5.2 Fix Circular Dependency

- [ ] Move `PropelTypes` to `src/Propel/Common/Types/PropelTypes.php`
- [ ] Update all imports in Generator (~30 files)
- [ ] Update all imports in Runtime (~20 files)
- [ ] Add class alias in old location for BC

**Verification:** `composer stan` passes

### 5.3 Remove Deprecated Code

- [ ] Remove `ConnectionManagerMasterSlave.php` (use `ConnectionManagerPrimaryReplica`)
- [ ] Remove deprecated `Criteria::addJoinCondition()` method
- [ ] Remove deprecated `Propel::init()` method
- [ ] Search and remove any `@deprecated` code older than 2 versions

**Verification:** No `@deprecated` markers for removed code

### 5.4 Address TODO/FIXME Comments

Review and resolve each:
- [ ] `PropelDateTime.php` - constructor call issue
- [ ] `MysqlAdapter.php` - PDO hack (verify if still needed)
- [ ] `AbstractCriterion.php` - optimize with early outs
- [ ] `OnDemandFormatter.php` - dead variable
- [ ] `ObjectFormatter.php` - dead populateObject
- [ ] Document any that are intentionally deferred

**Verification:** Reduced TODO/FIXME count

### 5.5 Final Quality Pass

- [ ] Run `composer cs-fix` for code style
- [ ] Run `composer stan` - should pass level 8
- [ ] Run `composer psalm` - should pass
- [ ] Run full test suite all databases
- [ ] Generate test models and verify output
- [ ] Create git tag `v3.0.0-alpha`

**Verification:** All quality gates pass

---

## Verification Commands

```bash
# Quick validation (run frequently)
composer test:agnostic

# Static analysis
composer stan
composer psalm
composer cs-check

# Full test suite
composer test:sqlite
composer test:mysql
composer test:pgsql

# Full validation
composer testsuite
```

---

## Rollback Points

| Phase | Git Tag | Description |
|-------|---------|-------------|
| Start | `v2.x-pre-modernization` | Before any changes |
| Phase 1 | `v3.0-phase1-foundation` | After infrastructure |
| Phase 2 | `v3.0-phase2-syntax` | After syntax modernization |
| Phase 3 | `v3.0-phase3-objectbuilder` | After ObjectBuilder refactor |
| Phase 4 | `v3.0-phase4-platforms` | After platform cleanup |
| Phase 5 | `v3.0.0-alpha` | Ready for testing |

---

## Risk Matrix

| Change | Risk | Mitigation |
|--------|------|------------|
| strict_types | Medium | Incremental by directory, test after each |
| Typed properties | Medium | May surface bugs, fix as found |
| Match expressions | Low | Pure syntax change |
| Constructor promotion | Low | Pure syntax change |
| Enum conversions | Medium | Keep constants with @deprecated |
| ObjectBuilder traits | High | Add tests first, extract incrementally |
| Platform removal | Medium | Deprecate first, clean grep after |
| eval() removal | Medium | Test all behaviors thoroughly |

---

## Timeline Summary

| Phase | Duration | Cumulative |
|-------|----------|------------|
| Phase 1: Foundation | 2-3 days | 2-3 days |
| Phase 2: Syntax | 5-7 days | 7-10 days |
| Phase 3: ObjectBuilder | 7-10 days | 14-20 days |
| Phase 4: Platforms | 2-3 days | 16-23 days |
| Phase 5: Cleanup | 3-5 days | 19-28 days |

**Total estimated: 19-28 days**
