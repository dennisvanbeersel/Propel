# Phase A — Iteration Log

Per umbrella spec §4.15.3: iteration cycles are tracked here. Budget is 3 per round.

## Round 1 (mid-phase, after Tasks A.1–A.13)

### Cycle 1 — Round 1 fix sweep

**Triggered by:** 8 MUST-FIX + 5 SHOULD-FIX findings across 3 reviewer reports (`A-round-1-{architecture,bc,tooling}.md`).

**Resolution commits:**
- `1e6f8eed2` fix(infection): file move setup for working configDir
- `8f67794a3` fix(infection): point at tests/ configDir; drop duplicate --configuration arg
- `39d3cf6f8` fix: address Round 1 review MUST-FIX + SHOULD-FIX findings (bulk fix; details in commit message)

**Findings status:**
- 8/8 MUST-FIX → closed in this cycle.
- 1/5 SHOULD-FIX → closed (composer manifest drift).
- 4/5 SHOULD-FIX → waived per `A-waivers.md` with documented reasoning + future-reviewer/plan confirmation.
- NICE findings → tracked in summary; no action.

**Verify-mode re-engagement:** none required this cycle (fixes are mechanical and visible in diffs). Round 2 reviewers will validate end-to-end.

**Cycles remaining in Round 1 budget:** 2 of 3 unused. Cycle 1 sufficient.

## Round 2 (end-phase pre-merge, after Tasks A.14–A.42)

_Pending phase completion._
