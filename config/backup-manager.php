<?php

return [
    'routes' => [
        'enabled' => true,

        'all' => [
            'middleware' => [
                'web',
            ],
            'prefix' => '/backup',
            'as' => 'backup.',
        ],

        'management' => [
            'middleware' => [
                'auth',
            ],
        ],

        'download' => [
            'middleware' => [
                'signed',
            ],
        ],
    ],
];
