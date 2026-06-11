# Laravel Backup Manager

Database-driven backup management for Laravel, built on [spatie/laravel-backup](https://spatie.be/docs/laravel-backup).
Backup and cleanup schedules live in the database, storage destinations are dynamic database records, and all Spatie
Backup features (file selection, database dumping, compression, encryption, notifications, health checks) remain
available.

## Relationship with Spatie Backup

This package **wraps** Spatie Backup — it does not replace it. Two things are overridden at runtime when the database is
available:

- **`destination.disks`** — Replaced with active `FilesystemConfiguration` records from the `filesystem_configurations`
table. The `disks` array in `config/backup.php` is ignored.
- **Schedules** — Backup and cleanup schedules are stored in `backup_schedules` and `cleanup_schedules` tables, not
`routes/console.php`. The `BackupScheduler` class reads them and registers them with Laravel's task scheduler.

Everything else in `config/backup.php` applies as-is: source files, database connections to dump, compression,
encryption, notifications, monitoring health checks, and cleanup strategy.

## Namespace and conventions

- Root namespace: `SameOldNick\BackupManager`
- Package name in composer: `sameoldnick/laravel-backup-manager`
- Service provider: `SameOldNick\BackupManager\ServiceProvider`
- Config files: `config/backup-manager.php` (route settings), `config/backup.php` (standard Spatie Backup config)

## Directory structure

```
src/
Commands/ InstallBackupManager
Contracts/ Interfaces (responders, config providers, channel access)
Responders/ BackupDestinationsUiResponder, BackupsUiResponder, etc.
DataTransferObjects/ Typed DTOs for responder methods
Enums/ BackupTypes, BackupRunStatus, BackupStatus
Http/Controllers/ Route controllers (BackupController, BackupDestinationsController, etc.)
Jobs/ BackupJob (dispatched by scheduler or on-demand)
Notifiable/ Notifiable backup job
Listeners/ BackupProjectionSubscriber (syncs Spatie events to DB models)
Models/ BackupSchedule, CleanupSchedule, FilesystemConfiguration, BackupRun, Backup, BackupFile
Collections/ Custom Eloquent collections
Factories/ Model factories
Providers/ BackupDatabaseConfigurationProvider
Services/ PerformBackupService, BackupDestinationsService, BackupsService, etc.
SpatieBackup/ Bridge layer: DatabaseConfigProvider, DatabaseBackupConfigProvider,
DatabaseDestinationConfigProvider, BackupRunner, BackupJobFactory
Broadcasting/ Channel access management, notifications, artisan broadcaster
Filesystem/ DynamicFilesystemManager (resolves disks from database)
Testing/ Test responder doubles
config/
backup-manager.php Route middleware, prefix, name settings
backup.php Standard Spatie Backup config
resources/
stubs/ Scaffoldable responder classes (stacks/inertia, stacks/custom)
boost/ AI guidelines and skills (this file)
```

## Core concepts

### Responder pattern

The package uses a contract-based responder pattern for UI integration. Each UI concern (backups list, destinations
CRUD, schedules, performing backups) has an interface in `Contracts/Responders/` and stub implementations in
`resources/stubs/stacks/`. The user scaffolds them into their app:

```bash
php artisan backup-manager:install --stack=inertia
```

Supported stacks: `inertia` (returns Inertia page props), `custom` (empty method bodies). The command also generates
`App\Providers\BackupManagerServiceProvider` that binds each contract to its implementation, and auto-registers it in
`bootstrap/providers.php`.

**React/Vue components are not included in the package.** Example components are in the GitHub wiki. The package
provides only the backend responder layer.

### Database-driven destinations

`FilesystemConfiguration` is a polymorphic model. Its `configurable` relation points to a type-specific model:

| `disk_type` | Configurable model |
|---|---|
| `local` | `FilesystemConfigurationLocal` |
| `ftp` | `FilesystemConfigurationFTP` |
| `sftp` | `FilesystemConfigurationSFTP` |

Each destination generates a unique `slug` that becomes its Laravel disk name (`driver_name`). Only active destinations
(`is_active = true`) are returned to Spatie Backup.

### Backup runs

`BackupRun` tracks on-demand backup execution. Status flow: `Pending` → `Running` → `Successful` | `Failed`. The model
uses UUIDs (`HasUuids`), has `type` (BackupTypes), `disks` (nullable array), `started_at`, and `completed_at`.
**Important**: `started_at` and `completed_at` are NOT in `$fillable` — use `Model::unguard()` or direct property
assignment when updating them in tests.

### BackupRunner callbacks

`BackupRunner` wraps Spatie's `BackupJob` with lifecycle callbacks. The `create()` factory produces a runner that
updates a `BackupRun` model. **Known issue**: `onStartedCallback` is stored but never invoked in `run()`.

### Broadcasting

Backup progress is broadcast over private channels (`backups.{uuid}`). `ChannelAccessManager` uses `CacheStore`
(application cache, not the database) to manage channel leases. The `BackupJob` sends status updates during execution.

## Critical rules

- **Always resolve `Config` from the container**, not `config()` helper. When the database has
`filesystem_configurations` records, the package replaces `Config::class` with `DatabaseConfigProvider`.
`config('backup.backup.destination.disks')` returns stale values.

```php
$config = app(\Spatie\Backup\Config\Config::class);
$disks = $config->backup->destination->disks; // Database-driven
```

- **Schedules are database records**, not entries in `routes/console.php`. Use `BackupSchedule` and `CleanupSchedule`
models.
- **Backup schedules link to destinations** via a `BelongsToMany` on `filesystemConfigurations()`. Cleanup schedules
don't link to destinations — they use the disks from the database config.
- **Don't modify `vendor/spatie/laravel-backup`** — this package extends it through the service container, not by
editing vendor files.
