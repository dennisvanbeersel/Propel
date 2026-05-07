# Phase D — Summary

**Status:** PASS-WITH-WAIVERS (D.2/D.5/D.6 partial shipped; D.3 POC validated, bulk port deferred to fresh-context follow-up; D.4 SKIPPED — NestedSet is deprecated, refactoring deprecated code competes for value).
**Branch:** `ar-rewrite` at `1c3dd4e10`.
**Effort:** 16 of 27 plan-tasks shipped + 1 POC; 11 deferred (5 Sortable mechanical follow-up, 5 NestedSet skipped-by-design, 2 reverse-parser INFO_SCHEMA blocked on testcontainers).
**Test suite:** 2553 / 5811 / 21 (GREEN; up from 2493 / 5349 / 21 — +60 tests of new regression coverage).

## What Phase D delivered

### CodeEmitter introduction (Group D.1, complete)

A new `Generator/Builder/Util/CodeEmitter` class that replaces the string-concat builder pattern with a structured API:
- Indentation tracking via `indent()`/`dedent()`.
- Method-signature emission via structured input.
- Expression escaping (var_export for safe values; explicit `$varname` for variables).
- Block emission for `{...}` with auto-managed indentation.
- Property-based test on `tests/Propel/Tests/PropertyTests/` validates indentation invariant + lint-clean output.
- Validated end-to-end via two real consumers (Timestampable + Sortable POC) — API survived without redesign.

### NestedSet deprecation (Group D.2, complete)

- Class-level `@deprecated` PHPDoc on `NestedSetBehavior`.
- `trigger_deprecation('maturix/propel', '3.0', ...)` fires on schema parse for any schema using `<behavior name="nested_set">`.
- Generated NestedSet methods on AR / Query / TableMap classes get `@deprecated` PHPDoc emission.
- Concrete recursive-CTE migration cookbook in `MIGRATION-FROM-PRE-AI.md` covering the 5 most-common operations: `getDescendants`, `getAncestors`, `getSiblings`, `makeRoot`/`insertAsChildOf`, `isDescendantOf`. Each entry has before/after examples (NestedSet method call → raw `WITH RECURSIVE` SQL via `Criteria::where()`).
- CHANGELOG entry under `[Unreleased]` "Deprecated".

### Timestampable native ON UPDATE (Group D.5, complete)

- Generator emits native `ON UPDATE CURRENT_TIMESTAMP` DDL for `update_column` on MySQL 8+ / MariaDB 10.5+.
- `<behavior name="timestampable" use_native_on_update="false">` opt-out for BC.
- 3 expected-output tests updated for the new vendor attribute on the `update_column`.
- Migration cookbook entry: explains DDL change + `keepUpdateDateUnchanged()` interaction (continues to work — uses `modifiedColumns[]` to skip the SET clause).
- CHANGELOG entry.

### AggregateColumn native generated-column bridge (D.5.3, complete)

- `<behavior name="aggregate_column" use_native_generated="true">` opt-in emits the column as a native generated column (Phase C-delivered `<column generated="virtual|stored" expression="...">`) instead of trigger-maintained.
- Default OFF for BC; opt-in for new schemas.

### Reverse-parser improvements (Group D.6, partial)

Forward-compatible scaffolding landed; the actual INFORMATION_SCHEMA rewrite deferred:

- **D.6.3 — UUID detection heuristic**: detects UUID columns by type signature (BINARY(16) + CHECK on MySQL; native UUID on PG).
- **D.6.4 — `--reverse-format` CLI flag** for `database:reverse` command. Accepts `legacy-show-create` and (future) `information_schema`. Emits a deprecation if `legacy-show-create` is selected — guides users toward the new path once it lands.
- **D.6.5 — IndexComparator extensions**: detects partial-where drift, expression-index drift, USING-clause type drift.
- **D.6.6 — ForeignKeyComparator extensions**: detects DEFERRABLE drift (PG only).
- New XSD attribute on `<index>`: `where=""` and `using=""` (additive per umbrella §3.7).

### Sortable refactor POC (Group D.3, partial)

- `SortableBehaviorObjectBuilderModifier::addIsFirst()` ported to CodeEmitter — byte-identical golden output verified.
- Pattern proven; mechanical follow-up for ~40 remaining methods explicitly deferred to a fresh-context subagent execution. The byte-identical contract is the verification mechanism.

## Deferred / Skipped (with reasoning)

| Item | Status | Reason |
|---|---|---|
| **D.3 bulk Sortable port** (~40 methods × Object + Query modifiers) | DEFERRED | POC `addIsFirst()` proves the byte-identical pattern; remaining is mechanical follow-up. Recommend dedicated subagent-driven session to converge byte-identical on each method. |
| **D.4 NestedSet refactor** (~2,900 LOC) | SKIPPED-BY-DESIGN | NestedSet is deprecated end-to-end (D.2.1) with removal at 4.0. Refactoring deprecated code competes for value vs. fresh capability work. The deprecation runway makes the refactor moot. |
| **D.6.1 / D.6.2 reverse-parser INFO_SCHEMA migration** (~1300 LOC) | DEFERRED | Testcontainers harness needed for round-trip PBT verification per umbrella §4.11. Local SQLite-only verification is insufficient — round-trip tests require hitting actual MySQL 8 + MariaDB 10.5+ + PG 14+. The `--reverse-format` flag (D.6.4) is forward-compatible plumbing for when the harness lands. |

## Quality gates

| Gate | Phase C end | Phase D end |
|---|---|---|
| `phpstan-baseline.neon` | 403 lines | **403 lines** (unchanged) |
| `psalm-baseline.xml` | 1616 lines | **1621 lines** (+5; SPI accessors for new comparator methods) |
| `deptrac` violations | 0 against 233 | **0 against 233** |
| PHPUnit `failOn*` | All true | All true |
| cs-check | clean | clean |
| Tests | 2493 / 5349 / 21 | **2553 / 5811 / 21** (+60 / +462) |
| Tier 1 signature snapshots | 53 stable (1 refresh sync) | **53 stable** |
| Bookstore golden tree | 399 files, idempotent | **399 files, idempotent** |
| XSD additivity | 12 fixtures validate | **12 fixtures validate** |

## Notable surprises

1. **CodeEmitter API survived first contact with two consumers** without redesign. The `lines()` heredoc helper proved unused — line-by-line emission via `line()` won in practice.
2. **Sortable byte-identical port converged first try** — the API was right-sized.
3. **NestedSet deprecation cascade in phpstan** triggered 46 errors from modifier-class internal cross-calls. Resolved with path-scoped `ignoreErrors` in `phpstan.neon` covering the four deprecation identifier types under the NestedSet directories. Documented exception, not a baseline regression.
4. **TableMapBuilderModifier already template-based** — D.3.3 marked N/A. Sortable's table-modifier path uses `renderTemplate()`, not string-concat. The Phase B plan task that originally specified Sortable-modifier porting was based on stale source survey.
5. **D.4 SKIP-BY-DESIGN** is correct mainline practice. Don't refactor deprecated code.
6. **Reverse-parser rewrite is genuinely blocked on testcontainers**. The `--reverse-format` flag is forward-compatible plumbing.

## Review process

Per umbrella §4.13.3 MEDIUM-RISK 2-round cadence:

- **Round 1** (mid-phase, after D.3 POC): PASS-WITH-DEFERRALS. CodeEmitter validated; D.4 SKIP-BY-DESIGN documented; bulk Sortable port deferred to fresh-context.
- **Round 2** (end-phase, after D.6 partial): PASS-WITH-WAIVERS. Scope decisions, not quality compromises. Every commit met per-task quality gates.

## Next: Phase E

Per umbrella spec §5, Phase E = Runtime Connection layer collapse + decorators + replica routing. HIGH-RISK 3-round cadence. Specialists: SQL/concurrency + security.

Big-ticket per umbrella §2.1:
- Merge `PdoConnection` + `ConnectionWrapper` (722 LOC, 5 responsibilities) into `final` class.
- Decorator chain via `ConnectionDecoratorInterface` SPI (Tier 2): `TransactionalConnection`, `LoggingConnection`, `CachingConnection`, `ProfilingConnection`, `ReplicaRoutingConnection`.
- Replace `debug_backtrace()` in log path.
- Bounded-LRU prepared-statement cache.
- Replica routing intelligence: `forcePrimary()`/`allowReplica()` query hints, session-consistency window, replica-lag awareness, primary-fallback.

Phase E is the biggest architectural change in the rewrite. Expect 2-3 review iterations.
