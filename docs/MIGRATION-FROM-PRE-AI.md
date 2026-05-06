# Migrating to Propel 3.0 (post-AI-rewrite)

This guide is for projects upgrading from pre-rewrite Propel (anything before commit `b317e1c24` on the `ar-rewrite` branch). It covers every breaking change introduced by the modernization, with concrete migration steps.

For full context on the modernization scope and BC contract see `docs/plans/2026-05-06-modernization-umbrella-spec.md`.

---

## Quick checklist

- [ ] PHP 8.3+ (8.4 supported; 8.4 will become the floor at Propel 4.0).
- [ ] MySQL 8.0+ / MariaDB 10.5+ / PostgreSQL 14+ (older versions unsupported — see "Database versions" below).
- [ ] Symfony 7.2+ (older Symfony versions unsupported).
- [ ] If you used the `Validate` or `QueryCache` behaviors: replace per "Removed behaviors".
- [ ] If your `propel.yaml` references `oracle` / `mssql` / `sqlsrv` adapters: switch DB or stay on Propel 2.x LTS.
- [ ] If your `propel.yaml` uses `slaves:` / `master:` keys: rename to `replicas:` / `primary:` (old keys still work in 3.x with a deprecation warning).
- [ ] If your code references `DebugPDO` / `PropelPDO` directly: switch to `ConnectionWrapper` (aliases still work in 3.x with a deprecation warning).
- [ ] Run `composer update` and verify the test suite is green.

---

## Database versions

Propel 3.0 supports:
- **MySQL 8.0+, MariaDB 10.5+** — with full recursive CTEs, JSON, generated columns, INVISIBLE columns, IF NOT EXISTS.
- **PostgreSQL 14+** — IDENTITY columns (replaces `serial`/`bigserial`), JSONB, generated columns, partial indexes.
- **SQLite** — kept for tests + small projects; frozen at the existing feature surface.

Unsupported:
- **MySQL ≤5.7** — fix-during-runtime hacks (PECL bug #9919, MyISAM defaults, `getColumnBindingPHP` workarounds) all removed.
- **PostgreSQL ≤13** — `serial`/`bigserial` deprecated in PG 10; we now emit `IDENTITY` exclusively.
- **Oracle, MSSQL, SQL Server** — adapters removed years ago; the runtime config validator now produces a clear error pointing here. If you need these databases, stay on Propel 2.x LTS or use Doctrine.

If your config references an unsupported adapter:
```
Propel\Common\Config\Exception\InvalidConfigurationException:
  Adapter "oracle" is no longer supported in Propel 3.0+.
  See docs/MIGRATION-FROM-PRE-AI.md.
```

The `mysql|pgsql|sqlite` adapter enum is the only valid set.

---

## Removed behaviors

### `Validate`

**Why removed:** imported `Symfony\Component\Validator\DefaultTranslator` and `StaticMethodLoader`, both removed in Symfony 3.0 (2015). Generated dead code on Symfony 4+.

**Migration:** validate at the application layer using Symfony Validator on DTOs:
```php
// Before — Validate behavior on Book schema
$book->setTitle('');
$book->save();  // throws ValidationException

// After — explicit DTO validation
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints\NotBlank;

$validator = Validation::createValidator();
$violations = $validator->validate($book->getTitle(), new NotBlank());
if (count($violations) > 0) {
    throw new \RuntimeException((string) $violations);
}
$book->save();
```

If your schema XML has `<behavior name="validate">`, the schema parser now throws:
```
Propel\Generator\Exception\BuildException:
  Validate behavior was removed in Propel 3.0 (it imported Symfony 3.0-removed
  classes and produced dead generated code). Use Symfony Validator on
  application DTOs instead. See docs/MIGRATION-FROM-PRE-AI.md.
```

Remove the `<behavior name="validate">` element and its parameters from your schema.

### `QueryCache`

**Why removed:** depended on `apc_*` (removed from PHP in 5.5, 2013). Cannot run on supported PHP.

**Migration:** use a PSR-6/PSR-16 cache at the application layer:
```php
// Before — QueryCache behavior cached query results internally
$results = BookQuery::create()->find();  // implicitly cached via APC

// After — explicit PSR-6 cache wrapper
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$cache = new FilesystemAdapter();
$results = $cache->get('books_all', function () {
    return BookQuery::create()->find();
});
```

Remove `<behavior name="query_cache">` from your schema. Schema parser throws a similar clear error.

### `NestedSet` (deprecated, not removed)

NestedSet still works in 3.x but emits a deprecation warning. Plan to migrate before Propel 4.0 (which will remove it).

**Migration:** use recursive CTEs (supported in MySQL 8, MariaDB 10.2.2+, PostgreSQL 8.4+) over an adjacency-list (parent_id) column. CTEs are simpler, faster, and don't require bespoke insert/delete logic. Doctrine and Cycle ORM both ship recursive-CTE helpers as inspiration.

---

## Removed column types (deprecated, not removed)

These types still parse in schemas and emit a deprecation warning when resolved. They will be removed in Propel 4.0.

| Old type | Replacement |
|---|---|
| `BU_DATE` (pre-1970-Unix workaround) | `DATE` or `TIMESTAMP` |
| `BU_TIMESTAMP` | `TIMESTAMP` |
| `BOOLEAN_EMU` (pre-PG-bool emulation) | `BOOLEAN` |
| `OBJECT` (serialized PHP in BLOB) | `JSON` or app-layer storage |
| `PHP_ARRAY` (CSV in TEXT column) | `JSON` |

Update your `<column type="BU_DATE">` to `<column type="TIMESTAMP">` etc. The schema XSD still parses the old values per the additivity promise (`docs/BACKWARD_COMPATIBILITY.md` §3.7), but the deprecation warning surfaces at codegen time.

---

## Removed connection classes (alias, kill in 4.0)

`DebugPDO` and `PropelPDO` were 5–8-line empty subclasses of `ConnectionWrapper` kept for BC. They still work in 3.x with a deprecation warning; they will be deleted in 4.0.

If your `propel.yaml` has:
```yaml
connection:
  classname: \Propel\Runtime\Connection\DebugPDO
```

Replace with:
```yaml
connection:
  classname: \Propel\Runtime\Connection\ConnectionWrapper
```

If you reference these classes in PHP code, simply update the import. The Rector ruleset shipped at 4.0 (`propel/rector-rules`) will do this mechanically.

---

## Config-key renames (alias, kill in 4.0)

The master/slave terminology is deprecated. Your `propel.yaml` continues to work with the old keys, but emits deprecation warnings:

| Old key | New key |
|---|---|
| `slaves` | `replicas` |
| `master` | `primary` |

Both `connection` (singular) and `connections` (plural) keys are accepted; no rename needed.

---

## API surface changes

### `: self` return type on generated setters (NOT `: static`)

Previously: generated `setX()` had no return type annotation. We considered adding `: static` but reverted to `: self` because `: static` is an LSP break for user subclasses overriding `setX($v)` without a return type. With `: self`, your existing overrides remain valid.

If you write code that depends on Propel-3.0+ being installed:
```php
return $this->setTitle('foo');  // returns self|static — both work
```

### `Collection::offsetGet` no longer returns by reference

Previously: `public function &offsetGet($offset)`. PHP 8 emits a notice when returning `null` from a by-reference function. Fixed to non-reference.

If your code did:
```php
$coll = new Collection();
$ref = &$coll[0];   // by-reference assignment — no longer works
$ref = 'new';       // would have mutated the collection
```

Use the explicit API instead:
```php
$coll->setData([0 => 'new']);
// or
$coll[0] = 'new';
```

### `Collection` no longer implements `Serializable`

The interface was soft-deprecated in PHP 8.1 and is fully obsolete. `Collection::__serialize()` / `__unserialize()` still work — `serialize($collection)` still produces and consumes the same bytes. Code calling `$collection->serialize()` or `$collection->unserialize()` directly will break; use `serialize($collection)` / `unserialize($s)` (the language functions) instead.

### `PropelDateTime` migrated to `__serialize` / `__unserialize`

Previously had `__sleep` / `__wakeup`. PHP 8 now uses `__serialize` from the parent `DateTime` class which shadowed `__sleep`. The new methods preserve serialization semantics with microsecond precision and gracefully handle invalid stored timezones (fall back to UTC with E_USER_WARNING instead of throwing mid-unserialize).

If you persisted serialized PropelDateTime objects: existing serialized data created with the old `__sleep` format remains readable via the parent DateTime's standard format. New serializations use the cleaner 2-field shape.

---

## Internal changes (Tier 3 — not BC, listed for awareness)

These changed but do not affect consumer code:

- `bin/propel-internal-dump-signatures` — new tool for the BC signature-diff CI gate.
- `tests/snapshots/` — frozen Tier 1 surface snapshots.
- `tests/snapshots/bookstore-golden/` — frozen generated bookstore tree.
- `Validator/Constraints/` — entire directory removed (Symfony 6+ supports `DateTimeInterface` natively).
- MyISAM plumbing in `MysqlPlatform` — removed.
- HHVM strict-issue comments in `PdoConnection` — removed.
- XSLT pipeline in `AbstractManager::loadDataModels` — removed.
- ~92 method overrides — `#[\Override]` attribute added.

---

## Need help?

- Read the umbrella spec: `docs/plans/2026-05-06-modernization-umbrella-spec.md`.
- Read the BC contract: `docs/BACKWARD_COMPATIBILITY.md`.
- Read upgrade-specific notes: `docs/UPGRADE-3.0.md`.
- For 4.0 (PHP 8.4 minimum) preview: `docs/UPGRADE-4.0.md` (lands at Phase G).

If a deprecation warning fires that this guide doesn't cover, file an issue — gaps in this doc are bugs.
