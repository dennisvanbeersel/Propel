<?php

// Phase G.2.7: `Propel::initConfiguration()` was a pre-modernization bootstrap
// helper that no longer exists in modern Propel. The PropelInitConfigurationRector
// rule rewrites callsites to the modern service-container path.

use Propel\Runtime\Propel;

Propel::getServiceContainer()->setConfiguration([
    'datasources' => [
        'default' => [
            'adapter' => 'sqlite',
            'connection' => [
                'dsn' => 'sqlite::memory:',
            ],
        ],
    ],
]);
