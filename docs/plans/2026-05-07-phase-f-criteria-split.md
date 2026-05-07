# Phase F: Criteria / ActiveQuery Split + Operator Enums + Typed Criterion DSL Stretch

> **For agentic workers:** REQUIRED SUB-SKILL: Use `superpowers:subagent-driven-development` (recommended for F.1, F.3, F.6, F.7) or `superpowers:executing-plans` (recommended for F.2 + F.4 + F.5 + F.8 — refactor / parser-correctness clusters). Steps use checkbox (`- [ ]`) syntax for tracking.

**Date:** 2026-05-07
**Branch:** `ar-rewrite` (commit; do NOT push)
**Goal:** Implement umbrella §2.2 in full. Split `Runtime/ActiveQuery/Criteria.php` (2526 LOC, 9 responsibilities) into a thin Tier-1 facade (~600 LOC) backed by three internal namespaces — `Compiler/` (tokenizer-based name resolver + the cross-phase `PreparedStatementKey` SPI carried over from Phase E.4), `Plan/` (extracted `JoinPlan`, `WhereTree`, `OrderClause`), and `Operator/` (four backed-string enums alongside the ~38 frozen `Criteria::*` constants). Land the umbrella §6.1 + §6.2 bug-fixes targeted at Phase F: rewrite the hand-rolled `replaceNames` SQL parser at `Criteria.php:2174–2230` as a proper tokenizer; add `trigger_deprecation` to the eight Java-Hashtable `Criteria` methods (`put`, `putAll`, `get`, `keys`, `containsKey`, `keyContainsValue`, `size`, `equals`); introduce a parameterized alternative to the raw-SQL `Criteria::CUSTOM` injection vector and deprecate the raw form. Stretch: opt-in per-column typed Criterion DSL generated when `<table generate-typed-criterion="true">` is set in schema — default OFF in 3.x.

**Architecture:** Three concentric pieces.

1. **Tier 1 facade.** `ActiveQuery/Criteria.php` becomes the public surface only — every consumer-facing method (the ~140 public methods in today's class plus the ~38 `const`s in §3.1) keeps its exact signature. The body of every method that touches a join graph, a WHERE tree, an ORDER clause, or compiles names-in-SQL is delegated into the new `Plan/` and `Compiler/` namespaces. Target: ~600 LOC, mostly thin forwarders + the public constants.

2. **Internal compiler (Tier 3, with one Tier 2 SPI).** `ActiveQuery/Compiler/NameResolver.php` is a proper SQL-aware tokenizer (state machine with explicit token types: `IDENT`, `STRING`, `NUMBER`, `WHITESPACE`, `OPERATOR`, `COMMENT`, `BACKTICK_IDENT`) that replaces `Criteria::replaceNames`'s hand-rolled char-by-char scan + nested `preg_replace_callback` (`Criteria.php:2174–2230`). The PBT in F.2 enforces token-equivalence vs the legacy implementation on a 10k-input corpus — same input, byte-identical output. `ActiveQuery/Compiler/PreparedStatementKey.php` adopts the cache-key shape Phase E.4 settled on (`CachingConnection::buildCacheKey()`); both consumers (E's `CachingConnection` and F's `Plan/*` if any caches plans) call into the same SPI. Tier-2 SPI per the umbrella §2.2 promise.

3. **Plan + Operator extraction.** `Plan/JoinPlan.php`, `Plan/WhereTree.php`, `Plan/OrderClause.php` are pure value objects + tree-builder logic extracted from `Criteria` (today's `joins`, `aliases`, `criterion`, `orderByColumns` machinery). `Operator/Comparison.php`, `Operator/JoinType.php`, `Operator/SortOrder.php`, `Operator/LogicalOperator.php` are backed-string enums whose `value` matches the corresponding `Criteria::*` constant exactly — `Comparison::Equal->value === Criteria::EQUAL` is a contract test (umbrella §4.11 PBT row). The enums are Tier 2 (consumer-visible additive surface); they NEVER replace the constants.

The stretch group F.8 (typed Criterion DSL) is the only **new capability** in Phase F. Every other group is a structural split + bug-fix + Tier-2 axis introduction.

**Tech Stack:** PHP 8.3, MySQL 8.0+, MariaDB 10.5+, PostgreSQL 14+, SQLite (frozen), PHPUnit 11, PHPStan level 7, Spryker code-sniffer, Infection ^0.29 (MSI ≥75 on touched files per umbrella §4.4 — Phase F threshold), Deptrac ^2, innmind/black-box ^6 for PBT, a small fuzz corpus generator for the `replaceNames` rewrite.

**Reference spec:** `docs/plans/2026-05-06-modernization-umbrella-spec.md` (umbrella §2.2 target architecture, §3.1 Tier 1 frozen Criteria constants, §3.2 Tier 2 Criterion extension surface, §4.4 mutation MSI ≥75, §4.10 perf targets, §4.13.3 HIGH-RISK 3-round cadence, §5 Phase F row, §6.1 Java-Hashtable kill-list, §6.2 risk #2 CUSTOM raw-SQL injection vector, §6.4 capability additions).
**Predecessors:** `docs/plans/2026-05-06-phase-a-foundations.md`, `docs/plans/2026-02-03-builder-om-modernization.md` + `docs/plans/2026-05-06-phase-b-amendments.md`, `docs/plans/2026-05-06-phase-c-schema-ddl-modernization.md`, `docs/plans/2026-05-07-phase-d-behaviors-codeemitter.md`, `docs/plans/2026-05-07-phase-e-connection-collapse.md`.
**Phase summaries:** `docs/PHASE-A-SUMMARY.md`, `docs/PHASE-B-SUMMARY.md`, `docs/PHASE-C-SUMMARY.md`, `docs/PHASE-D-SUMMARY.md`, `docs/PHASE-E-SUMMARY.md`.

**Review tier (per umbrella §4.13.3):** **HIGH-RISK 3-round cadence**. Round 1 mid-phase after F.4 (enums + NameResolver tokenizer + PreparedStatementKey SPI + first Plan extraction proven). Round 2 end-phase pre-merge after F.7 (full split landed, Java-Hashtable methods deprecated, parameterized CUSTOM alternative shipped). Round 3 post-merge canary 7 days after merge to integration (per umbrella §4.13.3 HIGH-RISK row).

**Specialists (per umbrella §4.13.2):** standing 5 reviewers + **Compiler / parser specialist** (tokenizer correctness, token-equivalence proof, fuzzing-corpus adequacy, edge cases in nested string-quoting and SQL-comment handling, ANSI-standard backtick-vs-double-quote interpretation, regex-callback parity) + **Security reviewer** (parameterized CUSTOM alternative escapes user input correctly; Java-Hashtable deprecation does not silently expose internal state; tokenizer is not a new injection surface; typed Criterion DSL generated class names are namespace-collision safe).

**Test/quality gate state at start of phase (carried from Phase E end per `docs/PHASE-E-SUMMARY.md`):**
- `phpstan-baseline.neon` 403 lines, 0 blanket regex.
- `psalm-baseline.xml` 1621 lines, suppression-clean.
- Deptrac 0 violations against 233-line baseline.
- All `failOn*` flags strict.
- Test suite: 2706 / 13988 / 21 GREEN.
- Tier 1 signature snapshots: 53 stable. `Criteria` snapshot includes the two additive Phase E methods (`forcePrimary`, `allowReplica`).
- `Criteria` constant snapshot (`tests/snapshots/criteria-constants.txt`, Phase A): ~38 constants, all Tier 1 frozen. **Phase F MUST NOT change this snapshot.**
- Bookstore golden tree: 399 files, idempotent.
- Phase E delivered the cache-key shape via `CachingConnection::buildCacheKey()` — Phase F adopts as `PreparedStatementKey` SPI per the inversion documented in `docs/PHASE-E-SUMMARY.md` ("Per Phase E's PreparedStatementKey SPI deferral, Phase F should pick up that contract").

---

## File Structure (created or modified by this phase)

**Created:**

- `src/Propel/Runtime/ActiveQuery/Compiler/NameResolver.php` — tokenizer-based name resolver. Replaces the hand-rolled char-by-char loop at `Criteria.php:2174–2230`. Public methods: `resolve(string $sql, array<string, string> $aliases): string` and `findReplacements(string $sql): array<string, string>`. State machine with token types listed below. Tier 3 (internal); consumed by `Criteria` and `ModelCriteria` only.
- `src/Propel/Runtime/ActiveQuery/Compiler/Tokenizer.php` — the underlying state machine (`tokenize(string $sql): iterable<Token>`) used by `NameResolver`. Token types: `Token\Ident`, `Token\BacktickIdent`, `Token\StringLiteral`, `Token\Number`, `Token\Whitespace`, `Token\Operator`, `Token\LineComment`, `Token\BlockComment`, `Token\Punct`. Tier 3 internal. Documented as the substitute for the leading-keyword routing-classifier heuristic that Phase E left in `ReplicaRoutingConnection` (per `docs/PHASE-E-SUMMARY.md` Risk #8).
- `src/Propel/Runtime/ActiveQuery/Compiler/Token.php` — readonly value object: `string $type, string $value, int $offset`. Tier 3.
- `src/Propel/Runtime/ActiveQuery/Compiler/PreparedStatementKey.php` — Tier 2 SPI. Two static methods: `forSql(string $sql, array $driverOptions = []): string` (the build-key entrypoint) and `equals(string $a, string $b): bool` (defensive comparison). Imports the `ksort` + `serialize` + `sha256` shape Phase E.4 chose. Phase E's `CachingConnection::buildCacheKey()` delegates here at task F.3.2; the inversion documented in `docs/PHASE-E-SUMMARY.md` is closed.
- `src/Propel/Runtime/ActiveQuery/Plan/JoinPlan.php` — extracted from `Criteria::joins` machinery (today: `Criteria.php` properties `$joins`, `$aliases`, `$asColumns`, `$selectModifiers`, `$selectColumns`, plus methods `addJoin`, `addJoinObject`, `getJoins`, `getAliases`, `addAlias`). `JoinPlan` is a mutable value object — methods return `void` or `static`. Tier 3 internal but has a Tier 2 read-side getter set so `ModelCriteria` and `Behavior` extension surface can introspect.
- `src/Propel/Runtime/ActiveQuery/Plan/WhereTree.php` — extracted from `Criteria::criterion` machinery (today: `Criteria.php` `$criterion`, `$having`, `add()`/`addAnd()`/`addOr()`/`addCond()`/`combine()`). `WhereTree` exposes a tree-walker for Tier-2 `Criterion` consumers; the existing 19 `Criterion/*.php` classes are unchanged externally but the tree they live in is moved.
- `src/Propel/Runtime/ActiveQuery/Plan/OrderClause.php` — extracted from `Criteria::orderByColumns` machinery (today: `addAscendingOrderByColumn`, `addDescendingOrderByColumn`, `getOrderByColumns`, `clearOrderByColumns`).
- `src/Propel/Runtime/ActiveQuery/Operator/Comparison.php` — backed string enum. Cases: `Equal = '='`, `NotEqual = '<>'`, `AltNotEqual = '!='`, `GreaterThan = '>'`, `LessThan = '<'`, `GreaterEqual = '>='`, `LessEqual = '<='`, `Like = 'LIKE'`, `NotLike = 'NOT LIKE'`, `ILike = 'ILIKE'`, `NotILike = 'NOT ILIKE'`, `In = 'IN'`, `NotIn = 'NOT IN'`, `IsNull = 'IS NULL'`, `IsNotNull = 'IS NOT NULL'`, `BinaryAnd = '&'`, `BinaryOr = '|'`, `BinaryAll = 'BINARY_ALL'`, `BinaryNone = 'BINARY_NONE'`, `ContainsAll = 'CONTAINS_ALL'`, `ContainsSome = 'CONTAINS_SOME'`, `ContainsNone = 'CONTAINS_NONE'`, `All = 'ALL'`, `Custom = 'CUSTOM'`, `CustomEqual = 'CUSTOM_EQUAL'`, `Raw = 'RAW'`. The `value` of each case MUST equal the corresponding `Criteria::*` string constant — F.1.1 step 2 is the contract test that pins this.
- `src/Propel/Runtime/ActiveQuery/Operator/JoinType.php` — backed string enum. Cases: `Left = 'LEFT JOIN'`, `Right = 'RIGHT JOIN'`, `Inner = 'INNER JOIN'`, `Default = 'JOIN'`. Mirrors `Criteria::LEFT_JOIN`, `RIGHT_JOIN`, `INNER_JOIN`, `JOIN`.
- `src/Propel/Runtime/ActiveQuery/Operator/SortOrder.php` — `Asc = 'ASC'`, `Desc = 'DESC'`. Mirrors `Criteria::ASC`, `Criteria::DESC`.
- `src/Propel/Runtime/ActiveQuery/Operator/LogicalOperator.php` — `And = 'AND'`, `Or = 'OR'`. Mirrors `Criteria::LOGICAL_AND`, `Criteria::LOGICAL_OR`.
- `src/Propel/Runtime/ActiveQuery/Operator/OperatorAcceptor.php` — internal trait used by `Criteria` setters that should accept either an enum case OR the equivalent string constant. Single static helper: `normalizeOperator(string|Comparison $op): string`. Documented in `docs/MIGRATION-FROM-PRE-AI.md` so consumers know either form is permanent (no deprecation).
- `src/Propel/Runtime/ActiveQuery/Criterion/Exception/UnsafeCustomConditionException.php` — Tier 2. Thrown when a consumer calls the new `customCondition()` with a non-parameterized SQL fragment that contains a likely user-input position (heuristic flag, conservative; bypassable via `customCondition($sql, [], allowRawSql: true)` for the genuinely-no-input case). Documented in the migration cookbook.
- `tests/Propel/Tests/Runtime/ActiveQuery/Operator/ComparisonTest.php` — unit tests for enum→constant equivalence.
- `tests/Propel/Tests/Runtime/ActiveQuery/Operator/JoinTypeTest.php` — same.
- `tests/Propel/Tests/Runtime/ActiveQuery/Operator/SortOrderTest.php` — same.
- `tests/Propel/Tests/Runtime/ActiveQuery/Operator/LogicalOperatorTest.php` — same.
- `tests/Propel/Tests/Runtime/ActiveQuery/Compiler/NameResolverTest.php` — unit + edge-case tests for the tokenizer-based resolver (string literals, escaped quotes, line/block comments, backtick-quoted idents, unicode idents, mixed quote styles, dot-separated qualified names, alias prefixes).
- `tests/Propel/Tests/Runtime/ActiveQuery/Compiler/TokenizerTest.php` — unit tests for the underlying state machine. One test method per token type. Edge cases: empty input, whitespace-only, terminated-mid-string, terminated-mid-comment, nested-quote-style.
- `tests/Propel/Tests/Runtime/ActiveQuery/Compiler/PreparedStatementKeyTest.php` — unit tests covering deterministic key generation, `$driverOptions` re-ordering produces the same key, distinct options produce distinct keys.
- `tests/Propel/Tests/Runtime/ActiveQuery/Plan/JoinPlanTest.php` — unit tests for the extracted join machinery.
- `tests/Propel/Tests/Runtime/ActiveQuery/Plan/WhereTreeTest.php` — unit tests for the WHERE-tree walker.
- `tests/Propel/Tests/Runtime/ActiveQuery/Plan/OrderClauseTest.php` — unit tests for ORDER BY accumulation.
- `tests/Propel/Tests/Runtime/ActiveQuery/JavaHashtableDeprecationTest.php` — assert each of the 8 deprecated methods (`put`, `putAll`, `get`, `keys`, `containsKey`, `keyContainsValue`, `size`, `equals`) emits exactly one `trigger_deprecation` per call category and the underlying behavior still works (BC commitment).
- `tests/Propel/Tests/Runtime/ActiveQuery/CustomConditionTest.php` — covers the new parameterized `customCondition()` method and the `UnsafeCustomConditionException` heuristic; covers raw-CUSTOM deprecation emission.
- `tests/PropertyTests/ActiveQuery/NameResolverTokenEquivalenceTest.php` — black-box PBT (innmind/black-box). Generators emit random SQL fragments (mix of `Book.AuthorID`, `b.title = 'foo''bar'`, backslash-escapes, comments, mixed quote styles) and feed into both the legacy `replaceNames` and the new `NameResolver::resolve`. Assertion: byte-identical output. **Commit-only-when-zero-diff** on a 10k-input seeded corpus per umbrella §4.11 + the F-specific fuzzing row (umbrella §4.14).
- `tests/PropertyTests/ActiveQuery/CriterionRoundTripTest.php` — PBT: `Criteria::add(...)` then read back via `WhereTree`'s walker; assert tree topology preserved across compose/decompose. Per umbrella §4.11 Phase F row.
- `tests/Fuzzing/ActiveQuery/NameResolverFuzzCorpus.php` — per umbrella §4.14 fuzzing row. 10k seeded SQL fragments (committed as a checked-in corpus file); each fragment fed through both `replaceNames` (legacy) and the new resolver; differential check committed to `docs/reviews/F-fuzz.md`. The corpus is not random per-CI-run (otherwise non-reproducible) — fixed seed, committed.
- `tests/Propel/Tests/Generator/Behavior/TypedCriterion/TypedCriterionBuilderTest.php` — F.8 stretch: the per-column typed Criterion builder (BookCriterion::title()->equals(...)). Default-off in 3.x; tests opt in via `<table generate-typed-criterion="true">`.
- `tests/Fixtures/typed-criterion-bookstore/schema.xml` — F.8: a fixture that DOES set `generate-typed-criterion="true"`. Generated output committed alongside.
- `tests/Fixtures/typed-criterion-bookstore/build/golden/` — F.8: golden output for the typed-Criterion DSL fixture.
- `docs/CRITERIA-SPLIT.md` — concise architectural reference. Documents: where each former-`Criteria` responsibility lives; the operator-enum-vs-constant interop story; the parameterized-CUSTOM migration; the typed-Criterion DSL opt-in.
- `docs/reviews/F-round-1-*.md`, `F-round-2-*.md`, `F-round-3-*.md`, `F-summary.md`, `F-iterations.md`, `F-waivers.md`, `F-fuzz.md`, `F-mutation.json`.
- `docs/PHASE-F-SUMMARY.md` (written at phase exit, mirroring the others).

**Modified:**

- `src/Propel/Runtime/ActiveQuery/Criteria.php` — slim down from 2526 LOC to ≤650 LOC. Tier 1 method signatures NOT changed; the ~38 constants NOT changed. Body of every `add*`, `combine`, `addJoin`, `addAscendingOrderByColumn`, `replaceNames` method becomes a one-line delegation into `Plan/*` or `Compiler/*`. Eight Java-Hashtable methods (`put`, `putAll`, `get`, `keys`, `containsKey`, `keyContainsValue`, `size`, `equals`) keep their signatures; gain `trigger_deprecation('maturix/propel', '3.0', '...')` exactly once per call category; documented as removed-in-4.0 in `docs/BACKWARD_COMPATIBILITY.md`. New methods added: `customCondition(string $name, string $sql, array $params = [], bool $allowRawSql = false): static` (Tier 1 additive) — the parameterized alternative to `Criteria::add($name, $sql, Criteria::CUSTOM)` raw-SQL.
- `src/Propel/Runtime/ActiveQuery/ModelCriteria.php` — same treatment for the model-aware flavor of methods that today inline join/where/order-by logic (`addJoinConditions`, `getColumnFromName`, `addUsingOperator`, `combine`). Body delegates into `Plan/*`. Tier 1 method signatures (`find`, `findOne`, `filterBy*`, `use*Query`, `where`, `joinWith`, etc.) NOT changed.
- `src/Propel/Runtime/ActiveQuery/Criterion/AbstractCriterion.php` — no change to Tier 2 surface. Internal: switch from string-literal operator (`'='`) to `Comparison::Equal` enum case where the type is a runtime-defaulted comparison; the public `getComparison(): string` getter stays string for BC. Tier 2 contract unchanged.
- `src/Propel/Runtime/ActiveQuery/Criterion/AbstractModelCriterion.php` — same.
- `src/Propel/Runtime/ActiveQuery/Criterion/CustomCriterion.php` — Tier 2 surface preserved. Internal: when constructed from the new `customCondition()` path, captures the `$params` and routes through prepared-statement binding rather than string-interpolating into SQL. Documented in the security-review entry: closes umbrella §6.2 risk #2 ("`Criteria::CUSTOM` raw-SQL injection vector").
- `src/Propel/Runtime/Connection/Internal/CachingConnection.php` — Phase E's `buildCacheKey()` static delegates to the new `Compiler/PreparedStatementKey::forSql()` per F.3. Phase E behavior unchanged at the call-site level.
- `src/Propel/Generator/Builder/Om/QueryBuilder.php` — F.8 stretch only. When a `<table>` element has `generate-typed-criterion="true"`, the builder emits a per-column typed Criterion class alongside the `XxxQuery` class. Default-off; if attribute absent, builder behavior unchanged. Generated output passes the same lint parity gate as hand-written code (umbrella §4.6).
- `src/Propel/Generator/Builder/Om/AbstractOMBuilder.php` — supports the new typed-criterion builder hook.
- `resources/xsd/database.xsd` — additive: new optional `generate-typed-criterion` attribute on `<table>` (boolean, default `false`). Per umbrella §3.7 XSD additivity.
- `tests/snapshots/tracked-classes.txt` — adds `Propel\Runtime\ActiveQuery\Operator\{Comparison,JoinType,SortOrder,LogicalOperator}` (Tier 2). Adds `Propel\Runtime\ActiveQuery\Compiler\PreparedStatementKey` (Tier 2). Adds `Propel\Runtime\ActiveQuery\Compiler\{NameResolver,Tokenizer,Token}` and `Propel\Runtime\ActiveQuery\Plan\{JoinPlan,WhereTree,OrderClause}` (Tier 3 with documented stability commitment to public methods). `Criteria` Tier 1 snapshot refreshed for the additive `customCondition()` method only.
- `tests/snapshots/criteria-constants.txt` — **NO change**. Phase F's enums sit alongside; constants stay frozen.
- `tests/snapshots/Criteria.signatures.json` — refreshed for the additive `customCondition()` method only. Eight Java-Hashtable methods' signatures unchanged (only the body changed to add `trigger_deprecation`).
- `tests/snapshots/ModelCriteria.signatures.json` — refreshed only if `ModelCriteria`'s public surface gets the additive `customCondition()` forwarder; otherwise unchanged.
- `tests/Fixtures/bookstore/build/golden/` — **NO change** in the standard fixture (typed-Criterion is opt-in via the new `generate-typed-criterion` schema attribute, which the standard bookstore fixture does NOT set). A separate `tests/Fixtures/typed-criterion-bookstore/build/golden/` tree is added for the F.8 fixture.
- `tests/agnostic.phpunit.xml` — register `tests/PropertyTests/ActiveQuery/`, `tests/Fuzzing/ActiveQuery/`, and any new `Plan/`/`Compiler/`/`Operator/` test directories if not covered by glob.
- `tests/deprecations.allowlist.json` — refreshed for the eight new `trigger_deprecation` categories from the Java-Hashtable methods + the raw-CUSTOM deprecation emitted when `Criteria::add($name, $sql, Criteria::CUSTOM)` is called without going through `customCondition()`. Per-category emission, not per-call (umbrella §3.4 trigger_deprecation tooling).
- `docs/MIGRATION-FROM-PRE-AI.md` — three new sections: "Operator enums alongside `Criteria::*` constants (3.0)", "Java-Hashtable method deprecation runway (3.0 → 4.0)", "Parameterized `customCondition()` replaces raw `Criteria::CUSTOM` (3.0)". Each has concrete before/after code snippets.
- `docs/UPGRADE-3.0.md` — capability summary: enums, parameterized custom conditions, optional typed Criterion DSL.
- `docs/BACKWARD_COMPATIBILITY.md` — Tier 1 additive: `Criteria::customCondition()`. Tier 1 deprecated-but-callable: the eight Java-Hashtable methods (with explicit "removed at 4.0" runway). Tier 2 additive: the four `Operator/*` enums; `PreparedStatementKey` SPI; `UnsafeCustomConditionException`. Tier 3 with public-method stability: `Plan/*` and `Compiler/{NameResolver,Tokenizer,Token}`.
- `CHANGELOG.md` — Phase F entries under `[Unreleased]`.

**Deleted:**

- None in Phase F. Per umbrella §3.6 alias-don't-delete and §3.2 deprecation-runway disciplines, every consumer-visible method stays callable on the runway. The eight Java-Hashtable methods are tagged for removal in 4.0; all 19 `Criterion/*.php` classes are Tier 2 unchanged. The hand-rolled `replaceNames` body is replaced (not the public method) — `Criteria::replaceNames(string &$sql): bool` keeps its signature; the body becomes `return $this->getNameResolver()->resolve($sql, $this->aliases) ...`.

---

## Group F.1: Operator enums (Tasks F.1.1–F.1.4)

**Verification after each task:** `composer test:agnostic` + the contract test that asserts `Comparison::Equal->value === Criteria::EQUAL` (and analogues) + signature-diff snapshot stable for `Criteria` Tier 1 (no constants added or removed).

Group F.1 is the simplest group and validates the alongside-not-replacing premise. After F.1, four enums exist; none of `Criteria`'s ~38 constants have changed; consumer code can pick either form.

---

### Task F.1.1: Publish `Comparison` enum (Tier 2)

**Files:**
- Create: `src/Propel/Runtime/ActiveQuery/Operator/Comparison.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/Operator/ComparisonTest.php`
- Modify: `tests/snapshots/tracked-classes.txt` (add the new enum to Tier 2 snapshot)
- Modify: `docs/BACKWARD_COMPATIBILITY.md` (Tier 2 entry)

- [ ] **Step 1: Define the enum**

```php
namespace Propel\Runtime\ActiveQuery\Operator;

use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Tier 2 — alongside Criteria::* constants per umbrella §3.1. Constants stay strings forever.
 *
 * @api
 */
enum Comparison: string
{
    case Equal = Criteria::EQUAL;                  // '='
    case NotEqual = Criteria::NOT_EQUAL;           // '<>'
    case AltNotEqual = Criteria::ALT_NOT_EQUAL;    // '!='
    case GreaterThan = Criteria::GREATER_THAN;
    case LessThan = Criteria::LESS_THAN;
    case GreaterEqual = Criteria::GREATER_EQUAL;
    case LessEqual = Criteria::LESS_EQUAL;
    case Like = Criteria::LIKE;
    case NotLike = Criteria::NOT_LIKE;
    case ILike = Criteria::ILIKE;
    case NotILike = Criteria::NOT_ILIKE;
    case In = Criteria::IN;
    case NotIn = Criteria::NOT_IN;
    case IsNull = Criteria::ISNULL;
    case IsNotNull = Criteria::ISNOTNULL;
    case BinaryAnd = Criteria::BINARY_AND;
    case BinaryOr = Criteria::BINARY_OR;
    case BinaryAll = Criteria::BINARY_ALL;
    case BinaryNone = Criteria::BINARY_NONE;
    case ContainsAll = Criteria::CONTAINS_ALL;
    case ContainsSome = Criteria::CONTAINS_SOME;
    case ContainsNone = Criteria::CONTAINS_NONE;
    case All = Criteria::ALL;
    case Custom = Criteria::CUSTOM;
    case CustomEqual = Criteria::CUSTOM_EQUAL;
    case Raw = Criteria::RAW;
}
```

PHP allows referencing class constants in enum case definitions when the constants are themselves compile-time-constant strings, which the `Criteria::*` constants are. This is the load-bearing language detail — if PHP can't resolve, the fallback is to inline the string values and add a contract assertion in the test (Step 2).

- [ ] **Step 2: Contract test — enum value equals constant**

```php
public function testEnumValueEqualsCriteriaConstant(): void
{
    self::assertSame(Criteria::EQUAL, Comparison::Equal->value);
    self::assertSame(Criteria::NOT_EQUAL, Comparison::NotEqual->value);
    // ... one assertion per case
}
```

This test exists FOREVER — it's the contract that every enum case stays in lockstep with its constant. If a future refactor changes either, this test fires.

- [ ] **Step 3: Snapshot + BC commit**

Update `tests/snapshots/tracked-classes.txt` (add `Propel\Runtime\ActiveQuery\Operator\Comparison`); update `docs/BACKWARD_COMPATIBILITY.md` Tier 2 entry. Tier 2 commitment statement: "`Comparison` enum case identifiers are frozen for the 3.x line. Removal of a case requires a deprecation runway. Addition of a case is BC-additive and goes alongside any new `Criteria::*` constant. The `Comparison::*->value` contract with `Criteria::*` is permanent."

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Operator/Comparison.php tests/Propel/Tests/Runtime/ActiveQuery/Operator/ComparisonTest.php tests/snapshots/tracked-classes.txt docs/BACKWARD_COMPATIBILITY.md
git commit -m "feat(runtime/activequery/operator): publish Comparison enum (Tier 2; alongside Criteria::*)"
```

---

### Task F.1.2: Publish `JoinType` enum (Tier 2)

**Files:**
- Create: `src/Propel/Runtime/ActiveQuery/Operator/JoinType.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/Operator/JoinTypeTest.php`
- Modify: `tests/snapshots/tracked-classes.txt`

- [ ] **Step 1: Define the enum**

```php
enum JoinType: string
{
    case Left = Criteria::LEFT_JOIN;
    case Right = Criteria::RIGHT_JOIN;
    case Inner = Criteria::INNER_JOIN;
    case Default = Criteria::JOIN;
}
```

- [ ] **Step 2: Contract test**

Same shape as F.1.1 step 2.

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Operator/JoinType.php tests/Propel/Tests/Runtime/ActiveQuery/Operator/JoinTypeTest.php tests/snapshots/tracked-classes.txt
git commit -m "feat(runtime/activequery/operator): publish JoinType enum (Tier 2)"
```

---

### Task F.1.3: Publish `SortOrder` and `LogicalOperator` enums (Tier 2)

**Files:**
- Create: `src/Propel/Runtime/ActiveQuery/Operator/SortOrder.php`
- Create: `src/Propel/Runtime/ActiveQuery/Operator/LogicalOperator.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/Operator/SortOrderTest.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/Operator/LogicalOperatorTest.php`
- Modify: `tests/snapshots/tracked-classes.txt`

- [ ] **Step 1: Define the enums**

```php
enum SortOrder: string {
    case Asc = Criteria::ASC;
    case Desc = Criteria::DESC;
}

enum LogicalOperator: string {
    case And = Criteria::LOGICAL_AND;
    case Or = Criteria::LOGICAL_OR;
}
```

- [ ] **Step 2: Contract tests**

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Operator/SortOrder.php src/Propel/Runtime/ActiveQuery/Operator/LogicalOperator.php tests/Propel/Tests/Runtime/ActiveQuery/Operator/SortOrderTest.php tests/Propel/Tests/Runtime/ActiveQuery/Operator/LogicalOperatorTest.php tests/snapshots/tracked-classes.txt
git commit -m "feat(runtime/activequery/operator): publish SortOrder + LogicalOperator enums (Tier 2)"
```

---

### Task F.1.4: Operator-acceptor interop trait

**Files:**
- Create: `src/Propel/Runtime/ActiveQuery/Operator/OperatorAcceptor.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/Operator/OperatorAcceptorTest.php`
- Modify: `docs/MIGRATION-FROM-PRE-AI.md` (interop section)

- [ ] **Step 1: Define a static helper**

```php
/**
 * Accepts either a Comparison enum case or the equivalent Criteria::* string.
 * Returns the canonical string form used by the Tier 2 Criterion classes.
 *
 * Interop is permanent — both forms are accepted forever per umbrella §3.1.
 *
 * @api
 */
final class OperatorAcceptor
{
    public static function normalizeComparison(string|Comparison $op): string
    {
        return is_string($op) ? $op : $op->value;
    }
    public static function normalizeJoinType(string|JoinType $op): string { ... }
    public static function normalizeSortOrder(string|SortOrder $op): string { ... }
    public static function normalizeLogicalOperator(string|LogicalOperator $op): string { ... }
}
```

The trait/class is internal; the interop story it implements IS Tier 1 contract: any `Criteria` method that today accepts a `string` operator MUST also accept the equivalent enum case. F.5 wires this in at the per-method level.

- [ ] **Step 2: Tests cover both directions**

Pass an enum case to `Criteria::add(...)`; pass a string constant; assert identical resulting `WhereTree`.

- [ ] **Step 3: Migration doc entry**

`docs/MIGRATION-FROM-PRE-AI.md` "Operator enums alongside `Criteria::*` constants (3.0)". Concrete: pre-Phase-F code passes `Criteria::EQUAL`; post-Phase-F may pass either `Criteria::EQUAL` or `Comparison::Equal`. **Both forms work forever** — the constants will not be deprecated.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Operator/OperatorAcceptor.php tests/Propel/Tests/Runtime/ActiveQuery/Operator/OperatorAcceptorTest.php docs/MIGRATION-FROM-PRE-AI.md
git commit -m "feat(runtime/activequery/operator): OperatorAcceptor interop helper; both forms accepted forever"
```

---

## Group F.2: NameResolver tokenizer (Tasks F.2.1–F.2.5)

**Verification after each task:** unit (each token-type test) + property-based (10k-input token-equivalence vs legacy `replaceNames`) + fuzzing (corpus-driven differential check).

Group F.2 closes umbrella §6.1's #1 Phase F bug fix: the hand-rolled char-by-char SQL parser at `Criteria.php:2174–2230` is fragile, slow (per-char branch + nested `preg_replace_callback`), and security-relevant (the regex callback runs on user-influenced strings via raw `Criteria::add($name, $sql, ...)`). The replacement is a proper state-machine tokenizer.

---

### Task F.2.1: Define `Token` value object + `Tokenizer` state machine

**Files:**
- Create: `src/Propel/Runtime/ActiveQuery/Compiler/Token.php`
- Create: `src/Propel/Runtime/ActiveQuery/Compiler/Tokenizer.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/Compiler/TokenizerTest.php`

- [ ] **Step 1: `Token` shape**

```php
final readonly class Token
{
    public function __construct(
        public string $type,    // 'IDENT' | 'BACKTICK_IDENT' | 'STRING' | 'NUMBER' | 'WS' | 'OP' | 'LCOMMENT' | 'BCOMMENT' | 'PUNCT'
        public string $value,
        public int $offset,     // byte offset in original SQL
    ) {}
}
```

A type-as-string union (not its own enum) keeps the inner loop a single integer-cmp branch — perf rationale.

- [ ] **Step 2: `Tokenizer::tokenize(string $sql): iterable<Token>`**

State machine. States: `START`, `IN_STRING_SINGLE`, `IN_STRING_DOUBLE`, `IN_BACKTICK`, `IN_LINE_COMMENT`, `IN_BLOCK_COMMENT`, `IN_NUMBER`, `IN_IDENT`. Backslash-escape handling inside `IN_STRING_*`. ANSI standard `''` (doubled-single-quote) escape inside single-quoted strings. SQL line comment is `--` to end-of-line; block comment is `/* ... */` (non-nested, per ANSI; MySQL does not support nested block comments).

The state machine is `O(n)` over the SQL length with one branch per char. Compare to today's implementation: `O(n)` outer loop + a `preg_replace_callback("/[\w\\\]+\.\w+/", ...)` per non-string segment, which is `O(n)` regex + a callback per match — significantly slower, and the regex itself does not handle backtick-quoted identifiers, which the new tokenizer does.

- [ ] **Step 3: Tests — one method per token type**

Each test method feeds a hand-crafted minimal input and asserts the emitted token sequence. Edge cases:
- Empty input — empty iterable.
- Whitespace only — single WS token.
- Unterminated string — final token is `STRING` with `value` containing everything from the opening quote to EOF (heal-by-eof, NOT throw — matches legacy behavior since the legacy parser would loop to end-of-string in the same scenario).
- Unterminated block comment — same heal-by-eof.
- Backslash-escaped quote inside string — single STRING token, escape preserved in `value`.
- Doubled-single-quote ANSI escape — single STRING token spanning the doubled quote.
- Mixed quote styles — independent STRING tokens.
- Backtick-quoted ident with embedded space — single BACKTICK_IDENT.
- Line comment to EOL — single LCOMMENT, no spillover into next line's tokens.
- Block comment with internal newline — single BCOMMENT.
- Number formats: `123`, `1.5`, `1e10`, `1.5e-3`, `.5` — each yields one NUMBER.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Compiler/Token.php src/Propel/Runtime/ActiveQuery/Compiler/Tokenizer.php tests/Propel/Tests/Runtime/ActiveQuery/Compiler/TokenizerTest.php
git commit -m "feat(runtime/activequery/compiler): SQL tokenizer state machine (replaces hand-rolled scanner)"
```

---

### Task F.2.2: Implement `NameResolver`

**Files:**
- Create: `src/Propel/Runtime/ActiveQuery/Compiler/NameResolver.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/Compiler/NameResolverTest.php`

- [ ] **Step 1: API shape**

```php
final class NameResolver
{
    /** @param array<string, string> $aliases */
    public function __construct(private readonly Tokenizer $tokenizer = new Tokenizer()) {}

    /**
     * Replaces qualified column names of the form `Class.Column` or `alias.column` in $sql,
     * mirroring the legacy Criteria::replaceNames behavior. Returns the rewritten SQL.
     *
     * @param array<string, string> $aliases  alias-to-real-table-name map (today's $this->aliases)
     * @param callable(string $name): string $resolveName  callback equivalent to today's
     *        Criteria::doReplaceNameInExpression — the per-match replacement function
     */
    public function resolve(string $sql, array $aliases, callable $resolveName): string {}

    /**
     * Returns the array of name replacements found, equivalent to today's
     * Criteria::$replacedColumns property, for telemetry and logging.
     *
     * @return array<int, array{string, string}>  list of [original, replacement]
     */
    public function getReplacements(): array {}
}
```

The legacy `Criteria::replaceNames(string &$sql): bool` keeps its public signature; the body becomes:

```php
public function replaceNames(string &$sql): bool
{
    $resolver = $this->getNameResolver();
    $sql = $resolver->resolve($sql, $this->aliases, [$this, 'doReplaceNameInExpression']);
    $this->replacedColumns = $resolver->getReplacements();
    return count($this->replacedColumns) > 0;
}
```

- [ ] **Step 2: Implementation**

Walk tokens. For each non-STRING, non-COMMENT token of type IDENT (or BACKTICK_IDENT containing a `.`), apply the same regex match the legacy parser uses (`/[\w\\\]+\.\w+/`) and call the user callback. STRING and COMMENT tokens pass through verbatim. This matches the legacy parser's intent: only ident-like chunks outside strings get replacement.

The crucial difference vs the legacy scanner: the new resolver knows what a "quoted" region is via the tokenizer, so it does not need the char-by-char `$isAfterBackslash` / `$isInString` flag dance. Also, BACKTICK_IDENT (`` `book.author_id` ``) is a token type the legacy scanner doesn't natively handle — it currently treats backticks as plain chars. The new resolver treats them as identifier-quoting (MySQL convention) and applies replacement. **This IS a behavior change vs the legacy parser** — call it out in F.2.5 step 1 in the migration doc, and verify in F.2.4 PBT that the corpus does not regress on backtick-free input (which is the vast majority of input).

- [ ] **Step 3: Unit tests**

- Plain qualified name: `book.author_id = ?` → callback invoked once on `book.author_id`.
- Inside string: `'book.author_id'` → callback NOT invoked.
- Inside backtick ident: `` `book.author_id` `` → callback invoked (NEW behavior).
- Inside line comment: `-- book.author_id` → NOT invoked.
- Inside block comment: `/* book.author_id */` → NOT invoked.
- Mixed: `book.author_id = 'book.title' AND author.name LIKE 'foo''bar'` → callback invoked twice (`book.author_id`, `author.name`); NOT on the strings.
- Unicode ident: `book.naïve` (per `\w` = `[A-Za-z0-9_]`, the `ï` does NOT match — same behavior as legacy).
- Empty SQL: returns empty string, callback never invoked.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Compiler/NameResolver.php tests/Propel/Tests/Runtime/ActiveQuery/Compiler/NameResolverTest.php
git commit -m "feat(runtime/activequery/compiler): NameResolver — tokenizer-driven, replaces hand-rolled scan"
```

---

### Task F.2.3: Wire `NameResolver` into `Criteria::replaceNames`

**Files:**
- Modify: `src/Propel/Runtime/ActiveQuery/Criteria.php` (replace lines 2174–2230 with delegation)
- Modify: `tests/Propel/Tests/Runtime/ActiveQuery/CriteriaTest.php` (re-run; should pass unchanged)

- [ ] **Step 1: Replace the body of `replaceNames`**

Body becomes ~6 lines of delegation (per Step 1 of F.2.2). The public signature `public function replaceNames(string &$sql): bool` is unchanged.

- [ ] **Step 2: Re-run existing `CriteriaTest`**

Every existing test against `Criteria::replaceNames` MUST pass without test changes — that is the BC contract. Tests live in `tests/Propel/Tests/Runtime/ActiveQuery/CriteriaTest.php` (Phase A baseline).

- [ ] **Step 3: Signature-diff snapshot**

`Criteria.signatures.json` for `replaceNames` MUST be unchanged (signature stable; only body swapped).

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Criteria.php tests/Propel/Tests/Runtime/ActiveQuery/CriteriaTest.php tests/snapshots/Criteria.signatures.json
git commit -m "refactor(runtime/activequery): Criteria::replaceNames delegates to NameResolver"
```

---

### Task F.2.4: Property-based test — token-equivalence with legacy

**Files:**
- Create: `tests/PropertyTests/ActiveQuery/NameResolverTokenEquivalenceTest.php`
- Modify: `tests/agnostic.phpunit.xml` if needed

- [ ] **Step 1: Generate random SQL fragments**

innmind/black-box generators emit fragments by composing:
- 1–10 qualified names (`Book.AuthorID`, `b.title`, `t1.col1`, etc.).
- 0–5 string literals (single-quoted, double-quoted, with various escape patterns).
- 0–3 line + block comments containing qualified-name-like text.
- 0–3 numeric literals.
- 0–5 SQL operators (`=`, `<`, `<>`, `LIKE`, `AND`, `OR`).
- Optional whitespace between segments.

The grammar is: `fragment = (segment WS?)+`. Each segment is one of the above categories. Composition is randomized; fixed seed for reproducibility.

- [ ] **Step 2: Differential assertion**

For each generated fragment $f$:
1. Capture a snapshot of `(new LegacyCriteriaForTesting())->replaceNames($f)` and the resulting modified `$f`.
2. Run `(new Criteria())->replaceNames($g = $f)` (the new path) and capture the modified `$g`.
3. Assert `$f === $g` byte-for-byte.

`LegacyCriteriaForTesting` is a snapshot of the pre-Phase-F implementation, captured at `tests/PropertyTests/ActiveQuery/Legacy/LegacyReplaceNames.php` — a single static method that runs the old char-by-char scan against test input. It is committed once at F.2.4 and never touched again.

The PBT runs 10,000 generated cases per CI run (umbrella §4.11). Seeded for reproducibility; seed documented in `docs/reviews/F-round-1-summary.md`.

- [ ] **Step 3: Document EXPECTED divergence cases**

The new tokenizer treats backtick-idents as quoted; the legacy parser treats them as plain chars. Generate the corpus EXCLUDING backtick characters for the byte-equivalence assertion. A separate test method runs backtick-containing inputs through both and asserts the new path's behavior is the documented improvement (replacement applied) — not byte-equivalence.

- [ ] **Step 4: Commit**

```bash
git add tests/PropertyTests/ActiveQuery/NameResolverTokenEquivalenceTest.php tests/PropertyTests/ActiveQuery/Legacy/LegacyReplaceNames.php tests/agnostic.phpunit.xml
git commit -m "test(pbt): NameResolver token-equivalence with legacy replaceNames (10k corpus)"
```

---

### Task F.2.5: Fuzzing corpus + commit-only-when-zero-diff gate

**Files:**
- Create: `tests/Fuzzing/ActiveQuery/NameResolverFuzzCorpus.php`
- Create: `tests/Fuzzing/ActiveQuery/corpus/` — 10,000 committed seed inputs
- Create: `docs/reviews/F-fuzz.md` — differential report

- [ ] **Step 1: Generate the seed corpus once**

Run the F.2.4 generators with a fixed seed; commit the 10k inputs as plain-text fixtures under `tests/Fuzzing/ActiveQuery/corpus/`. Each input is one line; the file names are stable hashes for git stability.

- [ ] **Step 2: Differential check on every CI run**

`NameResolverFuzzCorpus::testEveryCorpusInputMatches` iterates the corpus, runs both paths, asserts byte-equivalence. Any non-match emits a diagnostic: input, legacy output, new output, the first byte that differs.

- [ ] **Step 3: Commit-only-when-zero-diff**

The plan's standing instruction is: **the F.2 group does not commit if the corpus differential is non-empty**. The fuzz check is hard — every diff is investigated, the cause classified (real bug in new tokenizer vs documented intentional divergence vs corpus-input invalid). For the documented divergences (backtick handling), a per-line allowlist file `tests/Fuzzing/ActiveQuery/corpus/expected-divergences.json` lists the input hashes + the rationale. Anything else fails CI.

- [ ] **Step 4: Differential report**

`docs/reviews/F-fuzz.md` summarizes: corpus size, divergences found, allowlisted divergences, runtime cost of the differential check.

- [ ] **Step 5: Commit**

```bash
git add tests/Fuzzing/ActiveQuery/ docs/reviews/F-fuzz.md
git commit -m "test(fuzzing): NameResolver corpus + differential check (10k inputs, byte-equivalent or allowlisted)"
```

---

## Group F.3: PreparedStatementKey SPI (Tasks F.3.1–F.3.3)

**Verification after each task:** unit (deterministic key generation; option-order independence; collision resistance) + Phase E `CachingConnection` regression (same cache hits as before).

Group F.3 closes the inversion noted in `docs/PHASE-E-SUMMARY.md`: Phase E settled the cache-key shape (`CachingConnection::buildCacheKey(string $sql, array $driverOptions)`); Phase F adopts it as a published Tier-2 SPI under the `Compiler/` namespace. Both Phase E's `CachingConnection` and any future plan-cache in `Plan/*` use the same SPI.

---

### Task F.3.1: Publish `PreparedStatementKey` SPI

**Files:**
- Create: `src/Propel/Runtime/ActiveQuery/Compiler/PreparedStatementKey.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/Compiler/PreparedStatementKeyTest.php`
- Modify: `tests/snapshots/tracked-classes.txt` (Tier 2 entry)
- Modify: `docs/BACKWARD_COMPATIBILITY.md`

- [ ] **Step 1: Define the SPI**

```php
namespace Propel\Runtime\ActiveQuery\Compiler;

/**
 * Tier 2 SPI per umbrella §2.2. Adopted from Phase E.4's CachingConnection::buildCacheKey shape.
 *
 * Shape: SHA-256 of `$sql . "\x00" . serialize(ksort($driverOptions))`.
 *
 * @api Tier 2 — frozen for the 3.x line. Shape change requires a deprecation runway.
 */
final class PreparedStatementKey
{
    public static function forSql(string $sql, array $driverOptions = []): string {
        $opts = $driverOptions;
        ksort($opts);
        return hash('sha256', $sql . "\x00" . serialize($opts));
    }

    public static function equals(string $a, string $b): bool {
        return hash_equals($a, $b);
    }
}
```

The `equals` method uses `hash_equals` — defensive against timing-based comparison in case a key is ever logged at a granularity that could let an attacker probe. Phase E's security-review entry on cache-key derivation flagged this; F.3 closes.

- [ ] **Step 2: Unit tests**

Cover: deterministic across calls; option-order independent (`['a' => 1, 'b' => 2]` and `['b' => 2, 'a' => 1]` produce identical key); distinct options produce distinct keys; SQL with internal nulls handled (sentinel byte `\x00` is the separator; ensure SQL containing `\x00` is rejected or escaped — implementation rejects with `InvalidArgumentException` since real SQL never contains `\x00`).

- [ ] **Step 3: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Compiler/PreparedStatementKey.php tests/Propel/Tests/Runtime/ActiveQuery/Compiler/PreparedStatementKeyTest.php tests/snapshots/tracked-classes.txt docs/BACKWARD_COMPATIBILITY.md
git commit -m "feat(runtime/activequery/compiler): PreparedStatementKey SPI (Tier 2; adopted from Phase E)"
```

---

### Task F.3.2: Wire Phase E's `CachingConnection` to the new SPI

**Files:**
- Modify: `src/Propel/Runtime/Connection/Internal/CachingConnection.php`
- Modify: `tests/Propel/Tests/Runtime/Connection/Internal/CachingConnectionTest.php` (no behavior change; assert the key shape forwards correctly)

- [ ] **Step 1: Replace the inline `buildCacheKey` body with delegation**

```php
public static function buildCacheKey(string $sql, array $driverOptions = []): string
{
    return PreparedStatementKey::forSql($sql, $driverOptions);
}
```

Phase E behavior unchanged at the cache-hit-rate level — the same SHA-256 of the same shape produces the same keys.

- [ ] **Step 2: Re-run Phase E's `CachingConnectionTest`**

Every existing test in `tests/Propel/Tests/Runtime/Connection/Internal/CachingConnectionTest.php` MUST pass without test changes.

- [ ] **Step 3: Re-run Phase E's PBT**

`tests/PropertyTests/Connection/PreparedStatementLruInvariantTest.php` (Phase E.4.2) MUST pass — the cache-key shape change is a no-op semantically.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/Connection/Internal/CachingConnection.php tests/Propel/Tests/Runtime/Connection/Internal/CachingConnectionTest.php
git commit -m "refactor(runtime/connection): CachingConnection adopts PreparedStatementKey SPI from Phase F"
```

---

### Task F.3.3: Document the SPI adoption

**Files:**
- Modify: `docs/CRITERIA-SPLIT.md` (cross-reference Phase E coordination)
- Modify: `docs/CONNECTION-DECORATORS.md` (Phase E doc — add "PreparedStatementKey SPI" section)
- Modify: `docs/PHASE-E-SUMMARY.md` (closing the inversion noted at Phase E end)

- [ ] **Step 1: `docs/CRITERIA-SPLIT.md` cross-reference**

Section "Compiler/PreparedStatementKey": "Phase E ships the cache-key shape inside `CachingConnection`; Phase F publishes it as a Tier 2 SPI here. Both consumers — Phase E's connection-side cache and any future Phase F plan-cache — call this SPI."

- [ ] **Step 2: `docs/CONNECTION-DECORATORS.md` update**

Add a "PreparedStatementKey SPI" subsection: "The cache key shape is now published as `Propel\Runtime\ActiveQuery\Compiler\PreparedStatementKey`. Third-party decorators that build their own caches should call `PreparedStatementKey::forSql()` rather than implement their own — guarantees coherence with the standard `CachingConnection`."

- [ ] **Step 3: `docs/PHASE-E-SUMMARY.md` close-out**

Append a "Phase F follow-up: PreparedStatementKey SPI adopted" note. The "deviation from umbrella §2.2 sequence" called out at Phase E end is now resolved.

- [ ] **Step 4: Commit**

```bash
git add docs/CRITERIA-SPLIT.md docs/CONNECTION-DECORATORS.md docs/PHASE-E-SUMMARY.md
git commit -m "docs: PreparedStatementKey SPI cross-references; close Phase E inversion"
```

---

## Group F.4: Plan/* extraction (Tasks F.4.1–F.4.4)

**Verification after each task:** unit tests for each extracted class + the Tier 1 `Criteria` signature-diff snapshot is unchanged (only `customCondition` additive in F.7) + every existing `CriteriaTest` test passes without modification.

Group F.4 extracts the join, where, and order-by machinery from `Criteria` into three pure value-object classes under `Plan/`. After F.4, the bulk of `Criteria.php` is delegation forwarders. Round 1 review fires after F.4.

---

### Task F.4.1: Extract `JoinPlan`

**Files:**
- Create: `src/Propel/Runtime/ActiveQuery/Plan/JoinPlan.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/Plan/JoinPlanTest.php`
- Modify: `src/Propel/Runtime/ActiveQuery/Criteria.php` (`addJoin`/`addJoinObject`/`getJoins`/`getAliases`/`addAlias` delegate)

- [ ] **Step 1: API shape**

```php
final class JoinPlan
{
    /** @var array<int, Join> */
    private array $joins = [];
    /** @var array<string, string> */
    private array $aliases = [];
    /** @var array<string, string> */
    private array $asColumns = [];
    /** @var list<string> */
    private array $selectModifiers = [];
    /** @var list<string> */
    private array $selectColumns = [];

    public function addJoin(Join $join): void {}
    public function addJoinFromCriteria(Criteria $c, string $left, string $right, ?string $type): Join {}
    /** @return array<int, Join> */
    public function getJoins(): array { return $this->joins; }
    public function addAlias(string $alias, string $table): void {}
    /** @return array<string, string> */
    public function getAliases(): array { return $this->aliases; }
    // ... and so on for the other methods carved out
}
```

- [ ] **Step 2: Move logic verbatim from `Criteria`**

The join-related methods in today's `Criteria.php` (lines roughly `addJoin`, `addJoinObject`, `getJoins`, `getAliases`, `addAlias` + the `$joins`, `$aliases`, `$asColumns`, `$selectModifiers`, `$selectColumns` properties) move into `JoinPlan`. Verbatim — no refactoring inside the move.

- [ ] **Step 3: `Criteria` delegates**

```php
public function addJoin(...$args) { $this->joinPlan->addJoin(...$args); return $this; }
public function getJoins(): array { return $this->joinPlan->getJoins(); }
// ... etc.
```

The public signature on `Criteria` does not change. The body becomes a one-line forwarder.

- [ ] **Step 4: Unit tests for `JoinPlan`**

Mirror the existing `CriteriaTest` cases that exercise joins. Plus: `JoinPlan` directly instantiable (no need to construct a full `Criteria`).

- [ ] **Step 5: Re-run `CriteriaTest`**

MUST pass with no test changes.

- [ ] **Step 6: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Plan/JoinPlan.php src/Propel/Runtime/ActiveQuery/Criteria.php tests/Propel/Tests/Runtime/ActiveQuery/Plan/JoinPlanTest.php tests/Propel/Tests/Runtime/ActiveQuery/CriteriaTest.php
git commit -m "refactor(runtime/activequery/plan): extract JoinPlan from Criteria"
```

---

### Task F.4.2: Extract `WhereTree`

**Files:**
- Create: `src/Propel/Runtime/ActiveQuery/Plan/WhereTree.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/Plan/WhereTreeTest.php`
- Create: `tests/PropertyTests/ActiveQuery/CriterionRoundTripTest.php`
- Modify: `src/Propel/Runtime/ActiveQuery/Criteria.php` (`add`/`addAnd`/`addOr`/`addCond`/`combine` + `$criterion`, `$having` move)

- [ ] **Step 1: API shape**

```php
final class WhereTree
{
    /** @var array<string, AbstractCriterion> */
    private array $criterion = [];
    private ?Criterion $having = null;
    /** @var array<string, AbstractCriterion> */
    private array $namedCriterions = [];

    public function add(AbstractCriterion $c): void {}
    public function addAnd(AbstractCriterion $c): void {}
    public function addOr(AbstractCriterion $c): void {}
    public function addCond(string $name, AbstractCriterion $c): void {}
    public function combine(array $names, string|LogicalOperator $operator = Comparison::Equal): AbstractCriterion {}
    public function setHaving(AbstractCriterion $c): void {}
    public function getHaving(): ?AbstractCriterion {}
    /** @return array<string, AbstractCriterion> */
    public function getCriterions(): array {}
    /** Tree walker for Tier 2 Criterion consumers. */
    public function walk(callable $visitor): void {}
}
```

- [ ] **Step 2: Move logic from `Criteria` verbatim**

Same pattern as F.4.1.

- [ ] **Step 3: `Criteria` delegates**

`Criteria::add`, `addAnd`, `addOr`, `addCond`, `combine`, `getCriterion`, `getHaving`, etc. all forward to `WhereTree`. Public signatures unchanged.

- [ ] **Step 4: Property-based test — Criterion round-trip**

`CriterionRoundTripTest`: PBT generates a sequence of `add/addAnd/addOr/addCond/combine` operations on a fresh `WhereTree`, then walks the tree and reconstructs the operation set, asserting topology equivalence. Per umbrella §4.11 + §7.2 Phase F validation row.

- [ ] **Step 5: Re-run `CriteriaTest`**

MUST pass without modification.

- [ ] **Step 6: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Plan/WhereTree.php src/Propel/Runtime/ActiveQuery/Criteria.php tests/Propel/Tests/Runtime/ActiveQuery/Plan/WhereTreeTest.php tests/PropertyTests/ActiveQuery/CriterionRoundTripTest.php
git commit -m "refactor(runtime/activequery/plan): extract WhereTree from Criteria + PBT round-trip"
```

---

### Task F.4.3: Extract `OrderClause`

**Files:**
- Create: `src/Propel/Runtime/ActiveQuery/Plan/OrderClause.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/Plan/OrderClauseTest.php`
- Modify: `src/Propel/Runtime/ActiveQuery/Criteria.php` (`addAscendingOrderByColumn`/`addDescendingOrderByColumn`/`getOrderByColumns`/`clearOrderByColumns` delegate)

- [ ] **Step 1: API shape**

```php
final class OrderClause
{
    /** @var list<string> */
    private array $columns = [];

    public function addAscending(string $column): void {}
    public function addDescending(string $column): void {}
    public function add(string $column, string|SortOrder $order): void {}
    /** @return list<string> */
    public function getColumns(): array {}
    public function clear(): void {}
}
```

- [ ] **Step 2: Move logic; delegate from Criteria**

Same pattern.

- [ ] **Step 3: Tests**

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Plan/OrderClause.php src/Propel/Runtime/ActiveQuery/Criteria.php tests/Propel/Tests/Runtime/ActiveQuery/Plan/OrderClauseTest.php
git commit -m "refactor(runtime/activequery/plan): extract OrderClause from Criteria"
```

---

### Task F.4.4: Verify Tier 1 surface unchanged after F.4

**Files:**
- Modify: `tests/snapshots/Criteria.signatures.json` (verify diff is empty pre-F.7)
- Modify: `tests/snapshots/criteria-constants.txt` (verify diff is empty)
- Modify: `tests/snapshots/ModelCriteria.signatures.json` (verify diff is empty)

- [ ] **Step 1: Run signature-diff gate**

```bash
bin/propel internal:dump-signatures > /tmp/post-F4.json
diff tests/snapshots/Criteria.signatures.json /tmp/post-F4.json
# expected: empty (the only additive method is in F.7, not yet committed)
```

- [ ] **Step 2: Verify constant snapshot stable**

```bash
diff tests/snapshots/criteria-constants.txt <(php -r 'foreach ((new ReflectionClass(Propel\Runtime\ActiveQuery\Criteria::class))->getReflectionConstants() as $c) echo $c->getName() . "\n";' | sort)
# expected: empty
```

- [ ] **Step 3: Commit if any snapshot drift discovered**

If the diffs are non-empty (which would be a bug — F.4 should be pure refactor), STOP and investigate. F.4 does not commit if Tier 1 surface drifted.

```bash
git add tests/snapshots/
git commit -m "chore(snapshots): verify Tier 1 Criteria surface stable after F.4 extraction"
```

---

## Round 1 Review Checkpoint (mid-phase, after F.1 + F.2 + F.3 + F.4)

**Trigger:** all tasks F.1.1–F.4.4 landed and green. Enums + tokenizer + PreparedStatementKey SPI + first Plan extraction proven.
**Reviewers (per umbrella §4.13.3 mid-phase HIGH-RISK):** Architecture + BC + Compiler/parser specialist + Security reviewer (4 lenses).
**Lens:**

- **Architecture (Plan/Compiler/Operator split shape):** Is the `JoinPlan`/`WhereTree`/`OrderClause` split single-responsibility? Could a future maintainer mistake `WhereTree` for a SQL-emission concern (it's pure tree topology — emission stays in `SqlBuilder/`)? Is the `Operator/` enum-vs-constant alongside-not-replacing rule clear in the code? Does any `Plan/*` class know about a specific adapter?
- **BC (Tier 1 surface integrity):** Are `Criteria`'s ~140 public methods still bit-for-bit signature-stable? Are the ~38 Tier 1 constants unchanged? Does the new `customCondition()` method (F.7, not yet landed) pass the signature-diff gate cleanly when it lands? Are the Tier 2 enum case identifiers (`Equal`, `Asc`, etc.) defensible — would a third party adding a `case` cause naming collision?
- **Compiler/parser specialist:** Is the new tokenizer state machine handling SQL edge cases correctly? PG `$$dollar-quoted strings$$`? MySQL `# line comment` (alternative to `--`)? Empty-input edge case? Unicode in idents (per `\w` semantics; documented as same-as-legacy)? Is the regex callback contract preserved (the legacy resolver passed `[$this, 'doReplaceNameInExpression']` — does the new resolver pass identical args in identical order)? Is the 10k-input PBT corpus diverse enough — what coverage of legacy edge-cases does it actually exercise?
- **Security reviewer:** Is the `PreparedStatementKey::equals` `hash_equals` use defensible (yes — defensive against timing-based oracle on key comparison; same rationale as Phase E's security review entry). Is the new tokenizer a new injection surface? (No — it only TOKENIZES; injection happens at SQL execution, which the tokenizer is upstream of.) Does the `customCondition()` implementation actually parameterize, or does it just rename the raw-SQL hole? (Verify F.7 implementation when it lands.) Is the typed-Criterion DSL (F.8) generated-class-name namespace-collision safe?

**Outputs:** `docs/reviews/F-round-1-architecture.md`, `F-round-1-bc.md`, `F-round-1-compiler-parser.md`, `F-round-1-security.md`, `F-round-1-summary.md`.
**Iteration budget:** 3 cycles per umbrella §4.15.3. Track in `F-iterations.md`.

Findings tagged `MUST-FIX` block progression to F.5 (Criteria slim-down), F.6 (Java-Hashtable deprecation), F.7 (CUSTOM parameterization), and F.8 (typed Criterion DSL stretch). `SHOULD-FIX` may be waived per umbrella §4.15.4.

---

## Group F.5: Criteria.php slim-down (Tasks F.5.1–F.5.4)

**Verification after each task:** Tier 1 signature-diff stable; every existing `CriteriaTest` test passes; LOC measurement confirms shrinkage trend.

After F.4, `Criteria.php` is structurally lighter but still long because every Tier-1 method is preserved as a forwarder. F.5 ensures the forwarders are truly thin (one line each, no inline logic) and audits for any drift between F.4's extractions and what the public methods actually do.

---

### Task F.5.1: Audit each Tier 1 method for "thin forwarder" compliance

**Files:**
- Modify: `src/Propel/Runtime/ActiveQuery/Criteria.php`
- Create: `docs/reviews/F-thinness-audit.md`

- [ ] **Step 1: Manual audit**

Walk every public method in `Criteria.php`. For each method that touches join/where/order-by, verify the body is ≤3 lines (a forwarder + return). Methods that violate this are candidates for further extraction or are accepted-as-is with reasoning.

- [ ] **Step 2: Document audit results**

`docs/reviews/F-thinness-audit.md`: a table of every Tier 1 method, current LOC, post-F.4 LOC, exempt-or-thin status. Methods exempt from the thin-forwarder rule are explicitly listed (e.g., `__clone`, `__set_state`, the magic `__call` dispatch, the `replaceNames` method which is now ~6 lines).

- [ ] **Step 3: Refine forwarders**

Any forwarder that's >3 lines and doesn't have an exemption gets refined: extract the inline logic into the appropriate `Plan/*` or `Compiler/*` class.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Criteria.php docs/reviews/F-thinness-audit.md
git commit -m "refactor(runtime/activequery): Criteria thinness audit; forwarders ≤3 lines"
```

---

### Task F.5.2: ModelCriteria parallel slim-down

**Files:**
- Modify: `src/Propel/Runtime/ActiveQuery/ModelCriteria.php`
- Modify: `tests/snapshots/ModelCriteria.signatures.json` (verify still stable; only additive changes possible)

- [ ] **Step 1: Audit `ModelCriteria` for the same**

`ModelCriteria` extends `Criteria` and adds model-aware methods. The same audit applies — `addJoinConditions`, `getColumnFromName`, `addUsingOperator`, `combine`, `joinWith` should all be thin forwarders to `Plan/*` (where applicable) or to `Criteria`'s now-thin methods.

- [ ] **Step 2: Refine where needed**

- [ ] **Step 3: Tier 1 signature-diff check**

`ModelCriteria.signatures.json` MUST be unchanged.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/ModelCriteria.php tests/snapshots/ModelCriteria.signatures.json
git commit -m "refactor(runtime/activequery): ModelCriteria parallel slim-down (Tier 1 unchanged)"
```

---

### Task F.5.3: Verify LOC target

**Files:**
- Modify: `docs/reviews/F-thinness-audit.md` (append LOC measurement)

- [ ] **Step 1: Measure**

```bash
wc -l src/Propel/Runtime/ActiveQuery/Criteria.php
# target: ≤ 650 LOC; hard cap: 700
wc -l src/Propel/Runtime/ActiveQuery/ModelCriteria.php
# document the measurement; ModelCriteria has no specific target — depends on F.5.2
```

If `Criteria.php` is over the hard cap, the responsibility split is incomplete; iterate.

- [ ] **Step 2: Commit**

```bash
git add docs/reviews/F-thinness-audit.md
git commit -m "chore(metrics): post-F.5 Criteria + ModelCriteria LOC measurements"
```

---

### Task F.5.4: Tier 1 signature-diff regression gate

**Files:**
- Modify: `tests/snapshots/Criteria.signatures.json`
- Modify: `tests/snapshots/criteria-constants.txt` (must be unchanged)

- [ ] **Step 1: Re-run signature-diff**

```bash
bin/propel internal:dump-signatures > /tmp/post-F5.json
diff tests/snapshots/Criteria.signatures.json /tmp/post-F5.json
# expected: empty
diff tests/snapshots/criteria-constants.txt <(...)
# expected: empty
```

- [ ] **Step 2: Commit if stable**

```bash
git add tests/snapshots/
git commit -m "chore(snapshots): Tier 1 Criteria + constants stable through F.5"
```

If the diff is non-empty without an accompanying `@deprecated`, STOP — the Tier 1 contract is broken. Investigate which forwarder accidentally narrowed/widened a signature.

---

## Group F.6: Java-Hashtable method deprecation (Tasks F.6.1–F.6.3)

**Verification after each task:** the eight methods still work end-to-end; deprecation message emitted exactly once per method category per process; baseline `tests/deprecations.allowlist.json` regenerated to capture exactly the eight new categories.

Group F.6 closes umbrella §6.1 line: "`Criteria::put`/`putAll`/`get`/`keys`/`containsKey`/`keyContainsValue`/`size`/`equals` — Deprecate; kill 4.0. Java-Hashtable rump." Per umbrella §3.2 deprecation runway, the methods stay callable through 3.x; 4.0 removes them.

---

### Task F.6.1: Add `trigger_deprecation` to the eight methods

**Files:**
- Modify: `src/Propel/Runtime/ActiveQuery/Criteria.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/JavaHashtableDeprecationTest.php`

- [ ] **Step 1: Per-method deprecation triggers**

Each of the eight methods gains a one-line `trigger_deprecation` at the top of the body, e.g.:

```php
public function put(string $key, $value)
{
    trigger_deprecation('maturix/propel', '3.0', 'Criteria::put() is a Java-Hashtable rump and is deprecated. Use Criteria::add() / addAnd() / addOr() instead. Removal targeted for 4.0.');

    // ... existing body unchanged ...
}
```

The replacement-method pointer in each message:
- `put` / `putAll` → `Criteria::add()` / `addAnd()` / `addOr()`
- `get` → access via `getCriterion(string $columnName)`
- `keys` → `getCriterion()` returns the WhereTree's keys; or directly via `WhereTree::getCriterions()` Tier 3 helper.
- `containsKey` → `hasCondition(string $columnName)` (existing Tier 1 method)
- `keyContainsValue` → `hasCondition(...) && getCriterion(...)->getValue() === $v` — verbose but explicit; documented in migration doc.
- `size` → `count($this->getCriterions())` / `count($this->getJoins())` — context-dependent, documented
- `equals` → no direct replacement (was a Java-Hashtable equality check that misbehaves on real `Criteria` objects); documented as "do not use for object equality; use spl_object_id or your own domain comparison"

- [ ] **Step 2: Tests assert exactly-once-per-category emission**

```php
public function testPutTriggersDeprecationExactlyOncePerCategory(): void
{
    $crit = new Criteria();
    $crit->put('Book.Title', 'foo');
    $crit->put('Book.Author', 'bar');  // second call SHOULD also fire — per-call, not per-construction
    $this->assertDeprecationCount('Criteria::put', 2);
}
```

`assertDeprecationCount` is a phpunit-bridge utility. The eight categories each have a similar test method.

- [ ] **Step 3: Functional contract preservation**

Each method's underlying behavior is unchanged — Java-Hashtable methods STILL WORK in 3.x. Test that the result of `$crit->put('Book.Title', 'foo')` is still effective (equivalent to `add(Book.Title, foo)`).

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Criteria.php tests/Propel/Tests/Runtime/ActiveQuery/JavaHashtableDeprecationTest.php
git commit -m "feat(runtime/activequery): deprecate Criteria Java-Hashtable methods (umbrella §6.1; runway to 4.0)"
```

---

### Task F.6.2: Refresh deprecations allowlist

**Files:**
- Modify: `tests/deprecations.allowlist.json`

- [ ] **Step 1: Regenerate baseline**

```bash
SYMFONY_DEPRECATIONS_HELPER='max[self]=999999&baselineFile=tests/deprecations.allowlist.json&generateBaseline=true' \
  vendor/bin/phpunit -c tests/agnostic.phpunit.xml
```

- [ ] **Step 2: Verify exactly the eight new entries land**

The diff against the previous baseline should show exactly 8 new entries (one per method category). No others.

- [ ] **Step 3: Commit**

```bash
git add tests/deprecations.allowlist.json
git commit -m "chore(deprecations): allowlist Criteria Java-Hashtable method deprecations"
```

---

### Task F.6.3: Migration cookbook entry

**Files:**
- Modify: `docs/MIGRATION-FROM-PRE-AI.md` (Java-Hashtable section)
- Modify: `docs/BACKWARD_COMPATIBILITY.md` (Tier 1 deprecated-but-callable entry)
- Modify: `CHANGELOG.md`

- [ ] **Step 1: Migration cookbook**

`docs/MIGRATION-FROM-PRE-AI.md` "Java-Hashtable method deprecation runway (3.0 → 4.0)" section. For each of the eight methods: pre/post code snippet showing the modern equivalent. Worked example: a real existing usage of `Criteria::put` in a downstream codebase, rewritten.

- [ ] **Step 2: BC commitment statement**

`docs/BACKWARD_COMPATIBILITY.md` Tier 1 entries: "Deprecated (will be removed in 4.0): Criteria::put, putAll, get, keys, containsKey, keyContainsValue, size, equals. All emit `trigger_deprecation` in 3.x; remain callable. Removal in 4.0 paired with Rector rule for mechanical migration."

- [ ] **Step 3: Commit**

```bash
git add docs/MIGRATION-FROM-PRE-AI.md docs/BACKWARD_COMPATIBILITY.md CHANGELOG.md
git commit -m "docs: Java-Hashtable method deprecation runway + migration cookbook"
```

---

## Group F.7: CUSTOM raw-SQL parameterized alternative (Tasks F.7.1–F.7.3)

**Verification after each task:** new method produces parameterized SQL (asserts via inspection of bind values); old raw-CUSTOM still works but emits `trigger_deprecation`; security review verifies the parameterization actually parameterizes.

Group F.7 closes umbrella §6.2 risk #2: "`Criteria::CUSTOM` raw-SQL injection vector". The current `Criteria::add($name, $sql, Criteria::CUSTOM)` interpolates `$sql` directly into the WHERE clause — any user-derived data in `$sql` is a direct injection vector. Phase F introduces `customCondition($name, $sql, array $params)` that parameterizes via the standard PDO bind-value path; deprecates raw-`Criteria::CUSTOM` after.

---

### Task F.7.1: Implement `customCondition()` Tier-1 additive method

**Files:**
- Modify: `src/Propel/Runtime/ActiveQuery/Criteria.php`
- Modify: `src/Propel/Runtime/ActiveQuery/Criterion/CustomCriterion.php`
- Create: `src/Propel/Runtime/ActiveQuery/Criterion/Exception/UnsafeCustomConditionException.php`
- Create: `tests/Propel/Tests/Runtime/ActiveQuery/CustomConditionTest.php`
- Modify: `tests/snapshots/Criteria.signatures.json` (additive)

- [ ] **Step 1: API shape**

```php
/**
 * Parameterized alternative to add($name, $sql, Criteria::CUSTOM).
 *
 * Bind parameters via standard PDO placeholder syntax. Internally builds a CustomCriterion
 * whose binding path matches the rest of the WHERE tree.
 *
 * @api Tier 1 additive
 *
 * @param string $name      Condition name (e.g., 'price_in_range')
 * @param string $sql       SQL fragment with `?` placeholders for $params
 * @param array  $params    Bind values (in $sql placeholder order)
 * @param bool   $allowRawSql  When true, skips the heuristic check on $sql; only set when
 *                            you have audited that $sql contains no user input
 *
 * @throws UnsafeCustomConditionException When $sql appears to contain user-input markers and
 *                                        $allowRawSql is false (heuristic flag, conservative)
 */
public function customCondition(
    string $name,
    string $sql,
    array $params = [],
    bool $allowRawSql = false,
): static {}
```

The heuristic flag in step 2 of `customCondition` looks for common user-input markers in `$sql` (presence of `'`, `"`, `;`, or values that look interpolated). Conservative — if any flag fires, `UnsafeCustomConditionException` is thrown unless `$allowRawSql=true`. The flag reduces accidental footguns; consumers explicitly opting into raw-SQL pass `true`.

- [ ] **Step 2: `CustomCriterion` parameterized binding path**

`CustomCriterion::__construct(... $sql, array $params)` accepts `$params`; `appendPsTo(string &$sb, array &$params)` (the bind-output method) appends the SQL with positional placeholders and merges `$params` into the output bind list, exactly as `BasicCriterion` and `InCriterion` do today.

The OLD `CustomCriterion` constructor (no `$params`) keeps working — `params` defaults to empty; raw-SQL path stays callable; `Criteria::add($n, $sql, Criteria::CUSTOM)` continues to function (with deprecation in F.7.2).

- [ ] **Step 3: Tests cover parameterization**

- `customCondition('p1', 'price BETWEEN ? AND ?', [10, 100])` produces SQL `(price BETWEEN ? AND ?)` with bind values `[10, 100]`. Inspect the resulting prepared statement.
- `customCondition('p2', 'TRIM(name) = ?', ['foo'])` parameterizes correctly.
- `customCondition('p3', "name = 'foo'", [])` throws `UnsafeCustomConditionException` (single-quote in SQL flagged as potential interpolation marker).
- `customCondition('p4', "name = 'foo'", [], allowRawSql: true)` accepted (explicit opt-in).

- [ ] **Step 4: Snapshot refresh**

`tests/snapshots/Criteria.signatures.json` gains exactly one new method entry: `customCondition`. Tier 1 additive — gate passes.

- [ ] **Step 5: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Criteria.php src/Propel/Runtime/ActiveQuery/Criterion/CustomCriterion.php src/Propel/Runtime/ActiveQuery/Criterion/Exception/UnsafeCustomConditionException.php tests/Propel/Tests/Runtime/ActiveQuery/CustomConditionTest.php tests/snapshots/Criteria.signatures.json
git commit -m "feat(runtime/activequery): customCondition() — parameterized alternative to raw CUSTOM"
```

---

### Task F.7.2: Deprecate raw `Criteria::CUSTOM` usage

**Files:**
- Modify: `src/Propel/Runtime/ActiveQuery/Criteria.php` (deprecation in `add()` when third arg is `CUSTOM`)
- Modify: `tests/Propel/Tests/Runtime/ActiveQuery/CustomConditionTest.php` (assert deprecation fires)

- [ ] **Step 1: Add deprecation in `Criteria::add` for `CUSTOM` operator**

```php
public function add(string $columnName, $value = null, $operator = null): static
{
    if ($operator === Criteria::CUSTOM || $operator === Comparison::Custom) {
        trigger_deprecation('maturix/propel', '3.0', 'Criteria::add($name, $sql, Criteria::CUSTOM) interpolates raw SQL — vulnerable to injection. Use Criteria::customCondition($name, $sql, $params) instead. Removal of raw CUSTOM is not currently scheduled, but new code should use the parameterized form.');
    }
    // ... existing body unchanged ...
}
```

Note: per umbrella §6.2 risk #2, we DEPRECATE the raw form but do NOT remove it in 3.x. Removal in 4.0 is reasonable but only if a Rector rule exists to migrate consumers. The 3.x runway is adequate.

- [ ] **Step 2: Test asserts deprecation fires**

`CustomConditionTest::testRawCustomEmitsDeprecation` — calls `Criteria::add('foo', 'bar = baz', Criteria::CUSTOM)`, asserts the deprecation is fired exactly once.

- [ ] **Step 3: Allowlist refresh**

`tests/deprecations.allowlist.json` gets one more entry for the raw-CUSTOM category.

- [ ] **Step 4: Commit**

```bash
git add src/Propel/Runtime/ActiveQuery/Criteria.php tests/Propel/Tests/Runtime/ActiveQuery/CustomConditionTest.php tests/deprecations.allowlist.json
git commit -m "feat(runtime/activequery): deprecate raw Criteria::CUSTOM in favor of customCondition()"
```

---

### Task F.7.3: Migration cookbook for CUSTOM

**Files:**
- Modify: `docs/MIGRATION-FROM-PRE-AI.md`
- Modify: `docs/BACKWARD_COMPATIBILITY.md`
- Modify: `docs/CRITERIA-SPLIT.md`
- Modify: `CHANGELOG.md`

- [ ] **Step 1: Migration cookbook**

"Parameterized `customCondition()` replaces raw `Criteria::CUSTOM` (3.0)" section. Concrete: any consumer with `Criteria::add('cond', "name = '$userInput'", Criteria::CUSTOM)` is a SQL-injection bug; the migration is `Criteria::customCondition('cond', 'name = ?', [$userInput])`. Worked examples for the common cases (BETWEEN, IN with subquery, function call with arg, raw expression).

- [ ] **Step 2: Security note in CRITERIA-SPLIT.md**

A security section: "Raw SQL via `Criteria::add(..., Criteria::CUSTOM)` is deprecated in 3.0. Use `customCondition()` for parameterized binding. The deprecation message points to this section."

- [ ] **Step 3: BC entry**

`BACKWARD_COMPATIBILITY.md`: Tier 1 deprecated-but-callable: raw-`CUSTOM`. Tier 1 additive: `customCondition()`.

- [ ] **Step 4: CHANGELOG**

`CHANGELOG.md`: under `[Unreleased]` → "Security: parameterized `Criteria::customCondition($name, $sql, $params)` mitigates the raw-CUSTOM SQL injection vector (umbrella §6.2 risk #2). Raw `Criteria::add(..., Criteria::CUSTOM)` is now deprecated."

- [ ] **Step 5: Commit**

```bash
git add docs/MIGRATION-FROM-PRE-AI.md docs/BACKWARD_COMPATIBILITY.md docs/CRITERIA-SPLIT.md CHANGELOG.md
git commit -m "docs: customCondition migration cookbook + raw CUSTOM security note"
```

---

## Group F.8: Optional typed Criterion DSL — STRETCH (Tasks F.8.1–F.8.5)

**Verification after each task:** opt-in fixture builds and passes; default-bookstore fixture untouched; lint parity green; signature-diff for the typed-Criterion fixture's generated classes is stable.

**Risk:** F.8 is the only NEW capability in Phase F. Default-OFF in 3.x; opt-in via the `<table generate-typed-criterion="true">` schema attribute. The umbrella §6.4 row is explicit: "Per-column typed Criterion classes (opt-in) — Phase F (stretch)". The opt-in mitigation is what keeps F.8 from being a Tier-1 risk for the standard bookstore fixture.

**Stretch deferral path:** if Round 2 review finds F.8 too risky for Phase F, F.8 defers to a Phase F.1 follow-up (per umbrella §4.15.3 maintainer-escalation outcome (c)). Round 2 is the explicit decision point.

---

### Task F.8.1: Add `generate-typed-criterion` XSD attribute

**Files:**
- Modify: `resources/xsd/database.xsd`
- Modify: `tests/Fixtures/typed-criterion-bookstore/schema.xml` (a new fixture that opts in)

- [ ] **Step 1: XSD additive**

Add a new optional `generate-typed-criterion` boolean attribute to the `<xs:complexType name="tableType">`. Default `false`. Per umbrella §3.7: schema instances valid against today's XSD remain valid against the new XSD.

- [ ] **Step 2: Fixture**

`tests/Fixtures/typed-criterion-bookstore/schema.xml` — a small bookstore-flavored schema with `<table name="book" generate-typed-criterion="true">`. Two columns: `id`, `title`. Demonstrates the DSL's per-column class.

- [ ] **Step 3: XSD additivity test**

`tests/Propel/Tests/Generator/Schema/XsdAdditivityTest.php` (Phase A) re-runs against the modified XSD. The 12 fixtures it validates MUST all still validate. The new `typed-criterion-bookstore` fixture validates against the new attribute.

- [ ] **Step 4: Commit**

```bash
git add resources/xsd/database.xsd tests/Fixtures/typed-criterion-bookstore/schema.xml
git commit -m "feat(schema): generate-typed-criterion attribute on <table> (opt-in; default off)"
```

---

### Task F.8.2: `QueryBuilder` emits typed Criterion classes when opt-in

**Files:**
- Modify: `src/Propel/Generator/Builder/Om/QueryBuilder.php`
- Modify: `src/Propel/Generator/Builder/Om/AbstractOMBuilder.php`
- Create: `src/Propel/Generator/Builder/Om/TypedCriterionBuilder.php`

- [ ] **Step 1: TypedCriterionBuilder**

A new builder class that, given a `Table`, emits a `{TableName}Criterion` PHP class (e.g., `BookCriterion`). The class has one static method per column: `BookCriterion::title(): TypedColumnCriterion`. `TypedColumnCriterion::equals($value): AbstractCriterion`, `notEquals`, `greaterThan`, `lessThan`, `like`, `in(array)`, etc. — one per `Comparison` enum case (per F.1.1).

- [ ] **Step 2: `QueryBuilder` opt-in hook**

```php
// in QueryBuilder
if ($this->getTable()->getAttribute('generate-typed-criterion') === 'true') {
    $this->addBuilder(new TypedCriterionBuilder($this->getTable()));
}
```

- [ ] **Step 3: Generated-code lint parity**

The emitted `{TableName}Criterion` class passes `composer cs-check && composer stan && composer psalm` exactly as hand-written code. Per umbrella §4.6 generated-code lint parity gate (Phase A).

- [ ] **Step 4: Namespace-collision check**

Generated class name is `{TableName}Criterion`. If a consumer already has a `{TableName}Criterion` in their hand-written code, the generated one would collide. Risk register entry: opt-in mitigates (consumer has to opt in by setting the attribute, so they're aware); but the migration doc warns about the naming convention.

- [ ] **Step 5: Commit**

```bash
git add src/Propel/Generator/Builder/Om/QueryBuilder.php src/Propel/Generator/Builder/Om/AbstractOMBuilder.php src/Propel/Generator/Builder/Om/TypedCriterionBuilder.php
git commit -m "feat(generator/builder): TypedCriterionBuilder — opt-in per-column DSL"
```

---

### Task F.8.3: Generate the typed-criterion fixture

**Files:**
- Create: `tests/Fixtures/typed-criterion-bookstore/build/golden/` (committed generated output)

- [ ] **Step 1: Run the generator on the fixture**

```bash
bin/propel model:build --schema-dir=tests/Fixtures/typed-criterion-bookstore/ --output-dir=tests/Fixtures/typed-criterion-bookstore/build/classes/
```

- [ ] **Step 2: Commit the golden tree**

```bash
git add tests/Fixtures/typed-criterion-bookstore/build/
git commit -m "test(fixtures): typed-criterion-bookstore golden tree (opt-in DSL)"
```

---

### Task F.8.4: Tests use the generated DSL end-to-end

**Files:**
- Create: `tests/Propel/Tests/Generator/Behavior/TypedCriterion/TypedCriterionBuilderTest.php`

- [ ] **Step 1: Smoke test the generated DSL**

```php
$crit = BookQuery::create();
$crit->where(BookCriterion::title()->like('Foundation%'));
$crit->where(BookCriterion::id()->in([1, 2, 3]));
$result = $crit->find();
// assert SQL is parameterized (the new DSL goes through customCondition, NOT raw CUSTOM)
```

- [ ] **Step 2: Default-bookstore fixture untouched**

A regression test asserts `tests/Fixtures/bookstore/build/golden/` does NOT have any `BookCriterion.php` etc. — the standard fixture is opt-OUT. Goldens are byte-identical.

- [ ] **Step 3: Commit**

```bash
git add tests/Propel/Tests/Generator/Behavior/TypedCriterion/TypedCriterionBuilderTest.php
git commit -m "test(generator/behavior/typed-criterion): smoke test typed DSL + default fixture untouched"
```

---

### Task F.8.5: Documentation

**Files:**
- Modify: `docs/CRITERIA-SPLIT.md` (Typed Criterion DSL section)
- Modify: `docs/MIGRATION-FROM-PRE-AI.md`
- Modify: `docs/UPGRADE-3.0.md`

- [ ] **Step 1: DSL reference**

`docs/CRITERIA-SPLIT.md` "Typed Criterion DSL (opt-in, 3.0)" section. How to opt in. Pre/post code samples. Performance characteristics (no measurable overhead — DSL produces the same AbstractCriterion as the raw `Criteria::add`). Naming-collision warning.

- [ ] **Step 2: UPGRADE doc**

`docs/UPGRADE-3.0.md` capability list: typed Criterion DSL added (opt-in via `generate-typed-criterion="true"`).

- [ ] **Step 3: Commit**

```bash
git add docs/CRITERIA-SPLIT.md docs/MIGRATION-FROM-PRE-AI.md docs/UPGRADE-3.0.md
git commit -m "docs: typed Criterion DSL reference + opt-in instructions"
```

---

## Round 2 Review Checkpoint (end-phase, after F.5 + F.6 + F.7 + F.8)

**Trigger:** all tasks F.1–F.8 landed; quality gates green; ready to merge.
**Reviewers (per umbrella §4.13.3 end-phase HIGH-RISK):** **all 5 standing** + **Compiler/parser specialist** + **Security reviewer**.
**Lens:**

- **Architecture:** Is the Plan/Compiler/Operator split honest single-responsibility? Does any `Plan/*` class know too much about SQL emission (it shouldn't — that's `SqlBuilder/`'s job)? Does the `Operator/` enum-vs-constant alongside-not-replacing rule survive end-to-end? Is the typed-Criterion DSL (F.8) a clean addition or has it crept into Tier-1 territory?
- **BC:** Are `Criteria`'s ~140 public methods and ~38 constants truly intact? Does the additive `customCondition()` method pass the signature-diff gate? Are the eight Java-Hashtable methods still callable AND emitting deprecations exactly once per category? Does `tests/snapshots/criteria-constants.txt` still show 0 diff?
- **Quality:** baselines monotonic; coverage delta ≥ 0; mutation MSI ≥ **75** on touched files (umbrella §4.4 — Phase F threshold); deptrac green; PBT seeds documented; fuzzing corpus differential check passes (10k zero-diff except allowlisted divergences).
- **Performance:** umbrella §4.10 query overhead target unchanged. Tokenizer-vs-legacy benchmark in `F-bench.md`: tokenizer should be no slower, ideally faster (the legacy regex+callback approach is `O(n)` regex per non-string segment).
- **Ambition:** every umbrella §6.4 capability item delivered: enums alongside ✓, typed Criterion DSL ✓ (opt-in stretch). Every umbrella §6.1 / §6.2 bug fix landed: NameResolver tokenizer ✓, Java-Hashtable deprecation ✓, parameterized customCondition ✓, raw-CUSTOM deprecated ✓. Every umbrella §2.2 namespace shipped: Compiler/, Plan/, Operator/.
- **Compiler/parser specialist:** Is the tokenizer state machine handling MySQL-specific (`#` line comment), PG-specific (dollar-quoted strings if applicable), MSSQL-specific (`[bracketed]` idents — out of scope per §1.2 but the tokenizer should still tolerate them as plain chars without choking) edge cases? Is the 10k-input fuzzing corpus diverse enough to flush bugs? Is the legacy-vs-new differential gate airtight (especially around the documented backtick-divergence case)? Is the regex callback contract preserved bit-for-bit (the legacy resolver passed `[$this, 'doReplaceNameInExpression']` as a callable; the new resolver MUST pass identical args in identical order to that callable)?
- **Security reviewer:** Does `customCondition()`'s parameterization actually parameterize? (Inspect bind values via prepared statement reflection — no string interpolation should land in the SQL string.) Is the `UnsafeCustomConditionException` heuristic too lenient (false negatives) or too strict (false positives causing churn)? Is the typed-Criterion DSL emitted code injection-safe (uses `customCondition()` internally, not `Criteria::add(..., Criteria::CUSTOM)`)? Is `PreparedStatementKey::equals` use of `hash_equals` defensible?

**Outputs:** `docs/reviews/F-round-2-{architecture,bc,quality,performance,ambition,compiler-parser,security}.md` + `F-round-2-summary.md`. Plus `F-bench.md`, `F-mutation.json`, `F-fuzz.md`, `F-waivers.md`.

**Iteration budget:** 3 cycles. Maintainer-escalation triggers per umbrella §4.15.3.

---

## Group F.9: Phase F DoD verification (Tasks F.9.1–F.9.2)

**Verification:** the 15-box Definition of Done from umbrella §4.9 passes.

---

### Task F.9.1: Full quality stack

**Files:**
- Modify: `docs/reviews/F-bench.md`
- Modify: `docs/PHASE-F-SUMMARY.md`

- [ ] **Step 1: Run the full matrix**

```
composer test                    # all 16 cells green
composer testsuite               # static + style + tests
composer stan                    # baseline monotonic
composer psalm                   # baseline monotonic
composer cs-check                # clean
vendor/bin/deptrac analyse       # 0 violations
vendor/bin/infection             # MSI ≥75 on touched files (umbrella §4.4 Phase F)
php tools/check-baseline-monotonic.php  # green
php tools/regen-golden.php       # idempotent (no diff for default fixture; new diff for typed-criterion fixture)
diff -ru tests/snapshots/{Tier1-snapshots-pre,post}/  # only additive customCondition entry on Criteria
```

- [ ] **Step 2: Capture LOC drawdown**

Pre-Phase-F: `Criteria.php` 2526 LOC, `ModelCriteria.php` 2635 LOC. Total: 5161 LOC.
Post-Phase-F: target `Criteria.php` ≤ 650 LOC, `ModelCriteria.php` ~ TBD (proportional). Plus `Plan/*` (~600 LOC), `Compiler/*` (~400 LOC), `Operator/*` (~200 LOC). Net ~ flat to -10% on the ActiveQuery namespace; the win is structural single-responsibility split, not LOC reduction.

- [ ] **Step 3: Capture bench results**

| Path | Pre-F (Phase E end) | Post-F |
|---|---|---|
| `Criteria::replaceNames` cost per call (microbench) | TBD | ≤1× pre (tokenizer no slower) |
| Statement-cache hit rate via PreparedStatementKey SPI | ≥90% (Phase E) | ≥90% (unchanged; same shape) |
| Query overhead per call vs raw PDO | ≤2× (Phase E) | ≤2× (no decorator change here) |
| `customCondition()` overhead vs raw `add(..., Criteria::CUSTOM)` | n/a | ≤±5% (path is the same after Criterion construction) |

- [ ] **Step 4: Commit**

```bash
git add docs/reviews/F-bench.md
git commit -m "perf(runtime/activequery): Phase F performance characterization"
```

---

### Task F.9.2: Phase F summary + handoff to Phase G

**Files:**
- Create: `docs/PHASE-F-SUMMARY.md`

- [ ] **Step 1: Author the summary**

Mirror Phase E's structure. Sections: status, what shipped, deferred (if any), quality gates table, notable surprises, review process recap, next: Phase G.

Forward-references for Phase G: Phase F's `Comparison`/`JoinType`/`SortOrder`/`LogicalOperator` enums are consumed by Phase G's PHP 8.4 backed-enum-aware property hooks. Phase F's `Plan/*` classes are candidates for Phase G's lazy-object treatment. Phase F's `customCondition()` parameterized binding is the substrate for Phase G's streaming `Generator`-based formatter.

- [ ] **Step 2: Commit**

```bash
git add docs/PHASE-F-SUMMARY.md
git commit -m "docs(phase-f): summary + handoff to Phase G"
```

---

## Round 3 Review Checkpoint (post-merge canary, 7 days after merge to integration)

**Trigger:** Phase F merged to integration branch; ecosystem-advisory CI has run for 7 days; any consumer-smoke regressions surface.
**Reviewers (per umbrella §4.13.3 HIGH-RISK Round 3):** Performance + Quality + BC reviewers (3 lenses).
**Lens:**

- **Performance:** has any consumer reported a real-workload regression > 5% on `replaceNames` (the tokenizer should be no slower on real SQL — the regression budget is 5%)? Has any consumer reported `customCondition()` overhead vs the deprecated raw form?
- **Quality:** any new GitHub issues related to the Plan/Compiler/Operator split? Any unexpected `Criteria` Tier 1 surface drift? Any false-positive flagging from the `UnsafeCustomConditionException` heuristic causing churn? Any chaos-test scenarios surfaced post-merge that the Phase F plan missed?
- **BC:** any third-party packages broken by the Plan/Compiler split? `instanceof` checks against `Criteria` are unchanged. Any consumer code that pattern-matched on the SQL output of `replaceNames` and is now surprised by the tokenizer's normalization? `Criteria::put`/`get`/etc. deprecation noise — is it manageable?

**Outputs:** `docs/reviews/F-round-3-{performance,quality,bc}.md` + `F-round-3-summary.md`.

**Severity-1 trigger:** any reported SQL-injection vulnerability traced to the new `customCondition()` (would mean the parameterization is unsound), or any reported behavioral divergence in `replaceNames` output that breaks consumer code. If triggered: rollback procedure per umbrella §7.1.

**Outcome decision:** sign-off OR follow-up-issue list filed for a Phase F.1 patch release.

---

## Definition of Done (umbrella §4.9 — 15 boxes)

- [ ] All test matrix cells green: PHP {8.3, 8.4} × DB {agnostic, mysql, pgsql, sqlite} × Symfony {7.2, 7.latest} = 16 cells.
- [ ] `phpstan-baseline.neon` ≤ 403 lines (Phase E end; Phase F should not increase, may decrease as the Plan/* split removes phpstan-suppressed cases).
- [ ] `psalm-baseline.xml` ≤ 1621 lines (Phase E end).
- [ ] Coverage delta ≥ 0%; floor 70% Runtime / 60% Generator / 70% generated bookstore output (Phase A).
- [ ] **Mutation MSI ≥ 75** on touched files (umbrella §4.4 — Phase F threshold). Touched files: every `ActiveQuery/Compiler/*`, `ActiveQuery/Plan/*`, `ActiveQuery/Operator/*`, `ActiveQuery/Criteria.php`, `ActiveQuery/ModelCriteria.php`, `ActiveQuery/Criterion/CustomCriterion.php`, `Generator/Builder/Om/QueryBuilder.php` (F.8 stretch only).
- [ ] Deptrac green; 0 violations against 233 baseline. New `Runtime/ActiveQuery/{Compiler,Plan,Operator}/*` rules verified — Compiler+Plan internal-only; Operator Tier 2 published.
- [ ] **Performance benchmarks within umbrella §4.10 targets**: `replaceNames` ≤1× pre cost; `customCondition` ≤±5% vs deprecated raw form; PreparedStatementKey hit rate stable. No regression > 5% vs Phase E end. Bench in `F-bench.md`.
- [ ] `CHANGELOG.md` updated (per task F.7.3 list).
- [ ] Deprecation message audit clean (allowlist accepts 8 Java-Hashtable categories + 1 raw-CUSTOM category; no surprise leakage).
- [ ] Generated-code lint parity green for both default bookstore (untouched) and the F.8 typed-criterion fixture (new).
- [ ] Golden-file diff reviewed: default bookstore byte-identical; typed-criterion fixture is the new opt-in tree (no other generated-code changes).
- [ ] Phase plan updated with retrospective notes (this file, appended after Round 3).
- [ ] All review-round reports committed under `docs/reviews/F-round-{1,2,3}-*.md`; consolidated `F-summary.md`.
- [ ] **Surgical-test battery executed (umbrella §4.14)**: PBT (token-equivalence + Criterion round-trip); mutation report; fuzzing corpus differential (10k inputs); perf benchmarks vs §4.10; differential test against Phase E end (no Tier 1 / Tier 2 surface drift); consumer-smoke run; ecosystem advisory CI run; signature-diff (Criteria additive customCondition only); golden-file (default unchanged + new typed-criterion fixture).
- [ ] All MUST-FIX closed; SHOULD-FIX closed or waived in `F-waivers.md`; iteration cycles within budget (3 per round); Round 3 post-merge canary signed off.

---

## Risk Register (Phase F-specific)

1. **Tier 1 surface preservation is the load-bearing check.** The ~140 public methods and ~38 constants in `Criteria.php` MUST remain bit-for-bit signature-stable through the entire split, except for the single additive `customCondition()` method in F.7. The signature-diff snapshot (`tests/snapshots/Criteria.signatures.json`) and constant snapshot (`tests/snapshots/criteria-constants.txt`) are the gates. **Mitigation:** every task in F.4 + F.5 runs the signature-diff gate post-commit; F.5.4 is a dedicated regression check; Round 1 BC reviewer's lens explicitly checks; Round 2 BC reviewer re-checks. Any drift without an accompanying `@deprecated` fails CI per umbrella §3.4.

2. **NameResolver tokenizer behavior change vs legacy.** Even subtle whitespace normalization in the tokenizer's output could break consumer code that string-matches the SQL output of `replaceNames`. The PBT (F.2.4) verifies token-equivalence on a 10k-input corpus; the fuzzing differential (F.2.5) hardens it; the documented backtick-handling divergence is the sole allowlisted case. **Mitigation:** commit-only-when-zero-diff on the corpus (F.2.5 step 3); allowlist file lists every documented divergence; Round 1 compiler-parser specialist reviews adequacy; Round 2 specialist re-reviews. If a consumer reports byte-divergence post-merge that the corpus missed, file as Phase F.1 patch.

3. **PreparedStatementKey ownership negotiation with Phase E.** Phase E.4 settled the cache-key shape inline (`CachingConnection::buildCacheKey`); F.3 publishes the same shape as a Tier 2 SPI. Consumers that built code against E's static method must continue to work. **Mitigation:** F.3.2 leaves Phase E's `CachingConnection::buildCacheKey` as a delegate to `PreparedStatementKey::forSql` — semantically identical, callable as before. Round 1 architecture reviewer verifies the SPI is a true publication (not a renaming) and that Phase E behavior is unchanged at the cache-hit-rate level.

4. **`Criteria::CUSTOM` deprecation runway risk.** Third-party code uses raw-CUSTOM heavily — it's the documented escape hatch for any SQL the framework can't express. Sudden deprecation noise will be loud. **Mitigation:** the deprecation message includes a concrete migration pointer (`Criteria::customCondition`); migration cookbook (F.7.3) walks worked examples; raw-CUSTOM stays callable forever in 3.x; removal in 4.0 only if a Rector rule is in place. Round 2 ambition reviewer audits the cookbook's coverage.

5. **Enum-vs-constant interop must coexist.** Both `Criteria::EQUAL` and `Comparison::Equal` MUST work in any Tier 1 Criteria method that accepts an operator. The OperatorAcceptor helper (F.1.4) is the load-bearing seam. **Mitigation:** unit tests for both directions on every Tier 1 method that takes an operator; Round 1 BC reviewer probes by passing one form to a method documented as taking the other. The interop is permanent (no deprecation) per umbrella §3.1.

6. **F.8 typed-Criterion DSL namespace collision.** Generated public class names per column (`{TableName}Criterion`); a consumer with a hand-written `BookCriterion` class would collide. **Mitigation:** opt-in via `<table generate-typed-criterion="true">`; default OFF in 3.x. The opt-in is the consumer's signal that they're aware. Migration doc (F.8.5) warns about the naming convention. Round 1 architecture reviewer verifies the opt-in mechanism is robust (cannot accidentally trigger).

7. **Java-Hashtable method deprecation noise on existing test suites.** Every test that calls `Criteria::put`/`putAll`/etc. will trigger one deprecation per call. Phase A's Criteria test suite has many such calls. **Mitigation:** `tests/deprecations.allowlist.json` baseline regenerated at F.6.2; the per-category deprecation lands in the allowlist exactly once. Downstream packages MUST allowlist on their side; the migration cookbook flags this.

8. **`UnsafeCustomConditionException` heuristic false-positive risk.** The conservative flag in `customCondition()` rejects SQL that contains a `'` or `"` even if the consumer has audited it. Consumers will hit this on perfectly safe SQL and need to pass `allowRawSql: true`. **Mitigation:** the exception message names the heuristic flag and the exact escape hatch; migration doc gives the worked-example "audited raw SQL" pattern; Round 2 security reviewer verifies the heuristic is conservative-not-paranoid (false-negative rate is what matters, false-positive rate is acceptable).

9. **`replaceNames` is called in a hot path during query compilation.** Performance regression here would degrade every query. **Mitigation:** F.9.1 includes a microbench of `replaceNames` cost; the tokenizer is `O(n)` over input length, no worse than the legacy `O(n)` regex+callback (and likely faster — the legacy uses regex per non-string segment, which has its own sub-linear cost). Round 2 performance reviewer verifies the bench result.

10. **F.2 PBT seed must be committed for reproducibility.** A flaky tokenizer-equivalence test would erode confidence in the F.2 invariant. **Mitigation:** F.2.4 commits the seed; the corpus in F.2.5 is checked in (not regenerated per CI run). Round 1 compiler-parser specialist verifies seed is stable.

11. **Plan/* extraction may inadvertently change `Criteria`'s observable behavior.** Even verbatim moves can introduce subtle bugs (forgotten property initialization, missed clone semantics, etc.). **Mitigation:** every existing `CriteriaTest` test passes without modification AT EVERY F.4 task commit; the test suite is the regression net. F.4 does not commit if any existing test fails. Round 1 architecture reviewer verifies the move was verbatim.

12. **Deptrac rules for the new namespaces.** `Compiler/*` and `Plan/*` are `Runtime/Internal/`-tier (per Phase A Deptrac rules); they MUST NOT be importable by `Generator/*`. `Operator/*` is Tier 2 published — importable from anywhere. **Mitigation:** F.4 and F.2 each commit a Deptrac rule update; Round 1 quality reviewer verifies. The 233-line Deptrac baseline grows (additively) for the new layer rules.

13. **Generated-code lint parity for the F.8 fixture.** The `TypedCriterionBuilder`'s emitted code MUST pass `composer cs-check && stan && psalm` like hand-written code. **Mitigation:** F.8.3 generates the fixture; F.8.4 + F.9.1 run lint parity against the new tree; Round 1 quality reviewer verifies.

14. **Mutation MSI ≥ 75 (Phase F) target on the new tokenizer is harder than it looks.** Tokenizer state-machine code has many edge cases that are hard to mutate-cover (state transitions). **Mitigation:** the F.2.4 PBT covers a 10k-input corpus, which has high mutation coverage. Round 2 quality reviewer verifies MSI on `Compiler/Tokenizer.php` and `Compiler/NameResolver.php`. Mutation score on `Plan/*` and `Operator/*` is straightforward (pure logic / pure value objects).

15. **Coordination with Phase E's Risk #8 (`SELECT`-vs-`WITH-DELETE` heuristic).** Phase E left a leading-keyword routing-classifier heuristic in `ReplicaRoutingConnection`; `docs/PHASE-E-SUMMARY.md` says "Phase F's tokenizer-based parser will replace the heuristic". Phase F's `Compiler/Tokenizer.php` IS the tokenizer; F.2 is the place to integrate it into the routing-classifier. **Mitigation:** F.2 explicitly notes the future Phase E coupling; the actual integration into `ReplicaRoutingConnection::classifyOperation` is OUT-OF-SCOPE for F (deferred to a Phase E follow-up so that F doesn't mix concerns). The risk is documented; the resolution is deferred.

---

## Out of Scope (explicit)

Phase F explicitly does NOT do:

- **Replacing `Criteria` entirely.** Per umbrella §1.3: "Not building a new ORM. Active Record API stays. ... Not building a new query DSL alongside Criteria." `Criteria` stays Tier 1; Phase F is a structural split + bug fix + enum addition, not an API redesign.
- **Removing the eight Java-Hashtable methods.** Only deprecating; removal in 4.0 (per umbrella §3.2 + §6.1). The 3.x runway is non-negotiable.
- **Removing raw `Criteria::CUSTOM`.** Only deprecating; the 3.x runway is open-ended (removal at 4.0 only if a Rector rule exists). The parameterized `customCondition()` is the migration target.
- **JSON shape DSL for `filterByJsonb`.** Per umbrella §1.3 stretch goal — explicit out-of-scope. Phase F's typed-Criterion DSL (F.8) is the per-column DSL stretch; the JSON-shape variant is its own future cycle.
- **Replacing `ReplicaRoutingConnection::classifyOperation` heuristic with the new tokenizer.** Phase E explicitly left this for "a future cycle" per `docs/PHASE-E-SUMMARY.md` Risk #8. Phase F ships the tokenizer; the integration into `classifyOperation` is a Phase E follow-up (or a small standalone task at F-end).
- **Streaming `Generator`-based formatter.** Phase G — PHP 8.4 substrate.
- **Lazy-object collection treatment of `Plan/*` value objects.** Phase G.
- **`final`-ing `Criteria`.** Per umbrella §10 open question: "whether `Criteria` becomes `final` post-deprecation cycle. Decision deferred to F plan; constraint: cannot break Tier 2 Behavior subclasses." Decision: **NOT in Phase F.** Tier 2 Behavior subclasses extend `Criteria` indirectly; finalizing it would break the extension surface. Re-deferred to a Phase G follow-up.
- **Worker-mode-aware `Plan/*` instances.** Phase J — fiber-safe state.
- **PHP 8.4 `#[\Deprecated]` attribute migration on the eight Java-Hashtable methods.** Phase G — PHPDoc `@deprecated` + `trigger_deprecation` is the 3.x convention per umbrella §3.4.
- **Removing `ConnectionFactory::$useProfilerConnection` static.** Phase E left this as a future-cycle target; Phase F does not touch.
- **Adapter-side normalized lag value standardization.** Phase E left per-adapter SQL; cluster-topology handling is out-of-scope here.

---

## Self-Review Checklist (writing-plans skill)

**Spec coverage check:**

| Umbrella spec promise (Phase F from §2.2 + §5 + §6.1 + §6.2 + §6.4) | Task |
|---|---|
| `ActiveQuery/Compiler/NameResolver.php` (tokenizer-based) | F.2.1, F.2.2, F.2.3 |
| `ActiveQuery/Compiler/PreparedStatementKey.php` (Tier 2 SPI; Phase E coordination) | F.3.1, F.3.2 |
| `ActiveQuery/Plan/JoinPlan.php` | F.4.1 |
| `ActiveQuery/Plan/WhereTree.php` | F.4.2 |
| `ActiveQuery/Plan/OrderClause.php` | F.4.3 |
| `ActiveQuery/Operator/Comparison.php` enum | F.1.1 |
| `ActiveQuery/Operator/JoinType.php` enum | F.1.2 |
| `ActiveQuery/Operator/SortOrder.php` enum | F.1.3 (a) |
| `ActiveQuery/Operator/LogicalOperator.php` enum | F.1.3 (b) |
| `Criteria.php` slim-down to ~600 LOC | F.4 + F.5 |
| Tier 1 ~38 constants frozen (snapshot stable) | F.5.4 (DoD gate) |
| Java-Hashtable methods (`put`/`putAll`/`get`/`keys`/`containsKey`/`keyContainsValue`/`size`/`equals`) deprecated | F.6.1 |
| Hand-rolled `replaceNames` SQL parser rewrite (umbrella §6.1) | F.2.1, F.2.2, F.2.3 |
| `Criteria::CUSTOM` parameterized alternative (umbrella §6.2 risk #2) | F.7.1, F.7.2 |
| Per-column typed Criterion classes (opt-in stretch — umbrella §6.4) | F.8.1, F.8.2, F.8.3, F.8.4, F.8.5 |
| `Comparison::Equal->value === Criteria::EQUAL` contract test (umbrella §4.11 + §7.2) | F.1.1 step 2 |
| Token-equivalence PBT against legacy implementation (umbrella §7.2 Phase F) | F.2.4 |
| Fuzzing corpus differential (umbrella §4.14 Phase F row) | F.2.5 |
| Mutation MSI ≥ 75 (umbrella §4.4 Phase F threshold) | DoD #5 |
| Operator interop (both forms accepted forever) | F.1.4 |
| HIGH-RISK 3-round review cadence (umbrella §4.13.3) | Round 1 / Round 2 / Round 3 checkpoints above |
| Compiler/parser specialist + Security reviewer (umbrella §4.13.2) | Round 1, Round 2 |

**Type-consistency check:** All file paths verified against `src/Propel/Runtime/ActiveQuery/Criteria.php` (2526 LOC pre-phase, verified via `wc -l`), `src/Propel/Runtime/ActiveQuery/ModelCriteria.php` (2635 LOC), the 19 existing files in `src/Propel/Runtime/ActiveQuery/Criterion/` (verified via `ls`), `src/Propel/Runtime/Connection/Internal/CachingConnection.php` (Phase E), `tests/snapshots/`, `tests/Fixtures/bookstore/build/golden/`. Specific line citations: `Criteria.php:2174–2230` (`replaceNames` body), `:564, :576, :590, :838, :858, :877, :1760, :1773` (the eight Java-Hashtable methods).

**Placeholder scan:** No TBDs in tasks; no "TODO" markers; every task has a primary file, a concrete change, a verify command, and a commit-message stub. Two intentionally-resolved-at-task-time decisions: (1) the heuristic flag set in `UnsafeCustomConditionException` (F.7.1 step 1; conservative-not-paranoid; Round 2 security reviewer verifies); (2) the `Criteria.php` post-F LOC (F.5.3 measures; ≤650 target, ≤700 hard cap).

---

## Execution Handoff

Phase F is HIGH-RISK 3-round per umbrella §4.13.3. Recommended execution shape:

**1. Subagent-Driven (recommended for F.1, F.3, F.6, F.7)** — fresh subagent per task; well-scoped enum-publication, SPI-publication, deprecation-trigger, and cookbook work. Each task is 1–3 commits and clean-context per task.

**2. Inline Execution (recommended for F.2 + F.4 + F.5 + F.8)** — `NameResolver` + tokenizer + PBT + fuzzing (F.2) is a chain of related work where the corpus and the tokenizer-state-machine evolve together. `Plan/*` extraction (F.4) requires holding `Criteria`'s 9-responsibility map in working memory across three sub-tasks. `Criteria` slim-down (F.5) is the audit pass that benefits from continuity. F.8 is a stretch group with builder + fixture + tests as a tight cluster.

**3. Mixed (recommended for F.9)** — inline for the DoD audit (F.9.1); subagent for the summary writing (F.9.2).

Always: per-task `composer test:agnostic` + `composer stan` (baseline monotonic) + signature-diff check. The fuzzing corpus differential is the F.2 exit gate — every input must produce byte-identical output (or land in the allowlisted-divergences file) before F.3 starts.

Per-task signature-diff check on `Criteria.signatures.json`: only the additive `customCondition` from F.7.1 may appear. On `criteria-constants.txt`: MUST be empty diff. On `ModelCriteria.signatures.json`: empty diff (the F.5.2 parallel slim-down is pure refactor).

Round 1 mid-phase trigger: `git log --oneline tests/Propel/Tests/Runtime/ActiveQuery/Plan/JoinPlanTest.php tests/Propel/Tests/Runtime/ActiveQuery/Compiler/NameResolverTest.php` shows tasks F.1–F.4 landed.
Round 2 end-phase trigger: `git log --oneline docs/PHASE-F-SUMMARY.md` shows F.9.2 committed.
Round 3 post-merge canary trigger: `git log --oneline integration` shows Phase F merged 7 days ago + ecosystem-advisory CI has run.

---

## Phase F Retrospective

(Filled at phase exit; mirrors Phase E.)
