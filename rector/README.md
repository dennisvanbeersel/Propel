# propel/rector-rules

Mechanical migration ruleset for upgrading user code from Propel 3.x to Propel 4.0.

## Install

```bash
composer require --dev propel/rector-rules rector/rector
```

## Usage

Create or update your `rector.php`:

```php
use Propel\Rector\Set\Propel4Migration;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withSets([Propel4Migration::SET]);
```

Run:

```bash
vendor/bin/rector process src/
```

## What it covers

- `DebugPDO` and `PropelPDO` constructions → `ConnectionWrapper` (`useDebug(true)` toggle for DebugPDO).
- `ConnectionManagerMasterSlave` → `ConnectionManagerPrimaryReplica` (incl. `isForceMasterConnection` → `isForcePrimaryConnection`).
- `slaves` / `master` config keys → `replicas` / `primary` (PHP/array configs).
- Java-Hashtable Criteria methods (`put`, `putAll`, `get`, `keys`, `containsKey`, `keyContainsValue`, `size`, `equals`) → modern equivalents.
- Raw `Criteria::CUSTOM` `add()` calls → `customCondition()`.
- Legacy `PropelTypes::BU_DATE`/`BU_TIMESTAMP`/`BOOLEAN_EMU`/`OBJECT`/`PHP_ARRAY` → modern types (informational; XML schema rewrites are advisory).
- `Propel::initConfiguration(...)` → `Propel::getServiceContainer()->setConfig(...)`.
- `NestedSetBehavior` use → informational comment + cookbook pointer (cannot mechanically rewrite tree-traversal queries).
- Direct property writes on subclasses of generated AR base (asymmetric-visibility BC bridge) → setter calls.

## Caveats

- The `PropertyWriteToSetter` rule scopes to subclasses of generated AR bases. Verify before applying to a polymorphic codebase.
- The `PropelTypesLegacyToModern` rule is informational on XML schema files; XML AST rewriting is fragile and left as a manual step with cookbook guidance.
- The `NestedSetBehaviorWarning` rule cannot mechanically rewrite tree queries; it only flags occurrences for manual migration.
