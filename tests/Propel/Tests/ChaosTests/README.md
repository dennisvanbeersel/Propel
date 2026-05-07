# Chaos / Failure-Injection Tests

Per umbrella spec §4.12 + §4.14, this directory holds tests that simulate runtime failures (PDO connection drops mid-transaction, statement-cache evictions during prepared call, deadlock retry, fiber cancellation, replica-lag spikes, etc.).

Phase A scaffolds this directory but adds no actual chaos tests yet — the targeted failures live in code paths that Phase E (connection collapse + decorator chain) and Phase J (worker-mode / fiber-safety) introduce or tighten.

When adding a chaos test:
- Subclass `PHPUnit\Framework\TestCase`.
- Inject failures via mocks or fault-injection wrappers (NOT live database).
- Assert the runtime degrades gracefully — exception types, transaction state, instance pool consistency.
- Tag with `#[Group('chaos')]` so the suite can be filtered.

## Phase E targets (umbrella §7.2)

- PDO drop mid-transaction → `RollbackException` propagates, `nestedTransactionCount` resets to 0.
- Statement-cache evict during `prepare()` → next prepare re-prepares cleanly (no crash).
- Deadlock retry → bounded by configured max-attempts; never infinite loop.

## Phase J targets

- Fiber cancellation in middle of hydration → transaction commits or rolls back deterministically; instance pool not corrupted.
- Worker `onRequestEnd` hook fires even on uncaught exceptions in the request body.
- Long-running worker: 10k requests with no leaked transactions, no instance-pool growth past configured cap.
