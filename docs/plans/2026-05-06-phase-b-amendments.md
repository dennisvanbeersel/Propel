# Phase B — Amendments and Kickoff

**Date:** 2026-05-06
**Branch:** `ar-rewrite`
**Companion plan:** `docs/plans/2026-02-03-builder-om-modernization.md` (the original Phase B plan, drafted before Phase A executed).

This document amends the companion plan with three things:
1. Maps the original-plan tasks against what already landed pre-Phase-A.
2. Pins the umbrella-spec-driven changes (`: self` not `: static`, golden-diff per task, signature-diff gate semantics).
3. Sequences the remaining work.

---

## 1. Status of original Phase B plan (37 tasks)

### Phase 1 of original plan — COMPLETE pre-Phase-A

These landed in early `ar-rewrite` commits before Phase A started. Verified via `git log master..ar-rewrite`:

| Original task | Commit | Status |
|---|---|---|
| 1.1 — `strftime()` → `date()` in query header | `ccc030cd4` | DONE |
| 1.2 — `!= 0` → `!== null` FK lazy load | `0f5ee2ac4` | DONE |
| 1.3 — null guard in 1:1 FK bidirectional binding | `8b49afc2d` | DONE |
| 1.4 — rewind stream before dirty check | `eba736be4` | DONE |
| 1.5 — `addAnd` vs `add` first criterion in `addFilterByArrayCol` | `7c6f435c0` | DONE |
| 1.6 — UuidConverter import in Query | `bb9dbdb30` | DONE |
| 1.7 — strict null comparison in TableMapBuilder | `8c7e38df6` | DONE |
| 1.8 — `addslashes(null)` in QueryBuilder | `279051637`, `2611eb15d` | DONE |
| 1.9 — class names in inheritance builder errors | `c9db2eb54` | DONE |
| 1.10 — `JSON_THROW_ON_ERROR` everywhere | `0c2a91775`, `fcc16a684` | DONE |
| 1.11 — `allowed_classes` to generated `unserialize()` | `709a5119f`, `bef50aa36` | DONE |
| 1.12 — mutator visibility for addElement/removeElement | `545b7c9ed` | DONE |

**Phase 1 verdict:** 12/12 done. No re-execution needed.

### Phase 2 of original plan — TO EXECUTE (Generated code modernization)

10 tasks. PHP 8.3+ generated code: typed properties, return types, `match` expressions, `declare(strict_types=1)` at the top of every generated file.

| Task | Notes after Phase A |
|---|---|
| 2.1 — `declare(strict_types=1)` in generated output | Execute |
| 2.2 — Fix legacy PropelTypes aliases (BU_DATE→DATE etc.) | **Already deprecated** in Phase A.25 with `trigger_deprecation`; type aliasing in PropelTypes still pending — execute |
| 2.3 — Typed properties for generated column attributes | Execute |
| 2.4 — Typed properties for generated FK + referrer attributes | Execute |
| 2.5 — Typed properties on base object attributes template | Execute |
| **2.6 — Return types on generated setters** | **AMENDED: use `: self` not `: static`** per umbrella §3.5 (LSP-safe for user subclasses) |
| 2.7 — Return types on generated FK getters/setters | Execute. Same `: self` rule applies. |
| 2.8 — Return types on base object methods template | Execute |
| 2.9 — Fix `strftime()` reference in temporal accessor docblock | Execute |
| 2.10 — Use `match` expressions in builder code | Execute |

### Phase 3 of original plan — TO EXECUTE (ENUM backed enums)

5 tasks. Generate PHP 8.1 backed string enum classes for schema `<column type="ENUM">` declarations. Execute as written.

### Phase 4 of original plan — TO EXECUTE (Builder infrastructure cleanup)

10 tasks. Internal generator cleanup. Execute as written.

### Original "Phase 4.10 — final verification" — REPLACED

The original plan's "regenerate fixture and manually inspect" step is replaced by the umbrella-spec quality gates (signature-diff, lint-generated, golden-diff) which run automatically per task. See §3 below.

---

## 2. Umbrella-spec amendments (apply to every task in Phase 2/3/4)

### 2.1 `: self` not `: static` on generated setters

Round 1 BC reviewer caught this. `: static` would break LSP for user subclasses overriding `setX($v)` without a return type. The companion plan's task 2.6 explicitly committed `: static` — reverse it.

The same rule applies to generated FK setters (task 2.7) and any future generated setter return type.

### 2.2 Golden-file regeneration is a per-task deliverable

Per Phase A.35 + umbrella §4.7, every task that changes generated code MUST:

1. Regenerate `tests/snapshots/bookstore-golden/` via `php tools/regen-golden.php`.
2. Commit the regenerated tree in the SAME commit as the generator-source change.
3. Reviewer sees the diff in PR and approves line-by-line.

This catches regressions that signature-diff (which only watches Tier 1 hand-written runtime classes per W6) cannot — comment drift, method-order shifts, file additions/removals.

### 2.3 Lint-parity gate must pass on regenerated code

Per Phase A.13 + umbrella §4.6, the regenerated bookstore tree must pass:
- `vendor/bin/phpcs --standard=phpcs.xml tests/Fixtures/bookstore/build/classes/`
- `vendor/bin/phpstan analyze --level=7 tests/Fixtures/bookstore/build/classes/ --no-progress`

These run automatically on PR via the `lint-generated` CI job. Locally, run them after each task before committing.

### 2.4 Signature-diff gate covers Tier 1 hand-written runtime classes only

Per W6 (Round 2 waiver), generated `Propel\Tests\Bookstore\Base\*` classes are NOT in `tests/snapshots/tracked-classes.txt`. They're enforced via golden-diff instead.

**Implication for Phase B:** generator changes will produce LARGE golden-diff per task (typed properties touch every column in every generated class). Reviewers must approve the diff carefully. If golden-diff becomes too noisy, revisit the W6 waiver — but for now it's the working contract.

### 2.5 `composer test:agnostic` must remain GREEN per task

The Phase A failOn* discipline is now strict. Any deprecation, warning, risky test, or notice introduced by a Phase B change fails the suite. If a generator change emits new deprecations that consumers will see, the deprecation must be allowlisted (regenerate `tests/deprecations.allowlist.json`) AND documented in `docs/MIGRATION-FROM-PRE-AI.md`.

### 2.6 Baselines are monotonic

`phpstan-baseline.neon` (currently 403 lines) and `psalm-baseline.xml` (currently 1621 lines) must NOT grow during Phase B. If a task introduces new analysis errors on generated code, fix at the generator source, not via baselining. Per the user directive "no batch stuff, no ignoring 2000 errors."

### 2.7 Per-phase review-and-test protocol applies

Phase B is **HIGH-RISK 3-round cadence** per umbrella §4.13.3:
- Round 1 mid-phase (after ~Phase 2 completion): architecture + BC + code-generator specialist.
- Round 2 end-phase pre-merge: all 5 standing reviewers + code-generator specialist.
- Round 3 post-merge canary: performance + quality + BC.

Specialist for Phase B: **code-generator specialist** (template safety, generated-code idiom correctness, BC-safe codegen).

---

## 3. Execution sequencing

Recommended order:

| Step | Tasks | Notes |
|---|---|---|
| **B.2.1** | Original 2.1 (`declare(strict_types=1)`) | Smallest deliverable; massive golden-diff (one extra line in every generated file). Sets baseline for all subsequent diffs. |
| **B.2.2-2.5** | Typed properties (2.2, 2.3, 2.4, 2.5) | Each task has its own commit + golden-diff. Run lint-generated locally before each commit. |
| **B.2.6-2.8** | Return types with `: self` | Especially careful with 2.6 — verify user-override LSP via property-based test on the bookstore fixture (regenerate into a temp dir, instantiate user-subclass shim, ensure no fatals). |
| **B.2.9-2.10** | Docblock fix + match expressions | Smaller tasks; group commit acceptable. |
| **Round 1 review** | After Phase 2 of original plan completes | 3-4 reviewers. |
| **B.3.1-3.5** | EnumBuilder | New code path; needs explicit unit tests (not just golden-diff). |
| **B.4.1-4.9** | Builder infrastructure cleanup | Internal Tier 3 — golden-diff is the contract. |
| **Round 2 review** | After Phase 4 of original plan completes | All standing + specialist. |
| **B.4.10 → DoD check** | Replaced by umbrella §4.9 15-box DoD verification | Append retrospective to this amendment file. |
| **Round 3 canary** | After merge to integration branch | 7-day post-merge with consumer-smoke + ecosystem advisory. |

---

## 4. Tasks blocking Phase B execution

None. Phase A's deliverables fully unblock Phase B:
- Quality gates wired and enforcing.
- BC contracts pinned (Tier 1 frozen via signature-diff; generated code via golden-diff).
- Deprecation telemetry operational.
- Test suite green at 2413/5176/21 with all strict failOn flags.
- Baselines below Phase A targets (403 phpstan, 1621 psalm).
- Documentation foundation established.

Phase B can begin immediately with task 2.1.

---

## 5. Iteration log placeholder

Round 1 / Round 2 / Round 3 iteration cycles will be recorded under `docs/reviews/B-iterations.md` once Phase B begins. Round 1 budget: 3 cycles. Round 2 budget: 3 cycles. Round 3 budget: 3 cycles.

Findings tagged `MUST-FIX` / `SHOULD-FIX` / `NICE` per umbrella §4.13.4.
