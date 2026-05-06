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

### `PropelTypes::*_NATIVE_TYPE` constant value drift

Several public constants on `Propel\Generator\Model\PropelTypes` had their string values normalized to PHP-canonical names:

| Constant | Old value | New value |
|---|---|---|
| `PropelTypes::REAL_NATIVE_TYPE` | `'double'` | `'float'` |
| `PropelTypes::FLOAT_NATIVE_TYPE` | `'double'` | `'float'` |
| `PropelTypes::DOUBLE_NATIVE_TYPE` | `'double'` | `'float'` |
| `PropelTypes::BOOLEAN_NATIVE_TYPE` | `'boolean'` | `'bool'` |
| `PropelTypes::BOOLEAN_EMU_NATIVE_TYPE` | `'boolean'` | `'bool'` |

The helper `PropelTypes::isPhpPrimitiveType()` accepts both old and new spellings so behavior driven through the helper is unchanged. **But code that reads the constant directly and compares against a literal string will silently fail:**

```php
// Before — worked because constant was 'double'
if ($column->getPhpType() === 'double') { /* ... */ }

// Before — also worked but always was the right pattern
if ($column->getPhpType() === PropelTypes::DOUBLE_NATIVE_TYPE) { /* ... */ }

// After — the literal-string comparison silently goes false
if ($column->getPhpType() === 'double') { /* never enters */ }

// After — the constant comparison stays correct (resolves to 'float')
if ($column->getPhpType() === PropelTypes::DOUBLE_NATIVE_TYPE) { /* still works */ }
```

Recommendation: search your codebase for hard-coded `'double'` and `'boolean'` literals near Propel's column-type APIs and replace them with constant references. Grep pattern: `grep -rn "['\"]double['\"]\|['\"]boolean['\"]" src/ | grep -i propel`.

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

Previously: generated `setX()` had no return type annotation. The rewrite adds `: self`. We picked `: self` over `: static` because `: static` imposes extra covariance pressure on subclasses (every override has to return `static`-compatible).

**This is a BC break for any user subclass that overrides a generated setter without a matching return type.** PHP enforces strict LSP on declared return types: an override that lacks `: self` (or the concrete class name, or `: static`) fatals at class load:

```
Fatal error: Declaration of App\MyBook::setTitle($v) must be compatible
with Propel\Tests\Bookstore\Base\Book::setTitle(?string $v): self
```

The same applies to generated FK getters (`: ?Publisher`, `: ?Author`, etc.).

**Migration — concrete code:**

```php
// Before (pre-rewrite Propel) — works because parent setter has no return type
class MyBook extends Book
{
    public function setTitle($v)
    {
        // custom logic
        return parent::setTitle($v);
    }
}

// After Propel 3.0 — must declare a return type matching the parent
class MyBook extends Book
{
    public function setTitle($v): self
    {
        // custom logic
        return parent::setTitle($v);
    }
}
```

You can also use the concrete class name (`: MyBook`) or `: static`, which are stricter covariant alternatives.

**Finding overrides that need updating:**

```bash
# Grep for setter overrides without return types in your project
grep -rEn 'function set[A-Z][A-Za-z0-9_]*\([^)]*\)\s*\{' src/ tests/ | grep -v ': '

# Same for FK getter overrides
grep -rEn 'function get[A-Z][A-Za-z0-9_]*\([^)]*\)\s*\{' src/ tests/ | grep -v ': '
```

A `propel/rector-rules` package is planned for Propel 4.0 with a mechanical fix (`AddSelfReturnTypeToSetterOverridesRector`); for 3.x the migration is manual.

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

### Generated AR base classes migrated to `__serialize` / `__unserialize` (wire-format BC break)

The base class emitted into `templates/Builder/Om/baseObjectMethods.php` now implements `__serialize(): array` / `__unserialize(array $data): void` instead of the deprecated `__sleep(): array` magic. PHP-level behavior of `serialize($activeRecord)` / `unserialize($s)` is preserved, but the on-the-wire byte sequence is **not** backward-compatible.

The old format serialized a list of property names (PHP serializer materialised values via `__sleep` semantics). The new format serializes `array<string, mixed>` directly, capturing values up-front. PHP picks `__serialize` over `__sleep` when both are present.

**Impact — any persisted serialized AR object created on a pre-rewrite Propel will not round-trip through the new methods.** Specifically affected:

- Sessions storing AR objects (`$_SESSION['user'] = $userActiveRecord;`).
- PSR-6 / PSR-16 caches keyed on AR objects.
- Message queues (Symfony Messenger, Laravel queues, RabbitMQ envelopes) carrying AR objects in payloads.
- Filesystem dumps of `serialize(...)` output.

**Migration — invalidate caches at deploy time.** This is the simplest and safest path:

```bash
# Symfony cache
bin/console cache:pool:clear cache.app

# Sessions backed by Symfony cache
bin/console cache:pool:clear cache.session

# File-backed sessions — wipe the directory
rm -rf var/sessions/*

# Redis sessions — flush the relevant keyspace
redis-cli --scan --pattern 'sess:*' | xargs redis-cli del
```

**Migration — one-time read-old-write-new (only if invalidation is not acceptable):**

```php
// Run this in a maintenance script before deploying the new Propel version.
// Requires the OLD Propel version to be available — typically you check out
// a tag of your project, deserialize, write to a side store, then deploy
// the new version and re-read.
foreach ($legacyKeys as $key) {
    $obj = $oldCache->get($key);          // unserializes via __sleep / wakeup
    $newCache->set($key, $obj->toArray()); // store as plain array
}

// After deploy: rebuild AR objects from the stored arrays.
foreach ($newCache->all() as $key => $array) {
    $obj = new Book();
    $obj->fromArray($array, TableMap::TYPE_PHPNAME);
    $newCache->set($key, $obj);            // re-serializes via __serialize
}
```

Most projects can simply invalidate; the read-old-write-new path is only relevant when persisted state has high value (e.g. long-running message queues that cannot be drained).

---

## Schema feature additions in 3.x (Phase C)

Phase C (umbrella §6.4) lands native database type support, generated columns,
CHECK constraints, INVISIBLE columns (MySQL/MariaDB), and PostgreSQL IDENTITY
columns. All additions are XSD-additive — existing schemas continue to work
unchanged. Five new column types: `JSON`, `JSONB`, `INET`, `CIDR`, `TSVECTOR`
(natively mapped on PG; folded to compatible types on MySQL; folded to TEXT
on SQLite).

### PG: `serial` / `bigserial` → `IDENTITY` migration

Why migrate: `serial` has been deprecated in PostgreSQL since 10 (released 2017).
The standard SQL form is `GENERATED ... AS IDENTITY`; it has cleaner ownership
semantics, doesn't leak the underlying sequence into `\d+` introspection, and
plays nicer with PG's privilege system.

**As of Phase C, new auto-increment PKs emit IDENTITY by default.** Existing
schemas with `<column type="INTEGER" autoIncrement="true">` will surface as
`INTEGER GENERATED BY DEFAULT AS IDENTITY` in the next `sql:build` /
`migration:diff`.

If you have a runway constraint (e.g. downstream tooling that depends on the
literal `serial` keyword), opt back into the legacy emission per-column:

```xml
<column name="id" type="INTEGER" primaryKey="true" autoIncrement="true">
    <vendor type="pgsql">
        <parameter name="legacy-serial" value="true"/>
    </vendor>
</column>
```

This emits a deprecation warning and is scheduled for removal in 4.0.

### Manual conversion of an existing `serial` column to IDENTITY

This is a non-destructive ALTER on PostgreSQL 14+ — no full table rewrite,
typical lock window <2s on tables up to ~50M rows.

```sql
-- Lock briefly; takes < 2s on PG 14+
BEGIN;

-- Stop new INSERTs from using the sequence default.
ALTER TABLE foo ALTER COLUMN id DROP DEFAULT;

-- Drop the underlying sequence (the column has already taken its values).
DROP SEQUENCE foo_id_seq;

-- Adopt IDENTITY, seeded past the highest existing value.
ALTER TABLE foo ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    START WITH (SELECT max(id) + 1 FROM foo)
);

COMMIT;
```

Rollback: re-create the sequence, restore the default, then drop IDENTITY.

```sql
BEGIN;
CREATE SEQUENCE foo_id_seq OWNED BY foo.id;
SELECT setval('foo_id_seq', (SELECT max(id) FROM foo));
ALTER TABLE foo ALTER COLUMN id SET DEFAULT nextval('foo_id_seq');
ALTER TABLE foo ALTER COLUMN id DROP IDENTITY;
COMMIT;
```

### SQLite frozen features

Per umbrella spec §1.2, SQLite is frozen at its pre-Phase-C feature set. The
following Phase C features throw `EngineException` on SQLite with a pointer
back to this section:

- Generated columns (`<column generated="virtual|stored" expression="..."/>`)
- INVISIBLE columns (`<column invisible="true"/>`)
- JSONB columns (`<column type="JSONB"/>`) — use `type="JSON"` instead, which
  passes through as TEXT affinity.
- CHECK constraints (`<check ... />`)

If you depend on these features, switch the target platform to MySQL 8 / PG 14
on the offending fixture or production schema.

### JSONB operators on PG (PDO `?` collision)

PG's literal `?`, `?&`, `?|` JSONB operators collide with PDO's positional
placeholder marker. Use the `JsonbOperator` helper (`Propel\Runtime\ActiveQuery\
Operator\JsonbOperator`) so the `?` is escaped as `??` per
`PDO::ATTR_EMULATE_PREPARES` semantics:

```php
use Propel\Runtime\ActiveQuery\Operator\JsonbOperator;

$clause = JsonbOperator::buildClause(
    'event_log.payload',
    JsonbOperator::ContainsKey,
    ':key',
);
// event_log.payload ?? :key

$query->addUsingAlias($alias, $value, $clause);
```

The helper supports `ContainsKey` (`?`), `ContainsAll` (`?&`), `ContainsAny`
(`?|`), `JsonbContains` (`@>`), `JsonbContainedBy` (`<@`).

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
