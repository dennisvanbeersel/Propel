<?php

return [
    'database' => [
        'connections' => [
            'default' => [
                'adapter' => 'mysql',
                'dsn' => 'mysql:host=primary;dbname=test',
                'slaves' => [
                    ['dsn' => 'mysql:host=replica1;dbname=test'],
                    ['dsn' => 'mysql:host=replica2;dbname=test'],
                ],
            ],
        ],
    ],
];
