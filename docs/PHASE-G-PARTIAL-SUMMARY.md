# Phase G — Partial Summary (in-progress)

**Status:** PARTIAL EXECUTION — 6 commits landed in this session; 25+ remaining tasks deferred to follow-up cycles. The 4.0 release is **not** ready to tag from this state — additional execution sessions required.
**Branch:** `ar-rewrite` post-G.2.4.
**Plan:** `docs/plans/2026-05-07-phase-g-php-84-and-4-0-release.md` (commit `ae56403a1`).

## What landed (6 commits)

| # | Task | Commit | Notes |
|---|------|--------|-------|
| 1 | Plan committed | `ae56403a1` | 33+ task plan, HIGH-RISK 3-round cadence per umbrella §4.13.3. |
| 2 | G.1.1 — PHP 8.4 minimum bump | `de0d8915b` | composer.json `>=8.4`, CI matrix collapsed to 8.4-only. |
| 3 | G.1.2 — phpcs phpVersion bump | `ed0f3ae41` | Sniff config aligned with PHP 8.4 floor. |
| 4 | G.2.1 — Remove DebugPDO + PropelPDO | `7b96a0597` | Plus rector ruleset scaffold (`rector/` directory) + 2 rules + fixtures. |
| 5 | G.2.2 — Remove ConnectionManagerMasterSlave + master-named shims | `d82af4544` | Plus `ConnectionManagerMasterSlaveToPrimaryReplicaRector` + fixture. |
| 6 | G.2.3 — Hard-error slaves/master config keys | `2bfce00e4` | Plus `Slaves/MasterTo*ConfigRector` rules + fixtures. |
| 7 | G.2.4 — End Java-Hashtable deprecation runway (deletion deferred) | `7e7c3b45d` | See deferral note below. |

## Quality state at this point

| Gate | At Phase I exit | At Phase G partial exit |
|---|---|---|
| Tests (agnostic) | 2869 / 14364 / 21 | **2845 / 14054 / 21** (-24 tests due to deleted DebugPDO/PropelPDO/ConnectionManagerMasterSlave/JavaHashtableDeprecation runway tests; 3 hard-error tests added in DeprecatedConfigKeysTest replace removed deprecation tests) |
| `phpstan-baseline.neon` | 403 lines | **403 lines** (unchanged) |
| `psalm-baseline.xml` | 1621 lines | **1606 lines** (-15; 3 stale baseline entries pruned for deleted classes) |
| deptrac | 0 violations | 0 violations |
| cs-check | clean | clean |
| Tier 1 surface | stable | stable (only deleted classes were Tier 2 deprecated-since-3.0 — `DebugPDO`, `PropelPDO`, `ConnectionManagerMasterSlave`) |

## What's deferred (25+ tasks remaining)

### G.2 — Remaining 3.x deprecation removals
- **G.2.4 method deletion proper** — the 8 Java-Hashtable methods on Criteria still exist; runway is over (no `trigger_deprecation`) but the methods remain callable. Deletion blocked on parallel update of `TableMapBuilder` + `QueryBuilder` code-emission templates that emit `containsKey()` / `keyContainsValue()` into every generated entity's TableMap.php. Cannot delete without breaking every consumer's generated code on first re-build. Plan amendment: split into G.2.4a (deprecation runway end — done) + G.2.4b (template migration + method deletion + bookstore golden regen — deferred).
- **G.2.5 Hard-error raw `Criteria::CUSTOM` `add()` path** — not started.
- **G.2.6 Hard-error legacy PropelTypes + serial vendor flag** — not started.
- **G.2.7 Delete NestedSetBehavior, legacy reverse-format flag, Propel::initConfiguration()** — not started.
- **G.1.3 `#[\Deprecated]` attribute migration sweep** — not started.

### G.3 — Lazy relation objects (4 tasks)
Not started. The plan's risk register (item #1) flagged that PHP 8.4 lazy-ghost objects + property hooks haven't been combined extensively in production ORMs. Surface area: schema parsing (`use-lazy-objects`), `LazyRelationBuilder` emitter, ObjectBuilder integration, performance benchmark.

### G.4 — Asymmetric visibility on properties (4 tasks)
Not started. **Documented BC break** when landed.

### G.5 — Property hooks for dirty-tracking (4 tasks)
Not started. Coupling with G.3 lazy-objects identified as a risk.

### G.6 — Streaming Generator-based formatter (3 tasks)
Not started.

### G.7 — WeakMap instance pool (3 tasks)
Not started.

### G.8 — `propel/rector-rules` package (5 tasks)
**Partial:** Package scaffolded under `rector/` directory; 5 rules + fixture pairs landed alongside their G.2.x removals (DebugPDO, PropelPDO, ConnectionManagerMasterSlave, Slaves→Replicas, Master→Primary). Remaining rules pending corresponding source-side removals: CriteriaJavaHashtableMethods, CriteriaCustomToCustomCondition, PropelTypesLegacyToModern, PropelInitConfiguration, NestedSetBehaviorWarning, PropertyWriteToSetter.

### G.9 — Documentation (3 tasks)
Not started — depends on actual final scope.

### G.10 — Round 1 / Round 2 / Round 3 review checkpoints
Not started.

## Notable surprises during execution

1. **`DebugPDO` / `PropelPDO` removal had wide string-config blast radius.** Both classes were deprecated `class extends ConnectionWrapper` aliases, but ~30+ test fixtures referenced them as `'classname' => 'Propel\\Runtime\\Connection\\DebugPDO'` strings rather than as type-hinted PHP dependencies. The fix-out was mechanical (sed across YAML + PHP) but the fan-out meant the commit touched 33 files for what looked superficially like a 2-file delete.
2. **Java-Hashtable Criteria methods are baked into the code-emission templates.** `TableMapBuilder.php:1569,1588` and `QueryBuilder.php:1074,1085,1096,1229` emit literal `$this->containsKey($key)` and `$criteria->keyContainsValue(...)` strings into every generated entity's TableMap. The plan acknowledged the deprecation runway, but deletion in 4.0 requires parallel template overhaul + bookstore golden regen — much larger scope than the plan task framed. Resolution: end the runway (strip `trigger_deprecation`), keep methods callable into 4.x, schedule full deletion as G.2.4b.
3. **Psalm baseline has stale entries that prune cleanly when their target classes vanish.** Three entries (DebugPDO, PropelPDO, ConnectionManagerMasterSlave) auto-pruned, shrinking baseline from 1621 → 1606. This is exactly what umbrella §4.1's monotonic-shrink rule predicted.
4. **Symfony Config `beforeNormalization` `then()` callbacks must accept the value-arg even if unused.** Initial hard-error implementation took `(array $v): array` but never used `$v`; IDE flagged unused parameter. Fixed by switching to `(): never` since the `throw` exits the closure unconditionally.

## Recommended next session

1. G.2.7 + G.2.6 + G.2.5 — quick wins (deletion targets are isolated; rector rules already scoped).
2. G.6 streaming formatter — orthogonal to other work, low risk.
3. G.3 lazy-objects — needs the dedicated PHP 8.4 internals specialist review per §4.13.2.
4. G.4 + G.5 (asymmetric visibility + property hooks) — coupled, will need parallel golden regen, will land Round 1 review at the end of G.4.
5. G.8 expansion alongside G.2.x deletions — one-rule-per-deletion contract from the plan.
6. G.9 docs at the very end, when final scope is known.
7. G.2.4b (Java-Hashtable method deletion proper) — must follow B'/D template work, may slip to a 4.1-cycle minor.

The 4.0 tag is **not** appropriate from this state. Tag should wait until at least G.2.5/.6/.7 + G.3 + G.4 + G.5 + G.6 land and Round 1 + Round 2 reviews close.
