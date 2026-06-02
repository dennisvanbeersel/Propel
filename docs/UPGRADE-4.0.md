# Upgrade Guide — Propel 4.0

For users upgrading from Propel 3.x to 4.0. For users coming from pre-rewrite
Propel (any earlier branch), see `MIGRATION-FROM-PRE-AI.md` first; for the
2.x → 3.0 step, see `UPGRADE-3.0.md`.

## §1. Required environment changes

| Component | 3.x minimum | 4.0 minimum |
|---|---|---|
| PHP | 8.3 | **8.4** |
| MySQL | 8.0 | 8.0 |
| MariaDB | 10.5 | 10.5 |
| PostgreSQL | 14 | 14 |
| SQLite | 3.6.19 | 3.6.19 |
| Symfony | 7.2 | 7.2 |

PHP 8.4 unlocks the lazy-relation, asymmetric-visibility, property-hook, and
streaming-formatter features detailed below — none of them are runtime-shimmable
to earlier engines.

## §2. Removed APIs

All Tier 2 deprecations from 3.x are removed at 4.0. The
`propel/rector-rules` package handles every callsite-level rewrite:

```sh
composer require --dev propel/rector-rules
vendor/bin/rector --rules=Propel4Migration src/
```

| 3.x symbol | 4.0 replacement | Rector rule |
|---|---|---|
| `Propel\Runtime\Connection\DebugPDO` | `(new ConnectionWrapper($pdo))->useDebug(true)` | `DebugPdoToConnectionWrapper` |
| `Propel\Runtime\Connection\PropelPDO` | `new ConnectionWrapper($pdo)` | `PropelPdoToConnectionWrapper` |
| `Propel\Runtime\Connection\ConnectionManagerMasterSlave` | `ConnectionManagerPrimaryReplica` | `ConnectionManagerMasterSlaveToPrimaryReplica` |
| Config `slaves:` / `master:` keys | `replicas:` / `primary:` | `SlavesToReplicasConfig`, `MasterToPrimaryConfig` |
| `Criteria::add($n, $sql, Criteria::CUSTOM)` | `Criteria::customCondition($n, $sql, $params)` | `CriteriaCustomToCustomCondition` |
| `Criteria::put/putAll/get/keys/containsKey/keyContainsValue/size/equals` | Modern equivalents (see plan §G.2.4) | `CriteriaJavaHashtableMethods` |
| `<column type="BU_DATE">` | `<column type="TIMESTAMP">` | `PropelTypesLegacyToModern` (informational) |
| `<column type="BU_TIMESTAMP">` | `<column type="TIMESTAMP">` | same |
| `<column type="BOOLEAN_EMU">` | `<column type="BOOLEAN">` | same |
| `<column type="OBJECT">` | `<column type="JSON">` or app-layer storage | same |
| `<column type="ARRAY">` | `<column type="JSON">` | same |
| `<vendor type="pgsql"><parameter name="legacy-serial" .../>` | `<column type="IDENTITY">` | (manual) |
| `--reverse-format=legacy-show-create` | (default) `--reverse-format=information-schema` | (manual) |
| `<behavior name="nested_set">` | Recursive-CTE / closure-table pattern | `NestedSetBehaviorWarning` (informational) |
| `Propel::initConfiguration(...)` | `Propel::getServiceContainer()->setConfiguration(...)` | `PropelInitConfiguration` |

## §3. Tier 1 frozen public API

Tier 1 surfaces remain frozen across the 4.x line. The same
`tests/snapshots/tracked-classes.txt` + `*.signatures.json` enforcement
applies. 4.0 grew the surface additively only:

- `Criteria::forcePrimary()` / `Criteria::allowReplica()` (Phase E.5).
- `Criteria::customCondition()` (Phase F.7; the parameterized successor to
  raw `CUSTOM`).
- `ModelCriteria::findStream()` (Phase G.6.2; streaming counterpart to
  `find()`).

No Tier 1 method was removed, narrowed, or had its signature change in a way
that breaks consumers.

## §4. New AR semantics

### §4.1 Lazy relation objects (opt-in)

`<table useLazyObjects="true">` switches the per-relation init from eager
`new ObjectCollection` + `setModel()` to PHP 8.4
`ReflectionClass::newLazyGhost()`. The collection materializes on first read
instead of in `init<Rel>()`.

Opt-in for 4.0; the default flips on in 4.1 if the §G rollback criterion
holds (≤+5% latency on a 100k-row `with()` query — measured in
`docs/reviews/G-bench-lazy.md`, currently 0.996×).

### §4.2 Streaming `findStream()`

```php
foreach ($query->findStream() as $book) {
    // yields one Book per row as the cursor walks; never materializes the full collection
}
```

Memory profile is O(1) in row count (modulo DataFetcher buffering). One-to-many
`with()` is rejected — the eager formatter's PK-keyed parent dedup needs
materialization. Pair with the WeakMap instance-pool variant (see §4.5) to
keep instance-pool memory bounded across a 100k-row stream.

### §4.3 Property hooks for dirty-tracking

(Phase G.5; lands during the same release cycle as G.4 below.)

Generated setters route through PHP 8.4 `set` property hooks for typed column
properties. Direct property-access reads remain first-class; the hook fires
post-assignment to mark the column as modified. Behavior-equivalent to the
3.x setter body — only the routing changes — but consumers that bypassed
setters for direct property writes will see the modified-flag fire
automatically.

### §4.4 Asymmetric property visibility (BC break for direct writes)

Generated typed column properties switched from `protected` to
`public protected(set)` on the base class. The shape changes:

```php
// 3.x:
abstract class Author { protected ?int $id = null; protected ?string $first_name = null; ... }

// 4.0:
abstract class Author { public protected(set) ?int $id = null; public protected(set) ?string $first_name = null; ... }
```

What this means in practice:

- **Read remains free.** `$author->id` works from anywhere — both subclasses
  and external callers.
- **Direct writes from external callers fatal.** Code outside the AR class
  hierarchy that did `$author->id = 5` previously could only do so when the
  property was on the same scope chain (i.e. nothing, since `protected`
  blocked it too). The practical change: PHPStan and runtime now both reject
  the same write that used to silently noop on protected properties.
- **Subclass writes still work.** The `protected(set)` floor (rather than
  the more aggressive `private(set)` the plan originally proposed) keeps
  user-defined subclasses, ConcreteInheritance child classes, and behavior-
  injected subclasses able to redeclare and write properties freely.

Untyped column properties (temporal / LOB / SET) keep the legacy `protected
$col;` shape — PHP 8.4 forbids asymmetric visibility on untyped properties,
and forcing `mixed` would break subclasses that historically redeclared
these with their own narrower type.

External-callsite migration:

```sh
vendor/bin/rector --rules=PropertyWriteToSetter src/
```

The `PropertyWriteToSetter` Rector rule (G.8.5) rewrites
`$author->id = 5` to `$author->setId(5)` automatically.

### §4.5 WeakMap instance pool (opt-in)

Phase G.7. Set `propel.instancePool.strategy: weak-map` in your runtime
configuration to swap the strong-ref `InstancePoolTrait::$instances` array
for a `WeakMap` variant. Entities are evicted from the pool when no strong
references remain — useful with `findStream()` over very long row sets.

Strong-ref remains the default to preserve cross-request behavior under
traditional FPM. Phase J revisits the default for worker-mode runtimes
(RoadRunner / FrankenPHP / Swoole).

## §5. Migration via Rector — one command

```sh
composer require --dev propel/rector-rules
vendor/bin/rector --rules=Propel4Migration src/
vendor/bin/propel model:build
```

The first command pulls the rule set; the second rewrites callsites in
`src/`; the third regenerates AR base classes from your schema with the
new emission. After this you should expect a clean `composer test`
+ `composer stan` cycle on a 3.x-clean codebase.

If you maintain custom subclasses of generated entities and historically
wrote properties directly (e.g. inside hydration helpers), those callsites
need either the Rector rewrite OR a manual switch to `setX()` calls.

## §6. Optional new packages

Propel 4.0 ships these as `composer suggest`, not hard requires:

- `propel/telemetry-otel` — OpenTelemetry adapter for the
  {@see TelemetryInterface} (Phase I).
- `propel/telemetry-prometheus` — Prometheus adapter, same SPI.
- `propel/rector-rules` — the migration rule set above.

## §7. What did NOT change

The following are intentionally NOT touched in 4.0:

- Tier 1 surface stability (per §3).
- Migration tool surface (`migration:diff` / `migration:migrate` accept the
  same flags; only the underlying tracking-table schema redesign from H.1
  is invisible to consumers).
- Schema XSD additivity — every 3.x schema continues to **parse** in 4.0,
  even with removed-type columns. The generator pipeline rejects code
  emission for removed types (per §2), but the validator does not.

## Reading order if you're upgrading from pre-rewrite Propel directly to 4.0

1. `MIGRATION-FROM-PRE-AI.md` — pre-rewrite to 2.x base.
2. `UPGRADE-3.0.md` — 2.x → 3.0.
3. This file — 3.0 → 4.0.

The Rector rule set covers callsite work for 3→4 only. Pre-3.x callsite
patterns (e.g. raw `Criteria::CUSTOM` add() with non-empty SQL) are
caught by the same 4.0 rule set because the deprecation runway started in 3.0.
