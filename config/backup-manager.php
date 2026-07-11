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

        'backups' => [
            'prefix' => '/backups',
            'as' => 'backups.',
        ],

        'perform' => [
            'prefix' => '/perform',
            'as' => 'perform.',
        ],

        'destinations' => [
            'prefix' => '/destinations',
            'as' => 'destinations.',
        ],

        'monitors' => [
            'prefix' => '/monitors',
            'as' => 'monitors.',
        ],

        'schedules' => [
            'prefix' => '/schedules',
            'as' => 'schedules.',
        ],

        'files' => [
            'prefix' => '/files',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Channel Leases
    |--------------------------------------------------------------------------
    |
    | Channel leases are used to authorize real-time broadcasting access during
    | backup and destination test operations.
    | The lease prefix is used to generate the channel ID for the lease, and the TTL (in minutes) is used to determine how long the lease is valid.
    |
     */
    'channel_leases' => [
        'perform_backup' => [
            'prefix' => 'backups',
            'ttl' => 180, // 3 hours
        ],

        'test_backup_destination' => [
            'prefix' => 'test-destination',
            'ttl' => 180, // 3 hours
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
