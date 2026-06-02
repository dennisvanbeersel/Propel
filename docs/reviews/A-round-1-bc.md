# Phase A Round 1 — BC & Migration Realism Review

**Scope:** commits `7fa030e29..fc1b7799b` (Tasks A.1–A.13). Focus: signature-diff snapshot capture (`8c608a176`), PHPUnit deprecation sweep (`e06188773`, `5bead168f`), Symfony bridge wiring.

**Verdict:** the scaffolding is mostly in place but ships with **two real correctness defects** in the deprecation telemetry and the signature-diff metadata, plus several silent BC gaps the spec promised to enforce. Do not treat the gate as load-bearing yet.

---

## Findings

### [MUST-FIX] 1. Deprecation allowlist file is in the wrong shape — silently broken
`tests/deprecations.allowlist.json:1-4` is `{"deprecations": [], "_comment": "..."}`. Symfony's bridge expects a **flat JSON array** of `{location, message, count}` objects: see `vendor/symfony/phpunit-bridge/DeprecationErrorHandler/Configuration.php:144-148`:

```php
$map = json_decode(file_get_contents($this->baselineFile));
foreach ($map as $baseline_deprecation) {
    $this->baselineDeprecations[$baseline_deprecation->location][$baseline_deprecation->message] = $baseline_deprecation->count;
}
```

I executed this against the real file and confirmed: `json_decode` returns a `stdClass` with `deprecations` and `_comment` properties; the `foreach` iterates those two properties; both lookups for `->location`/`->message` resolve to `null`, populating the baseline map with garbage keyed by `""`. Today the suite doesn't trigger any deprecations so this hides; the moment **any** deprecation fires (Phase A.25/A.28/A.29 will trigger several intentionally), the baseline match fails and `max[self]=0` triggers test failure even for entries we tried to allowlist.

Worse, the bundled "Regenerate via `generateBaseline=true`" comment in `_comment` will overwrite the file with the correct flat array shape on first regeneration, **silently dropping the `_comment` self-documentation** the engineer put in. There is no hook to preserve metadata. Replace with a flat array now (`[]`) and move the comment to a sibling `.md` or a README. CI must include a smoke that loads the allowlist via the bridge to catch this regression — currently nothing exercises the load path.

### [MUST-FIX] 2. Signature-diff dump does not record `extends`/`implements` — A.27 will slip through
`bin/propel-internal-dump-signatures:53-62` builds a snapshot with `class`, `kind`, `isAbstract`, `isFinal`, `methods`, `properties`, `constants`. **No `extends`, no `implements`.** Verified by reading every captured `.signatures.json` (e.g. `tests/snapshots/Propel_Runtime_Collection_Collection.signatures.json:1-6`).

Phase A.27 is explicitly slated to drop `Serializable` from `Collection` (`docs/plans/2026-05-06-phase-a-foundations.md:67`, `:1518-1547`). This **is** a Tier 2 BC break for any consumer doing `instanceof Serializable` or providing `Serializable` typed parameters. The spec promised the gate would catch it; the gate cannot see it. Add `'extends'` and `'implements'` keys to `extractClass()` in the dump script, regenerate the 13 snapshots, and re-commit. Also add `'parents'` (a class can extend transitively); at minimum the direct `getParentClass()` and `getInterfaceNames()`.

Same hole hides: removed `IteratorAggregate`/`Countable`/`ArrayAccess` (none planned, but the gate is supposed to tell you when somebody plans them by accident).

### [MUST-FIX] 3. `ActiveRecordInterface` `@method toArray()` PHPDoc contract is not captured
Spec §3.1 (`docs/plans/2026-05-06-modernization-umbrella-spec.md:192`) explicitly lists `@method toArray()` as a contractual promise alongside `isPrimaryKeyNull()`. The dump script only uses Reflection (`bin/propel-internal-dump-signatures:82-118`), which cannot see PHPDoc `@method` declarations. The captured snapshot at `tests/snapshots/Propel_Runtime_ActiveRecord_ActiveRecordInterface.signatures.json` has exactly **one** method (`isPrimaryKeyNull`), missing the `@method array toArray(...)` declared at `src/Propel/Runtime/ActiveRecord/ActiveRecordInterface.php:18`.

Consequence: somebody can refactor the source to delete the `@method` line and the gate will not notice. Either (a) parse the PHPDoc with `phpdocumentor/reflection-docblock` and emit a `phpdocMethods` key, or (b) add a hand-maintained companion `.contract.json` that the diff also enforces. The latter is simpler for now.

### [MUST-FIX] 4. Generated-code surface is silently out of scope
Spec §3.1 puts `XxxQuery::create()`, `useXxxQuery()`, magic `findByXAndY`/`filterByX` dispatch in **Tier 1**. The current `tracked-classes.txt:1-43` covers only runtime; no `tests/Fixtures/bookstore/build/classes/Propel/...` entries. The `lint-generated` CI job (`.github/workflows/quality-gates.yml:81-103`) lints generated code with phpcs+phpstan but never signature-diffs it. So:

- Phase B can change a generated method's return type from `: self` to `: static` and zero gates trip.
- Phase B can drop a generated `findByXxx` magic method and zero gates trip.

The Phase A plan's description string (`docs/plans/2026-05-06-phase-a-foundations.md:550`) literally says the script dumps "generated bookstore classes" — but the implementation dumps runtime classes, and there is **no Phase A task** scheduled to add bookstore signature capture (verified via grep on the plan). This is a silent scope reduction. Either schedule a Phase A.x task to capture a curated subset of generated Base\* signatures (e.g., `BookBaseQuery`, `BookBase`), or amend the umbrella spec to admit Tier 1 generated-code is not gated until Phase B. As-is, finding #10 below has no safety net.

### [SHOULD-FIX] 5. No test enforces the `: self` (not `: static`) generator contract
Spec §3.5 (`docs/plans/2026-05-06-modernization-umbrella-spec.md:235-239`) makes this an explicit LSP-safety promise. Phase B amends task 2.6 to honor it (`:506`). But Phase A ships no test that asserts a generated setter's return type is `self` rather than `static`. Combined with finding #4, an accidental Phase B regression will slip both gates. A 30-line test loading a generated class and asserting `(new ReflectionMethod($cls, 'setTitle'))->getReturnType()->getName() === 'self'` would close this.

### [SHOULD-FIX] 6. Class deletion detection only fires for tracked classes
`.github/workflows/quality-gates.yml:35-38` errors when `tests/snapshots/$base` exists but `/tmp/sig-current/$base` does not — i.e., a tracked class was deleted. Fine. But A.21 ("Kill `Validator/Constraints`") deletes classes that are **not** in `tracked-classes.txt` because they were never Tier 1. If somebody downstream extends `Propel\Runtime\Validator\Constraints\...`, they break with no signal. This is acknowledged-by-design in the spec (`docs/plans/2026-05-06-modernization-umbrella-spec.md:217`), but should at least be called out in `BACKWARD_COMPATIBILITY.md` (which doesn't exist yet — Phase A.36+).

### [SHOULD-FIX] 7. No XSD additivity gate
Spec §3.7 (`docs/plans/2026-05-06-modernization-umbrella-spec.md:249-253`) commits to "Schema instances valid against today's XSD remain valid against the new XSD." `resources/xsd/database.xsd` is in the repo but **not snapshotted, not validated against canonical fixtures, not gated**. Phase C will edit it. A regression that changes a `<xs:enumeration>` from optional to required, or removes a value, slips through every existing CI step.

Recommended: add `tests/snapshots/database.xsd` (a copy) and a CI step that runs `xmllint --schema tests/snapshots/database.xsd tests/Fixtures/**/*-schema.xml` to prove every committed schema fixture validates against today's XSD. Then in subsequent phases, when XSD changes, regenerate the snapshot and re-run validation against fixtures plus a known-good external corpus. Phase A is the right time to install this; deferring to Phase C means we'll already have edited the XSD without a baseline.

### [SHOULD-FIX] 8. Config alias preservation has zero test coverage
Spec §3.8 / `docs/plans/2026-05-06-phase-a-foundations.md:60` promises `slaves`/`master`/`connection` keys keep parsing. The fix lands in A.29; mid-phase that's defensible. But the **test harness** for the contract should land first. Today there is no `ConfigurationManagerTest` case asserting that `['slaves' => [...]]` deserializes to the same object as `['replicas' => [...]]` (or whatever the canonical key becomes). When A.29 is implemented, the test will be retrofitted in the same commit — meaning the assertion was authored by someone who already believes their implementation is correct. That is not red-then-green TDD; it is post-hoc rationalisation. Schedule the test now; let A.29 turn it green.

### [SHOULD-FIX] 9. `package.json` first-arg for `trigger_deprecation` is correct, but no calls exist yet
`composer.json:2` is `"name": "dennisvanbeersel/propel"`. Verified. So future `trigger_deprecation('dennisvanbeersel/propel', '3.X', ...)` calls (Phase A.29 forward) will attribute correctly. **However, `grep -rn "trigger_deprecation\b" src/` returned zero hits today** — none of the existing six `@deprecated` markers (`Propel.php:117`, `ConnectionManagerMasterSlave.php:16,23,35`, `Criteria.php:1065`, `ObjectBuilder.php:2439`) actually emit a runtime deprecation. They are PHPDoc-only. Spec §3.4 explicitly requires both. Should be fixed alongside A.28/A.29; flagging here so it's not forgotten.

### [SHOULD-FIX] 10. Consumer-smoke project does not exist; BC promise is currently untestable
Spec §7.3 / `:678` and `:444` make consumer-smoke a "run on every PR" gate. Phase A.36 schedules creation of the skeleton (`docs/plans/2026-05-06-phase-a-foundations.md:1939-1985`). It does not exist today (`tests/integration/` is absent). So the answer to "does the BC promise hold for a real consumer today?" is "no test exists to answer that question." Mid-phase A is acceptable, but this should not slip to Phase B.

### [NICE] 11. PHPUnit doc-comment migration is clean — random spot-check passes
Verified `tests/Propel/Tests/Common/Pluralizer/EnglishPluralizerTest.php:98,108` correctly carries `#[\PHPUnit\Framework\Attributes\DataProvider('getPluralFormDataProvider')]` matching the original `@dataProvider getPluralFormDataProvider`. Same for `tests/Propel/Tests/Common/Util/SetColumnConverterTest.php:28,73`. `@author` PHPDoc preserved at `SetColumnConverterTest.php:18` (Rector correctly leaves non-PHPUnit annotations alone). No regressions found in spot-check.

### [NICE] 12. Snapshot determinism is good
`bin/propel-internal-dump-signatures:113-115,135-137,155-158` sorts methods, properties, and constants by `(visibility, name)` / `name`. Combined with `JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES`, output is byte-stable across PHP patch versions. Commit message claims back-to-back zero-diff verification; I trust that.

### [NICE] 13. Counts match spec promises
`Propel\Runtime\Propel`: 18 methods captured — matches §3.1's "18 named static methods" exactly. `Propel\Runtime\ActiveQuery\Criteria`: 38 constants captured — matches §3.1's corrected "~38" claim (and refutes v1's "31"). 113 methods captured. Tier 1 surface accounting is sound.

---

## Summary of action items
1. **Fix `tests/deprecations.allowlist.json` shape** to flat array; add CI smoke that loads it through the bridge.
2. **Extend dump script** to include `extends` + `implements`; regenerate all 13 snapshots before A.27 lands.
3. **Capture `@method` PHPDoc** for `ActiveRecordInterface` (and any other tracked class with one).
4. **Schedule a Phase A task** to snapshot a curated set of generated Base\* classes — Tier 1 promises require it.
5. Add the `: self` LSP regression test now (cheap, blocks B.2.6 backslide).
6. Add the `slaves`/`master` config alias test now (red), let A.29 turn it green.
7. Stand up an XSD additivity smoke (`xmllint` over fixtures) before Phase C edits the schema.
