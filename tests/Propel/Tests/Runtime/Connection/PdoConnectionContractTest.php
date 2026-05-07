<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Tests\Runtime\Connection;

use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Connection\PdoConnection;

/**
 * Concrete: bare {@see PdoConnection} against SQLite (umbrella §1.2 agnostic target).
 */
class PdoConnectionContractTest extends ConnectionInterfaceContractTestCase
{
    /**
     * @return \Propel\Runtime\Connection\ConnectionInterface
     */
    #[\Override]
    protected function makeConnection(): ConnectionInterface
    {
        return new PdoConnection('sqlite::memory:');
    }
}
