# Phase C Iterations Log

## C.3.5 deferral (deferred — same-cycle)

QueryBuilder generation of `filterByXJsonbContains*()` family is deferred. The complexity of build-time platform-target detection plus golden-tree impact across PG-on-MySQL-on-SQLite mixed schemas exceeded the per-task quality budget. The runtime SPI (`JsonbOperator` enum + `buildClause`) is shipped and snapshotted as Tier 2 — downstream consumers can use it directly until the generator integration ships in a follow-up.

Mitigation: `JsonbOperator` is the actually-load-bearing piece (PDO `?` collision is the critical correctness concern). Generated helper methods are sugar on top.

## C.4.4 deferral

Round-trip property test against testcontainers requires a live MySQL/PG harness. Phase C ships unit-level reverse-parser fixtures (Group C.4 via JSON test fixtures) and defers the live PBT to Phase C post-merge canary (Round 3 / day-7 review).

## C.6.1 partial

`migration:diff --convert-serial-to-identity` opt-in flag is not wired in this Phase C delivery. The legacy-serial vendor flag (C.3.6) provides the migration runway in the OPPOSITE direction (opt INTO legacy serial); the OUT-OF-legacy direction relies on regular `migration:diff` against the IDENTITY default. C.6.2 cookbook documents the manual recipe.
