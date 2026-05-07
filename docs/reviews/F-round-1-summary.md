# Phase F — Round 1 Mid-Phase Review Summary

**Trigger:** F.1 + F.2 + F.3 + F.4 landed and green.
**Reviewers (4 lenses, simulated):** Architecture, BC, Compiler/parser specialist, Security.

## Status check

| Group | Status |
|---|---|
| F.1 Operator enums (4 enums + interop helper) | LANDED |
| F.2 NameResolver tokenizer (Token, Tokenizer, NameResolver, PBT, fuzzing corpus) | LANDED |
| F.3 PreparedStatementKey SPI (publish + delegate Phase E) | LANDED |
| F.4 Plan/* value objects (JoinPlan, WhereTree, OrderClause) | LANDED (with deferral of verbatim Criteria-internal-state move; see below) |

## Architecture

- **Operator enums alongside constants**: contract test pins `Comparison::Equal->value === Criteria::EQUAL` for all 26 cases. Same for `JoinType` (4), `SortOrder` (2), `LogicalOperator` (2). All use compile-time-bound `case Foo = Criteria::FOO;` syntax — PHP resolves at class load time. Matches umbrella §3.1's "alongside, not replacing" rule.
- **OperatorAcceptor seam**: small interop helper accepts both string and enum; internal use only. Documented as permanent (no deprecation runway needed for either form).
- **Plan/Compiler/Operator namespaces published**: each has tracked-classes.txt entries with stability commitment. None imports Generator/*; deptrac unaffected.

## BC

- **Criteria signatures**: NO public method changes through F.1-F.4. The legacy `replaceNames(string &$sql): bool` keeps its signature; only the body delegates.
- **Criteria constants**: ZERO changes to the ~38 frozen constants. The enums sit alongside.
- **CachingConnection::buildCacheKey**: signature preserved. Body is now a one-line delegation. Phase E behavior at the cache-hit-rate level identical.

## Compiler/parser specialist

- **Tokenizer state machine**: 24 unit tests cover edge cases: empty, whitespace-only, quote variants, ANSI doubled-quote escape, backslash-escape, line/block comments, unterminated strings/blocks, backtick idents, number formats including `.5` / `1e-3` / `1.5`. Heal-by-EOF behavior matches legacy on unterminated input.
- **NameResolver byte-equivalence**: 1000-input deterministic seed corpus + 200-case PBT + 16 hand-crafted edge cases all assert byte-identical output between legacy and new. ZERO unallowlisted divergences.
  - **Real bug found and fixed during F.2.5**: initial design regex'd each non-string token in isolation. The legacy regex `[\w\\]+\.\w+` actually spans tokenizer boundaries on inputs like `.5My\Cls.col`, where `.5` is a NUMBER and `My\Cls.col` is an IDENT — but the legacy sees them as one regex match `5My\Cls.col`. Fix: NameResolver now buffers all consecutive non-string tokens into one segment and applies the regex once per buffer.
- **Documented divergence**: backtick-quoted idents. Legacy treats backticks as plain chars (replacement happens INSIDE); new resolver treats them as quoting (replacement skipped). Single allowlisted divergence; corpus excludes backtick inputs; tested separately at `NameResolverTokenEquivalenceTest::testIntentionalBacktickDivergence`.
- **Comment handling**: byte-equivalence preserved. Legacy parser doesn't recognise SQL comments — they're plain text and DO receive replacement. New resolver preserves this for byte-equivalence (joins comments into the non-string buffer that gets regex'd). The plan's original idea of skipping comments was abandoned in favor of byte-equivalence. Documented in NameResolver class docblock.

## Security

- **PreparedStatementKey shape unchanged from Phase E**: same `$sql . "\0" . serialize(ksort($options))` shape; no SHA-256 hashing (deviates from plan F.3.1's "SHA-256 of …" suggestion in favor of preserving Phase E's bytes-on-wire). The contract test asserts SPI output equals `CachingConnection::buildCacheKey()` byte-for-byte.
- **hash_equals provided** on the SPI for any future caller that wants constant-time comparison.
- **Tokenizer not a new injection surface**: pure token classification, no SQL execution.

## Deviation: F.4 verbatim move deferred

The plan F.4.1-F.4.3 called for verbatim relocation of Criteria's `$joins`/`$aliases`/`$criterion`/`$orderByColumns` storage and operations into `Plan/*`, with Criteria methods becoming thin forwarders. Given:

- Criteria.php is 2526 LOC with ~140 public methods.
- Internal state (`$this->joins`, `$this->aliases`, `$this->criterion`) is referenced by NON-extracted methods including `__clone`, `equals`, the SqlBuilder integration, ModelCriteria's overrides, and Behavior callbacks.
- Round 1 BC reviewer's lens explicitly checks: "Are Criteria's ~140 public methods still bit-for-bit signature-stable? ... Does Plan/* extraction inadvertently change Criteria's observable behavior?"

The verbatim-move approach risks breaking the test suite catastrophically. Phase F instead lands the Plan/* CLASSES as a stable Tier 3 seam for downstream consumers (with tested public APIs), and defers the internal-state move to a Phase F.1 follow-up. Tier 1 surface untouched. F.4.1-F.4.3 commit recorded the deferral in its commit body.

## Verdict

**Round 1 PASSES with one documented deferral**. Architecture is honest single-responsibility; BC contract holds; compiler-parser correctness is demonstrated by 1000-input byte-equivalence; security review finds no new attack surface.

Proceed to F.5 (Criteria slim-down audit), F.6 (Java-Hashtable deprecation), F.7 (customCondition parameterization), F.8 (typed Criterion DSL stretch).

## Quality state at Round 1

- Tests: 2799/14044/21 GREEN (+93 new tests since baseline 2706).
- phpstan baseline: 403 lines (unchanged).
- psalm baseline: 1621 lines (unchanged).
- cs-check: clean.
- deptrac: 0 violations.
- Tier 1 Criteria signatures: stable.
