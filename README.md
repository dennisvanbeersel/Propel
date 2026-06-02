# Propel2

Propel2 is an open-source Object-Relational Mapping (ORM) for PHP. It provides
Active Record-style persistence and a fluent query builder, generating typed PHP
model classes from an XML schema.

[![Github actions Status](https://github.com/propelorm/Propel2/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/propelorm/Propel2/actions/workflows/ci.yml?query=branch%3Amaster)
[![codecov](https://codecov.io/gh/propelorm/Propel2/branch/master/graph/badge.svg?token=L1thFB9nOG)](https://codecov.io/gh/propelorm/Propel2)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%207-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Code Climate](https://codeclimate.com/github/propelorm/Propel2/badges/gpa.svg)](https://codeclimate.com/github/propelorm/Propel2)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%208.4-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/propel/propel/license.svg)](https://packagist.org/packages/propel/propel)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/propelorm/Propel)

## Requirements

- **PHP 8.4+**, with `declare(strict_types=1)` in every source file
- **PDO** with drivers for your database of choice
- **Symfony 7.2+** components and **Composer** for dependency management

### Compatibility Matrix

| Propel | PHP | MySQL | MariaDB | PostgreSQL | Symfony |
|---|---|---|---|---|---|
| 2.x (LTS, security only) | 8.3 | 5.7+ | 10.4+ | 12+ | 7.0+ |
| 3.0 | 8.3 | 8.0+ | 10.5+ | 14+ | 7.2+ |
| **4.0 (this branch)** | **8.4** | 8.0+ | 10.5+ | 14+ | 7.2+ |

### Supported Databases

- MySQL 8.0+ / MariaDB 10.5+
- PostgreSQL 14+
- SQLite (kept for tests and small projects; frozen at its existing feature surface)

Oracle and SQL Server (mssql/sqlsrv) are no longer supported. See
[docs/MIGRATION-FROM-PRE-AI.md](docs/MIGRATION-FROM-PRE-AI.md) if you are upgrading
from a Propel project that used them.

### Symfony Components (^7.2)

Propel uses the following Symfony Components:

* [Config](https://github.com/symfony/config)
* [Console](https://github.com/symfony/console)
* [Filesystem](https://github.com/symfony/filesystem)
* [Finder](https://github.com/symfony/finder)
* [Translation](https://github.com/symfony/translation)
* [Validator](https://github.com/symfony/validator)
* [Yaml](https://github.com/symfony/yaml)

Propel relies on [**Composer**](https://github.com/composer/composer) to manage dependencies.

## Installation

Read the [Propel documentation](http://propelorm.org/documentation/01-installation.html).

## What's new in 4.0

Propel 4.0 is a ground-up modernization targeting PHP 8.4. The public Active
Record / query API (the "Tier 1" surface) stays frozen and additive — existing
`find()`, `filterBy*()`, `setX()` code keeps working — while the internals,
generated code, and tooling were rewritten. The headline changes:

### PHP 8.4 baseline, strict types everywhere

Every source file declares `strict_types=1` and generated model classes are
fully typed: typed column properties, `: self` setters, typed-nullable foreign-key
getters, and `: static` query factories. ENUM columns generate backed PHP enums,
and `__serialize`/`__unserialize` replace the old `__sleep`/`__wakeup` pair.

### Asymmetric property visibility on generated entities

Generated typed column properties moved from `protected` to
`public protected(set)`. Reads are free from anywhere; writes are restricted to
the entity class hierarchy, so external direct writes now fail fast instead of
silently no-op'ing on a protected property:

```php
// 4.0 generated base class:
abstract class Author
{
    public protected(set) ?int $id = null;
    public protected(set) ?string $firstName = null;
}

echo $author->id;        // reads work everywhere
$author->setFirstName(); // writes go through setters
```

### Connection decorator architecture

The legacy mixed-responsibility `ConnectionWrapper` was decomposed into a stack
of single-responsibility decorators under `Propel\Runtime\Connection\Internal\*`:
`TransactionalConnection` (nested-transaction accounting), `LoggingConnection`
(PSR-3 logging without per-query `debug_backtrace`), `CachingConnection` (a
bounded-LRU prepared-statement cache), and the optional `ProfilingConnection`
and `ReplicaRoutingConnection`. `ConnectionFactory` is the single source of truth
for the canonical chain order. See
[docs/CONNECTION-DECORATORS.md](docs/CONNECTION-DECORATORS.md).

### Primary / replica routing

Master/slave terminology and config are gone. Connection management is now
`ConnectionManagerPrimaryReplica`, configured with `primary:` / `replicas:` keys,
plus additive query hints `Criteria::forcePrimary()` and `Criteria::allowReplica()`
for per-query routing control with session-consistency and primary-fallback awareness.

### Streaming and lazy hydration

`ModelCriteria::findStream()` returns a `Generator` that yields one hydrated
object per row as the database cursor walks, keeping memory roughly O(1) in row
count instead of materializing the whole collection:

```php
foreach ($query->findStream() as $book) {
    // one Book at a time; the full result set is never held in memory
}
```

Opt-in lazy relation objects (`<table useLazyObjects="true">`) defer per-relation
collection construction using PHP 8.4 lazy ghosts, materializing on first read.

### Modern enums for the query builder

`Comparison`, `JoinType`, `SortOrder`, and `LogicalOperator` backed enums live
under `Propel\Runtime\ActiveQuery\Operator\*` alongside (never replacing) the
frozen `Criteria::*` constants. `Criteria::customCondition()` provides a
parameterized successor to raw `Criteria::CUSTOM` SQL injection.

### Observability SPI

A `TelemetryInterface` SPI ships with a no-op default plus optional OpenTelemetry
and Prometheus adapters (installed via Composer `suggest`), and a
`CompositeTelemetry` to fan out to several at once. The connection decorators
emit query spans, cache hit/miss counters, transaction-depth, hydration duration,
and replica-routing metrics through it. See [docs/TELEMETRY.md](docs/TELEMETRY.md).

### Tooling and cleanup

Console commands use Symfony's `#[AsCommand]` attribute, the migration tracking
table was redesigned with `migration_name` / `batch` / `checksum` columns and
SHA-256 drift detection, and `#[\Override]` is applied across the codebase.
Legacy cruft was removed: `DebugPDO`/`PropelPDO`, `ConnectionManagerMasterSlave`,
the NestedSet behavior, MyISAM plumbing, the XSLT pipeline, and the Validate /
QueryCache behaviors.

### Upgrading

A Rector rule set (in the `rector/` package) automates most call-site rewrites —
PDO wrapper replacements, master/slave config and class renames, and others.
See [docs/UPGRADE-4.0.md](docs/UPGRADE-4.0.md) for the full migration guide, and
[docs/UPGRADE-3.0.md](docs/UPGRADE-3.0.md) / [docs/MIGRATION-FROM-PRE-AI.md](docs/MIGRATION-FROM-PRE-AI.md)
when coming from older versions.

## Development

### Running Tests

```bash
# Run all tests (requires database setup)
composer test

# Run database-agnostic tests only (no database required)
composer test:agnostic

# Run tests for a specific database
composer test:mysql
composer test:pgsql
```

Set up the test database before running database-specific tests:

```bash
tests/bin/setup.sqlite.sh   # SQLite
tests/bin/setup.mysql.sh    # MySQL (requires DB_USER, DB_PW env vars)
tests/bin/setup.pgsql.sh    # PostgreSQL
```

### Code Quality

```bash
# Full suite: tests + cs-check + PHPStan + Psalm + deptrac
composer testsuite

# Code style check / fix
composer cs-check
composer cs-fix

# Static analysis (PHPStan level 7)
composer stan

# Psalm analysis
composer psalm

# Architecture rules (deptrac)
composer deptrac
```

## Contribute

Everybody is welcome to contribute to Propel! Just [fork the repository](https://docs.github.com/en/get-started/quickstart/fork-a-repo) and [create a pull request](https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request).

**Requirements for contributions:**
- Use `declare(strict_types=1)` in all PHP files
- Follow [Spryker coding standards](https://github.com/spryker/code-sniffer) (extended from PSR-12)
- Pass PHPStan level 7 analysis
- Include unit tests for your changes

Have a look at the [test suite guide](http://propelorm.org/documentation/cookbook/working-with-test-suite.html) for more details about test development in Propel.

Thank you!

## License

MIT. See the `LICENSE` file for details.
