<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests;

use Propel\Runtime\Connection\ConnectionWrapper;

/**
 * The same as TestCaseFixtures but makes additional sure that
 * database schema has been updated.
 *
 * @author William Durand <william.durand1@gmail.com>
 */
class TestCaseFixturesDatabase extends TestCaseFixtures
{
    /**
     * @var bool
     */
    protected static $withDatabaseSchema = true;

    /**
     * Database integration tests assert against ConnectionWrapper::getLastExecutedQuery(),
     * which is only populated when the connection runs in debug mode. Enable it for the
     * duration of each database test and restore the default afterwards so non-database
     * tests running later in the same process are unaffected.
     *
     * @var bool
     */
    private bool $previousDebugMode = false;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->previousDebugMode = ConnectionWrapper::$useDebugMode;
        ConnectionWrapper::$useDebugMode = true;
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        ConnectionWrapper::$useDebugMode = $this->previousDebugMode;
        parent::tearDown();
    }
}
