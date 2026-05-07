<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Runtime\Connection;

use PDO;
use Propel\Runtime\Adapter\Pdo\SqliteAdapter;
use Propel\Runtime\Connection\ConnectionFactory;
use Propel\Runtime\Connection\ConnectionWrapper;
use Propel\Runtime\Connection\Exception\ConnectionDecoratorException;
use Propel\Runtime\Connection\Internal\CachingConnection;
use Propel\Runtime\Connection\Internal\LoggingConnection;
use Propel\Runtime\Connection\Internal\ProfilingConnection;
use Propel\Runtime\Connection\Internal\ReplicaRoutingConnection;
use Propel\Runtime\Connection\Internal\TransactionalConnection;
use Propel\Runtime\Connection\ProfilerConnectionWrapper;
use Propel\Runtime\Exception\InvalidArgumentException;
use Propel\Tests\Helpers\BaseTestCase;

class ConnectionFactoryTest extends BaseTestCase
{
    /**
     * @return void
     */
    public function tearDown(): void
    {
        ConnectionFactory::$useProfilerConnection = false;
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testCreateFailsIfGivenIncorrectConfiguration()
    {
        $this->expectException(InvalidArgumentException::class);

        $con = ConnectionFactory::create([], new SqliteAdapter());
    }

    /**
     * @return void
     */
    public function testCreateReturnsAConnectionWrapperByDefault()
    {
        $con = ConnectionFactory::create(['dsn' => 'sqlite::memory:'], new SqliteAdapter());
        $this->assertInstanceOf('Propel\Runtime\Connection\ConnectionWrapper', $con);
    }

    /**
     * @return void
     */
    public function testCreateReturnsACustomConnectionClassIfPassedAsThirdArgument()
    {
        $con = ConnectionFactory::create(['dsn' => 'sqlite::memory:'], new SqliteAdapter(), 'Propel\Tests\Runtime\Connection\MyConnectionForFactoryTest1');
        $this->assertInstanceOf('Propel\Tests\Runtime\Connection\MyConnectionForFactoryTest1', $con);
    }

    /**
     * @return void
     */
    public function testCreateReturnsACustomConnectionClassIfPassedInConfiguration()
    {
        $con = ConnectionFactory::create(['dsn' => 'sqlite::memory:', 'classname' => 'Propel\Tests\Runtime\Connection\MyConnectionForFactoryTest2'], new SqliteAdapter());
        $this->assertInstanceOf('Propel\Tests\Runtime\Connection\MyConnectionForFactoryTest2', $con);
    }

    /**
     * @return void
     */
    public function testCreatePreferablyUsesCustomConnectionClassFromConfiguration()
    {
        $con = ConnectionFactory::create(['dsn' => 'sqlite::memory:', 'classname' => 'Propel\Tests\Runtime\Connection\MyConnectionForFactoryTest2'], new SqliteAdapter(), 'Propel\Tests\Runtime\Connection\MyConnectionForFactoryTest1');
        $this->assertInstanceOf('Propel\Tests\Runtime\Connection\MyConnectionForFactoryTest2', $con);
    }

    /**
     * @return void
     */
    public function testCreateReturnsWrappedConnectionBuildByTheAdapter()
    {
        $con = ConnectionFactory::create(['dsn' => 'sqlite::memory:'], new SqliteAdapter());
        $pdo = $con->getWrappedConnection();
        $this->assertInstanceOf('Propel\Runtime\Connection\PdoConnection', $pdo);
    }

    /**
     * @return void
     */
    public function testCreateSetsAttributesAfterConnection()
    {
        $con = ConnectionFactory::create(['dsn' => 'sqlite::memory:', 'attributes' => [PDO::ATTR_CASE => PDO::CASE_LOWER]], new SqliteAdapter());
        $pdo = $con->getWrappedConnection();
        $this->assertEquals(PDO::CASE_LOWER, $pdo->getAttribute(PDO::ATTR_CASE));
    }

    /**
     * @return void
     */
    public function testCreateSetsAttributesAfterConnectionAndExpandsConstantNames()
    {
        $con = ConnectionFactory::create(['dsn' => 'sqlite::memory:', 'attributes' => ['ATTR_CASE' => PDO::CASE_LOWER]], new SqliteAdapter());
        $pdo = $con->getWrappedConnection();
        $this->assertEquals(PDO::CASE_LOWER, $pdo->getAttribute(PDO::ATTR_CASE));
    }

    /**
     * @return void
     */
    public function testCreateFailsWhenPassedAnIncorrectAttributeName()
    {
        $this->expectException(InvalidArgumentException::class);

        $con = ConnectionFactory::create(['dsn' => 'sqlite::memory:', 'attributes' => ['ATTR_CAE' => PDO::CASE_LOWER]], new SqliteAdapter());
    }

    /**
     * @return void
     */
    public function testUseProfilerConnectionOverridesConnectionClass()
    {
        ConnectionFactory::$useProfilerConnection = true;
        $config = ['dsn' => 'sqlite::memory:', 'classname' => ConnectionWrapper::class];
        $con = ConnectionFactory::create($config, new SqliteAdapter());
        $this->assertInstanceOf(ProfilerConnectionWrapper::class, $con);
    }

    /**
     * @return void
     */
    public function testExplicitDecoratorListBuildsChain()
    {
        $con = ConnectionFactory::create(
            [
                'dsn' => 'sqlite::memory:',
                'decorators' => ['transactional', 'logging', 'caching'],
            ],
            new SqliteAdapter(),
        );

        $this->assertInstanceOf(CachingConnection::class, $con);
        // Walk the chain: caching -> logging -> transactional -> pdo.
        $logging = $con->getInner();
        $this->assertInstanceOf(LoggingConnection::class, $logging);
        $tx = $logging->getInner();
        $this->assertInstanceOf(TransactionalConnection::class, $tx);
    }

    /**
     * @return void
     */
    public function testProfilingDecoratorIncludedWhenRequested()
    {
        $con = ConnectionFactory::create(
            [
                'dsn' => 'sqlite::memory:',
                'decorators' => ['transactional', 'logging', 'caching', 'profiling'],
            ],
            new SqliteAdapter(),
        );

        $this->assertInstanceOf(ProfilingConnection::class, $con);
    }

    /**
     * @return void
     */
    public function testEmptyDecoratorListYieldsBareConnection()
    {
        $con = ConnectionFactory::create(
            [
                'dsn' => 'sqlite::memory:',
                'decorators' => [],
            ],
            new SqliteAdapter(),
        );

        $this->assertInstanceOf('Propel\Runtime\Connection\PdoConnection', $con);
    }

    /**
     * @return void
     */
    public function testMisorderedDecoratorListThrows()
    {
        $this->expectException(ConnectionDecoratorException::class);
        ConnectionFactory::create(
            [
                'dsn' => 'sqlite::memory:',
                'decorators' => ['caching', 'transactional'],
            ],
            new SqliteAdapter(),
        );
    }

    /**
     * @return void
     */
    public function testUnknownDecoratorThrows()
    {
        $this->expectException(ConnectionDecoratorException::class);
        ConnectionFactory::create(
            [
                'dsn' => 'sqlite::memory:',
                'decorators' => ['transactional', 'mystery'],
            ],
            new SqliteAdapter(),
        );
    }

    /**
     * @return void
     */
    public function testReplicasConfiguredWrapsInRoutingConnection()
    {
        $con = ConnectionFactory::create(
            [
                'dsn' => 'sqlite::memory:',
                'decorators' => ['transactional', 'logging', 'caching'],
                'replicas' => [
                    'r1' => ['dsn' => 'sqlite::memory:'],
                ],
            ],
            new SqliteAdapter(),
        );

        $this->assertInstanceOf(ReplicaRoutingConnection::class, $con);
    }
}

class MyConnectionForFactoryTest1 extends ConnectionWrapper
{
}
class MyConnectionForFactoryTest2 extends ConnectionWrapper
{
}
