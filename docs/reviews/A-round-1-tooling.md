# Phase A — Round 1 Tooling & CI Review

Range reviewed: `7fa030e29..fc1b7799b` on branch `ar-rewrite`.
Reviewer focus: tooling correctness, CI gate plumbing, reproducibility, false-green risk.

This is harsh by request. Tag legend: **MUST-FIX** blocks Phase-A exit, **SHOULD-FIX** required before Phase B, **NICE** improves rigor.

---

## Findings

### 1. Infection cannot start. **MUST-FIX**

`infection.json5:21` declares `phpUnit.configDir = "tests"`, but the project ships per-DB configs `tests/{agnostic,mysql,pgsql,sqlite}.phpunit.xml`. Infection probes `configDir` for one of `phpunit.xml`/`phpunit.xml.dist`/etc. and refuses to run when none exist:

```
$ composer infection -- --only-covering-test-cases --filter=Propel/Runtime/Map --threads=1 --min-msi=0 --min-covered-msi=0
The path "/Users/dvb/Projects/Propel2/tests" does not contain any of the requested files: "phpunit.xml", "phpunit.yml", "phpunit.xml.dist"...
Script vendor/bin/infection --threads=4 handling the infection event returned with error code 1
```

Spec §4.4 requires Infection to be runnable; commit `c1917f41f` claims the framework is wired but the wiring is broken on the base path. Worse: there is **no CI job for Infection** in `.github/workflows/quality-gates.yml` or `ci.yml` (`grep -i infection .github/workflows/*.yml` → empty), so the failure has been invisible. Not a single mutation has run on this branch. The §4.4 promise is an aspirational comment.

Fix: add a top-level `phpunit.xml.dist` (or rename `tests/agnostic.phpunit.xml` → `tests/phpunit.xml.dist`), or set `phpUnit.configDir` to a directory that does contain a default config. Then add a `quality-gates.yml` job that runs `composer infection -- --only-covering-test-cases --threads=2` on touched-file mutators per §4.4 / §4.9.

Side note: `infection.json5:18-19` already enforces `minMsi: 65 / minCoveredMsi: 75`; once it actually runs, it will fail open-ended on day one. Add an opt-in `--ignore-msi-with-no-mutations` for the bring-up job.

### 2. `lint-generated` job is permanently no-op. **MUST-FIX**

`.gitignore:22` excludes `tests/Fixtures/bookstore/build/` (verified: `git ls-files tests/Fixtures/bookstore/build/` returns nothing). The workflow at `.github/workflows/quality-gates.yml:97` runs:

```
vendor/bin/phpunit -c tests/agnostic.phpunit.xml --filter=BookstoreBuildTest --no-coverage || true
```

There is **no test class named `BookstoreBuildTest`** in the suite (`find tests -name 'BookstoreBuildTest*'` empty; running it locally prints `No tests executed!`). Combined with `|| true`, the regen step is a no-op. Then both `phpcs` and `phpstan` steps gate on `if: hashFiles('tests/Fixtures/bookstore/build/classes/**/*.php') != ''` (lines 99 and 102), and since the directory doesn't exist on a clean checkout, that hash is empty → both steps skip → job exits 0 with green check.

Net effect: the `lint-generated` job lies. Spec §4.6 says "Generated code must pass the same bar as hand-written code"; it is currently never run in CI. Replace the filter with a real generator command (e.g. `bin/propel model:build --schema-dir=tests/Fixtures/bookstore --output-dir=tests/Fixtures/bookstore/build/classes`) or remove the `|| true`, and remove the `if: hashFiles` guards so failure surfaces.

### 3. `composer testsuite` excludes Infection and Deptrac. **MUST-FIX**

`composer.json:69` defines:

```json
"testsuite": "composer run test && composer run cs-check && composer run stan && composer run psalm"
```

Per spec §4.9 every Definition-of-Done bullet must be in the local exit gate. Today a developer running `composer testsuite` doesn't run Deptrac (so layer violations land unreviewed) and doesn't run Infection (so MSI regressions are invisible). Update to `composer run test && composer run cs-check && composer run stan && composer run psalm && composer run deptrac && composer run infection -- --threads=2 --min-msi=65`. Even if Infection is not yet meeting threshold, run it as `--ignore-msi-with-no-mutations` until A is closed.

### 4. `tools/check-baseline-monotonic.php` line-counting asymmetry. **SHOULD-FIX**

The script uses two count methods that disagree on files without trailing newline:

```
$ printf "a\nb\nc" > /tmp/x; ...
fgets:        3
substr_count: 2
```

Local file is counted by `fgets()` (line 58, returns 3 for "a\nb\nc"), base-ref file is counted by `substr_count($output, "\n")` (line 73, returns 2 for the same content). If somebody hand-edits a baseline, drops the trailing newline locally, and pushes — phantom +1 delta and false fail. Worse: an editor that *adds* a trailing newline turns a real growth into perceived stability. Use one method consistently; recommend `count(explode("\n", rtrim($content, "\n")))` for both, or just `substr_count + (str_ends_with(... "\n") ? 0 : 1)`.

Bonus: when the merge-base ref is invalid (`countLinesAtRef` returns null), the script prints `✓ new (no base to compare)` and exits 0 — silent pass. Combined with workflow line 76 (`git fetch ... 2>/dev/null || true`), a fetch failure on CI quietly disables the gate. Either fail loud on missing ref, or distinguish "file didn't exist on base" from "ref couldn't be resolved".

### 5. `bin/propel-internal-dump-signatures` cross-PHP reproducibility traps. **SHOULD-FIX**

Verified: snapshots regenerate byte-identical on PHP 8.4.14 to what is committed. But the script (lines 87-101) has known PHP-version sensitivity:

- `var_export($p->getDefaultValue(), true)` (line 92) renders floats with locale-independent dot, but enum default values get a different repr in PHP ≥ 8.1 vs constants. Enum cases are rendered by `var_export` as `\Namespace\MyEnum::CASE_NAME` whereas pre-enum class constants used a synthetic name; mixing them in tracked classes will produce phantom diffs once Phase E touches enum-defaulted methods.
- `(string) $p->getType()` (line 96) and `(string) $m->getReturnType()` (line 110) are fine for `?Foo`/`Foo|null`/`A&B` in 8.1+, but reflection's canonicalization of *implicitly* nullable parameters changed: `function f(?int $x = null)` is `?int` in 8.0, `int|null` in 8.4 if declared explicitly. The current Tier-1 surface is shallow enough to be safe today, but as Tier 2 expands document the pinned PHP version that produces canonical snapshots (or run a `signature-diff` matrix on both 8.3 and 8.4 — currently the workflow pins 8.3 only at line 19, so 8.4 drift is undetected).
- `getDefaultValueConstantName()` (line 91) returns `self::FOO` for class consts but `MY_GLOBAL` (no leading backslash) for global. Mixing these is fine, but if an upstream class graduates a global constant to a class constant, no signature diff would catch it. NICE: warn when a constant default isn't fully qualified.

### 6. Deptrac: baseline correctly freezes; gate works. **No finding** (verified)

`composer deptrac` exits 0 with `Violations 0 / Skipped 233`. Verified the baseline records pair-keyed (consumer-class → forbidden-target-class) at `deptrac-baseline.yaml:1-89`. Injected a synthetic `use Propel\Runtime\Propel` into `src/Propel/Generator/Application.php`; `composer deptrac` correctly raised `Violations 1 / Skipped 233` and exited 1. File restored. Note: commit message of `4eb6991e3` says 233; the YAML actually contains 53 entries but each entry can have multiple forbidden targets — final reported skipped count is 233. Wording in the commit could be clearer.

### 7. PHPUnit fail-flags pass with strict configuration. **No finding** (verified)

```
$ composer test:agnostic
Tests: 2386, Assertions: 5126, Skipped: 21.  (0 deprecations, 0 warnings)
```

Matches `f85499fea` claim exactly. `tests/agnostic.phpunit.xml:10-16` enables all six fail-on flags; nothing trips them on a clean run.

### 8. Coverage works in CI; documented gap locally. **NICE**

`vendor/bin/phpunit -c tests/agnostic.phpunit.xml --coverage-clover=/tmp/cov.xml` does NOT generate coverage out-of-the-box (warning: "XDEBUG_MODE=coverage has to be set"). With `XDEBUG_MODE=coverage`, runtime jumps from 2.7s → 10.2s and coverage.xml is 1.6 MB / 26572 lines — fine. CI workflow (`.github/workflows/ci.yml:47`) installs `pcov`, so CI is OK. SHOULD-FIX: composer.json should add `test:coverage` script that sets `XDEBUG_MODE=coverage` for devs without PCOV; otherwise developers run with broken coverage and never notice.

### 9. Quality-gates workflow triggers correctly. **No finding**

`quality-gates.yml:3-7` triggers on `pull_request: (any branch)` and `push: branches: [master, ar-rewrite]`. YAML parses clean (`vendor/bin/yaml-lint` → OK). Workflow will fire on the next push.

### 10. Deprecation allowlist regenerate path. **NICE**

`tests/deprecations.allowlist.json` has the regenerate command embedded as a `_comment` string. There is **no `composer regen-deprecations` script**. When A.25 / A.28 / A.29 add real deprecations, the dev experience is "test suite fails → grep one of four phpunit.xml files for the env var → copy/paste from a JSON comment string". Add `"regen-deprecations": "SYMFONY_DEPRECATIONS_HELPER='max[self]=999999&baselineFile=tests/deprecations.allowlist.json&generateBaseline=true' vendor/bin/phpunit -c tests/agnostic.phpunit.xml --no-coverage"` to `composer.json:scripts`.

### 11. Rector cleanup. **No finding**

`composer.json` has no `rector/rector` in require-dev. The reference in `composer.lock` is a transitive dev-dep of an unrelated package. `rector.php` does not exist. Subagent 1 (`e06188773`) cleanly removed Rector after use.

### 12. `docs/reviews/` directory exists. **No finding**

`docs/reviews/` exists with `A-round-1-architecture.md` and `A-round-1-bc.md` already committed. The chicken-and-egg concern in the brief is moot. NICE: add a `docs/reviews/README.md` codifying the `<phase>-round-<n>-<topic>.md` filename convention before A.37 rolls in something different.

---

## Reproduction commands run

```
composer deptrac                                # 0 violations / 233 skipped, exit 0
[edit Application.php → add Runtime use]
composer deptrac                                # 1 violation, exit 1 ✓
[restore]
composer test:agnostic                          # 2386 tests, 5126 assertions, exit 0
XDEBUG_MODE=coverage vendor/bin/phpunit ... --coverage-clover=/tmp/cov.xml  # 1.6 MB clover
composer infection -- --filter=Propel/Runtime/Map --threads=1 --min-msi=0 --min-covered-msi=0
                                                # exit 1: configDir not found
php tools/check-baseline-monotonic.php HEAD     # =0 / =0, exit 0
[append 2 lines to phpstan-baseline.neon]
php tools/check-baseline-monotonic.php HEAD     # +2 detected, exit 1 ✓
[restore]
php tools/check-baseline-monotonic.php nonexistent-ref  # silent pass, exit 0 ✗
php bin/propel-internal-dump-signatures         # all snapshots byte-identical to committed
vendor/bin/phpunit --filter=BookstoreBuildTest  # No tests executed! ✗
git ls-files tests/Fixtures/bookstore/build/    # empty (gitignored) ✗
```

---

## Priority ordering

| ID | Tag | Item | File:Line |
|---|---|---|---|
| 1 | MUST-FIX | Infection broken; not in CI | `infection.json5:21`, missing in `quality-gates.yml` |
| 2 | MUST-FIX | `lint-generated` is permanent no-op | `quality-gates.yml:97-103`, `.gitignore:22` |
| 3 | MUST-FIX | `composer testsuite` missing infection + deptrac | `composer.json:69` |
| 4 | SHOULD-FIX | Baseline-monotonic line-count asymmetry + silent pass on bad ref | `tools/check-baseline-monotonic.php:51-74`, `quality-gates.yml:76` |
| 5 | SHOULD-FIX | Signature-dump PHP-version traps; CI pins 8.3 only | `bin/propel-internal-dump-signatures:91-110`, `quality-gates.yml:19` |
| 8 | SHOULD-FIX | Local coverage requires `XDEBUG_MODE` | `composer.json` (no `test:coverage` script) |
| 10 | NICE | No `regen-deprecations` composer script | `composer.json` |
| 12 | NICE | Document `docs/reviews/` filename convention | `docs/reviews/` |

Three MUST-FIX issues invalidate Phase A's promised quality machinery: mutation testing has never run, generated-code lint has never run, and the local exit gate omits two of the four new tools. Until those are fixed Phase A is checking boxes on tools that don't actually fire.
