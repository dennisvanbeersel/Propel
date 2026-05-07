<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection\Internal;

use PDO;
use Propel\Runtime\Connection\ConnectionDecoratorInterface;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;

/**
 * Tier 3 internal base for all five Phase-E connection decorators.
 *
 * Forwards every {@see ConnectionInterface} method to the wrapped inner
 * connection by default. Subclasses override only the methods they actually
 * decorate. Constructor accepts the inner connection by typed dependency.
 *
 * Composition order is enforced by `ConnectionFactory`, not by this class.
 * Walk the chain via {@see self::getInner()}.
 *
 * @internal Phase E — replaces the legacy `ConnectionWrapper` 745-LOC blob.
 *
 * @see \Propel\Runtime\Connection\ConnectionDecoratorInterface
 *
 * @psalm-suppress UnusedClass abstract scaffold consumed by Phase-E
 *                 decorator subclasses (TransactionalConnection,
 *                 LoggingConnection, CachingConnection, etc.).
 */
abstract class AbstractConnectionDecorator implements ConnectionDecoratorInterface
{
    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface $inner
     */
    public function __construct(protected readonly ConnectionInterface $inner)
    {
    }

    /**
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    #[\Override]
    public function getInner(): ConnectionInterface
    {
        return $this->inner;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    #[\Override]
    public function setName(string $name): void
    {
        $this->inner->setName($name);
    }

    /**
     * @return string|null
     */
    #[\Override]
    public function getName(): ?string
    {
        return $this->inner->getName();
    }

    /**
     * @return bool
     */
    #[\Override]
    public function beginTransaction(): bool
    {
        return $this->inner->beginTransaction();
    }

    /**
     * @return bool
     */
    #[\Override]
    public function commit(): bool
    {
        return $this->inner->commit();
    }

    /**
     * @return bool
     */
    #[\Override]
    public function rollBack(): bool
    {
        return $this->inner->rollBack();
    }

    /**
     * @return bool
     */
    #[\Override]
    public function inTransaction(): bool
    {
        return $this->inner->inTransaction();
    }

    /**
     * @param int $attribute
     *
     * @return mixed
     */
    #[\Override]
    public function getAttribute(int $attribute)
    {
        return $this->inner->getAttribute($attribute);
    }

    /**
     * @param string|int $attribute
     * @param mixed $value
     *
     * @return bool
     */
    #[\Override]
    public function setAttribute($attribute, $value): bool
    {
        return $this->inner->setAttribute($attribute, $value);
    }

    /**
     * @param string|null $name
     *
     * @return string|int
     */
    #[\Override]
    public function lastInsertId(?string $name = null)
    {
        return $this->inner->lastInsertId($name);
    }

    /**
     * @param mixed $data
     *
     * @return \Propel\Runtime\DataFetcher\DataFetcherInterface
     */
    #[\Override]
    public function getSingleDataFetcher($data): DataFetcherInterface
    {
        return $this->inner->getSingleDataFetcher($data);
    }

    /**
     * @param mixed $data
     *
     * @return \Propel\Runtime\DataFetcher\DataFetcherInterface
     */
    #[\Override]
    public function getDataFetcher($data): DataFetcherInterface
    {
        return $this->inner->getDataFetcher($data);
    }

    /**
     * @param callable $callable
     *
     * @return mixed
     */
    #[\Override]
    public function transaction(callable $callable)
    {
        return $this->inner->transaction($callable);
    }

    /**
     * @param string $statement
     *
     * @return int
     */
    #[\Override]
    public function exec(string $statement): int
    {
        return $this->inner->exec($statement);
    }

    /**
     * @param string $statement
     * @param array<int, mixed> $driverOptions
     *
     * @return \Propel\Runtime\Connection\StatementInterface|\PDOStatement|false
     */
    #[\Override]
    public function prepare(string $statement, array $driverOptions = [])
    {
        return $this->inner->prepare($statement, $driverOptions);
    }

    /**
     * @param string $statement
     *
     * @return \Propel\Runtime\DataFetcher\DataFetcherInterface|\PDOStatement|false
     */
    #[\Override]
    public function query(string $statement)
    {
        return $this->inner->query($statement);
    }

    /**
     * @param string $string
     * @param int $parameterType
     *
     * @return string
     */
    #[\Override]
    public function quote(string $string, int $parameterType = PDO::PARAM_STR): string
    {
        return $this->inner->quote($string, $parameterType);
    }
}
