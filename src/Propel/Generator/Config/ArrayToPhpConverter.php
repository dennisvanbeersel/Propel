<?php

/**
 * MIT License. This file is part of the Propel package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Propel\Generator\Config;

/**
 * Runtime configuration converter
 * From array to PHP string
 */
class ArrayToPhpConverter
{
    /**
     * Create a PHP configuration from an array
     *
     * @param array<string, mixed> $c The array configuration
     *
     * @return string
     */
    public static function convert(array $c): string
    {
        $conf = [];
        // set datasources
        if (isset($c['connections'])) {
            foreach ($c['connections'] as $name => $params) {
                if (!is_array($params)) {
                    continue;
                }

                // set adapters
                if (isset($params['adapter'])) {
                    $conf[] = "\$serviceContainer->setAdapterClass('{$name}', '{$params['adapter']}');";
                }

                // set connection settings
                if (isset($params['replicas'])) {
                    $conf[] = "\$manager = new \Propel\Runtime\Connection\ConnectionManagerPrimaryReplica('{$name}');";
                    $conf[] = '$manager->setReadConfiguration(' . var_export($params['replicas'], true) . ');';
                } elseif (isset($params['dsn'])) {
                    $conf[] = "\$manager = new \Propel\Runtime\Connection\ConnectionManagerSingle('{$name}');";
                } else {
                    continue;
                }

                if (isset($params['dsn'])) {
                    $primaryConfigurationSetter = isset($params['replicas']) ? 'setWriteConfiguration' : 'setConfiguration';
                    $connection = $params;
                    unset($connection['adapter']);
                    unset($connection['replicas']);
                    $conf[] = "\$manager->{$primaryConfigurationSetter}(" . var_export($connection, true) . ');';
                }

                $conf[] = '$serviceContainer->setConnectionManager($manager);';
            }

            // set default datasource
            if (isset($c['defaultConnection'])) {
                $defaultDatasource = $c['defaultConnection'];
            } elseif (is_array($c['connections'])) {
                // fallback to the first datasource
                $datasourceNames = array_keys($c['connections']);
                $defaultDatasource = $datasourceNames[0];
            }

            $conf[] = "\$serviceContainer->setDefaultDatasource('{$defaultDatasource}');";
        }

        // set profiler
        if (isset($c['profiler'])) {
            $profilerConf = $c['profiler'];
            if (isset($profilerConf['classname'])) {
                $conf[] = "\$serviceContainer->setProfilerClass('{$profilerConf['classname']}');";
                unset($profilerConf['classname']);
            }

            if ($profilerConf) {
                $conf[] = '$serviceContainer->setProfilerConfiguration(' . var_export($profilerConf, true) . ');';
            }
            unset($c['profiler']);
        }

        // set logger
        if (isset($c['log']) && count($c['log']) > 0) {
            foreach ($c['log'] as $key => $logger) {
                $conf[] = "\$serviceContainer->setLoggerConfiguration('{$key}', " . var_export($logger, true) . ');';
            }
            unset($c['log']);
        }

        $conf = implode(PHP_EOL, $conf);

        return preg_replace('/[ \t]*$/m', '', $conf);
    }
}
