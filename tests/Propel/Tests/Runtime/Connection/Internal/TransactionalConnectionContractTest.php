<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection\Internal;

use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\Internal\TransactionalConnection;
use Propel\Runtime\Connection\PdoConnection;
use Propel\Tests\Runtime\Connection\ConnectionInterfaceContractTestCase;

/**
 * Concrete: `TransactionalConnection(PdoConnection(SQLite))` MUST conform to
 * the {@see ConnectionInterface} contract.
 */
class TransactionalConnectionContractTest extends ConnectionInterfaceContractTestCase
{
    /**
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    #[\Override]
    protected function makeConnection(): ConnectionInterface
    {
        return new TransactionalConnection(new PdoConnection('sqlite::memory:'));
    }
}
