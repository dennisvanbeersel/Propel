<?php

declare(strict_types=1);

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Propel\Tests\Common\Config;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Propel\Common\Config\PropelConfiguration;
use Symfony\Bridge\PhpUnit\ExpectUserDeprecationMessageTrait;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * Per umbrella spec section 6.2: legacy "slaves" / "master" connection-config
 * keys should forward to the new "replicas" / "primary" names with a
 * deprecation pointer; unsupported adapters should fail with a migration-guide
 * message rather than a generic enum error.
 */
#[Group('legacy')]
class DeprecatedConfigKeysTest extends TestCase
{
    use ExpectUserDeprecationMessageTrait;

    /**
     * @return array<string, mixed>
     */
    private function processConfig(array $connection): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new PropelConfiguration(), [[
            'database' => [
                'connections' => [
                    'default' => $connection,
                ],
            ],
        ]]);
    }

    public function testSlavesKeyForwardsToReplicasAndEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Configuration key "slaves" is deprecated, use "replicas" instead.',
        );

        $config = $this->processConfig([
            'adapter' => 'mysql',
            'dsn' => 'mysql:host=localhost;dbname=test',
            'user' => 'root',
            'password' => '',
            'slaves' => [
                ['dsn' => 'mysql:host=replica;dbname=test', 'user' => 'root', 'password' => ''],
            ],
        ]);

        $connection = $config['database']['connections']['default'];
        self::assertArrayHasKey('replicas', $connection);
        self::assertArrayNotHasKey('slaves', $connection);
        self::assertSame('mysql:host=replica;dbname=test', $connection['replicas'][0]['dsn']);
    }

    public function testMasterKeyForwardsToPrimaryAndEmitsDeprecation(): void
    {
        $this->expectUserDeprecationMessage(
            'Since maturix/propel 3.0: Configuration key "master" is deprecated, use "primary" (or inline dsn/user/password directly) instead.',
        );

        $config = $this->processConfig([
            'adapter' => 'mysql',
            'master' => [
                'dsn' => 'mysql:host=localhost;dbname=test',
                'user' => 'root',
                'password' => '',
            ],
        ]);

        $connection = $config['database']['connections']['default'];
        self::assertSame('mysql:host=localhost;dbname=test', $connection['dsn']);
        self::assertArrayNotHasKey('master', $connection);
    }

    public function testOracleAdapterPointsAtMigrationGuide(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/Adapter "oracle" is not supported.+MIGRATION-FROM-PRE-AI/s');

        $this->processConfig([
            'adapter' => 'oracle',
            'dsn' => 'oci:dbname=test',
            'user' => 'system',
            'password' => 'oracle',
        ]);
    }
}
