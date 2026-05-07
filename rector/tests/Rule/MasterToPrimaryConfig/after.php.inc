<?php

return [
    'database' => [
        'connections' => [
            'default' => [
                'adapter' => 'mysql',
                'primary' => [
                    'dsn' => 'mysql:host=localhost;dbname=test',
                    'user' => 'root',
                ],
            ],
        ],
    ],
];
