<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection;

use PDO;
use Propel\Runtime\Connection\Internal\PdoAttributeMap;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\DataFetcher\PDODataFetcher;

/**
 * Bare bridge between `\PDO` and {@see ConnectionInterface}.
 *
 * Phase E rewrite — collapses the legacy mixed-responsibility class to a
 * thin pass-through. All decoration concerns (logging, caching,
 * transaction-counting, profiling, replica routing) live in
 * `Connection/Internal/*` decorators per umbrella spec §2.1.
 *
 * Behavior changes from pre-Phase-E:
 *  - `final`: never legitimately subclassable. Any `extends PdoConnection`
 *    is undeclared and unsupported.
 *  - PDO attribute string-name resolution moves to {@see PdoAttributeMap},
 *    which eager-resolves at class load (no `defined()`/`constant()` on
 *    the hot path; umbrella §6.2 risk #3).
 *  - HHVM `prepare`/`quote` overrides removed (umbrella §6.2 risk #4 —
 *    PHP 8.3 `\PDO::prepare`/`\PDO::quote` semantics are sufficient).
 *
 * @final Phase E — additive `final`. The class was Tier 3 internal per
 *        Phase A snapshot and never publicly subclassable in 3.x.
 */
final class PdoConnection implements ConnectionInterface
{
    use TransactionTrait;

    /**
     * The datasource name associated to this connection.
     */
    protected ?string $name = null;

    /**
     * The wrapped PDO instance. Composition, not inheritance.
     */
    protected PDO $pdo;

    /**
     * @param string $dsn
     * @param string|null $user
     * @param string|null $password
     * @param array<int|string, mixed>|null $options Driver-options keyed by either an
     *                            int PDO::ATTR_* constant or its string name (resolved via PdoAttributeMap).
     */
    public function __construct(string $dsn, ?string $user = null, ?string $password = null, ?array $options = null)
    {
        $pdoOptions = [];
        if ($options) {
            foreach ($options as $key => $option) {
                $pdoOptions[PdoAttributeMap::resolve($key)] = $option;
            }
        }

        $this->pdo = new PDO($dsn, $user, $password, $pdoOptions);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Forward any calls to an inaccessible method to the proxied PDO.
     *
     * @param string $method
     * @param array<int, mixed> $args
     *
     * @return mixed
     */
    public function __call(string $method, array $args)
    {
        return $this->pdo->$method(...$args);
    }

    /**
     * @param string $name
     *
     * @return void
     */
    #[\Override]
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    #[\Override]
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets a connection attribute.
     *
     * Accepts either a string PDO constant name (e.g. `'ATTR_CASE'` or
     * `'PDO::ATTR_CASE'`) or an int constant; string names resolve via
     * {@see PdoAttributeMap} (eager-cached; no runtime `defined()`).
     *
     * @param string|int $attribute
     * @param mixed $value
     *
     * @return bool
     */
    #[\Override]
    public function setAttribute($attribute, $value): bool
    {
        return $this->pdo->setAttribute(PdoAttributeMap::resolve($attribute), $value);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getAttribute(int $attribute)
    {
        return $this->pdo->getAttribute($attribute);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getDataFetcher($data): DataFetcherInterface
    {
        return new PDODataFetcher($data);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function getSingleDataFetcher($data): DataFetcherInterface
    {
        return $this->getDataFetcher($data);
    }

    /**
     * @inheritDoc
     *
     * @return \PDOStatement|false
     */
    #[\Override]
    public function query(string $statement)
    {
        return $this->pdo->query($statement);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function exec($statement): int
    {
        return (int)$this->pdo->exec($statement);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * @param string|null $name
     *
     * @return string|false
     */
    #[\Override]
    public function lastInsertId(?string $name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * @inheritDoc
     *
     * @return \PDOStatement|false
     */
    #[\Override]
    public function prepare(string $statement, array $driverOptions = [])
    {
        return $this->pdo->prepare($statement, $driverOptions);
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function quote(string $string, int $parameterType = PDO::PARAM_STR): string
    {
        return $this->pdo->quote($string, $parameterType);
    }

    /**
     * @return bool
     */
    #[\Override]
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    #[\Override]
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    #[\Override]
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }
}
