<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection;

/**
 * @deprecated since 3.0, will be removed in 4.0. The functionality of the
 *             original PropelPDO class lives in {@see ConnectionWrapper}
 *             (nested transactions, logging) and {@see PdoConnection}
 *             (the PDO wrapper). Depend on those directly.
 */
class PropelPDO extends ConnectionWrapper
{
    /**
     * @param \Propel\Runtime\Connection\ConnectionInterface $connection
     */
    public function __construct(ConnectionInterface $connection)
    {
        trigger_deprecation(
            'maturix/propel',
            '3.0',
            'Class "%s" is deprecated, use "%s" directly.',
            self::class,
            ConnectionWrapper::class,
        );

        parent::__construct($connection);
    }
}
