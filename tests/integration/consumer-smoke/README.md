# Consumer Smoke Project

Per umbrella spec §7.3, this directory hosts a minimal Propel-consumer project that exercises the Tier 1 surface end-to-end. Run on every PR via the `consumer-smoke` quality-gates CI job.

## Phase A scope (this commit)

- Directory + README scaffolded.
- Real `propel-consumer/` mini-project lands as part of Phase A.36's full implementation.

## What the consumer-smoke project will contain

- `composer.json` requiring `dennisvanbeersel/propel: dev-ar-rewrite` via path repository.
- A trivial schema (one `book` table, one `author`).
- `tests/SmokeTest.php` that calls every Tier 1 surface method:
  - `BookQuery::create()->find()`
  - `Book::save()`, `Book::delete()`
  - `BookQuery::create()->filterByTitle(...)->findOne()`
  - `BookQuery::create()->joinWith('Book.Author')->find()`
  - `BookQuery::create()->paginate()`
  - `Book::toArray()`, `Book::fromArray()`

The test asserts:
1. Each method returns the documented type.
2. Side effects (instance pool, transactions) match documented semantics.
3. No deprecation triggers fire on a stock execution.

## Running

```bash
cd tests/integration/consumer-smoke/propel-consumer
composer install
vendor/bin/phpunit
```

The CI job runs this on every PR. Exit non-zero blocks merge — Tier 1 break detected.
