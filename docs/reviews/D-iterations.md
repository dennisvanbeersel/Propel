# Phase D — Iteration Log

> Cycle ledger for Phase D (Behaviors cleanup + CodeEmitter introduction).
> Format mirrors Phase A/B/C iteration logs; one entry per noteworthy
> iteration / decision / blocker.

## D.1.4 — POC consumer choice

**Decision:** swap from AutoAddPk to **Timestampable** as the proof-of-concept
consumer for the CodeEmitter API.

**Reasoning:** AutoAddPk operates entirely on the schema model
(`Table::addColumn()` array literal); it never emits PHP method bodies, so
it cannot exercise the indented-block / `methodBody` / docblock helpers that
are the bulk of the CodeEmitter surface. The plan documented this fallback
explicitly (Phase D plan, D.1.4 step 1).

Timestampable's `objectMethods()` is a single-method emission with docblock,
method signature, body, and trailing newlines — exactly the shape we need
to validate. The byte-identical golden diff after the port was **empty on
first try** — first signal that the CodeEmitter API survives a real
consumer with no surprises.

**Output verification:** `git diff --stat tests/snapshots/bookstore-golden/`
empty after `php tools/regen-golden.php`. The pinning unit test
`TimestampableObjectMethodsCodeEmitterTest::testObjectMethodsByteIdenticalToPreRefactor`
asserts the exact output bytes.
