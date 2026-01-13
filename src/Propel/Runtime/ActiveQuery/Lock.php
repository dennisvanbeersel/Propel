<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\ActiveQuery;

/**
 * Class represents a query lock
 *
 * @author Tomasz Wójcik <tomasz.prgtw.wojcik@gmail.com>
 */
class Lock
{
    /**
     * @var string
     */
    public const SHARED = 'SHARED';

    /**
     * @var string
     */
    public const EXCLUSIVE = 'EXCLUSIVE';

    /**
     * @param string $type Lock type (either shared or exclusive)
     * @param array<string> $tableNames Table names to lock
     * @param bool $noWait Whether to issue a non-blocking lock
     *
     * @see self::SHARED
     * @see self::EXCLUSIVE
     */
    public function __construct(
        protected readonly string $type,
        protected readonly array $tableNames = [],
        protected readonly bool $noWait = false,
    ) {
    }

    /**
     * Lock type
     *
     * @see self::SHARED
     * @see self::EXCLUSIVE
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns table names to lock
     *
     * @return array<string>
     */
    public function getTableNames(): array
    {
        return $this->tableNames;
    }

    /**
     * Whether to issue a non-blocking lock
     *
     * @return bool
     */
    public function isNoWait(): bool
    {
        return $this->noWait;
    }

    /**
     * Checks whether a lock equals another lock object
     *
     * @param \Propel\Runtime\ActiveQuery\Lock|mixed $lock
     *
     * @return bool
     */
    public function equals($lock): bool
    {
        if (!($lock instanceof self)) {
            return false;
        }

        $aTableNames = $this->getTableNames();
        $bTableNames = $lock->getTableNames();

        return $this->getType() === $lock->getType()
            && $this->isNoWait() === $lock->isNoWait()
            && $aTableNames === array_intersect($aTableNames, $bTableNames)
            && $bTableNames === array_intersect($bTableNames, $aTableNames);
    }
}
