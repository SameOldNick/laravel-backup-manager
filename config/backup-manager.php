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

        'test-destinations' => [
            'prefix' => '/{destination}/test',
            'as' => 'test.',
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

    /*
    |--------------------------------------------------------------------------
    | Jobs
    |--------------------------------------------------------------------------
    | Here you can specify the default configuration for each job type.
     */
    'jobs' => [
        'backup' => [
            /**
             * The name of the connection the job should be sent to.
             *
             * @var string|BackedEnum|null
             */
            'connection' => env('BACKUP_QUEUE_CONNECTION', null),

            /**
             * The name of the queue the job should be sent to.
             *
             * @var string|BackedEnum|null
             */
            'queue' => env('BACKUP_QUEUE_NAME', null),

            /**
             * The job "group" the job should be sent to.
             *
             * @var string|BackedEnum|null
             */
            'group' => env('BACKUP_QUEUE_GROUP', null),

            /**
             * The delay (in seconds) before the job should be run.
             *
             * @var int|null
             */
            'delay' => env('BACKUP_QUEUE_DELAY', null),

            /**
             * The middleware the job should be dispatched through.
             *
             * @var array|null
             */
            'middleware' => env('BACKUP_QUEUE_MIDDLEWARE', null),

            /**
             * Indicates whether the job should be dispatched after all database transactions have committed.
             *
             * @var bool|null
             */
            'after_commit' => env('BACKUP_QUEUE_AFTER_COMMIT', null),
        ],

        'cleanup' => [
            /**
             * The name of the connection the job should be sent to.
             *
             * @var string|BackedEnum|null
             */
            'connection' => env('CLEANUP_QUEUE_CONNECTION', null),

            /**
             * The name of the queue the job should be sent to.
             *
             * @var string|BackedEnum|null
             */
            'queue' => env('CLEANUP_QUEUE_NAME', null),

            /**
             * The job "group" the job should be sent to.
             *
             * @var string|BackedEnum|null
             */
            'group' => env('CLEANUP_QUEUE_GROUP', null),

            /**
             * The delay (in seconds) before the job should be run.
             *
             * @var int|null
             */
            'delay' => env('CLEANUP_QUEUE_DELAY', null),

            /**
             * The middleware the job should be dispatched through.
             *
             * @var array|null
             */
            'middleware' => env('CLEANUP_QUEUE_MIDDLEWARE', null),

            /**
             * Indicates whether the job should be dispatched after all database transactions have committed.
             *
             * @var bool|null
             */
            'after_commit' => env('CLEANUP_QUEUE_AFTER_COMMIT', null),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Scheduler
    |--------------------------------------------------------------------------
    |
    | Scheduler registration runs whenever Laravel rebuilds its scheduled
    | events.
    */
    'scheduler' => [
        /**
         * The time-to-live (TTL) for handling errors during schedule registration.
         * This is the amount of time (in seconds) that the system will wait before errors are logged again for the same schedule registration issue.
         *
         * @var int
         */
        'error_ttl' => env('BACKUP_SCHEDULER_ERROR_TTL', 900),

        /**
         * Attributes applied to the scheduled backup and cleanup events.
         * Each attribute is keyed by schedule type ("backup" or "cleanup"),
         * so both schedule types can be configured independently.
         *
         * @var array{
         *     without_overlapping: array{backup: array{enabled: bool, expires_at: int, release_on_termination_signals: bool}, cleanup: array{enabled: bool, expires_at: int, release_on_termination_signals: bool}},
         *     on_one_server: array{backup: bool, cleanup: bool},
         *     user: array{backup: string|null, cleanup: string|null},
         *     environments: array{backup: array|string|null, cleanup: array|string|null},
         *     even_in_maintenance_mode: array{backup: bool, cleanup: bool},
         *     even_when_paused: array{backup: bool, cleanup: bool},
         *     timezone: array{backup: string|null, cleanup: string|null},
         *     when: array{backup: callable|null, cleanup: callable|null}
         * }
         */
        'attributes' => [
            /**
             * Configuration for the "without overlapping" feature of the scheduled events.
             * This feature prevents multiple instances of the same scheduled event from running simultaneously.
             *
             * It's recommended to use the \Illuminate\Queue\Middleware\WithoutOverlapping middleware for jobs
             * that are dispatched by the scheduled events, as it provides a more robust solution for preventing
             * overlapping jobs. This can be configured in the 'jobs' section of this configuration file.
             *
             * @var array{backup: array{enabled: bool, expires_at: int, release_on_termination_signals: bool}, cleanup: array{enabled: bool, expires_at: int, release_on_termination_signals: bool}}
             */
            'without_overlapping' => [
                'backup' => [
                    'enabled' => env('BACKUP_SCHEDULER_BACKUP_WITHOUT_OVERLAPPING', false),
                    'expires_at' => env('BACKUP_SCHEDULER_BACKUP_EXPIRES_AT', 1440),
                    'release_on_termination_signals' => env('BACKUP_SCHEDULER_BACKUP_RELEASE_ON_TERMINATION_SIGNALS', true),
                ],
                'cleanup' => [
                    'enabled' => env('BACKUP_SCHEDULER_CLEANUP_WITHOUT_OVERLAPPING', false),
                    'expires_at' => env('BACKUP_SCHEDULER_CLEANUP_EXPIRES_AT', 1440),
                    'release_on_termination_signals' => env('BACKUP_SCHEDULER_CLEANUP_RELEASE_ON_TERMINATION_SIGNALS', true),
                ],
            ],
            /**
             * Configuration for the "on one server" feature of the scheduled events.
             * This feature ensures that each scheduled event runs on only one server in a multi-server environment.
             * This requires a cache store that is shared by all servers.
             *
             * @var array{backup: bool, cleanup: bool}
             */
            'on_one_server' => [
                'backup' => env('BACKUP_SCHEDULER_BACKUP_ON_ONE_SERVER', true),
                'cleanup' => env('BACKUP_SCHEDULER_CLEANUP_ON_ONE_SERVER', true),
            ],

            /**
             * The user that the scheduled events should run as.
             *
             * @var array{backup: string|null, cleanup: string|null}
             */
            'user' => [
                'backup' => env('BACKUP_SCHEDULER_BACKUP_USER', null),
                'cleanup' => env('BACKUP_SCHEDULER_CLEANUP_USER', null),
            ],

            /**
             * The environments that the scheduled events should run in.
             * Each value may be an array of environment names or a comma-separated string.
             *
             * @var array{backup: array|string|null, cleanup: array|string|null}
             */
            'environments' => [
                'backup' => env('BACKUP_SCHEDULER_BACKUP_ENVIRONMENTS', '') ? implode(',', env('BACKUP_SCHEDULER_BACKUP_ENVIRONMENTS', '')) : null,
                'cleanup' => env('BACKUP_SCHEDULER_CLEANUP_ENVIRONMENTS', '') ? implode(',', env('BACKUP_SCHEDULER_CLEANUP_ENVIRONMENTS', '')) : null,
            ],

            /**
             * Indicates whether the scheduled events should run in maintenance mode.
             *
             * @var array{backup: bool, cleanup: bool}
             */
            'even_in_maintenance_mode' => [
                'backup' => env('BACKUP_SCHEDULER_BACKUP_EVEN_IN_MAINTENANCE_MODE', false),
                'cleanup' => env('BACKUP_SCHEDULER_CLEANUP_EVEN_IN_MAINTENANCE_MODE', false),
            ],

            /**
             * Indicates whether the scheduled events should run even when the scheduler is paused.
             *
             * @var array{backup: bool, cleanup: bool}
             */
            'even_when_paused' => [
                'backup' => env('BACKUP_SCHEDULER_BACKUP_EVEN_WHEN_PAUSED', false),
                'cleanup' => env('BACKUP_SCHEDULER_CLEANUP_EVEN_WHEN_PAUSED', false),
            ],

            /**
             * The timezone that the scheduled events should run in.
             *
             * @var array{backup: string|null, cleanup: string|null}
             */
            'timezone' => [
                'backup' => env('BACKUP_SCHEDULER_BACKUP_TIMEZONE', null),
                'cleanup' => env('BACKUP_SCHEDULER_CLEANUP_TIMEZONE', null),
            ],

            /**
             * The conditions that determine if the scheduled events should run.
             * Each value can be an anonymous function or a callable that returns a boolean value.
             *
             * @var array{backup: callable|null, cleanup: callable|null}
             */
            'when' => [
                'backup' => null,
                'cleanup' => null,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Config Fallbacks
    |--------------------------------------------------------------------------
    | Here you can specify the config values that will be used as fallbacks when the database
    | configuration is not available.
    */
    'config_fallbacks' => [
        /**
         * If true, the original 'backup.destinations.disks' config will be used for destination disks when the database is not available.
         * If false, the disks from the original configuration will be used instead.
         * If an array of disks is provided, those disks will be used.
         */
        'destination_disks' => true,
        /**
         * If true, the original 'backup.monitor_backups' config will be used for monitored backups when the database is not available.
         * If false, the monitored backups from the original configuration will be used instead.
         * If an array of monitored backups is provided, those monitored backups will be used.
         */
        'monitor_backups' => true,
    ],
];
