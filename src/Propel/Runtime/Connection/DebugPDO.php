<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Runtime\Connection;

/**
 * Connection wrapper class with debug enabled by default.
 *
 * @deprecated since 3.0, will be removed in 4.0. Use {@see ConnectionWrapper}
 *             directly and toggle debug mode via {@see ConnectionWrapper::useDebug()}.
 */
class DebugPDO extends ConnectionWrapper
{
    protected ?bool $useDebugModeOnInstance = true;

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
