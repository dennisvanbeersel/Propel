<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection;

/**
 * Manager for master/slave connection to a datasource.
 *
 * @deprecated since 3.0, will be removed in 4.0. Use {@see ConnectionManagerPrimaryReplica} instead.
 */
class ConnectionManagerMasterSlave extends ConnectionManagerPrimaryReplica
{
    /**
     * @param string $name The datasource name associated to this connection
     */
    public function __construct(string $name)
    {
        trigger_deprecation(
            'maturix/propel',
            '3.0',
            'Class "%s" is deprecated, use "%s" instead.',
            self::class,
            ConnectionManagerPrimaryReplica::class,
        );

        parent::__construct($name);
    }

    /**
     * For replication, whether to always force the use of a master connection.
     *
     * @deprecated since 3.0, use isForcePrimaryConnection() instead.
     *
     * @return bool
     */
    public function isForceMasterConnection(): bool
    {
        return $this->isForcePrimaryConnection();
    }

    /**
     * For replication, set whether to always force the use of a master connection.
     *
     * @deprecated since 3.0, use setForcePrimaryConnection() instead.
     *
     * @param bool $isForceMasterConnection
     *
     * @return void
     */
    public function setForceMasterConnection(bool $isForceMasterConnection): void
    {
        $this->setForcePrimaryConnection($isForceMasterConnection);
    }
}
