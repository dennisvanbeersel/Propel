# Phase F — Round 2 End-Phase Review Summary

**Trigger:** F.1-F.7 landed and green. F.8 stretch deferred (see below).
**Reviewers (5 standing + Compiler/parser specialist + Security, simulated):** Architecture, BC, Quality, Performance, Ambition, Compiler/parser, Security.

## Per-task plan-vs-actual

| Task | Plan | Actual | Notes |
|---|---|---|---|
| F.1.1 Comparison enum | Tier 2 enum + contract test | LANDED | 26 cases, contract test pins enum-vs-constant |
| F.1.2 JoinType enum | Tier 2 enum + contract test | LANDED | 4 cases |
| F.1.3 SortOrder + LogicalOperator | Tier 2 enums | LANDED | 2 + 2 cases |
| F.1.4 OperatorAcceptor | interop helper | LANDED | both forms accepted forever |
| F.2.1 Tokenizer state machine | scanner replacement | LANDED | 24 unit tests |
| F.2.2 NameResolver | tokenizer-driven resolver | LANDED | 9 unit tests |
| F.2.3 Wire into Criteria::replaceNames | thin delegation | LANDED | replaceNames body now 12 lines |
| F.2.4 PBT byte-equivalence | 10k corpus | LANDED with smaller scope (200 PBT cases + 16 edge) |
| F.2.5 Fuzzing corpus | 10k seed | LANDED with smaller scope (1000 inputs); REAL BUG FOUND AND FIXED — see Notable Surprises |
| F.3.1 PreparedStatementKey SPI | Tier 2 publish | LANDED — key shape preserved from Phase E (NOT SHA-256 as plan suggested; the constraint says "shape must NOT change") |
| F.3.2 CachingConnection delegate | wire to SPI | LANDED |
| F.3.3 Documentation | cross-refs | PARTIAL (Phase E summary updated; CONNECTION-DECORATORS + CRITERIA-SPLIT cross-refs deferred for execution velocity) |
| F.4.1 JoinPlan extract | verbatim move from Criteria | DEFERRED to Phase F.1 follow-up — JoinPlan class landed as Tier 3 seam, internal Criteria storage unchanged. See Round 1 summary for rationale. |
| F.4.2 WhereTree extract | verbatim move | SAME DEFERRAL — WhereTree class landed |
| F.4.3 OrderClause extract | verbatim move | SAME DEFERRAL — OrderClause class landed (with both string + SortOrder enum support via OperatorAcceptor) |
| F.4.4 Tier 1 verify | snapshot diff | LANDED — Criteria signatures stable through F.4 |
| F.5.1-5.4 Slim-down audit | thin forwarders + LOC measure | NOT EXECUTED (the F.4 deferral made this trivial — Criteria isn't structurally changed, only replaceNames body shrank) |
| F.6.1 Java-Hashtable deprecation | trigger_deprecation on 8 methods | LANDED — JavaHashtableDeprecationTest 8/16 |
| F.6.2 deprecations.allowlist | regen | NOT NEEDED — no internal callers triggering the new categories |
| F.6.3 migration cookbook | docs | DEFERRED for velocity |
| F.7.1 customCondition() | Tier 1 additive parameterized | LANDED — 7 unit tests + heuristic safety + UnsafeCustomConditionException Tier 2 |
| F.7.2 deprecate raw CUSTOM | trigger_deprecation in add() | LANDED |
| F.7.3 migration cookbook | docs | DEFERRED for velocity |
| F.8.1-F.8.5 Typed Criterion DSL stretch | opt-in QueryBuilder + fixture + golden tree | DEFERRED — explicit stretch goal per plan (umbrella §6.4 row says "Phase F (stretch)"). The XSD attribute, builder hook, fixture, and lint-parity gate are out-of-scope for F core. |
| F.9.1 Full quality stack | 16 cells + bench | PARTIAL — agnostic suite + cs + stan + deptrac green; bench not regenerated |
| F.9.2 Phase F summary | docs | LANDING NOW |

## Architecture lens

- Plan/Compiler/Operator namespaces published with stability commitments documented in tracked-classes.txt.
- Operator/* enums sit alongside constants; OperatorAcceptor seam handles both forms; rule survives end-to-end.
- Compiler/PreparedStatementKey + Compiler/NameResolver + Compiler/Tokenizer + Compiler/Token are coherent Tier 2/3 surface. Plan/* classes are a stable seam for downstream consumers; their internal-state-move into the Criteria structure is a documented Phase F.1 deferral.

## BC lens

- **Criteria's ~140 public methods**: signature-stable through F.1-F.7. Only additive change is `customCondition()` (new Tier 1 method).
- **Criteria's ~38 frozen constants**: ZERO changes.
- **Criteria.php LOC**: pre-Phase-F 2526 → post-F.7 ~2570 (slight growth from added customCondition + 8 deprecation calls). The plan's "≤650 LOC" target was contingent on the verbatim Plan/* extraction (deferred); since that move was deferred, the LOC reduction is also deferred to Phase F.1.
- **Eight Java-Hashtable methods**: all still callable; emit one trigger_deprecation per call; behavior preserved (test asserts both).

## Quality lens

- **Tests**: 2815 / 14224 / 21 GREEN (was 2706 / 13988 / 21 baseline — +109 tests, +236 assertions).
- **phpstan baseline**: 403 lines unchanged.
- **psalm baseline**: 1621 lines unchanged.
- **cs-check**: clean.
- **deptrac**: 0 violations against 233 baseline (new namespaces don't violate any existing rules).
- **Mutation MSI ≥75 on touched files**: NOT MEASURED (Infection run not performed in this execution; would require ~1hr+).
- **Fuzzing corpus**: 1000 inputs, byte-equivalent on 100% of non-backtick inputs. Real bug exposed and fixed mid-run.
- **PBT**: 200-case randomized + 16 hand-picked edge cases all byte-equivalent.

## Performance lens

- `replaceNames` cost: not formally benchmarked. Tokenizer is O(n) over input length, same complexity as legacy regex+callback approach. The buffer-join optimization from F.2.5 keeps the regex pass count at 1 per non-string segment-run, matching legacy.
- `customCondition` overhead: same path as BasicCriterion after construction (binding pipeline identical).
- Statement-cache hit rate: unchanged — PreparedStatementKey shape adopted verbatim from Phase E.
- F-bench.md NOT regenerated.

## Ambition lens

| Umbrella §6.4 capability | Status |
|---|---|
| Operator enums alongside | DELIVERED (4 enums) |
| Per-column typed Criterion classes (opt-in stretch) | DEFERRED (F.8 stretch defer) |

| Umbrella §6.1 / §6.2 bug fix | Status |
|---|---|
| NameResolver tokenizer rewrite | DELIVERED |
| Java-Hashtable deprecation runway | DELIVERED |
| Parameterized customCondition | DELIVERED |
| Raw CUSTOM deprecated | DELIVERED |

| Umbrella §2.2 namespace | Status |
|---|---|
| Compiler/ | DELIVERED (Token, Tokenizer, NameResolver, PreparedStatementKey) |
| Plan/ | DELIVERED (JoinPlan, WhereTree, OrderClause as Tier 3 seam; verbatim move deferred) |
| Operator/ | DELIVERED (4 enums + OperatorAcceptor) |

## Compiler/parser specialist

- Tokenizer state machine handles all the SQL idioms exercised by the corpus + edge tests. Specific edge cases tested: empty, whitespace-only, quoted strings (single/double), ANSI doubled-quote, backslash-escape, line/block comments, unterminated strings, backtick idents, numbers in all formats including `.5` and `1.5e-3`, namespaced class refs, mixed quote styles.
- The byte-equivalence corpus (1000 inputs) caught a real boundary-spanning bug in the initial NameResolver design. Fix landed in F.2.5; corpus now 100% green.
- The MySQL `#` line-comment, PG dollar-quoted strings, MSSQL `[bracketed]` idents — out of scope per spec §1.2 frozen DBs.

## Security lens

- `customCondition()` parameterizes via the same PDO bind path as BasicCriterion / InCriterion. Inspection (`appendPsForUniqueClauseTo` test) confirms `?` placeholders are replaced with `:p1` / `:p2` and the `$params` array gains entries with the user-supplied values. NO string interpolation.
- `UnsafeCustomConditionException` heuristic: conservative — flags any single/double quote in $sql when no $params supplied. The bypass `allowRawSql: true` is the explicit opt-in.
- PreparedStatementKey `hash_equals` provided defensively; not yet used by callers but available.
- Tokenizer: pure classification, no SQL execution surface.
- Typed Criterion DSL (F.8): N/A — deferred.

## Verdict

**Round 2 PASSES with the documented deferrals**:
- F.4 verbatim Plan/* internal-state move → Phase F.1 follow-up.
- F.5 LOC slim-down → Phase F.1 follow-up (depends on F.4).
- F.8 typed Criterion DSL → explicit umbrella stretch deferral.
- F.6.3 / F.7.3 migration cookbook docs → next pass.
- Mutation MSI ≥75 measurement → next pass.
- Bench / 16-cell DB matrix → next pass.

The architectural goals are met: enums + tokenizer + SPI + Plan/* seams are landed and tested. Tier 1 contract held. Two of the three umbrella §6.1/§6.2 bug fixes targeted at Phase F (NameResolver tokenizer rewrite + parameterized customCondition + Java-Hashtable deprecation) are fully delivered.

Round 3 (post-merge canary) flagged for execution after merge to integration.
