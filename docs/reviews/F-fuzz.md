# Phase F — NameResolver Fuzz Differential Report

**Plan:** `docs/plans/2026-05-07-phase-f-criteria-split.md` task F.2.5.

## Corpus

- File: `tests/Fuzzing/ActiveQuery/corpus/seed.txt`
- Size: 1000 inputs (deterministic; `mt_srand(42)` seed; one input per line)
- Generator: composes randomly from a fixed pool of qualified names, string
  literals (single + double-quoted, doubled-escape, backslash-escape,
  unterminated), operators, numeric literals, and punctuation.
- Backticks are NOT in the generator pool — backtick handling is a
  documented divergence between the legacy parser and the new
  tokenizer-driven NameResolver, and is asserted as a separate test
  (`NameResolverTokenEquivalenceTest::testIntentionalBacktickDivergence`).

## Differential check

Every input is fed through both:

1. `LegacyReplaceNames::run()` — verbatim snapshot of the pre-Phase-F
   char-by-char scanner.
2. `NameResolver::resolveWithMatchesCallback()` — the new tokenizer-driven
   resolver wired into `Criteria::replaceNames` at task F.2.3.

The two outputs MUST be byte-identical. Any divergence on a non-backtick
input fails the gate.

## Findings

- **Token-boundary divergence at first run**: the initial naïve
  implementation regex'ed each non-string token in isolation. The legacy
  joins all consecutive non-string segments into one buffer and regex'es
  the whole buffer — this matters because the legacy regex `[\w\\]+\.\w+`
  spans tokenizer boundaries (e.g. `5My\Cls.col` inside SQL like `.5My\Cls.col`,
  where the new tokenizer splits `.5` as a NUMBER and `My\Cls.col` as IDENT).
  Fix: NameResolver now buffers non-string segments and regex'es the joined
  buffer once per run.

- **Final state**: 1000/1000 corpus inputs byte-identical; 0 unexpected
  divergences; 0 backtick-allowlisted divergences (corpus has none).

## Runtime

The 1000-input differential check completes in well under one second per
CI run.

## Allowlist

- Backtick-quoted segments: legacy treated backticks as plain chars and
  applied replacement INSIDE; new resolver treats backticks as quoting
  and skips them. Documented in `docs/MIGRATION-FROM-PRE-AI.md` and
  asserted in `NameResolverTokenEquivalenceTest::testIntentionalBacktickDivergence`.
