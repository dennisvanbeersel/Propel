# Phase B — Round 2 end-phase pre-merge review

**Branch:** `ar-rewrite`
**Range under review:** `b82269742..1d4e0fca1` (22 commits — full Phase B)
**Reviewer mandate:** umbrella §4.13.3 Round 2 (5 standing + Code-Generator specialist; consolidated single-pass adversarial across 6 lenses for token efficiency)
**Date:** 2026-05-06
**Reviewer:** consolidated standing+specialist team

---

## 0. Pre-verified state (input from caller)

- composer test:agnostic: 2425 / 5196 / 21 GREEN
- composer stan: 0 errors; baseline 403 lines (unchanged Phase B)
- composer psalm: clean; baseline **1616** lines (DOWN from 1621 — Phase B B.4.1 removed dead code → 3-line baseline shrink documented in `da7b28e96`)
- composer deptrac: 0 violations / 175 allowed
- composer cs-check: clean

Round 2 verified independently: `wc -l phpstan-baseline.neon psalm-baseline.xml` → `403 / 1616`. ✓

---

## 1 — Findings by lens

### Lens 1 — Architecture & SOLID

#### L1-F1 — EnumBuilder integration is clean (SRP intact)
**Severity: NICE (verified-clean)**
- `src/Propel/Generator/Builder/Om/EnumBuilder.php:32` extends `AbstractOMBuilder` directly. Override discipline is correct: `getUnprefixedClassName()`, `getNamespace()`, `build()` overridden; the inherited `addClassOpen/Body/Close` template-method hooks are no-op'd because `build()` is fully bespoke (the file emits `enum`, not `class`).
- Registration site: `ModelManager::build()` at `src/Propel/Generator/Manager/ModelManager.php:83-91` iterates `$table->getColumns()` and dispatches `'enum'` to the configured builder. Configuration registers default class in `src/Propel/Common/Config/PropelConfiguration.php:451`. `QuickGeneratorConfig` inherits the registration via `array_replace_recursive` of the same default config.
- No leakage into existing builders. ObjectBuilder/QueryBuilder reach the enum CLASS-NAME via the new `AbstractOMBuilder::getEnumClassName()` helper (commit `b72754f9a`), not into the builder itself. SRP intact.

#### L1-F2 — InterfaceBuilder refactor is sound, but one nit
**Severity: NICE**
- `git diff fd8eff88d~..fd8eff88d` shows clean swap from `AbstractObjectBuilder` to `AbstractOMBuilder`. The dropped `getInterface()` indirection (was via `$this->getInterface()` — pulled in from `AbstractObjectBuilder`'s context-tied helpers) is replaced with a direct `$this->getTable()->getInterface()` call at `InterfaceBuilder.php:31`.
- All other `extends AbstractObjectBuilder` users (`ExtensionObjectBuilder`, `MultiExtendObjectBuilder`, `ObjectBuilder`) actually need the AbstractObjectBuilder-specific machinery; refactor doesn't break them.
- Nit: `InterfaceBuilder.php:31` uses unqualified `ClassTools::classname(...)` without a `use` — relies on same-namespace resolution (`Propel\Generator\Builder\Om`). Functional but inconsistent with the explicit imports elsewhere. Left as-is matches house style.

#### L1-F3 — `getInvalidDateString()` extraction covers all 3 sites the plan flagged
**Severity: NICE (verified-clean)**
- `grep -rn 'instanceof MysqlPlatform' src/Propel/Generator/Builder/Om/` returns ZERO hits. Verified.
- Three call sites converted to `$this->getPlatform()?->getInvalidDateString($column->getType())`:
  - `ColumnAccessorBuilderTrait.php:54` (temporal accessor body)
  - `ObjectBuilder.php:277` (default-value parser)
  - `ObjectBuilder.php:1374` (hydrate body)
- New SPI method added to `PlatformInterface::374`, default `null` in `DefaultPlatform.php:1636`, MySQL-specific in `MysqlPlatform.php:1127`. Clean dependency-inversion: builder no longer name-checks platforms.

#### L1-F4 — Mixed string-emit + template emission produces homogeneous output
**Severity: NICE (verified-clean)**
- `tests/snapshots/bookstore-golden/Propel/Tests/Bookstore/Base/Book.php` sample:
  - L3: `declare(strict_types=1);` ✓ (B.2.1)
  - L92: `protected array $modifiedColumns = [];` — typed property ✓ (B.2.5 templates)
  - L106: `protected ?int $id = null;` — typed property ✓ (B.2.3 string-emit)
  - L638: `public function setId($v): self` ✓ (B.2.6 string-emit)
  - L1971: `public function setPublisher(?ChildPublisher $v = null): self` ✓ (B.2.7)
  - L1999: `public function getPublisher(?ConnectionInterface $con = null): ?ChildPublisher` ✓ (B.2.7)
  - L487 (template-emitted): `public function setVirtualColumn(string $name, mixed $value): self` ✓ (B.2.8)
- Both emission styles emit the same idiom. The reviewer's preconception that they might drift is empirically wrong here.

---

### Lens 2 — BC & migration realism

#### L2-F1 — Tier 1 signature snapshots untouched
**Severity: NICE (verified-clean)**
- `git diff --stat b82269742..HEAD -- 'tests/snapshots/Propel_Runtime_*.signatures.json'` returns empty. Confirmed via independent re-run.
- Same for `tests/snapshots/tracked-classes.txt`.

#### L2-F2 — Generated `: self` / `: ?ClassName` / `: static` per umbrella §3.5 verified
**Severity: NICE (verified-clean)**
- Per-column setter (`setId`, `setTitle`, ...) → `: self` ✓
- FK setter (`setPublisher`) → `: self` ✓
- FK getter (`getPublisher`) → `: ?ChildPublisher` ✓
- `Query::create()` → `: static` ✓ (B.4.9 documented exception — static factory, LSP doesn't apply same way; B.4.9 commit message explicitly calls this out)

#### L2-F3 — Backed enum BC posture
**Severity: NICE (verified-clean) + 1 NICE-FIX nit**
- Sample regenerated: `tests/Fixtures/bookstore/build/classes/Propel/Tests/Bookstore/Book2Style.php`. Class lives at the user-stub namespace (peer of stub `Book2`), NOT at `App\Model\Enum\BookStatus` or `App\Model\Book\Status`. Decision is implemented in `EnumBuilder::getNamespace()` (returns `$this->getTable()->getNamespace()` — same as user stub).
- Naming: `<TablePhpName><ColumnPhpName>` → `Book2` + `Style` = `Book2Style`. Rule lives in `EnumBuilder::buildEnumClassName()`. **Decision is documented in code (`EnumBuilder.php:30`), but not in the spec.** The user-namespace conflict risk (consumer already has `App\Model\BookStatus` as a hand-written class) is unaddressed. **NICE-FIX:** add a `--no-enum-builder` schema attribute (or column-level) to opt out, and document the namespace-collision risk in `MIGRATION-FROM-PRE-AI.md`. Tracked as deferred.
- The setter-union `<EnumClass>|\BackedEnum|string|null` is wider than the plan's `<EnumClass>|string|null`. Lens 6 explores; verdict is NICE-FIX (functional, type-loose).

#### L2-F4 — Migration guide accuracy: `: self` LSP doc honesty triple-aligned
**Severity: NICE (verified-clean)**
- `docs/plans/2026-05-06-modernization-umbrella-spec.md:240` "Honest BC caveat — both options break overrides without a return type" ✓
- `docs/MIGRATION-FROM-PRE-AI.md:188-204` (": self return type on generated setters") includes before/after, fatal-error reproduction text, grep pattern, Rector-rule pointer ✓
- `CHANGELOG.md:48` "Generated setter return type — LSP requirement on subclass overrides" with cross-reference to MIGRATION-FROM-PRE-AI.md ✓
- Three docs are mutually consistent. Round 1 cycle 1 doc-honesty fix landed correctly.

#### L2-F5 — `__sleep` → `__serialize` BC note is concrete with code
**Severity: NICE (verified-clean)**
- `docs/MIGRATION-FROM-PRE-AI.md:267-312` documents the wire-format break, lists impact (sessions / PSR-6,16 caches / message queues / filesystem), provides invalidate-at-deploy cookbook with concrete commands (Symfony `cache:clear`, file `var/sessions` purge, `redis-cli FLUSHDB`), and a one-time read-old-write-new code recipe. CHANGELOG cross-references this. Concrete, not hand-waving.

#### L2-F6 — `PropelTypes::*_NATIVE_TYPE` drift covers all 5 constants
**Severity: NICE (verified-clean)**
- `docs/MIGRATION-FROM-PRE-AI.md:127-148` table covers REAL_NATIVE_TYPE, FLOAT_NATIVE_TYPE, DOUBLE_NATIVE_TYPE, BOOLEAN_NATIVE_TYPE, BOOLEAN_EMU_NATIVE_TYPE — all 5 ✓.
- Includes a before/after grep for sites that compare against `'double'` / `'boolean'` literal strings.

---

### Lens 3 — Quality & rigor

#### L3-F1 — Baselines unchanged or shrunk
**Severity: NICE (verified-clean)**
- phpstan-baseline.neon: 403 lines (Phase A end) → 403 lines (Phase B end). Unchanged. ✓
- psalm-baseline.xml: 1621 lines (Phase A end) → **1616** lines (Phase B end). Decreased by 5 (B.4.1 dead-code removal: `addGetPrimaryKeyNoPK`, `addDoInsertBodyStandard`, `addDoInsertBodyWithIdMethod` UnusedMethod entries cleaned up). ✓ MONOTONIC PROPERTY HONORED.

#### L3-F2 — Generated tree NOT lint-parity-clean (Round 1 finding restated)
**Severity: SHOULD-FIX (waiver candidate)**
- `vendor/bin/phpcs --standard=phpcs.xml -n tests/Fixtures/bookstore/build/classes/Propel/Tests/Bookstore/Base/Book.php` → 1818 fixable violations on a single file (Yoda comparisons, brace placement, long lines, etc.).
- `vendor/bin/phpstan analyze --level=7 …/Book.php --no-progress` → **479 errors** on a single file.
- `phpcs.xml` does not scan `tests/`, and `phpstan.neon` paths-block does not include the generated tree, so the gate doesn't actually run. Per Phase B amendment §2.3: "regenerated tree must pass phpcs/phpstan." Currently does NOT pass. The amendment is aspirational; the CI `lint-generated` job referenced does not exist.
- **Action:** either close the amendment (formally waive lint-parity until Phase D refactors generators) or burn down generated-code lint debt. Recommendation: **waive** with documented reasoning in `docs/reviews/B-waivers.md` (similar to A-waivers) and reschedule for Phase D where builder internals become a target.

#### L3-F3 — Per-task golden-tree atomicity (sampled)
**Severity: NICE (verified-clean for 4/5 sampled)**
- B.2.1 (`9eb887d78`): `declare(strict_types=1);` template-source + golden bookstore tree + tools/regen-golden.php co-staged. ✓
- B.2.4 (`d7150e701`): src/Builder/Om/{ForeignKeyBuilderTrait,ReferrerBuilderTrait}.php + 49 golden files in same commit. ✓
- B.2.6 (`dd64dbd4b`): src + 65 golden files in same commit. ✓
- B.4.3 (`e14099b7f`): builder-internal change only — no golden diff needed. ✓
- **B.4.9 (`1d4e0fca1`): SOURCE + GOLDEN-TREE STALENESS — see L3-F4 below.**

#### L3-F4 — **MUST-FIX: Bookstore golden tree is STALE post-B.4.9**
**Severity: MUST-FIX**
- `bash tests/bin/setup.sqlite.sh && php tools/regen-golden.php && git diff tests/snapshots/bookstore-golden/` produces a NON-EMPTY diff at HEAD.
- Six query files for single-table-inheritance subclasses are in the committed golden with `: Criteria` return on `create()` instead of the now-correct `: static`:
  - `tests/snapshots/bookstore-golden/Propel/Tests/Bookstore/Base/BookstoreCashierQuery.php`
  - `…/Base/BookstoreHeadQuery.php`
  - `…/Base/BookstoreManagerQuery.php`
  - `…/Base/DistributionOnlineQuery.php`
  - `…/Base/DistributionStoreQuery.php`
  - `…/Base/DistributionVirtualStoreQuery.php`
- B.4.9 (commit `1d4e0fca1`) updated `QueryBuilder::addFactoryOpen()` AND `QueryInheritanceBuilder::addCreate()` to emit `: static`, but the regeneration step in that commit only captured `Query` (top-level) golden files; it MISSED the `QueryInheritance`-emitted classes for the 6 STI subclasses listed.
- This violates umbrella §4.7 ("golden-file regression for generator") and the Phase B amendment §2.2 ("golden-file regeneration is a per-task deliverable").
- **Remediation:** re-run `php tools/regen-golden.php` and commit the 6-file golden refresh as a fix-up commit before merge. Verified locally that the regenerator produces the correct `: static` for these 6 files (output checked against `tests/Fixtures/bookstore/build/classes/Propel/Tests/Bookstore/Base/BookstoreCashierQuery.php` after `bash tests/bin/setup.sqlite.sh`).

#### L3-F5 — Test coverage gaps
**Severity: SHOULD-FIX**
- `EnumBuilderTest.php` covers: empty value set, numeric values, special chars, duplicate sanitised, camelCase, escaped quotes ✓ (8 cases). **Missing:** PHP-reserved-word values like `'list'`, `'class'`, `'match'`, `'enum'`, `'true'`, `'false'`, `'null'` — PHP 8.1+ allows these as enum case names (semi-reserved), but a defensive test would catch it if a future PHP version tightens. NICE-FIX.
- `EnumBackedEnumIntegrationTest.php` covers: enum instance, bare-string BC, null-getter, cross-namespace BackedEnum ✓ (4 cases). **Missing:** explicit `filterByX(BackedEnum::CASE)` coercion test on the regenerated `Book2Query`. The coercion code is emitted in B.3.4 (`b72754f9a`) but the only filterBy*() coverage with enums in the existing test suite uses `string` args. SHOULD-FIX — add a single `filterByStyle(Book2Style::NOVEL)->find()` assertion.
- `BookQuery::create(): static` LSP — no test asserts `BookQuery::create()` returns `instanceof BookQuery`. The 79 callsites use it for chaining (`BookQuery::create()->find...`) which exercises the binding implicitly, but no assertion pins the static-bound type. NICE-FIX (could be a `@phpstan-assert` doctest later).

#### L3-F6 — Bookstore regeneration determinism (post-fix)
**Severity: contingent on L3-F4 fix**
- After remediating L3-F4, re-run determinism: `bash setup.sqlite.sh && php regen-golden.php && diff -q` → exit 0. Verified manually pre-stash.

---

### Lens 4 — Performance

#### L4-F1 — Perf benchmark within ±5% gate
**Severity: NICE (verified-clean)**
- `criteria_build_filter_chain_1k`: 39.82 ms (Phase A end) → 40.13 ms (Phase B end). **Delta: +0.77%.** Within ±5% acceptance. See `docs/reviews/B-bench.md`.
- Caveat: this is the ONLY bench. Generated-AR-shape (typed properties, hydrate path) is NOT benchmarked. Phase B's actual touch-surface is invisible to the gate. **SHOULD-FIX (Phase A-debt):** Phase A was supposed to set per-§4.10 numerical targets; only one bench was wired. Recommend: add a `HydrateBench` (10k or 100k rows) before Phase C declares perf-clean.

#### L4-F2 — Match expressions vs switch
**Severity: NICE**
- B.2.10 (`ce18cce20`) converted builder switches to match. Builder runs at codegen time; not on hot path. Pure code-quality, not performance-relevant. As reviewer expected.

---

### Lens 5 — Ambition & capability

#### L5-F1 — Plan vs delivery: complete
**Severity: NICE**
- 12 (Phase 1) + 10 (Phase 2) + 5 (Phase 3) + 9 (Phase 4 active) = 36 tasks substantively addressed. Phase 4.10 (final verification) replaced by umbrella §4.9 DoD per amendment. **36/37 explicit + 1 transformed = 100%.**
- All 22 Phase B-execution commits present, atomic, conventional-commit-clean.

#### L5-F2 — Capability gaps vs Doctrine 3 / Cycle ORM
**Severity: NICE (Phase G-deferred)**
- Typed properties + return types — at-parity with modern ORMs at the AR-shape level. ✓
- **No lazy-object generation** — deferred to Phase G (PHP 8.4 native lazy objects). Acknowledged.
- **No asymmetric-visibility on properties** — deferred to Phase G (PHP 8.4). Acknowledged.
- **Native enum-typed setter SIGNATURE:** the mutator's `<EnumClass>|\BackedEnum|string|null` union accepts string for BC. Strict-enum-typed setter (drop `|string`) is a Phase G stretch. Note as deferred.
- **JSON columns still emit `?string`** — no shape-DSL or value-object generation. Phase C stretch per umbrella §1.3 OOS list. Acknowledged.

---

### Lens 6 — Code generator specialist

#### L6-F1 — EnumBuilder edge cases
**Severity: NICE**
- Empty value set: `EnumBuilder::build()` throws `EngineException`, asserted in `testEmptyValueSetThrows`. ✓
- Special chars: `testSpecialCharactersAreReplacedWithUnderscores` covers space/dash/slash. ✓
- Numeric-only values: `testNumericValuesAreSanitisedToValidIdentifiers` asserts `CASE_<index>` fallback. ✓
- Duplicate-after-sanitisation: `testDuplicateSanitisedNamesUseSafeFallback` covers. ✓
- Reserved-word values: NOT tested (e.g. `'list'`, `'class'`). PHP 8.1+ tolerates these as case names (semi-reserved), but a future PHP version could tighten. **NICE-FIX defensive test.**
- Unicode values: NOT tested. Sanitiser strips non-ASCII via `[^A-Za-z0-9_]+` regex → likely ends up as `CASE_<index>`. Functional but undocumented behavior.

#### L6-F2 — EnumBuilder namespace handling
**Severity: NICE**
- `EnumBuilder::getNamespace()` returns `$this->getTable()->getNamespace()` — peer of user stub. So for `App\Model\Book` the enum lands at `App\Model\BookStatus`. **Documented only in EnumBuilder docblock (L30), not in user-facing docs.** Should appear in `MIGRATION-FROM-PRE-AI.md` or an in-progress Phase B "what's new" section. NICE-FIX.

#### L6-F3 — EnumBuilder + custom `<class name="MyBook">` overriding
**Severity: SHOULD-FIX (untested edge case)**
- Schema's `<table phpName="MyBook">` would set `Table::$phpName = 'MyBook'`; `EnumBuilder::buildEnumClassName('MyBook', 'Status')` would produce `MyBookStatus`. Verified by reading `EnumBuilder::getUnprefixedClassName()`.
- BUT — what about `<column phpName="OverriddenColumn">` overriding the column phpName? Same path via `Column::getPhpName()` — should compose. **Not unit-tested.**
- Behavior-emitted sibling enums: a Versionable-mirrored table's ENUM column gets its own enum class (mirror table has its own phpName). This is what triggers the wider `\BackedEnum` mutator union. Confirmed by `b72754f9a` commit message + `MutatorTestProbeEnum` test fixture.

#### L6-F4 — Mutator union widening to `\BackedEnum`
**Severity: NICE (functional) / SHOULD-FIX (precision)**
- Setter signature: `ChildBook2Style|\BackedEnum|string|null` (verified at `tests/Fixtures/bookstore/build/classes/Propel/Tests/Bookstore/Base/Book2.php` setter for `setStyle`).
- `\BackedEnum` is wider than necessary — it accepts ANY backed enum from anywhere, not just sibling/mirror enums. Functional safety holds (the mutator coerces via `->value` then checks `valueSet`), so an unrelated enum's value will still be rejected by the in-array check.
- **Precision concern:** consumer IDE's autocomplete shows `\BackedEnum` and may surface unrelated enums as candidates. The union could be narrowed to `<EnumClass>|<MirrorEnumClass>|string|null` if the builder discovers sibling tables (Versionable/Translatable/etc. behaviors). Phase C/D builder refactor candidate.
- For now: acceptable as ENGINEERING TRADEOFF. Type loosey, runtime-tight.

---

## 2 — Definition-of-Done checklist (umbrella §4.9, 15 boxes)

| # | Box | Status | Note |
|---|---|---|---|
| 1 | All test matrix cells green: PHP {8.3, 8.4} × DB {agnostic, mysql, pgsql, sqlite} × Symfony {7.2, 7.latest} = 16 cells. | ☐ PARTIAL | Locally: agnostic GREEN (2425 / 5196 / 21). Mysql / pgsql tests not run in this review (no DB); CI must verify. PHP 8.4.14 verified locally; PHP 8.3 not run locally. |
| 2 | Baselines decreased by phase target. | ☑ PASS | psalm 1621→1616 (-5). phpstan 403→403 (target was "no growth"; held). |
| 3 | Coverage delta ≥ 0%; coverage floor met. | ☐ NOT MEASURED | Coverage gate not run in this review. Phase A established floor; Phase B added 13 new tests (8 EnumBuilderTest + 5 EnumBackedEnumIntegrationTest) + 5 typed-property tests in cycle 1. Net direction is +. |
| 4 | Mutation score ≥ threshold on touched files. | ☐ NOT MEASURED | Infection not run in this review. Round 3 canary should run. |
| 5 | Deptrac green; no new layer violations. | ☑ PASS | 0 errors / 175 allowed / 0 warnings. |
| 6 | Performance benchmarks within ±5% of pre-phase. | ☑ PASS | +0.77% on the only bench. See `B-bench.md`. Caveat: hydrate-path NOT benched (Phase A debt). |
| 7 | CHANGELOG updated. | ☑ PASS | New "BC Breaks (Phase B)" section landed in cycle 1. |
| 8 | Deprecation message audit clean (no new self-triggered deprecations). | ☑ PASS | `tests/deprecations.allowlist.json` = `[]`. Round 1 verified. |
| 9 | Generated-code lint parity green. | ☐ FAIL | phpcs: 1818 fixable per file. phpstan-7: 479 errors per file. The CI `lint-generated` job per the amendment does not exist; gate is not enforced. **Recommend formal waiver in `B-waivers.md`.** |
| 10 | Golden-file diff reviewed. | ☐ FAIL | **Golden tree is stale at HEAD** — see L3-F4 MUST-FIX. 6 STI subclass query files have outdated `: Criteria` return on `create()`. |
| 11 | Phase plan updated with retrospective notes. | ☑ PASS | `docs/reviews/B-iterations.md` documents Round 1 cycle 1 (4 findings closed). Phase B amendments doc is the canonical phase plan. |
| 12 | Review rounds completed per §4.13; reports committed under `docs/reviews/`. | ☑ PASS (after this commit) | Round 1: `B-round-1-summary.md`. Round 2: this file. Round 3 canary: pending post-merge. |
| 13 | Surgical-test battery executed; reports committed. | ☐ PARTIAL | Existing battery wired in Phase A; Phase B added enum-specific tests. Mutation battery + chaos battery not separately run for Phase B. |
| 14 | All MUST-FIX closed; SHOULD-FIX closed or waived in `<phase>-waivers.md`. | ☐ FAIL | 1 open MUST-FIX (L3-F4 golden staleness). 4 open SHOULD-FIX (L3-F2 lint parity, L3-F5 enum filter test gap, L4-F1 hydrate bench gap, L6-F3 untested phpName-override edge). No `B-waivers.md` exists yet. |
| 15 | Iteration cycles consumed within budget. | ☑ PASS | Round 1: 1 cycle of 3-budget consumed for doc-honesty + typed-property test sweep. Round 2: 0 consumed (this review). |

**Score:** **8 / 15 PASS**, **2 / 15 PARTIAL**, **5 / 15 FAIL or NOT MEASURED**.

**Score for "passable-with-discipline" interpretation** (P = pass + partial = 10/15): **10 / 15.**

---

## 3 — Verdict: **BLOCK** until L3-F4 (MUST-FIX) closes

**Rationale:** the bookstore golden tree at HEAD does not match what `tools/regen-golden.php` produces from the current builder code. This is a direct violation of umbrella §4.7 (golden-file regression for generator) and the Phase B amendment §2.2 (golden-file regeneration as per-task deliverable). The MUST-FIX is small (re-run + 6-file commit), but the gate is unconditional.

**Once L3-F4 closes:** verdict promotes to **PASS-WITH-WAIVERS**. The 4 SHOULD-FIX findings should be addressed pre-merge or formally waived in a new `docs/reviews/B-waivers.md`:
- L3-F2 (lint parity): waive until Phase D — generated-code lint is a generator-architecture problem, not Phase B.
- L3-F5 (enum filter test gap): trivial to add — should fix.
- L4-F1 (hydrate bench gap): waive (Phase A debt, not Phase B-introduced).
- L6-F3 (phpName-override edge): defensive test — should fix.
- L6-F1 (reserved-word enum value test): NICE-FIX — defer.

---

## 4 — Per-task plan-vs-actual

All 22 active Phase B tasks (B.2.1–B.2.10, B.3.1–B.3.5, B.4.1–B.4.9) and the Round 1 cycle 1 doc-and-test sweep:

| Task | Commit | Status | Notes |
|---|---|---|---|
| **B.2.1** declare(strict_types=1) generated | `9eb887d78` (pre-range, in A.30 closure) | DONE | Reviewed in Round 1. |
| **B.2.2** PropelTypes legacy aliases / canonical names | `aed19d243` | DONE-WITH-DEVIATION | Constant value drift documented in cycle 1 (L2-F5). |
| **B.2.3** Typed properties: column attributes | `d3b3b9ac3` | DONE | |
| **B.2.4** Typed properties: FK + referrer | `d7150e701` | DONE | |
| **B.2.5** Typed properties: base object attributes (template) | `1be33055c` | DONE | |
| **B.2.6** `: self` on column setters | `dd64dbd4b` | DONE-WITH-DEVIATION | LSP doc honesty correction landed in cycle 1; original spec was wrong about subclass overrides. |
| **B.2.7** Return types on FK getters/setters | `4668682a6` | DONE | |
| **B.2.8** Return types on baseObjectMethods (template) | `169e8273b` | DONE-WITH-DEVIATION | `__sleep` → `__serialize`/`__unserialize` is a wire-format BC break; cycle 1 added concrete migration doc. |
| **B.2.9** strftime() docblock fix | `c19644622` | DONE | |
| **B.2.10** match expressions in builder | `ce18cce20` | DONE | |
| Round 1 mid-phase review | `B-round-1-summary.md` | DONE | 1 MUST-FIX + 3 SHOULD-FIX closed in cycle 1. |
| Round 1 cycle 1 fix sweep | `d5b08e677` + `595ac5733` | DONE | 4 doc/test fixes. |
| **B.3.1** EnumBuilder class | `2e5be1284` | DONE | |
| **B.3.2** Register EnumBuilder in pipeline | `2e5be1284` (combined) | DONE | Single commit per amendment §3 sequencing. |
| **B.3.3** ENUM accessor returns backed enum | `b72754f9a` | DONE | |
| **B.3.4** ENUM mutator accepts BackedEnum | `b72754f9a` (combined) | DONE-WITH-DEVIATION | Union widened to `\BackedEnum` (vs. plan's narrower `<EnumClass>|string|null`); justified by Versionable mirror tables. See L6-F4. |
| **B.3.5** Hydrate / persistence for enum | `b72754f9a` (combined) | DONE | |
| **B.4.1** Remove dead code | `da7b28e96` | DONE | |
| **B.4.2** Java-era ClassTools cleanup + PHP 8.x reserved words | `5ff075b31` | DONE | |
| **B.4.3** declareClasses() variadic | `e14099b7f` | DONE | |
| **B.4.4** Extract phpDoc generation to AbstractOMBuilder | `6c8b4affa` | DONE | |
| **B.4.5** MySQL date handling on Platform | `e6a5fdf9a` | DONE | |
| **B.4.6** InterfaceBuilder + tableMapBuilder casing | `fd8eff88d` | DONE | |
| **B.4.7** Loose → strict comparisons | `277124d7c` | DONE | |
| **B.4.8** Drop commented-out code from generated FK getter | `160acf214` | DONE | |
| **B.4.9** create() → `: static` | `1d4e0fca1` | **DONE-WITH-DEVIATION** | **Golden tree NOT fully regenerated — 6 STI subclass query files stale.** See L3-F4 MUST-FIX. |
| **B.4.10** Final verification | replaced by umbrella §4.9 DoD | TRANSFORMED | 8/15 PASS + 2/15 PARTIAL boxes. |

---

## 5 — Summary

**MUST-FIX:** 1 (L3-F4 — golden tree staleness, 6 files).
**SHOULD-FIX:** 4 (L3-F2 lint parity waiver, L3-F5 enum filter test gap, L4-F1 hydrate bench gap, L6-F3 untested phpName-override).
**NICE / NICE-FIX:** ~12 (LSP doc clean ✓, Tier 1 untouched ✓, baseline shrink ✓, mostly verified-clean across L1).

**DoD:** 8 PASS / 2 PARTIAL / 5 FAIL or NOT-MEASURED out of 15.

**Verdict:** **BLOCK pending L3-F4 closure.** Promotes to **PASS-WITH-WAIVERS** when the golden tree is re-regenerated for the 6 STI subclass query files and a `B-waivers.md` documents the SHOULD-FIX disposition.

---

**Reviewer signature:** consolidated single-pass (Lens 1 architecture, Lens 2 BC, Lens 3 quality, Lens 4 perf, Lens 5 ambition, Lens 6 code-generator specialist) per umbrella §4.13.3 token-efficiency variant.
