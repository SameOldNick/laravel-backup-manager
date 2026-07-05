# Laravel Backup Manager

[![codecov](https://codecov.io/gh/SameOldNick/laravel-backup-manager/graph/badge.svg?token=SgfZngm6IB)](https://codecov.io/gh/SameOldNick/laravel-backup-manager)

A database-driven backup management package for Laravel that centralizes backup and cleanup schedules, supports dynamic storage destinations, and extends [Spatie Backup](https://github.com/spatie/laravel-backup) for production workflows. Provides backend responders for your front-end stack — example React components are available in the [wiki](https://github.com/SameOldNick/laravel-backup-manager/wiki).

## Requirements

- PHP 8.4+
- Laravel 11 / 12 / 13
- [spatie/laravel-backup](https://github.com/spatie/laravel-backup)

## Installation

```bash
composer require sameoldnick/laravel-backup-manager
```

Publish the configuration and migrations:

```bash
php artisan vendor:publish --provider="SameOldNick\BackupManager\ServiceProvider"
php artisan migrate
```

Publish the Spatie Backup configuration if you haven't already:

```bash
php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"
```

## Quick Start

1. **Run the installer** to scaffold app-level responder classes for your front-end stack:

```bash
php artisan backup-manager:install --stack=inertia
```

Supported stacks: `inertia`, `custom`

2. **Create a storage destination** through the UI at `/backup/destinations` (or programmatically).

3. **Create a backup schedule** — choose the backup type, cron expression, and target storage disks.

4. **Run your first backup** from the UI or let the scheduler handle it.

## Features

### Database-Driven Schedules

Backup and cleanup schedules live in the database — no hard-coded entries in `routes/console.php`. Each backup schedule links to one or more storage destinations.

- **Backup schedules**: Type (`full`, `files`, `databases`), cron expression, active/inactive
- **Cleanup schedules**: Cron expression, active/inactive
- Schedules are automatically registered with Laravel's task scheduler

### Dynamic Storage Destinations

Storage destinations (local, FTP, SFTP) are stored as database records and resolved at runtime. You can add, edit, test, and remove destinations without touching config files.

- **Local** — path-based storage on the same server
- **FTP** — remote FTP server with optional SSL and passive mode
- **SFTP** — secure SSH file transfer with key or password auth

Each destination has a unique slug that becomes its Laravel disk name.

### Built on Spatie Backup

The package wraps [spatie/laravel-backup](https://github.com/spatie/laravel-backup) and extends its configuration. All Spatie Backup features — file selection, database dumping, compression, encryption, notifications, and health checks — work as expected.

### Front-End Integration

The package provides responder contracts and implementations that return responses for your front-end stack. Run the installer to scaffold them into your application:

```bash
php artisan backup-manager:install --stack=inertia
```

Supported stacks: `inertia`, `custom`

The Inertia responders return page-level props for:

- Listing and downloading backups
- Creating, editing, testing, and deleting storage destinations
- Managing backup and cleanup schedules
- Triggering and monitoring backup runs

The UI layer is contract-based — swap in your own responders for a custom stack (REST API, Blade, Livewire, etc.).

> **Note:** React/Vue components are not included in the package. Example React components for the Inertia responders are available in the [GitHub wiki](https://github.com/SameOldNick/laravel-backup-manager/wiki).

### Real-Time Notifications

Backup progress, success, and failure events are broadcast over WebSockets so your UI can update without polling. Channel access is managed through the application cache.

### Signed Download URLs

Backup files are served through signed, time-limited URLs, keeping your storage disks private while allowing secure downloads.

### Programmatic API

All operations are backed by a service layer you can use in your own code, CLI commands, or scheduled jobs:

```php
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Services\PerformBackupService;

$service = app(PerformBackupService::class);
$lease = $service->openBackupChannel($uuid, $user);
$backupRun = $service->dispatchBackupJob($lease, BackupTypes::Full, $user);
```

## Additional Information

The GitHub [wiki](https://github.com/SameOldNick/laravel-backup-manager/wiki) contains additional information about Laravel Backup Manager.

## Testing

```bash
composer test
```

## License

MIT — see [LICENSE](LICENSE) for details.
