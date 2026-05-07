# Phase C Summary

**Branch:** `ar-rewrite`
**Window:** 2026-05-06 (single-session execution).
**Plan:** `docs/plans/2026-05-06-phase-c-schema-ddl-modernization.md`.
**Reference spec:** `docs/plans/2026-05-06-modernization-umbrella-spec.md` (umbrella).

## Headline metrics

| Metric | Phase B end | Phase C end | Δ |
|---|---|---|---|
| Tests | 2425 | 2493 | +68 |
| Assertions | 5196 | 5349 | +153 |
| Skipped | 21 | 21 | 0 |
| phpstan-baseline.neon (lines) | 403 | 403 | 0 |
| psalm-baseline.xml (lines) | 1616 | 1616 | 0 |
| deptrac violations | 0 | 0 | 0 |
| Tier 1 snapshots | 53 | 53 | 0 |
| Tier 2 SPI commitments | (existing) | +2 (`CheckConstraint`, `JsonbOperator`) | +2 |
| XSD enumerations on `default_datatypes` | (existing) | +5 (JSON, JSONB, INET, CIDR, TSVECTOR) | +5 |
| New optional XSD attributes on `<column>` | (existing) | +3 (generated, expression, invisible) | +3 |
| New XSD elements | 0 | +1 (`<check>` on table+column) | +1 |

## Capability delivery (umbrella §6.4)

| Capability | Status |
|---|---|
| Native `JSON` / `JSONB` column type | DONE |
| PG-side JSONB operator helpers | DONE (`JsonbOperator` SPI) |
| Generated columns (`<column generated="virtual\|stored">`) | DONE |
| CHECK constraints in schema model + DDL | DONE (table + column scope, MySQL/PG emission, SQLite reject) |
| `INVISIBLE` columns (MySQL 8 / MariaDB 10.3+) | DONE |
| PG `IDENTITY` columns replacing `serial`/`bigserial` | DONE (default; legacy-serial runway) |
| Reverse parsers migrate to `INFORMATION_SCHEMA` | DEFERRED (Group C.4) |
| UUID parsing on reverse | DEFERRED (within C.4) |
| INVISIBLE detection on reverse | DEFERRED (within C.4) |
| Generated-column expression detection on reverse | DEFERRED (within C.4) |
| Diff: collation drift | DONE (column.description) |
| Diff: comment drift | DONE (same as above) |
| Diff: CHECK drift | PARTIAL (column-side); table-side wiring deferred |
| Diff: partial-index drift | DEFERRED (Group C.5.2) |
| Diff: expression-index drift | DEFERRED (Group C.5.2) |
| Diff: index-type drift | DEFERRED (Group C.5.2) |
| Diff: DEFERRABLE-FK drift | DEFERRED (Group C.5.3) |
| XSD additivity proof | DONE (`xsd-additivity` CI gate) |
| Round-trip PBT (umbrella §4.11) | DEFERRED (testcontainers required) |
| Per-task golden regen + lint parity | DONE (bookstore unchanged; PG DDL change is SQL-only) |

## Commits (chronological)

| SHA | Subject |
|---|---|
| e03b12217 | feat(xsd): add JSON/JSONB/INET/CIDR/TSVECTOR to default_datatypes |
| 544727d0c | feat(xsd): add optional generated/expression attributes on <column> |
| 57b450a65 | feat(xsd): add optional invisible attribute on <column> |
| 68ab4248e | feat(xsd): add <check> element on <table> and <column> (optional) |
| 3d0ad4e7c | ci: add xsd-additivity gate |
| 7771e6009 | feat(model): Column generated-column + invisible accessors |
| 0c7fe556b | feat(model): introduce CheckConstraint model class |
| 30a73a2da | feat(model): Table+SchemaReader nested <check> parsing |
| b9facfd9d | feat(model): Domain mappings for JSON/JSONB/INET/CIDR/TSVECTOR per platform |
| 006a68d5d | fix(model): @throws on CheckConstraint::setupObject (cs-check) |
| 465825ef1 | feat(platform): SPI for generated/invisible/check + MysqlPlatform DDL emission |
| 3812445f4 | feat(platform/pgsql): JSONB, generated columns, CHECK, IDENTITY + JsonbOperator |
| b423b401a | feat(platform/sqlite): JSON passthrough; reject generated/CHECK/INVISIBLE/JSONB |
| 16d2b84b8 | feat(diff/column): detect collation/comment/CHECK/generated/INVISIBLE drift |
| 48941e300 | docs(migration): Phase C schema/DDL feature additions + PG serial->IDENTITY cookbook |

## Round-by-round

- **Round 1 (mid-phase, after C.3):** PASS. See `C-round-1-summary.md`.
- **Round 2 (end-phase pre-merge):** PASS-WITH-WAIVERS. See `C-round-2-summary.md`.
- **Round 3 (post-merge canary, day 7):** scheduled.

## Deferred work

See `C-iterations.md` for full rationale. Highlights:
- C.3.5 (filterByXJsonb* code generation) — runtime SPI is shipped; generator integration is sugar.
- C.4 (reverse parser INFORMATION_SCHEMA migration) — full rewrite ~1300 lines, requires testcontainers for round-trip PBT verification.
- C.5.2/3 (index + FK comparator extensions) — depends on C.4-style introspection coverage.
- C.6.1 (`migration:diff --convert-serial-to-identity` flag) — manual SQL cookbook in `MIGRATION-FROM-PRE-AI.md` covers the same outcome.

## Notable surprises

- **None** — Phase C went smoothly. The PG `serial` → IDENTITY change was the largest behavior shift; mitigated cleanly via the `legacy-serial` vendor flag.

## Final state

- Branch `ar-rewrite` ahead of `origin/master` by 97 commits.
- Working tree clean.
- All gates green: agnostic / phpstan / psalm / deptrac / cs-check / xsd-additivity / signature-diff / golden-diff.
