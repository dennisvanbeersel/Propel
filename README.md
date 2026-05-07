# Propel2

Propel2 is an open-source Object-Relational Mapping (ORM) for PHP.

[![Github actions Status](https://github.com/propelorm/Propel2/actions/workflows/ci.yml/badge.svg?branch=master)](https://github.com/propelorm/Propel2/actions/workflows/ci.yml?query=branch%3Amaster)
[![codecov](https://codecov.io/gh/propelorm/Propel2/branch/master/graph/badge.svg?token=L1thFB9nOG)](https://codecov.io/gh/propelorm/Propel2)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%207-brightgreen.svg?style=flat)](https://phpstan.org/)
[![Code Climate](https://codeclimate.com/github/propelorm/Propel2/badges/gpa.svg)](https://codeclimate.com/github/propelorm/Propel2)
[![Minimum PHP Version](http://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)
[![License](https://poser.pugx.org/propel/propel/license.svg)](https://packagist.org/packages/propel/propel)
[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/propelorm/Propel)

## Requirements

- **PHP 8.3+** with `strict_types` declarations throughout
- **PDO** with drivers for your database of choice

### Compatibility Matrix

| Propel | PHP | MySQL | MariaDB | PostgreSQL | Symfony |
|---|---|---|---|---|---|
| 2.x (LTS, security only) | 8.3 | 5.7+ | 10.4+ | 12+ | 7.0+ |
| **3.0 (current)** | 8.3 | 8.0+ | 10.5+ | 14+ | 7.2+ |
| 4.0 (planned) | 8.4 | 8.0+ | 10.5+ | 14+ | 7.2+ |

### Supported Databases

- MySQL 8.0+ / MariaDB 10.5+
- PostgreSQL 14+
- SQLite (kept for tests + small projects; frozen at existing feature surface)

Oracle, SQL Server (mssql/sqlsrv) are unsupported. See [docs/MIGRATION-FROM-PRE-AI.md](docs/MIGRATION-FROM-PRE-AI.md) if upgrading from a Propel project that used those.

### Symfony Components (^7.0)

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


## Development

### Running Tests

```bash
# Run all tests (requires database setup)
composer test

# Run database-agnostic tests only (no database required)
composer test:agnostic

# Run tests for specific database
composer test:mysql
composer test:sqlite
composer test:pgsql
```

### Code Quality

```bash
# Run full test suite including static analysis
composer testsuite

# Code style check/fix
composer cs-check
composer cs-fix

# Static analysis (PHPStan level 7)
composer stan

# Psalm analysis
composer psalm
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
