<?php

use SameOldNick\BackupManager\DbDumper\MySqlPHP;

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Manager Routes
    |--------------------------------------------------------------------------
    |
    | Here you can specify the settings for the routes used by the backup manager.
    | You can disable the routes by setting 'enabled' to false. You can also specify
    | the middleware, prefix and name for the routes. The 'all' key is used for all routes,
    | while the 'management' and 'download' keys are used for the management and download routes respectively.
     */
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

    /*
    |--------------------------------------------------------------------------
    | Database Dumper Extenders
    |--------------------------------------------------------------------------
    | Here you can specify the classes that will be used to extend the Spatie\DbDumper package.
     */
    'db_dumper_extenders' => [
        'mysql' => MySqlPHP::class,
    ],
];
