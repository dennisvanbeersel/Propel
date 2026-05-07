<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection\Internal;

use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\Internal\LoggingConnection;
use Propel\Runtime\Connection\PdoConnection;
use Propel\Tests\Runtime\Connection\ConnectionInterfaceContractTestCase;

/**
 * Concrete: `LoggingConnection(PdoConnection(SQLite))` MUST conform to
 * the {@see ConnectionInterface} contract.
 */
class LoggingConnectionContractTest extends ConnectionInterfaceContractTestCase
{
    /**
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    #[\Override]
    protected function makeConnection(): ConnectionInterface
    {
        return new LoggingConnection(new PdoConnection('sqlite::memory:'));
    }
}
