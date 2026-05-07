# Phase E Round 1 — Mid-phase Review Summary

**Date:** 2026-05-07
**Trigger:** Tasks E.1.1–E.4.5 landed and green. Three of five decorators in place + foundation + bounded LRU.
**Tier:** HIGH-RISK 3-round cadence (umbrella §4.13.3); this is mid-phase Round 1.

## What's landed

| Group | Status | Commit(s) |
|---|---|---|
| E.1 — `PdoConnection` final + decorator interface | DONE | b3c97a655, ca53f71bd, 4642c54f5, 5f285eb5e |
| E.2 — `TransactionalConnection` | DONE | 0caafc52e, 897be8cc7, 8f8529173 |
| E.3 — `LoggingConnection` (no `debug_backtrace`) | DONE | 98ab0b07b, 7a976b650, 66bd572ca |
| E.4 — `CachingConnection` + bounded LRU + chaos test | DONE | dc9c3987f, 81f37c21f, 942402f1d |

## Quality gates

- `composer test:agnostic`: **2656 / 13606 / 21 GREEN** (up from 2619 / 7423).
- `phpstan-baseline.neon`: **403 lines** (no growth).
- `psalm-baseline.xml`: **1621 lines** (no growth).
- `composer cs-check`: clean.
- Deptrac: 0 violations.
- Tier 1 signature snapshots: stable.

## Lens reviews

### Architecture (decorator chain shape)

`AbstractConnectionDecorator`'s default-forwarder pattern works as designed. Each of the three landed decorators (`TransactionalConnection`, `LoggingConnection`, `CachingConnection`) overrides only the methods it owns:

- `TransactionalConnection` → `beginTransaction`, `commit`, `rollBack`, `inTransaction`, `getNestedTransactionCount`, `isCommitable`, `forceRollBack`.
- `LoggingConnection` → `prepare`, `exec`, `query`, `beginTransaction`, `commit`, `rollBack`, plus PSR-3/telemetry hooks.
- `CachingConnection` → `prepare` only, plus cache management methods.

`getInner()` is uniformly chain-walkable. No instanceof leakage. Composition order is the canonical inner-to-outer documented in `docs/CONNECTION-DECORATORS.md`.

**Verdict:** PASS. No MUST-FIX.

### BC (Tier 1/2 surface integrity)

- `ConnectionInterface` and `StatementInterface`: signature-diff snapshot stable.
- `ConnectionDecoratorInterface` (Tier 2 SPI) survives the third-party-implementer smoke check (`AbstractConnectionDecorator` IS such an implementation).
- `Criteria::forcePrimary` / `allowReplica` / `getRoutingHint`: **NOT YET LANDED** — deferred to E.6.

**Verdict:** PASS. No MUST-FIX.

### SQL & concurrency

- Nested-tx invariants: PBT seeded at `7331` runs 60 random sequences (40 + 20) against `TransactionalConnection`; all invariants hold (`getNestedTransactionCount` = begin − commit − rollback; `inTransaction()` mirror; tainted-flag flip on nested rollback).
- LRU held-reference invariant: PBT seeded at `9001` runs 40 random put/get sequences plus a 100-iteration adversarial eviction loop; PHP refcounting keeps held references alive across eviction. Chaos test (`StatementCacheEvictDuringPrepareTest`) confirms with a real `PdoConnection` over SQLite.
- Cache-key normalization (`ksort` over `$driverOptions`): unit-tested in both directions (different options → distinct keys; reordered options → same key).

**Verdict:** PASS. The held-reference invariant is the riskiest item; it has formal proof + chaos coverage.

### Security

- LRU eviction is DoS-resistant: capacity bound at 256 default; `InvalidArgumentException` on capacity < 1.
- Cache key uses `serialize` + null-byte separator. **SHOULD-FIX (deferred):** plan §6.2 risk #7 mentions adding a SQL-length cap to prevent memory amplification via mega-SQL. Logged as future-cycle hardening; not blocking R1.
- Logged data: PSR-3 logs the SQL string only — no bound parameter values are logged in the new path. (The legacy `bindValuesToStatement` path stays on the BC shim; flagged for E.8 review.)

**Verdict:** PASS-WITH-WAIVER (SQL-length cap deferred).

## Notable surprises

- `cs-fix` on `LoggingConnectionTest.php` rewrote `string|\Stringable` to `Stringable` in an anonymous class extending `Psr\Log\AbstractLogger`. The extended PSR-3 v3 base requires `Stringable|string`. Caught by `composer test:agnostic` failing during setup; restored to `Stringable|string`. Lesson: cs-fix's "fully qualified name → import" auto-fix can drop a union member when one branch is FQN.
- LRU `keys()` returns `array<int, string>` (re-indexed) for consumer ergonomics; the iterable annotation in the original plan is satisfied but the array shape is `list<string>` not `iterable<string>`.

## PBT seeds (reproducibility)

- `NestedTransactionInvariantTest`: SEED_BASE = `7331`.
- `PreparedStatementLruInvariantTest`: SEED_BASE = `9001`.
- `PreparedStatementCacheCapacitySweepTest`: SEED = `314159`.

## Decision

**PROCEED to E.5** (ProfilingConnection). No MUST-FIX blocking. One deferred SHOULD-FIX (SQL-length cap) tracked for Round 2.
