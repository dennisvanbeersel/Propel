<?php

return [
    'database' => [
        'connections' => [
            'default' => [
                'adapter' => 'mysql',
                'master' => [
                    'dsn' => 'mysql:host=localhost;dbname=test',
                    'user' => 'root',
                ],
            ],
        ],
    ],
];
