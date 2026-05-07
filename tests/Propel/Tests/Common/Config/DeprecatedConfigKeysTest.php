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
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

/**
 * Per umbrella spec section 6.2: legacy "slaves" / "master" connection-config
 * keys were @deprecated in 3.0 and removed in 4.0. Using either now produces
 * a hard InvalidConfigurationException pointing at the modern key plus the
 * Rector ruleset. Unsupported adapters likewise fail with a migration-guide
 * message rather than a generic enum error.
 */
#[Group('legacy')]
class DeprecatedConfigKeysTest extends TestCase
{

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

    public function testSlavesKeyThrowsInPropel4(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/Configuration key "slaves" was removed in Propel 4\.0.+Propel4Migration/s');

        $this->processConfig([
            'adapter' => 'mysql',
            'dsn' => 'mysql:host=localhost;dbname=test',
            'user' => 'root',
            'password' => '',
            'slaves' => [
                ['dsn' => 'mysql:host=replica;dbname=test', 'user' => 'root', 'password' => ''],
            ],
        ]);
    }

    public function testMasterKeyThrowsInPropel4(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/Configuration key "master" was removed in Propel 4\.0.+Propel4Migration/s');

        $this->processConfig([
            'adapter' => 'mysql',
            'master' => [
                'dsn' => 'mysql:host=localhost;dbname=test',
                'user' => 'root',
                'password' => '',
            ],
        ]);
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
