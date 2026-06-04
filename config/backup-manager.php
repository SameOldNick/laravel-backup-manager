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

        'download' => [
            'middleware' => [
                'signed',
            ],
        ],
    ],
];
