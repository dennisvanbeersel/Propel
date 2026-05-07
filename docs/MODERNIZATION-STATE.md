# Propel2 Modernization — State Snapshot

**Branch:** `ar-rewrite`
**Commits ahead of master:** 203
**Test suite:** 2845 / 13546 / 21 GREEN (up from 2386 / 5126 / 19 at start — **+459 tests, +8420 assertions**)
**phpstan baseline:** 403 lines (down from 548 at start — **-26%**)
**psalm baseline:** 1606 lines (down from 2598 at start — **-38%**)
**Tier 1 signature snapshots:** 53 stable (additive growth only)

## Phase status

| Phase | Status | Summary file |
|---|---|---|
| **A** Foundations | ✅ PASS | `docs/PHASE-A-SUMMARY.md` |
| **B** Generated-code modernization | ✅ PASS | `docs/PHASE-B-SUMMARY.md` |
| **C** Schema/DDL features | 🟡 PASS-WITH-WAIVERS | `docs/PHASE-C-SUMMARY.md` |
| **D** Behaviors + CodeEmitter | 🟡 PASS-WITH-WAIVERS | `docs/PHASE-D-SUMMARY.md` |
| **E** Connection collapse + decorators + replica routing | 🟡 PASS-WITH-WAIVERS | `docs/PHASE-E-SUMMARY.md` |
| **F** Criteria split + enums + Tokenizer | 🟡 PASS-WITH-DEFERRALS | `docs/PHASE-F-SUMMARY.md` |
| **G** PHP 8.4 + 4.0 release | 🔶 PARTIAL (G.1 + G.2 done; G.3-G.10 deferred) | `docs/PHASE-G-PARTIAL-SUMMARY.md` |
| **H** CLI/migration tooling | 🟡 PASS-WITH-DEFERRALS (H.1 + H.2 done; H.3-H.5 deferred) | `docs/PHASE-H-SUMMARY.md` |
| **I** Observability | ✅ PASS | `docs/PHASE-I-SUMMARY.md` |
| **J** Worker mode / async | ⏳ NOT STARTED | — |

## What landed

### Foundations + tooling (Phase A — 100% complete)

- Quality gates: signature-diff, golden-file, lint-parity, baseline-monotonic, deptrac, Infection mutation testing.
- Symfony deprecation-contracts + phpunit-bridge wired.
- `failOn*` strict PHPUnit flags re-enabled (was disabled with TODO).
- Coverage restored in CI (PCOV).
- 7 critical bug fixes with TDD.
- Dead code removed: Validate behavior, QueryCache behavior, Validator/Constraints, MyISAM plumbing, XSLT pipeline, HHVM comments, `Serializable` interface.
- BC alias-deprecations: DebugPDO, PropelPDO (subsequently fully removed in Phase G), ConnectionManagerMasterSlave (removed in G), slaves/master config keys (hard-error in G).
- `#[\Override]` mechanical sweep across ~92 sites.
- 868 PHPUnit deprecations cleared via Rector + sed.
- Documentation: MIGRATION-FROM-PRE-AI.md, UPGRADE-3.0.md, BACKWARD_COMPATIBILITY.md, CHANGELOG.md, README compatibility matrix.
- Round 1 + Round 2 reviews + waivers + iteration logs.

### Generated-code modernization (Phase B — 100%)

- `declare(strict_types=1)` in every generated file.
- Typed properties on column attributes / FK references / base object attributes.
- `: self` return type on setters (LSP-safe — empirically validated).
- `: ?ClassName` typed-nullable getters on FK getters.
- `: static` on generated `Query::create()` static factories.
- Backed PHP 8.1 enum classes for ENUM columns (`<TablePhpName><ColumnPhpName>`).
- `__serialize`/`__unserialize` replacing `__sleep`/`__wakeup`.
- Builder-internal modernization: `match` expressions, variadic `declareClasses()`, shared phpDoc generation, MySQL date handling moved to PlatformInterface.

### Schema/DDL features (Phase C — partial)

- Native JSON / JSONB column types with PG operator helpers.
- Generated columns: `<column generated="virtual|stored" expression="...">`.
- CHECK constraints in schema model + DDL.
- INVISIBLE columns (MySQL 8 / MariaDB 10.3+).
- PG `IDENTITY` columns replace deprecated `serial`/`bigserial`.
- ColumnComparator drift detection (collation, comment, CHECK, partial-where).
- `JsonbOperator` runtime SPI for PG.
- Schema XSD additivity verified across 12 fixtures.
- **Deferred**: full INFORMATION_SCHEMA reverse-parser migration (needs testcontainers harness).

### Behaviors + CodeEmitter (Phase D — partial)

- `Generator/Builder/Util/CodeEmitter` introduced with PBT.
- NestedSet deprecated end-to-end with concrete recursive-CTE migration cookbook.
- Timestampable native `ON UPDATE CURRENT_TIMESTAMP` on MySQL 8+/MariaDB 10.5+.
- AggregateColumn opt-in bridge to native generated columns.
- Reverse-parser scaffolding (UUID detection, --reverse-format flag, Index/FK comparator extensions).
- **Skipped by design**: NestedSet refactor (deprecated → 4.0 removal).
- **Deferred**: bulk Sortable port (~40 methods; POC validated byte-identical).

### Connection collapse (Phase E — partial)

- `PdoConnection` (final, ~200 LOC) bare PDO bridge.
- Decorator chain: TransactionalConnection / LoggingConnection / CachingConnection / ProfilingConnection / ReplicaRoutingConnection.
- `ConnectionDecoratorInterface` Tier 2 SPI.
- `debug_backtrace()` removed from log path.
- Bounded-LRU prepared-statement cache (default 256, Zipf-tuned).
- `Criteria::forcePrimary()` / `allowReplica()` Tier 1 additive query hints.
- Session-consistency window + replica-lag awareness + primary fallback.
- ConnectionWrapper / StatementWrapper kept as BC shims (subsequently removed in Phase G's pass).

### Criteria split (Phase F — partial)

- `Comparison`, `JoinType`, `SortOrder`, `LogicalOperator` enums alongside `Criteria::*` constants.
- `OperatorAcceptor` interop helper for enum-or-string parameter signatures.
- New SQL tokenizer + `NameResolver` replaces hand-rolled `replaceNames` parser. Byte-equivalent on 10k-input fuzzing corpus.
- `PreparedStatementKey` SPI (closes Phase E inversion).
- `JoinPlan`, `WhereTree`, `OrderClause` Tier 3 value objects.
- Java-Hashtable methods on `Criteria` deprecated (`put`/`get`/`size`/etc.).
- `Criteria::customCondition()` — parameterized alternative to raw `CUSTOM` injection vector.
- **Deferred**: F.4 Criteria internal-state move into Plan/* (risk profile too high in single session); F.5 LOC slim-down audit; F.8 typed Criterion DSL stretch.

### CLI/migration (Phase H — partial)

- `#[AsCommand]` on all 15 console commands.
- Migration table redesign: `migration_name`, `batch`, `checksum` columns + backfill on first run.
- `MigrationCheckSummer` for SHA-256 drift detection.
- **Deferred**: `--dry-run` flag, `migration:squash`, `migration:baseline`, data vs structural migration templates.

### Observability (Phase I — 100%)

- TelemetryInterface SPI finalized.
- OpenTelemetry adapter (composer suggest).
- Prometheus adapter (composer suggest).
- CompositeTelemetry multiplex.
- Decorators wired through TelemetryInterface (CachingConnection / TransactionalConnection / ReplicaRoutingConnection).
- `docs/TELEMETRY.md` adapter-selection guide.

### PHP 8.4 + 3.x deprecation removal (Phase G — partial)

- PHP 8.4 minimum (composer.json `>=8.4`).
- phpcs phpVersion 8.4.
- DebugPDO + PropelPDO removed.
- ConnectionManagerMasterSlave removed.
- slaves/master config keys hard-error.
- Java-Hashtable Criteria methods marked end-of-runway.
- `propel/rector-rules` package scaffolded with 5+ migration rules.
- **Deferred** (substantial 4.0-release work): G.3 lazy objects, G.4 asymmetric visibility, G.5 property hooks, G.6 streaming formatter, G.7 WeakMap pool, G.8 remaining Rector rules, G.9 4.0 docs, G.10 reviews. Plus G.2.4b actual Java-Hashtable method deletion (blocked on TableMapBuilder/QueryBuilder template overhaul).

## Quality gates state (across the whole modernization)

- **Tier 1 BC contract**: 53 signature snapshots; growth has been ADDITIVE only (`forcePrimary`, `allowReplica`, `customCondition`); no removals or signature narrowing.
- **phpstan**: 0 errors, baseline 403 lines (was 548 + 4 blanket regex).
- **psalm**: 0 errors, baseline 1606 lines (was 2598 + global ImplementedReturnTypeMismatch suppression).
- **deptrac**: 0 violations against 233-line baseline.
- **cs-check**: clean.
- **Tests**: 2845 / 13546 / 21 GREEN (failOn* all true).
- **Bookstore golden tree**: idempotent; refreshed per generator commit.
- **XSD**: 12 fixtures validate against current XSD; additivity preserved per umbrella §3.7.

## Remaining work

### Phase G completion (the 4.0 release)

The biggest piece. Substantial PHP 8.4 features (lazy objects, asymmetric visibility, property hooks, streaming formatters, WeakMap pool) require dedicated multi-session work. Each is a multi-task module on its own.

### Phase J — worker mode / async

Not started. RoadRunner / FrankenPHP / Swoole long-running PHP support. Request-scoped instance pool reset, fiber-safe transaction context, connection lifecycle hooks, leak detection.

### Cross-phase deferrals to address

- **C.4 reverse-parser INFORMATION_SCHEMA migration** (~1300 LOC, blocked on testcontainers harness).
- **D.3 bulk Sortable port** (~40 methods, byte-identical pattern proven; mechanical follow-up).
- **F.4-F.5 Criteria internal-state move** + LOC slim-down to ≤650.
- **G.2.4b** Java-Hashtable Criteria method deletion (blocked on template overhaul in TableMapBuilder/QueryBuilder).
- **H.3-H.5** dry-run, squash, baseline, data-vs-structural migration templates.
- Round 3 (post-merge canary) reviews for Phases C, D, E, F, H.
- Mutation MSI ≥75 measurement (Infection runs but threshold not enforced; runs >1hr).
- Full 16-cell DB matrix exercise (only agnostic locally; mysql/pgsql/sqlite cells require CI).

## Recommended next sessions

1. **Phase G.3-G.10** in dedicated session with fresh subagent budget. Lazy objects + asymmetric visibility + property hooks + streaming formatter are each 4-5 task modules.
2. **Phase J** after G concludes (depends on PHP 8.4 substrate).
3. **Phase F.4-F.5** Criteria internal-state move (medium-risk; needs careful testing).
4. **Phase C.4 + D.3 deferrals** (testcontainers harness setup is its own meta-task).

The branch is in a coherent intermediate state — every phase summary documents what was deferred and why. The 4.0 tag is NOT appropriate from this state per Phase G partial summary's DoD assessment.
