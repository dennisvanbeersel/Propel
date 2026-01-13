# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Propel2 is an open-source Object-Relational Mapping (ORM) for PHP. It provides Active Record-style persistence and query building for PHP applications.

## Build and Development Commands

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

# Run a single test file
vendor/bin/phpunit tests/Propel/Tests/Path/To/TestFile.php

# Run a single test method
vendor/bin/phpunit --filter testMethodName tests/Propel/Tests/Path/To/TestFile.php
```

### Database Setup for Tests

Before running database-specific tests, set up the test database:
```bash
tests/bin/setup.sqlite.sh  # SQLite
tests/bin/setup.mysql.sh   # MySQL (requires DB_USER, DB_PW env vars)
tests/bin/setup.pgsql.sh   # PostgreSQL
```

### Code Quality

```bash
# Run full test suite including static analysis
composer testsuite

# Code style check
composer cs-check

# Fix code style automatically
composer cs-fix

# PHPStan static analysis (level 7)
composer stan

# Psalm static analysis
composer psalm
```

## Architecture

### Source Code Structure (`src/Propel/`)

- **Common/** - Shared utilities: configuration management, pluralizers, SET column converters
- **Generator/** - Code generation and CLI tools:
  - `Builder/` - Generates PHP model classes from schema
  - `Command/` - CLI commands (model:build, migration:*, sql:*, etc.)
  - `Behavior/` - Table behaviors (Timestampable, Sluggable, Versionable, NestedSet, I18n, Sortable, etc.)
  - `Platform/` - Database-specific SQL generation (MySQL, PostgreSQL, SQLite, Oracle, MSSQL)
  - `Reverse/` - Database reverse engineering (schema parsers per database type)
  - `Model/` - Schema model classes (Database, Table, Column, ForeignKey, etc.)
- **Runtime/** - Runtime ORM components:
  - `ActiveQuery/` - Query building with fluent interface
  - `ActiveRecord/` - Base classes for generated model objects
  - `Adapter/` - Database adapter abstraction (PDO adapters per database)
  - `Connection/` - Database connection management
  - `Collection/` - Object collection classes
  - `Map/` - Table and column metadata mapping
  - `Propel.php` - Main entry point and service container

### Test Structure

Tests use PHPUnit with database-specific configurations:
- `tests/agnostic.phpunit.xml` - Excludes database-dependent tests
- `tests/mysql.phpunit.xml`, `tests/sqlite.phpunit.xml`, `tests/pgsql.phpunit.xml` - Include database tests

Test groups:
- `@group database` - Tests requiring any database
- `@group mysql` - MySQL-specific tests
- `@group pgsql` - PostgreSQL-specific tests

### CLI Tool

The `bin/propel` CLI provides commands for:
- `model:build` - Generate PHP classes from schema
- `sql:build` - Generate SQL from schema
- `sql:insert` - Execute generated SQL
- `migration:diff` - Generate migration from schema diff
- `migration:migrate` - Run pending migrations
- `database:reverse` - Generate schema from existing database

## Coding Standards

- Uses Spryker coding standard (extended from PSR-12)
- PHPStan level 7 compliance required
- PHP 8.3+ compatibility required
- Use `declare(strict_types=1)` in all PHP files

## Development Principles

Follow these core principles when making changes:

### YAGNI (You Aren't Gonna Need It)
- Only implement what is currently needed
- Don't add features, abstractions, or configurability "for the future"
- Remove unused code rather than commenting it out

### SOLID
- **Single Responsibility**: Each class/method should have one reason to change
- **Open/Closed**: Extend behavior through inheritance/composition, not modification
- **Liskov Substitution**: Subtypes must be substitutable for their base types
- **Interface Segregation**: Prefer small, focused interfaces over large ones
- **Dependency Inversion**: Depend on abstractions, not concrete implementations

### DRY (Don't Repeat Yourself)
- Extract common logic into reusable methods/traits
- But avoid premature abstraction - three occurrences before extracting
- Configuration and constants should have a single source of truth

### KISS (Keep It Simple, Stupid)
- Prefer simple, readable solutions over clever ones
- Minimize nesting and cyclomatic complexity
- Use early returns to reduce indentation
- Clear variable/method names over comments explaining unclear code

## Schema Definition

Schemas are defined in XML format (see `tests/Fixtures/` for examples). Key elements:
- `<database>` - Database configuration
- `<table>` - Table definition with columns, foreign keys, behaviors
- `<column>` - Column with type, constraints
- `<behavior>` - Attach behaviors like timestampable, sluggable, etc.
