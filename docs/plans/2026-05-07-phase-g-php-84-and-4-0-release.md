# Phase G — PHP 8.4 minimum + lazy objects + asymmetric visibility + property hooks + streaming formatter + WeakMap instance pool + Rector ruleset (4.0 release)

**Date:** 2026-05-07
**Risk tier:** HIGH (per umbrella §4.13.3 — 3-round cadence)
**Specialist (per §4.13.2):** PHP 8.4 internals specialist (lazy-object semantics, asymmetric-visibility BC implications, property-hook ordering) + Performance reviewer (second pass).
**Goal:** Ship Propel **4.0** — bump PHP minimum to 8.4, remove all 3.x deprecations introduced during Phases A–F, adopt PHP 8.4 native features (lazy objects, asymmetric visibility, property hooks, `#[\Deprecated]`), add a streaming `Generator`-based formatter, ship a `WeakMap`-backed instance-pool variant, and publish the `propel/rector-rules` package so consumers run Rector once and emerge 4.0-clean.
**Dependencies:** Phases A (deprecation infra + symfony/deprecation-contracts), B/B' (CodeEmitter + ObjectBuilderApi), C (schema-XSD additivity), D (behavior cleanup), E (connection collapse + decorator stack), F (Criteria split + enums + customCondition + Java-Hashtable runway), I (TelemetryInterface — hydration-duration emission lands here in G).
**Tagged for:** **Propel 4.0** release.

---

## §1. Scope and non-goals

### In scope

1. **PHP 8.4 minimum bump.** `composer.json` `"php": ">=8.4"` (was `>=8.3`). CI matrix collapses to 8.4-only. phpcs phpVersion bumps. `#[\Deprecated]` attribute migration sweep replaces `@deprecated` PHPDoc on retained methods (3.x APIs that survive but are softly deprecated for 4.x → 5.0 runway, e.g. legacy ConnectionWrapper bridging shim if kept).
2. **Remove all 3.x deprecations introduced during Phases A–F.** Every removal is paired with a `propel/rector-rules` rule so consumers run one command to migrate. Removals (per umbrella §6.1):
   - `DebugPDO`, `PropelPDO` classes → deleted; Rector rewrites construction to `ConnectionWrapper::useDebug()` toggle.
   - `ConnectionManagerMasterSlave` (and `isForceMasterConnection`/`setForceMasterConnection`) → deleted; Rector rewrites to `ConnectionManagerPrimaryReplica`.
   - `slaves` / `master` config keys (handled by `PropelConfiguration` Tier 2 alias) → deleted-with-Rector for YAML/PHP config files.
   - Java-Hashtable Criteria methods (`put`/`putAll`/`get`/`keys`/`containsKey`/`keyContainsValue`/`size`/`equals`) → deleted; Rector maps to modern equivalents (`addCondition`/`getValue`/`count($this)`/etc).
   - Raw `Criteria::CUSTOM` add path → keep the constant (frozen — §3.1) but `add($n, $sql, Criteria::CUSTOM)` becomes a hard error pointing at `customCondition()`. Rector rewrites callsites.
   - Legacy `BU_DATE`, `BU_TIMESTAMP`, `BOOLEAN_EMU`, `OBJECT`, `PHP_ARRAY` PropelTypes → schema parser hard error with migration guidance (XSD additive promise honored — these stay PARSEABLE forever per §3.7, but emit a clear "removed in 4.0, replace with X" exception when used in active schema).
   - Legacy `serial`/`bigserial` PG vendor flag → hard error pointing at `<column type="IDENTITY">`.
   - `getReverseFormat() === 'legacy-show-create'` flag → deleted; Rector handles command-line callers.
   - `NestedSetBehavior` → deleted; Rector emits inline comment + cookbook pointer (cannot mechanically rewrite tree-traversal queries).
3. **Lazy relation objects (G.3).** PHP 8.4 `ReflectionClass::newLazyGhost()` for `init<Rel>()`/`coll<Rel>Partial` chains in generated Object code. **Opt-in** for 4.0 via `<table use-lazy-objects="true">`; default flip to ON in 4.1 per umbrella §G's numerical rollback criterion (≤+5% latency on 100k-row `with()` query).
4. **Asymmetric visibility (G.4).** Generator emits `public private(set) ?int $id = null;` for typed entity properties — eliminates ~46 `protected $id;` + setter-chain pairs per generated entity. **Documented BC break** in `UPGRADE-4.0.md`: read-anywhere, write-via-setter is the new AR semantics. Subclasses that wrote properties directly must migrate to setters (Rector rule `PropelPropertyWriteToSetter` covers the common case).
5. **Property hooks for dirty-tracking (G.5).** Generator emits `set` hooks on every column-backed typed property that update `$this->modifiedColumns[...]`. Replaces explicit setter-body `modifiedColumns` mutation. Reads via direct property access become first-class.
6. **Streaming formatter (G.6).** New `StreamingObjectFormatter` returns a `Generator<int, T>` instead of materializing a full `ObjectCollection`. New ActiveQuery method `findStream(?ConnectionInterface = null): Generator`. Backed by new `Runtime/Collection/StreamingObjectCollection.php` shim that wraps a generator behind the existing collection interface for callsites that pass collections through.
7. **WeakMap instance pool (G.7).** New `Runtime/InstancePool/WeakMapInstancePool.php` variant — replaces strong-ref `InstancePoolTrait::$instances` array. Opt-in via `propel.instancePool.strategy: weak-map` config; strong-ref remains the default to ensure cross-request behavior under traditional FPM stays identical (Phase J will revisit for worker mode).
8. **`#[\Deprecated]` attribute migration (G.1.3).** Phase A used `@deprecated` PHPDoc + `trigger_deprecation`; PHP 8.4 ships native `#[\Deprecated]` attribute. Survey: 30+ sites currently using `@deprecated` PHPDoc. Sweep adds the attribute; existing `trigger_deprecation` calls remain for the runtime telemetry signal `symfony/phpunit-bridge` consumes.
9. **`propel/rector-rules` package (G.8).** New scaffolded composer package under `rector/` directory with one rule per 3.x deprecation. Verification harness: each rule has a `tests/Fixtures/before.php` + `tests/Fixtures/after.php` pair; rector runs the rule and asserts the fixture transforms exactly.
10. **Documentation (G.9).** `UPGRADE-4.0.md` (consumer migration guide), `CHANGELOG.md` 4.0 entry, `README.md` compatibility matrix update, `MIGRATION-FROM-PRE-AI.md` 4.0-jump section.

### Out of scope (deferred / other phases)

- Phase J worker-mode integration (RoadRunner/FrankenPHP/Swoole hooks; fiber-context propagation) — sequenced post-G per umbrella §5.1. WeakMap-instance-pool cross-request behavior under workers is **explicitly Phase J's problem**; Phase G ships the variant as opt-in only.
- Multi-tenancy / sharding — out of scope (umbrella §1.3).
- Promoting `propel/telemetry-otel` / `propel/telemetry-prometheus` to standalone Composer packages (Phase I shipped in-core under `suggest`). Release engineering for the 4.0 cycle.
- Removing `ConnectionWrapper` itself — kept; only the deprecated `DebugPDO`/`PropelPDO` aliases go.
- Generated typed Criterion DSL (Phase F.8 stretch) — already deferred; not Phase G's concern.
- Removing legacy `ConnectionManagerSingle` / `ConnectionFactory` Tier 1 surfaces — those are FROZEN (§3.1).

---

## §2. File structure

```
composer.json                                                      [MODIFY]   php: >=8.4; bin entry for rector-rules sub-package
.github/workflows/ci.yml                                           [MODIFY]   matrix php-version: ['8.4'] only
.github/workflows/quality-gates.yml                                [MODIFY]   php-version: '8.4'
phpcs.xml.dist (or .phpcs)                                          [MODIFY]   phpVersion 8.4
UPGRADE-4.0.md                                                     [NEW]      consumer migration guide
CHANGELOG.md                                                       [MODIFY]   4.0 entry
README.md                                                          [MODIFY]   compatibility matrix bumped
docs/MIGRATION-FROM-PRE-AI.md                                      [MODIFY]   add §4.0-jump

src/Propel/Generator/Builder/Om/
├── ObjectBuilder.php                                              [MODIFY]   asymmetric-visibility property emission + property-hook setter emission (gated by use-lazy-objects + 4.0)
├── LazyRelationBuilder.php                                        [NEW]      newLazyGhost() relation-collection initializer

src/Propel/Generator/Model/
├── Table.php                                                      [MODIFY]   parse <table use-lazy-objects="true">
├── PropelTypes.php                                                [MODIFY]   BU_DATE/BU_TIMESTAMP/BOOLEAN_EMU/OBJECT/PHP_ARRAY: trigger_deprecation → throw RuntimeException

src/Propel/Generator/resources/xsd/
├── propel-schema.xsd                                              [MODIFY]   add use-lazy-objects attribute (additive)

src/Propel/Generator/Behavior/NestedSet/                           [DELETE]   per umbrella §6.3
src/Propel/Generator/Command/DatabaseReverseCommand.php            [MODIFY]   remove legacy-show-create branch

src/Propel/Runtime/Connection/
├── DebugPDO.php                                                   [DELETE]
├── PropelPDO.php                                                  [DELETE]
├── ConnectionManagerMasterSlave.php                               [DELETE]

src/Propel/Common/Config/PropelConfiguration.php                   [MODIFY]   slaves/master alias paths emit hard error pointing at replicas/primary

src/Propel/Runtime/ActiveQuery/Criteria.php                        [MODIFY]   delete put/putAll/get/keys/containsKey/keyContainsValue/size/equals; raw-CUSTOM add() throws (constant survives — §3.1)

src/Propel/Runtime/Formatter/
├── StreamingObjectFormatter.php                                   [NEW]      Generator-based formatter
├── AbstractFormatter.php                                          [MODIFY]   doc the streaming sibling

src/Propel/Runtime/ActiveQuery/
├── ModelCriteria.php                                              [MODIFY]   findStream(): Generator

src/Propel/Runtime/Collection/
├── StreamingObjectCollection.php                                  [NEW]      Generator-backed collection shim

src/Propel/Runtime/InstancePool/
├── InstancePoolStrategyInterface.php                              [NEW]      SPI
├── StrongRefInstancePool.php                                      [NEW]      legacy default extracted
├── WeakMapInstancePool.php                                        [NEW]      8.4 variant

src/Propel/Runtime/ActiveQuery/InstancePoolTrait.php               [MODIFY]   delegate to InstancePoolStrategyInterface; default StrongRefInstancePool preserves byte-equivalent behavior

# Rector ruleset package (separate Composer package scaffolded under rector/)
rector/
├── composer.json                                                  [NEW]      "name": "propel/rector-rules", requires phpstan/phpdoc-parser, rector/rector
├── README.md                                                      [NEW]
├── src/
│   ├── Set/Propel4Migration.php                                   [NEW]      bundle of all rules
│   ├── Rule/SlavesToReplicasConfigRector.php                      [NEW]
│   ├── Rule/MasterToPrimaryConfigRector.php                       [NEW]
│   ├── Rule/DebugPdoToConnectionWrapperRector.php                 [NEW]
│   ├── Rule/PropelPdoToConnectionWrapperRector.php                [NEW]
│   ├── Rule/ConnectionManagerMasterSlaveToPrimaryReplicaRector.php [NEW]
│   ├── Rule/CriteriaJavaHashtableMethodsRector.php                [NEW]      put/putAll/get/keys/containsKey/keyContainsValue/size/equals
│   ├── Rule/CriteriaCustomToCustomConditionRector.php             [NEW]
│   ├── Rule/PropelTypesLegacyToModernRector.php                   [NEW]
│   ├── Rule/PropelInitConfigurationRector.php                     [NEW]
│   ├── Rule/NestedSetBehaviorWarningRector.php                    [NEW]      emits comment, cannot mechanically rewrite
│   └── Rule/PropertyWriteToSetterRector.php                       [NEW]      asym-visibility BC bridge
└── tests/
    └── Rule/<one fixture pair per rule>

tests/Propel/Tests/Runtime/
├── Formatter/StreamingObjectFormatterTest.php                     [NEW]
├── Collection/StreamingObjectCollectionTest.php                   [NEW]
├── InstancePool/WeakMapInstancePoolTest.php                       [NEW]
├── InstancePool/StrongRefInstancePoolTest.php                     [NEW]
└── ActiveQuery/Criteria4_0RemovalsTest.php                        [NEW]      assert removed methods + raw-CUSTOM throw

tests/Propel/Tests/Generator/Builder/Om/
├── LazyRelationBuilderTest.php                                    [NEW]
├── AsymmetricVisibilityEmissionTest.php                           [NEW]
└── PropertyHookSetterEmissionTest.php                             [NEW]
```

---

## §3. Task groups

Per-task quality cadence (every task, no exception): `composer test:agnostic`, `composer cs-check`, `composer stan`, `composer psalm`, `composer deptrac`. Baselines must NOT grow. ONE commit per task.

### G.1 — PHP 8.4 minimum bump (3 tasks)

#### G.1.1 `composer.json` + CI matrix bump.
- `composer.json`: `"php": ">=8.4"` (was `>=8.3`).
- `.github/workflows/ci.yml`: matrix `php-version: ['8.4']`.
- `.github/workflows/quality-gates.yml`: `php-version: '8.4'`.
- Update `code-quality` job's setup-php `php-version: '8.4'`.
- Run `composer test:agnostic` to confirm 8.4 baseline.

#### G.1.2 `phpcs` phpVersion bump.
- `phpcs.xml.dist` (or `.phpcs`): `<config name="php_version" value="80400"/>`.
- Run `composer cs-check`; if any new violations surface (e.g. spread-in-named-args sniff), fix.

#### G.1.3 `#[\Deprecated]` attribute migration sweep.
- Survey: `grep -rn "@deprecated" src/` → produce list (~30 sites).
- For methods/classes that REMAIN in 4.0 but stay deprecated (e.g. legacy ConnectionFactory shim if any kept): add `#[\Deprecated('replacement', '4.0')]` attribute alongside the existing `@deprecated` PHPDoc.
- For methods/classes BEING DELETED in this phase (DebugPDO, PropelPDO, ConnectionManagerMasterSlave, Java-Hashtable methods): no migration — they're going.
- The `trigger_deprecation` calls remain — different purpose (runtime telemetry vs IDE hint).

### G.2 — Remove 3.x deprecations (7 tasks)

Each removal task: delete the file/method + add corresponding Rector rule fixture pair under `rector/tests/Rule/<RuleName>/`. The rule itself lands in G.8; G.2 captures the fixture pair so the rule's TODO is concretely scoped.

#### G.2.1 Delete `DebugPDO` + `PropelPDO`.
- Delete `src/Propel/Runtime/Connection/DebugPDO.php`.
- Delete `src/Propel/Runtime/Connection/PropelPDO.php`.
- Grep for usages in tests/fixtures; update or delete.
- Add `rector/tests/Rule/DebugPdoToConnectionWrapper/` fixture pair.
- Add `rector/tests/Rule/PropelPdoToConnectionWrapper/` fixture pair.

#### G.2.2 Delete `ConnectionManagerMasterSlave` + drop `isForceMasterConnection`/`setForceMasterConnection` aliases on `ConnectionManagerPrimaryReplica`.
- Delete `ConnectionManagerMasterSlave.php`.
- Remove the `isForceMasterConnection` / `setForceMasterConnection` methods if they live on `ConnectionManagerPrimaryReplica` as forwards.
- Grep for usages in tests/fixtures; update.
- Add `rector/tests/Rule/ConnectionManagerMasterSlaveToPrimaryReplica/` fixture pair.

#### G.2.3 Hard-error `slaves` / `master` config keys in `PropelConfiguration`.
- Replace the `trigger_deprecation` branches with `throw new InvalidConfigurationException("slaves/master removed in 4.0; use replicas/primary. Run 'vendor/bin/rector --rules=Propel4Migration' to migrate config.")`.
- Add `rector/tests/Rule/SlavesToReplicasConfig/` + `rector/tests/Rule/MasterToPrimaryConfig/` fixture pairs (target YAML or PHP config arrays — Rector handles arrays).

#### G.2.4 Delete Java-Hashtable Criteria methods.
- From `Criteria.php` delete: `put()`, `putAll()`, `get()`, `keys()`, `containsKey()`, `keyContainsValue()`, `size()`, `equals()` (the Java-Hashtable `equals` — keep `equals(Criteria $other): bool` if it still exists with different signature; verify).
- Verify Phase F deprecation runway lines (`869`, `895`, etc.) get removed cleanly without breaking other methods that delegated.
- Delete corresponding tests in `Criteria*Test.php`.
- Add `rector/tests/Rule/CriteriaJavaHashtableMethods/` fixture pair (8 sub-fixtures, one per method).

#### G.2.5 Hard-error raw `Criteria::CUSTOM` `add()` path.
- Keep the `Criteria::CUSTOM` constant (Tier 1 frozen — §3.1 reaffirms).
- In `Criteria::add()` when `$comparison === Criteria::CUSTOM`, throw `\BadMethodCallException("Raw CUSTOM add() removed in 4.0; use customCondition(). Run 'vendor/bin/rector --rules=Propel4Migration' to migrate.")` UNLESS `$value` is empty (no SQL provided — harmless callsite).
- Replace internal use sites if any (Phase F.7 work should have already migrated them).
- Add `rector/tests/Rule/CriteriaCustomToCustomCondition/` fixture pair.

#### G.2.6 Hard-error legacy PropelTypes (`BU_DATE`, `BU_TIMESTAMP`, `BOOLEAN_EMU`, `OBJECT`, `PHP_ARRAY`) + legacy serial PG vendor flag.
- In `PropelTypes::getPropelType()` (or wherever the deprecation fires), throw `\InvalidArgumentException("Type BU_DATE removed in 4.0; use TIMESTAMP with default CURRENT_TIMESTAMP. See UPGRADE-4.0.md.")`.
- Same for the other 4 types.
- In `PgsqlPlatform`: throw on legacy-serial vendor flag, message points at `<column type="IDENTITY">`.
- Add `rector/tests/Rule/PropelTypesLegacyToModern/` fixture pair (XML schema transformation — Rector has YAML/XML support via custom handlers; alternatively this rule is a "comment-and-warn" since XML rewriting is fragile).

#### G.2.7 Delete `NestedSetBehavior`, legacy reverse-format flag, and `Propel::initConfiguration()`.
- Delete `src/Propel/Generator/Behavior/NestedSet/` directory.
- In `DatabaseReverseCommand`: delete the `legacy-show-create` branch + the `REVERSE_FORMAT_LEGACY_SHOW_CREATE` constant; default to INFORMATION_SCHEMA.
- In `Propel.php`: delete `initConfiguration()` if present.
- Add Rector fixtures: `NestedSetBehaviorWarning/` (emits inline comment per occurrence), `PropelInitConfiguration/`.

### G.3 — Lazy relation objects (4 tasks)

Lazy-object opt-in for 4.0; default flip to ON in 4.1 per umbrella §G's numerical rollback criterion.

#### G.3.1 Schema parsing: `<table use-lazy-objects="true">`.
- Modify `Table.php` to parse + expose `useLazyObjects(): bool`.
- Modify `propel-schema.xsd` — additive attribute `use-lazy-objects` (default `false`).
- Add unit test asserting default-off + opt-in parsing.

#### G.3.2 `LazyRelationBuilder`.
- New `src/Propel/Generator/Builder/Om/LazyRelationBuilder.php` — emits `init<Rel>()` body using `(new \ReflectionClass($collClass))->newLazyGhost(fn(ObjectCollection $c) => $this->doInit<Rel>($c))` instead of eager construction.
- Sister method `doInit<Rel>(ObjectCollection): void` is the actual hydration body; the lazy-ghost wrapper only triggers on first read.
- Test: assert generated code emits the lazy form when `use-lazy-objects="true"`, falls through to legacy when omitted.

#### G.3.3 ObjectBuilder integration.
- `ObjectBuilder` consults `Table::useLazyObjects()`; when true, delegates the relation-init emission to `LazyRelationBuilder`.
- Run golden-file regen: only the opt-in fixtures change.

#### G.3.4 Performance benchmark + rollback criterion verification.
- Add `tests/Performance/LazyRelationBenchmarkTest.php` — 100k-row hydrate-with-relation under both opt-in and opt-out.
- Assert `lazyLatency <= eagerLatency * 1.05`.
- If FAIL: opt-in stays the only path for 4.0; default flip target moves to 4.2.
- Capture report at `docs/reviews/G-bench-lazy.md`.

### G.4 — Asymmetric visibility on properties (4 tasks)

**This task changes AR semantics.** The BC break is documented in `UPGRADE-4.0.md`; Rector's `PropertyWriteToSetterRector` covers common subclass-write callsites mechanically.

#### G.4.1 Generator emission of typed property declarations.
- `ObjectBuilder` (or a new `ColumnPropertyEmitter` helper) emits `public private(set) ?string $title = null;` instead of `protected $title;` for every column-backed property.
- The internal generated setter remains; it's the only thing that can write the property now.
- Subclasses that previously wrote `$this->title = ...` directly outside of the generated setter will get a fatal — by design.

#### G.4.2 Update generated setter to satisfy `private(set)`.
- The generated `setTitle()` body still writes `$this->title = ...` — that works because `setTitle` lives on the base class, which has set-permission.
- Update column-mutator-builder trait to emit the cast/normalize step + the property write + the `modifiedColumns` mark.

#### G.4.3 Golden-file regen + diff review.
- Regenerate `tests/Fixtures/bookstore/build/golden/`.
- Diff review: verify property declarations changed shape; setter bodies unchanged; subclass-extension scenarios in tests still compile.

#### G.4.4 Document asymmetric-visibility BC break.
- `UPGRADE-4.0.md` §4.4 entry: "AR property write semantics changed. Direct `$this->title = ...` writes on subclasses will now fatal. Migrate to `setTitle()` calls. Run `vendor/bin/rector --rules=PropertyWriteToSetter` to mechanically rewrite."
- Add a Rector fixture demonstrating the rewrite.

### G.5 — Property hooks for dirty-tracking (4 tasks)

PHP 8.4 native property hooks replace explicit `$this->modifiedColumns[...] = true;` boilerplate scattered across generated setters. Hook ordering: PHP runs the hook AFTER assignment, so the modifiedColumns mark fires post-assignment — same observable order as the legacy setter body.

#### G.5.1 Generator emission of hook-equipped property declarations.
- For each column-backed property, emit:
  ```php
  public private(set) ?string $title = null {
      set(?string $value) {
          $value = $this->normalizeTitleValue($value);
          if ($this->title !== $value) {
              $this->modifiedColumns[BookTableMap::COL_TITLE] = true;
          }
          $this->title = $value;
      }
  }
  ```
- The generated `setTitle()` thin-wraps to `$this->title = $value;` (one line); the hook does the work.
- Existing `applyDefaultValues()` and unsetter-by-mass-assign still work.

#### G.5.2 Normalize/cast helpers per type.
- Date/time → `PropelDateTime` coercion.
- Boolean → `(bool)` cast.
- Decimal → string preservation.
- Each helper as a `private function normalize<Col>Value(...)` method on the generated entity, OR a static utility on `PropelTypes` if reusable.

#### G.5.3 Hook ordering test + interaction with NULL.
- Test: setting same value doesn't mark column modified.
- Test: setting NULL on already-NULL doesn't mark.
- Test: setting different value marks once, idempotent on repeat.
- Test: hook fires correctly on `$obj->title = 'x';` direct write (since `private(set)` means subclass can't but base-class set works — this is the surface of asymmetric-visibility behavior).

#### G.5.4 Golden-file regen + diff review.
- Verify generated entity hook syntax + setter thin-wrap.

### G.6 — Streaming Generator-based formatter (3 tasks)

#### G.6.1 `StreamingObjectFormatter`.
- New `src/Propel/Runtime/Formatter/StreamingObjectFormatter.php` extending `AbstractFormatter`.
- Method `format(DataFetcher): Generator<int, T>` — yields hydrated objects one at a time, NEVER materializes the full collection.
- Calls `recordHydrationDuration` per yielded row (Phase I telemetry surface — first concrete callsite).
- Internally consumes a `DataFetcher` (statement) and yields one entity per `fetch()`.

#### G.6.2 `findStream()` on ModelCriteria.
- Add `findStream(?ConnectionInterface $con = null): \Generator` to `ModelCriteria`.
- Wires through `StreamingObjectFormatter`.
- Tier 1 ADDITIVE (new method, doesn't change any existing signature).

#### G.6.3 `StreamingObjectCollection` shim + tests.
- `Runtime/Collection/StreamingObjectCollection.php` — `Iterator` + `Countable` (lazy count via underlying generator) backed by a generator. NOT array-backed; once consumed, NOT replayable. Documented in class docblock.
- Test: `findStream()` returns Generator, yields N items, doesn't OOM on a 100k-row stream.
- Test: Streaming collection can be passed where `iterable` is accepted.

### G.7 — WeakMap instance pool (3 tasks)

#### G.7.1 `InstancePoolStrategyInterface` SPI + `StrongRefInstancePool` (legacy default extracted).
- New `Runtime/InstancePool/InstancePoolStrategyInterface.php` — methods: `add($key, object $obj): void`, `get($key): ?object`, `remove($key): void`, `clear(): void`, `size(): int`.
- New `StrongRefInstancePool` — strong-ref array keyed by string (legacy behavior; default).
- Modify `InstancePoolTrait` to delegate to a strategy instance; default = `StrongRefInstancePool`.
- Verify existing tests pass byte-equivalent.

#### G.7.2 `WeakMapInstancePool`.
- New `WeakMapInstancePool` — backed by `\WeakMap<object, string>` PLUS a `string => \WeakReference<object>` index (because the SPI is keyed by string and needs to look up by string).
- When the entity object is GC'd, the WeakMap entry vanishes; the WeakReference yields null on subsequent get; the index is lazily pruned.

#### G.7.3 Configuration wire-up + tests.
- `propel.yaml` config key: `propel.instancePool.strategy: 'strong-ref' | 'weak-map'` (default `strong-ref`).
- Bootstrap reads config + injects strategy.
- Test: weak-map strategy releases entities under GC pressure.
- Test: strong-ref strategy preserves entities (legacy semantics).

### G.8 — `propel/rector-rules` package (5 tasks)

#### G.8.1 Scaffold the package.
- `rector/composer.json` — name `propel/rector-rules`, requires `rector/rector ^1.2`.
- `rector/README.md` — install + run instructions.
- `rector/src/Set/Propel4Migration.php` — bundles all rules.
- Add a smoke `phpunit.xml.dist` under `rector/`.

#### G.8.2 Connection migration rules (3 rules).
- `DebugPdoToConnectionWrapperRector` — rewrites `new DebugPDO($c)` to `(new ConnectionWrapper($c))->useDebug(true)`.
- `PropelPdoToConnectionWrapperRector` — rewrites `new PropelPDO(...)` to `new ConnectionWrapper(...)`.
- `ConnectionManagerMasterSlaveToPrimaryReplicaRector` — class-name swap + method-call swap (`isForceMasterConnection` → `isForcePrimaryConnection`).

#### G.8.3 Criteria migration rules (2 rules).
- `CriteriaJavaHashtableMethodsRector` — 8 method-call rewrites:
  - `put($k, $v)` → `addCondition($k, $v)`.
  - `get($k)` → `getValue($k)`.
  - `size()` → `count($criteria)`.
  - `keys()` → `getKeys()`.
  - `containsKey($k)` → `containsField($k)`.
  - `keyContainsValue($k, $v)` → `containsConditionWithValue($k, $v)`.
  - `equals($other)` → `isEquivalent($other)` (or whatever modern equivalent is).
  - `putAll($arr)` → `addConditions($arr)`.
- `CriteriaCustomToCustomConditionRector` — rewrites `add($name, $sql, Criteria::CUSTOM)` to `customCondition($name, $sql)`. Bind-params variant rewrites `add($name, $sql, Criteria::CUSTOM)` followed by `->setParams(...)` to the parameterized form.

#### G.8.4 Schema/types/config migration rules (4 rules).
- `SlavesToReplicasConfigRector` + `MasterToPrimaryConfigRector` — YAML/PHP array key rewrites.
- `PropelTypesLegacyToModernRector` — emits a comment + warning on schema XML files containing legacy types (XML doesn't lend itself to Rector AST rewriting; this rule is informational).
- `PropelInitConfigurationRector` — rewrites `Propel::initConfiguration(...)` to `Propel::getServiceContainer()->setConfig(...)` (or whatever the modern path is).
- `NestedSetBehaviorWarningRector` — emits a comment with cookbook pointer; cannot mechanically rewrite tree-traversal queries.

#### G.8.5 `PropertyWriteToSetterRector`.
- Detects direct property writes on classes inheriting from generated entity bases.
- Rewrites `$book->title = 'x'` to `$book->setTitle('x')`.
- Limitation: only rewrites known column properties; a deny-list of non-column properties (`title` accessor on a unrelated class) is configurable.

### G.9 — Documentation (3 tasks)

#### G.9.1 `UPGRADE-4.0.md`.
- §1 PHP 8.4 minimum
- §2 Removed APIs (one-liner per: DebugPDO/PropelPDO/ConnectionManagerMasterSlave/Java-Hashtable Criteria/raw-CUSTOM/legacy types)
- §3 New AR semantics (asymmetric visibility — direct property writes via subclasses break)
- §4 Property hooks (consumers don't see — generator-internal — but doc the dirty-tracking change for callsites that bypassed setters)
- §5 Lazy objects (opt-in in 4.0, default in 4.1)
- §6 Streaming formatter (`findStream()`)
- §7 WeakMap instance pool (opt-in)
- §8 Migration via Rector — one command: `composer require --dev propel/rector-rules && vendor/bin/rector --rules=Propel4Migration src/`
- §9 Tier 1 surface unchanged (signatures stable into 4.x line per umbrella §3.1)

#### G.9.2 `CHANGELOG.md` + `README.md` compatibility matrix.
- CHANGELOG.md: 4.0 entry summarizing all the above.
- README.md compatibility matrix: PHP 8.4+, Symfony 7.2+, MySQL 8/MariaDB 10.5+/PG 14+/SQLite 3.6.19+.

#### G.9.3 `MIGRATION-FROM-PRE-AI.md` 4.0-jump section.
- Existing doc covered the 2.x → 3.x rewrite for users on a pre-modernization checkout.
- Add a §4.0-jump section: "If you're upgrading from 2.x directly to 4.0, run Rector twice — once for 3.x deprecations, once for 4.0 removals" — actually no: the Rector 4.0 set already handles all deprecations introduced in 3.x because they're being removed at 4.0 simultaneously. Document this.

### G.10 — Round 1 / Round 2 / Round 3 review checkpoints + DoD

#### G.10.1 Round 1 mid-phase review (after G.4 — at the asymmetric-visibility landing).
- 4-lens review (Architecture + BC + PHP 8.4-internals specialist + Performance).
- Output: `docs/reviews/G-round-1-summary.md` consolidating reviewer reports.
- Iteration loop per umbrella §4.15 if MUST-FIX surface.

#### G.10.2 Round 2 end-phase pre-merge review (after G.8).
- 7-lens review (5 standing + PHP 8.4-internals specialist + Performance second pass).
- Output: `docs/reviews/G-round-2-summary.md`.
- Required to close all MUST-FIX before merge tag.

#### G.10.3 Round 3 post-merge canary (7 days post-merge).
- Performance + Quality + BC reviewers.
- Ecosystem advisory CI runs against 4.0 RC; downstream package smoke results captured.
- Output: `docs/reviews/G-round-3-summary.md`.

---

## §4. Definition of Done (15 boxes per umbrella §4.9)

1. ☐ All G.1–G.9 tasks executed (or explicitly deferred with documented rationale).
2. ☐ `composer test:agnostic` GREEN at phase end.
3. ☐ `composer cs-check` clean.
4. ☐ `composer stan` baseline ≤ 403 lines (must NOT grow).
5. ☐ `composer psalm` baseline ≤ 1621 lines (must NOT grow).
6. ☐ `composer deptrac` 0 violations against 233 baseline.
7. ☐ `phpunit.xml` `failOn*` flags all true.
8. ☐ Tier 1 surface stable: signature-diff gate green (no removed methods on Tier 1 classes other than the explicit-deprecation removals enumerated in §1 and corresponding to a Rector rule).
9. ☐ Schema XSD additive (`use-lazy-objects` is the only new attribute).
10. ☐ Golden-file regen complete; diff reviewed.
11. ☐ `propel/rector-rules` package: every removed-deprecation has a corresponding Rector rule with passing fixture pair.
12. ☐ Performance: lazy-object benchmark ≤+5% latency on 100k-row `with()` query OR rollback documented in `UPGRADE-4.0.md`.
13. ☐ Round 1 + Round 2 review reports committed; Round 3 scheduled.
14. ☐ `UPGRADE-4.0.md` + CHANGELOG + README compatibility matrix landed.
15. ☐ CI matrix collapsed to PHP 8.4-only; CI green.

---

## §5. Risk register (Phase G specifics)

| # | Risk | Mitigation |
|---|---|---|
| 1 | **Lazy-object correctness — newLazyGhost semantics under property hooks** | PHP 8.4 lazy-ghost objects + property hooks haven't been combined extensively in production ORMs. G.3 ships opt-in only. Numerical rollback criterion (≤+5% latency) gates the 4.1 default flip. If lazy-objects + property-hooks interact pathologically (e.g., hook fires on the lazy-stub before initialization), defer G.5 hook-equipped properties on lazy-object tables specifically. |
| 2 | **Asymmetric visibility changes AR semantics** | Documented BC break in `UPGRADE-4.0.md` §4.3. Rector `PropertyWriteToSetterRector` mechanical migration. Surface in the 4.0 release notes, not buried in CHANGELOG. |
| 3 | **Property-hooks execution order vs setter cast/normalize** | Hooks fire after assignment in PHP 8.4 — confirmed in G.5.1 emission shape. Test G.5.3 specifically guards ordering invariants. If the order surprises, fall back to thin-wrap-setter pattern (set hook = `$this->setX($value)` ouroboros disallowed; need explicit forms). |
| 4 | **Rector rule false positives** | Each rule has fixture pairs. The `PropertyWriteToSetterRector` is the highest-risk rule (false-positive on unrelated `$obj->title` writes). Mitigation: rule scopes to subclasses of `Propel\Runtime\ActiveRecord\BaseObject` (or detects the `<?php declare ...` extending generated base) — no blind rewriting of arbitrary `->title =` writes. |
| 5 | **Streaming formatter + instance-pool interaction** | Strong-ref instance-pool retains every yielded entity → streaming-formatter no-OOM promise breaks. WeakMap-instance-pool fixes this BUT is opt-in. G.6.3 test asserts: streaming + weak-map combined doesn't OOM on 100k stream; streaming + strong-ref under aggressive yield will retain memory — this is the intentional trade-off. Documented in `findStream()` PHPDoc. |
| 6 | **WeakMap instance pool cross-request behavior under workers** | Phase J's problem. G.7 ships opt-in; default remains strong-ref so traditional FPM behavior is byte-equivalent. Phase J revisits for RoadRunner/FrankenPHP/Swoole. |
| 7 | **3.x-deprecation removal ecosystem fallout** | Every removal has a Rector rule. Ecosystem advisory CI in Round 2 + Round 3 surfaces real downstream impact before tag. Phase F deprecation-runway ensures consumers had a release cycle of warning. |
| 8 | **PHP 8.4-only CI matrix collapses ability to detect 8.3-only regressions** | Intentional — Propel 4.0 is 8.4-floor. The 2.x line on the LTS branch retains 8.3 CI. |
| 9 | **Asym-visibility + reflection-based hydration interaction** | If any Propel internal uses `ReflectionProperty::setValue` to write a `private(set)` property from outside the class, it fatals. Audit Phase B+ generator output for any reflection write paths; replace with setter calls. Pre-merge sweep in G.10.2. |
| 10 | **`#[\Deprecated]` attribute IDE/static-analysis adoption** | PHPStan 2.x supports `#[\Deprecated]` natively. Psalm 5.x partial support. If Psalm 5 chokes on the attribute, gate the sweep to PHPStan-only and keep `@deprecated` PHPDoc as well (no harm in dual-marking). |

---

## §6. Out-of-scope (explicit deferrals)

- **Phase J worker-mode** — RoadRunner/FrankenPHP/Swoole hooks; fiber-context propagation; on-worker-start/end lifecycle. Sequenced post-G per umbrella §5.1.
- **Multi-tenancy / sharding** — out of scope project-wide (umbrella §1.3).
- **Standalone `propel/telemetry-otel` and `propel/telemetry-prometheus` Composer packages** — release-engineering activity for the 4.0 cycle; Phase I shipped in-core.
- **Default lazy-object flip** — targeted for 4.1 minor pending the §4.10 numerical rollback criterion.
- **Default WeakMap-instance-pool flip** — Phase J's call; depends on worker-mode safety analysis.
- **Removing `ConnectionWrapper`** — kept for 4.x line; only the deprecated alias classes go.
- **Generated typed Criterion DSL** — Phase F.8 stretch; not Phase G's concern.
