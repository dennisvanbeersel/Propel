# Phase B — Round 1 mid-phase review

**Branch:** `ar-rewrite`
**Range under review:** `b82269742..ce18cce20` (9 commits — B.2.2 through B.2.10; B.2.1 = strict_types in `9eb887d78` already in scope of A.30 closure)
**Reviewer mandate:** umbrella §4.13.3 Round 1 (3 lenses + Code-Generator specialist)

---

## 1 — Findings by lens

### Lens 1 — Architecture & SOLID

#### L1-F1 — `: self` on setters: docs/spec promise is empirically false
**Severity: MUST-FIX (documentation)**
- Empirically confirmed via PHP 8.x: a parent class with `public function setX($v): self` and a subclass override `public function setX($v) { ... }` (no return type) produces a fatal: `Declaration of … must be compatible with … : self`.
- Reproducer: created `MyBook extends \Propel\Tests\Bookstore\Book { public function setTitle($v) { … } }` against the regenerated bookstore tree → fatal at autoload, not runtime.
- Locations:
  - `docs/plans/2026-05-06-modernization-umbrella-spec.md:239` — claims "Existing user-subclass `setTitle($v)` overrides remain valid (they implicitly return `static`-compatible)."
  - `docs/MIGRATION-FROM-PRE-AI.md:160` — claims "With `: self`, your existing overrides remain valid."
- Both statements are factually wrong. Decision to use `: self` over `: static` is still correct (it's BC-easier than `: static` for the `static` covariance reason), but the BC-promise needs to be honestly stated: **subclass overrides MUST add a compatible return type (`: self`, the FK class, or `: static`); legacy override code WILL fatal-error at class load.**
- Same applies to FK getters (B.2.7: `: ?Publisher`) — confirmed with a second reproducer.
- Recommended remediation: amend §3.5 + migration guide; add a Rector rule or grep-check tip; possibly call out in CHANGELOG.

#### L1-F2 — ENUM/SET typed-property emission verified correct
**Severity: NICE (no action)**
- `ObjectBuilder::addColumnAttributeDeclaration` in `src/Propel/Generator/Builder/Om/ObjectBuilder.php:655–680` filters via `$column->isTemporalType() || $column->isLobType()` first → temporal/LOB stay untyped (correct). Then matches `phpType` to `int|float|bool|string`; default → untyped.
- ENUM: `getPhpNative()` resolves to `'int'` (PropelTypes:319), so property is `?int $style = null`. Accessor at `SortableTable13.php:392-403` reads via `$valueSet[$this->style]`; setter coerces via `array_search($v, $valueSet)`. Storage contract honored.
- SET: `?int $style2 = null` + companion untyped `$style2_converted` cache. Setter goes through `SetColumnConverter::convertToInt` before assignment. Verified in `SortableTable14.php:88-90,396-490`.
- ARRAY: native type `'array'`; falls through default → untyped. Correct (storage is serialized string, not array).
- JSON: native type `'string'` (PropelTypes:334) → `?string $bar = null`. Accessor unwraps via `json_decode`; setter encodes. Verified in `GeneratedObjectJsonColumnTypeTest.php:70-79`.

#### L1-F3 — `switch → match` semantic equivalence preserved
**Severity: NICE**
- `ColumnAccessorBuilderTrait::getTemporalTypeDefaultFormatConfigKey` (commit `ce18cce20`): inputs are `PropelTypes::*` constants which are themselves strings; both `==` and `===` agree. Equivalent.
- `ColumnMutatorBuilderTrait::addTemporalMutator` ditto; bonus: replaced raw `'DATE'`/`'TIME'` with `PropelTypes::DATE`/`PropelTypes::TIME` constants — type-safety win.

---

### Lens 2 — BC & migration realism

#### L2-F1 — Tier-1 signature snapshots untouched
**Severity: NICE (verified-clean)**
- `git diff --stat b82269742..HEAD -- 'tests/snapshots/*signatures*'` returns empty. No Tier-1 hand-written runtime class signature drift.

#### L2-F2 — `__sleep` → `__serialize`/`__unserialize` is wire-format BC break, undocumented
**Severity: SHOULD-FIX**
- `templates/Builder/Om/baseObjectMethods.php:215–242` (B.2.8 / commit `169e8273b`) replaces deprecated `__sleep` with `__serialize` + `__unserialize`. Old format = list of property names (PHP serializer then materialised values via `__sleep` semantics); new format = `array<string, mixed>` capturing values directly.
- Any persisted serialized AR object (session, cache, message queue, file) created on prior Propel versions WILL NOT round-trip through the new methods. PHP picks `__serialize` if defined.
- `docs/MIGRATION-FROM-PRE-AI.md:189–193` documents the analogous `PropelDateTime` change but does NOT document the AR-base-class change. **Add a section.**

#### L2-F3 — Setter return type LSP — see L1-F1
**Severity: MUST-FIX (docs)** — covered above; same root cause.

#### L2-F4 — Setter parameter types unchanged → user-subclass call sites safe
**Severity: NICE**
- `ColumnMutatorBuilderTrait.php:131-134` retains `($typeHint\$v$null)` param emission. No widening or narrowing introduced. Verified empirically: the type-error reproducer above only fails on return-type, not parameter-type.

#### L2-F5 — `PropelTypes::*_NATIVE_TYPE` constant value drift
**Severity: SHOULD-FIX (visibility)**
- `src/Propel/Generator/Model/PropelTypes.php:231-243` (B.2.2 / commit `aed19d243`): `REAL_NATIVE_TYPE`, `FLOAT_NATIVE_TYPE`, `DOUBLE_NATIVE_TYPE` shifted from `'double'` → `'float'`; `BOOLEAN_NATIVE_TYPE`, `BOOLEAN_EMU_NATIVE_TYPE` from `'boolean'` → `'bool'`. These are public class constants — observable by any consumer comparing against literal strings.
- `isPhpPrimitiveType` was extended to accept both names so the helper API still answers correctly, but downstream code reading the constant directly sees the new value. Migration guide doesn't mention this. Add a note (Tier-2 surface; minor risk but should be acknowledged).

#### L2-F6 — Deprecation allowlist still empty
**Severity: NICE**
- `tests/deprecations.allowlist.json` = `[]`. No deprecation surface added by Phase B.

---

### Lens 3 — Quality & rigor

#### L3-F1 — Baselines stable
**Severity: NICE**
- `phpstan-baseline.neon` = 403 lines (unchanged from Phase A end).
- `psalm-baseline.xml` = 1621 lines (unchanged).
- `composer stan` = 0 errors. `composer psalm` clean. `composer deptrac` = 0 violations / 171 allowed. `composer cs-check` clean.

#### L3-F2 — Generated tree not gated by phpstan/phpcs
**Severity: NICE (informational)**
- `tests/Fixtures/bookstore/build/classes` regenerated cleanly; running phpstan-7 against it surfaces 1000+ pre-existing errors. These errors exist in the *generated* code's static-analysis posture, but neither `phpcs.xml:13` nor `phpstan.neon` paths-block scans `tests/`. Lint-parity for generated tree is therefore not a CI gate; out of scope for Phase B but worth tracking for Phase B' / generator-architecture work.

#### L3-F3 — Per-task golden-tree refresh atomicity verified
**Severity: NICE**
- B.2.4 (`d7150e701`): src/Builder/Om/{ForeignKeyBuilderTrait,ReferrerBuilderTrait}.php + 49 golden files in same commit.
- B.2.6 (`dd64dbd4b`): src/Builder/Om/ColumnMutatorBuilderTrait.php + 65 golden files in same commit (244 ins / 244 del — pure signature touch).
- B.2.8 (`169e8273b`): templates/Builder/Om/baseObjectMethods.php + 65 golden files in same commit.
- B.2.10 (`ce18cce20`): correctly has NO golden-tree change because the match conversion is builder-internal — emitted output bytes identical.
- Commit hygiene clean.

#### L3-F4 — Bookstore fixture deterministic
**Severity: NICE**
- Pre-rebuild snapshot copy + `bash tests/bin/setup.sqlite.sh` + `diff -q` → exit 0. Re-running the build does not perturb output.
- `tests/Fixtures/bookstore/build/classes/Propel/Tests/Bookstore/Base/Book.php` matches `tests/snapshots/bookstore-golden/.../Book.php` byte-for-byte.

#### L3-F5 — Test coverage for new typed properties is missing
**Severity: SHOULD-FIX**
- No regression tests under `tests/Propel/Tests/` assert the strict-type contract for any of the now-typed AR attributes. Specifically: no test asserts that `(new Book())->setId(...)` followed by direct `$book->id = 'foo';` (or reflection-set) fails as `TypeError`. Phase B's contract is "consumer code that goes through setters keeps working; consumer code that tunnels through reflection or trait composition gets stricter typing." That second branch should have at least one positive-test as a regression sentinel.
- Recommend adding to a generator test (e.g. `tests/Propel/Tests/Generator/Builder/Om/GeneratedObjectColumnTypesTest.php` — new file) covering `?int / ?float / ?bool / ?string` columns each rejecting wrong-type direct assignment.

---

### Lens 4 — Code-generator specialist

#### L4-F1 — Template-vs-builder split is consistent
**Severity: NICE**
- B.2.3 (column attributes — string-emit) lives in `src/Propel/Generator/Builder/Om/ObjectBuilder.php:655-720`.
- B.2.4 (FK + referrer attributes — string-emit) lives in `ForeignKeyBuilderTrait.php` + `ReferrerBuilderTrait.php`.
- B.2.5 (4 base attributes — heredoc template) lives in `templates/Builder/Om/baseObjectAttributes.php`.
- B.2.8 (base methods — heredoc template) lives in `templates/Builder/Om/baseObjectMethods.php`.
- The two emission styles produce homogeneous output: typed `bool`/`array` from template + typed `?int`/`?string` from string-emit interleave cleanly in `Book.php:79-141`. No drift.

#### L4-F2 — Bookstore regeneration is idempotent (re-confirmed)
**Severity: NICE** — see L3-F4.

#### L4-F3 — Typed-property invariance: only one consumer fixture needed updating
**Severity: NICE**
- `grep -rn "public \$virtualColumns\|protected \$virtualColumns\|public \$modifiedColumns" src/ tests/` (excluding `Base/`, snapshots, `build/classes/`) returns empty after `tests/Propel/Tests/Runtime/ActiveRecordTestClasses.php` was fixed in `1be33055c`. No other consumer redeclares a now-typed parent property. Phase B did not miss any in-tree subclass.

#### L4-F4 — `: self` and `: ?ClassName` LSP — empirically a fatal
**Severity: MUST-FIX (documentation)** — covered in L1-F1 + L2-F3. The empirical reproducer fatals at class load for both:
1. Setter override without `: self` return type.
2. FK getter override without `: ?Publisher` return type (B.2.7 surface).

The `: self` decision over `: static` is still correct, but the BC-promise framing needs to be amended honestly.

---

## 2 — DoD checklist (mid-phase subset of umbrella §4.9)

| # | Check | Status | Evidence |
| - | ----- | ------ | -------- |
| 1 | Test matrix green | ✓ | `composer test:agnostic` → 2407 / 5158 / 21 |
| 2 | PHPStan baseline stable | ✓ | 403 lines, 0 errors |
| 3 | Psalm baseline stable | ✓ | 1621 lines, clean |
| 4 | Deptrac green | ✓ | 0 violations / 171 allowed |
| 5 | cs-check clean | ✓ | exit 0 |
| 6 | Tier-1 signatures untouched | ✓ | no `*signatures*` snapshot diff |
| 7 | Golden-diff per-commit | ✓ | builder src + golden tree co-staged in B.2.3 / 2.4 / 2.5 / 2.6 / 2.7 / 2.8 / 2.9 |
| 8 | Builder-only commits skip golden | ✓ | B.2.10 (`ce18cce20`) correctly has zero golden delta |
| 9 | Bookstore fixture regeneration deterministic | ✓ | re-run setup.sqlite.sh produced byte-identical output |
| 10 | Deprecation allowlist not grown | ✓ | `[]` |
| 11 | Migration guide reflects observable BC | ✓ (cycle 1) | L1-F1 / L2-F2 / L2-F5 sections added or rewritten in `MIGRATION-FROM-PRE-AI.md` and `CHANGELOG.md`; umbrella §3.5 amended honestly |
| 12 | Test coverage for new typed-property contract | ✓ (cycle 1) | `GeneratedObjectTypedPropertiesTest` (5 cases) — `?int`, `?string`, `?float`, `?bool` direct-assignment rejection + null acceptance sanity |

---

## 3 — Verdict

**PASS** (after cycle-1 amendments — see `B-iterations.md`).

All 4 actionable findings closed:

- **L1-F1 / L2-F3 / L4-F4 (MUST-FIX, doc honesty about `: self` LSP).** Closed.
  Umbrella spec §3.5 rewritten with the honest BC caveat (both `: self` and
  `: static` break overrides without a return type; `: self` is preferred
  because it's the easier of the two to fix in consumer code), grep-pattern
  pointer for finding overrides, and Rector-rule pointer planned for 4.0.
  `MIGRATION-FROM-PRE-AI.md` "API surface changes" rewritten with concrete
  before/after migration code and the FK-getter caveat. `CHANGELOG.md` BC
  Breaks subsection added.
- **L2-F2 (SHOULD-FIX, `__sleep` → `__serialize` wire-format break).** Closed.
  `MIGRATION-FROM-PRE-AI.md` got a new section near the existing
  `PropelDateTime` doc covering impact (sessions / caches / message queues),
  invalidate-at-deploy migration path, and one-time read-old-write-new
  fallback. `CHANGELOG.md` references it.
- **L2-F5 (SHOULD-FIX, `PropelTypes::*_NATIVE_TYPE` constant drift).**
  Closed. `MIGRATION-FROM-PRE-AI.md` got a section under "Removed column
  types" with the full constant table, before/after comparison code,
  recommendation to compare against the constant rather than the literal,
  and a grep pattern. `CHANGELOG.md` references it.
- **L3-F5 (SHOULD-FIX, typed-property regression test missing).** Closed.
  `tests/Propel/Tests/Generator/Builder/Om/GeneratedObjectTypedPropertiesTest.php`
  added with 5 test methods covering `?int`, `?string`, `?float`, `?bool`
  type rejection (using array-typed sentinels because PHP coerces between
  scalars; arrays cannot coerce) plus a sanity test for null acceptance.

Code/build hygiene was already solid: zero baseline drift, deterministic
regeneration, no Tier-1 signature break, atomic golden refreshes. Phase B
is cleared to advance to B.3 (EnumBuilder) and B.4 (Builder cleanup).

| Tally | Count | Closed |
| ----- | ----- | ------ |
| MUST-FIX | 1 (L1-F1 / L2-F3 / L4-F4 — same root cause: doc/migration honesty about LSP fatal) | ✓ |
| SHOULD-FIX | 3 (L2-F2 serialize wire-format; L2-F5 PropelTypes constant drift; L3-F5 typed-property regression test gap) | ✓ ✓ ✓ |
| NICE | 8 | (no action) |
