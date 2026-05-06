# Phase A — Round 1 Architecture & SOLID Review

**Reviewer lens:** Architecture & SOLID, with a focus on whether the foundational gates installed in A.1–A.13 are actually load-bearing.
**Range under review:** `7fa030e29..fc1b7799b`.
**Verdict:** The foundational scaffolding is in place but several gates are **theatre, not enforcement**. Two MUST-FIX items will silently let regressions ship; several SHOULD-FIX items hollow out the BC promise. Tone is harsh by design — work is mid-phase, fix now is cheap.

---

## MUST-FIX (blocks merge of A.14–A.44 onto integration)

### M1. The Symfony bridge baseline file shape is wrong; CI will throw `InvalidArgumentException` on first run.

`tests/deprecations.allowlist.json` is `{"deprecations": [], "_comment": "..."}`. Symfony bridge expects a **flat JSON array** of `{location, message, count}` objects: see `vendor/symfony/phpunit-bridge/DeprecationErrorHandler/Configuration.php:145-148`:

```php
$map = json_decode(file_get_contents($this->baselineFile));
foreach ($map as $baseline_deprecation) {
    $this->baselineDeprecations[$baseline_deprecation->location][$baseline_deprecation->message] = $baseline_deprecation->count;
}
```

`foreach` over a stdClass with property `_comment` (string) will attempt `->location` access on the string and either fatal or warn-then-NULL — silently mis-populating the baseline map. This is wired into all four phpunit configs (`tests/agnostic.phpunit.xml:29`, `tests/mysql.phpunit.xml:36`, `tests/pgsql.phpunit.xml:37`, `tests/sqlite.phpunit.xml:37`). The plan at `docs/plans/2026-05-06-phase-a-foundations.md:141-145` correctly told you to *generate* the file via `generateBaseline=true`; whoever shipped this commit hand-rolled an empty placeholder with the wrong schema. Drop the wrapper object — make it `[]` (empty array literal) until A.25/A.28/A.29 add real entries.

### M2. The `lint-generated` quality gate silently skips when generation fails — i.e., always.

`.github/workflows/quality-gates.yml:96-103`:

```yaml
- name: Regenerate bookstore fixture
  run: vendor/bin/phpunit -c tests/agnostic.phpunit.xml --filter=BookstoreBuildTest --no-coverage || true
- name: phpcs against generated bookstore
  if: hashFiles('tests/Fixtures/bookstore/build/classes/**/*.php') != ''
```

There is **no test class named `BookstoreBuildTest`** anywhere in the repo (`grep -rln BookstoreBuildTest tests/` returns zero hits). The phpunit invocation matches zero tests, exits non-zero, `|| true` swallows it, and the `hashFiles(...)` guard then evaluates to `''` because `tests/Fixtures/bookstore/build/` is gitignored (`.gitignore:21`). Both phpcs and phpstan steps are skipped on every CI run. The job reports green without ever lint-checking generated output. The plan at `docs/plans/2026-05-06-phase-a-foundations.md:732-733` and `:857-858` originated this filter as `|| true` "best-effort"; that is incompatible with §4.6's "Generated code must pass the same bar as hand-written code." Drive generation via `bin/propel test:prepare --vendor=sqlite --dsn=...` (see `tests/bin/setup.sqlite.sh`) and **let the step fail loudly** if generation fails. Drop `|| true`; drop the `hashFiles` guards.

### M3. Tier 1 surface from spec §3.1 is materially incomplete; signature-diff gate cannot enforce what it doesn't track.

`tests/snapshots/tracked-classes.txt` lists 13 classes. Spec §3.4 explicitly says the gate's snapshot location is `tests/snapshots/{Base_Book.php,Base_BookQuery.php,Base_BookTableMap.php,...}.signatures.json` — i.e., the **generated bookstore Base classes** are the primary load-bearing surface (`QueryBuilder` output is "the BIGGEST consumer surface" per §3.1). Zero generated classes are tracked. Tier 2 from spec §3.2 is also entirely missing:

- All 19 `Runtime/ActiveQuery/Criterion/*` classes (per spec line 202).
- `PropelException` + 8 typed subclasses in `src/Propel/Runtime/Exception/` (per spec line 203).
- `PropelDateTime`, `PropelModelPager`, `Profiler`, `UuidConverter` in `src/Propel/Runtime/Util/` (per spec line 204).
- `OnDemandCollection`, `ConnectionManagerInterface`, `ConnectionManagerSingle`, `ConnectionManagerPrimaryReplica`, `StatementWrapper`, `ConnectionWrapper` (per spec lines 205-207).

Until these land, the gate is enforcing about 8% of what spec §3 promised. The fact that A.10 step 2 (`docs/plans/2026-05-06-phase-a-foundations.md:683-687`) explicitly told the implementer to regenerate the bookstore fixture and append generated classnames means this was *known* and *skipped*.

---

## SHOULD-FIX (response required: fix or waiver)

### S1. The "BC-safe widening allowlist" mentioned in the gate is a comment, not code.

`.github/workflows/quality-gates.yml:44` advertises that "covariant return types, contravariant params, added optional params with defaults" are allowed widenings. The actual diff is a **byte-exact `diff -u`** at line 40. Any covariant return-type narrowing (the legitimate LSP-safe case) fails the gate. Phase B/B'/F changes will routinely add return types to currently-untyped methods, which is BC-safe widening per spec §3.4 — every such change will trip the gate and require manual snapshot regeneration with no signal that the change was actually safe. Either implement a real allowlist (e.g., a small PHP differ that classifies changes), or drop the misleading comment from the error message so reviewers don't assume the gate is smarter than it is.

### S2. Snapshot determinism: `var_export()` of empty array produces `"array (\n)"` — embedded literal newlines in JSON values are an eyesore but worse, they're locale-stable only because var_export ignores locale.

`bin/propel-internal-dump-signatures:90-92` emits `var_export($p->getDefaultValue(), true)`. For `[]` defaults you get `"array (\n)"`, for `0.1` you get `"0.10000000000000001"` on some libcs, and for floats this is **not** locale-independent on PHP <8.1 platforms historically (less so today, but still — `serialize()`/`json_encode()` would be safer for round-tripping). The snapshot already passes for current Tier 1 because no float-defaults exist there — but a generated `BaseBook::setRating(float $v = 0.0)` will surface this. Recommend canonicalize via `json_encode($default, JSON_PRESERVE_ZERO_FRACTION)` for scalars and a `'array{empty}'` sentinel for empty arrays. The current path is a footgun for Phase B.

### S3. Signature dump misses several BC-relevant facets the spec called out.

The script (`bin/propel-internal-dump-signatures:79-119`) does NOT capture:

- **Method order in the source class** (currently sorted by `(visibility, name)` — fine for stability, but spec §3.4 says the diff catches "signature reordering" which is meaningless once both sides are sorted; if you actually want to detect e.g. constructor-position reorders that affect named-argument calls, you need to capture parameter-position, which it does — good — but constructor *property promotion* changes aren't surfaced as "promoted: bool" anywhere).
- **PHPDoc `@return`** — spec §3.4 line 227 explicitly says the snapshot includes `phpDocReturn`; not implemented.
- **Class-level `@method` PHPDoc** — `ActiveRecordInterface` per spec §3.1 line 192 has a "declared `@method toArray()` PHPDoc contract"; not captured.
- **Trait usage** — `kind` distinguishes interface/trait/class but `usedTraits` aren't dumped, so a trait swap on a tracked class doesn't trip the gate.
- **Interface implementations & parent class** — a class silently dropping `Serializable` (cf. spec §6.1, planned for Phase A.21) is a Tier 2 BC break that the snapshot won't catch.

### S4. Composer manifest drift between root `composer.json` and the two CI matrix files.

CI uses `tests/composer/composer-symfony7-min.json` and `composer-symfony7-max.json` per `.github/workflows/ci.yml:57`. Neither contains:

- `infection/infection`
- `deptrac/deptrac`
- `innmind/black-box`

…nor the `infection/extension-installer` allow-plugin. So **every CI matrix run is using stale dev deps**, which means: (a) Infection/Deptrac/PBT cannot run on CI today, (b) `composer test:agnostic` on CI does not exercise the `tests/Propel/Tests/PropertyTests/SmokeTest.php` testsuite under Symfony 7-min/max because `Innmind\BlackBox\Set` won't autoload there. The signature-diff job uses the root `composer.json` (`.github/workflows/quality-gates.yml:23`) so it's fine, but the CI matrix is silently weakened.

### S5. Black-box has no seedable RNG; Phase F's `replaceNames` PBT will lack reproducible failures.

`vendor/innmind/black-box/src/Random.php:14-33` exposes only three engine choices (`default`/`secure`/`mersenneTwister`); none accept a seed. `Random\Engine\Mt19937` *is* seedable upstream but black-box constructs it without a seed parameter. `Set::sequence()->take(n)->values(Random::default)` therefore produces non-replayable failures. Eris by contrast supported `eris.io.seed=$seed` for shrinking. This is a future-Phase-F regression risk: if a `replaceNames` token-equivalence PBT fails in CI, you cannot re-run the same input. Either upstream a seed-injection PR, vendor a seedable wrapper now, or accept the risk and document it. The umbrella spec §4.11 promised `eris`; switching libraries was correct (eris is PHP-7), but the seedability gap was not explicitly acknowledged in the commit message at `4f9c1f332`. The smoke test at `tests/Propel/Tests/PropertyTests/SmokeTest.php` confirms basic generation but does not exercise reproducibility.

### S6. Deptrac config does not model the SPI axis from spec §3.

`deptrac.yaml` has three layers (Common/Generator/Runtime). The umbrella spec §3 introduces a third axis — `ConnectionDecoratorInterface`, `ObjectBuilderApi`, `PreparedStatementKey` — and §4.5 explicitly mandates a Tier 2 contract layer where "`Behavior` MUST consume only `ObjectBuilderApi`, never `ObjectBuilder` concrete." Phases B' (§5) and E (§5) introduce these; they're not in scope for A.1–A.13, but **a placeholder TODO comment in `deptrac.yaml` is required** so reviewers landing B'/E know to extend rather than rewrite. Currently the file looks like the layer model is final. Add a `# TODO Phase B'/E: add SPI axis (ObjectBuilderApi, ConnectionDecoratorInterface)` comment.

The baseline `deptrac-baseline.yaml` also has 99+ skip-violations baked in with **no monotonic check** — `quality-gates.yml:117` runs `composer deptrac` but never asserts the baseline shrinks. Spec §4.1 monotonic principle applies to PHPStan/Psalm; the same logic for Deptrac is missing. Compare with `tools/check-baseline-monotonic.php` which only checks the two static-analysis baselines.

### S7. The `baseline-monotonic` job's `git fetch --depth=1` is insufficient for non-shallow base refs.

`.github/workflows/quality-gates.yml:76` fetches `origin/${{ github.base_ref || 'master' }}` at depth 1. `actions/checkout@v4` with `fetch-depth: 0` (line 66) already has full history, so the fetch is redundant — but the `BASE_REF` variable on line 73 expands to `origin/${{ github.base_ref }}` which is **empty for `push` events** (`github.base_ref` is only populated on PRs). For a push to `ar-rewrite`, `BASE_REF=origin/` and `tools/check-baseline-monotonic.php origin/` will produce a `git show origin/:phpstan-baseline.neon` failure → `countLinesAtRef` returns null → "new (no base to compare against)" → silent pass. So the gate is a no-op on direct pushes to integration. Fix: in the `else` branch on line 74 use `origin/master` (already done), but rewrite line 76 to drop the conditional and just always set `BASE_REF=origin/master` for non-PR runs. Or simpler: pass the chosen ref through unconditionally.

### S8. PHPUnit metadata migration: spot-check confirms semantics preserved, but one file was hand-fixed and reviewers should verify.

`tests/Propel/Tests/Common/Util/SetColumnConverterTest.php:28,73` — `#[\PHPUnit\Framework\Attributes\DataProvider('convertValuesProvider')]` placed correctly; data provider method preserved at line 102. `tests/Propel/Tests/BookstoreLoggingTest.php:28,141-142` — `#[Group('database')]` and the testQueryJoins multi-group migration at lines 141-142 are correct. `tests/Propel/Tests/Runtime/Connection/TransactionTraitTest.php` — `getMockForTrait()` was replaced by an explicit `TransactionTraitTestHarness` abstract harness class; this is a behavioral change (mock target moves from anonymous trait-mock to a named harness) but the methods mocked (`beginTransaction`, `commit`, `rollBack`) match the trait's caller surface and tests still pass per the commit log (2386 / 5126 / 21 skipped). Acceptable. The `e06188773` commit message notes one hand-fix in `QuotingTest.php` (Group import collision); that's flagged for human review at end-phase Round 2.

The `5bead168f` commit's `willReturnCallback` workaround in `ModelTestCase.php` (for null defaults clashing with strict return types) is a legitimate fix, not a weakening. Documented in commit message; trail is clean.

---

## NICE (advisory, not blocking)

### N1. The dump script's failure handling halts after writing partial output.

`bin/propel-internal-dump-signatures:46-77`: failures (class-not-found) are accumulated but the script continues to write snapshots for found classes. Combined with `exit(2)` at the end, you can end up with partially-written snapshots committed. Acceptable for a tracked-list workflow; harden if it ever becomes load-bearing (e.g., delete partial output on failure).

### N2. `var_export` of `null` produces `"NULL"` (uppercase string) which renders confusingly next to the JSON literal `null` used for "no default available."

`tests/snapshots/Propel_Runtime_Propel.signatures.json:44` — `"default": "NULL"` for `?string $name = null` parameters. A reviewer scanning the diff has to know this convention. Consider `null` for both, and add `"hasDefault": true|false` to disambiguate. Cosmetic; doesn't break determinism.

### N3. Phase A plan task ordering: A.10 explicitly told the implementer to add bookstore Base classes; A.10 was marked done in the commit `8c608a176` without that step. Update the plan's checkbox status to reflect partial completion, or rebase A.10 to be split into A.10a (interfaces) / A.10b (generated classes after A.42 fixture regen).

### N4. `infection.json5` has `minMsi: 65` and `minCoveredMsi: 75` (line 18-19) — the umbrella spec §4.4 says MSI ≥ 75 for Phase E/F exit on touched files only. The current config applies it globally, which will fail on the first Infection run because nothing in `Runtime/Connection|ActiveQuery|ActiveRecord|Map|Adapter` has been hardened yet. Either gate Infection on Phase E/F entry, or document that the threshold is "track-only, not enforce" until Phase E.

### N5. Plan vs spec consistency: Phase A plan §"Group 4" (A.14–A.20, bug fixes) is consistent with spec §6.2. Pending tasks A.14–A.44 do not appear redundant given what A.1–A.13 shipped — none of the bug fixes, dead-code kills, alias-deprecations, or `#[\Override]` sweep have been started. No drift detected.

---

## Summary scorecard

| Gate | Status | Severity if not fixed |
|---|---|---|
| `signature-diff` | Wired but tracks 8% of Tier 1+2 surface | M3 — the gate is window-dressing |
| `lint-generated` | Silently skipped on every run | M2 — generated-code regressions ship undetected |
| `baseline-monotonic` | Works on PRs, no-ops on pushes | S7 — direct integration pushes bypass |
| `deptrac` (CI) | Runs; no monotonic check | S6 — baseline can grow |
| Symfony bridge deprecation telemetry | File shape will fault on first run | M1 — first PR fails inexplicably |
| Composer matrix consistency | Drift between root and 7-min/max | S4 — matrix runs with stale deps |
| PBT infrastructure | Smoke test green; not seedable | S5 — Phase F reproducibility blocker |

**Recommendation:** Block A.14 start until M1, M2, M3 close. S1–S7 may proceed in parallel with bug-fix tasks but must close before end-of-phase Round 2.
