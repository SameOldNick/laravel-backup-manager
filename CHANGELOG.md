# Changelog

All notable changes will be documented in this file.

## v2.0.1 - 2026-08-07

### Fixed

- Backup monitor age and storage limits can now be disabled correctly

### Documentation

- Updated README with revised publish commands and database-backed backup monitor details
- Clarified v1.x to v2.0 upgrade guidance, including migration handling and responder binding details

## v2.0.0 - 2026-08-01

### Breaking

- Removed `BackupConfigurationProvider` contract and `UsesBackupConfigurationProvider` trait
- Removed `BackupDatabaseConfigurationProvider` in favor of Spatie config provider integration
- Updated monitored backups configuration resolution to use database-backed monitored backups provider

### Upgrade Notes

- Run `php artisan vendor:publish --tag=backup-manager-migrations --force`
- Remove any duplicated migrations that were copied into your application's `database/migrations` directory
- Run `php artisan migrate`
- Update your backup manager config file to reflect the new defaults and fallback options
- Re-publish the package config if you previously published it, because the config file includes new options and fallback settings
- Add the UI responder and register it in `BackupManagerServiceProvider`

### Upgrade Prompt

Use this prompt to upgrade an existing Laravel app from Laravel Backup Manager 1.x to 2.0:

```text
Upgrade this Laravel application from Laravel Backup Manager (sameoldnick/laravel-backup-manager) 1.x to 2.0.

Follow this process:
1. Inspect composer.json, composer.lock, config/backup-manager.php, config/backup.php, bootstrap/providers.php, app/Providers/BackupManagerServiceProvider.php, and database/migrations for existing Laravel Backup Manager integration.
2. Update the package to v2.0 if it is not already installed.
3. Publish the latest package migrations with `php artisan vendor:publish --tag=backup-manager-migrations --force`.
4. Detect duplicate package migrations carefully. Do not delete or rename existing migration files for tables that already exist. Keep original migration filenames/timestamps (for example, keep `database/migrations/2026_07_07_021221_create_backup_destination_test_runs.php` and do not replace it with a newly timestamped copy like `database/migrations/2026_08_06_021221_create_backup_destination_test_runs.php`).
5. Run `php artisan migrate`.
6. Re-publish or manually update the published package config so it matches the v2.0 defaults, including any new fallback options.
7. Replace any usage of removed 1.x APIs, especially `BackupConfigurationProvider`, `UsesBackupConfigurationProvider`, and `BackupDatabaseConfigurationProvider`.
8. Ensure the application's UI responder classes exist and that `App\Providers\BackupManagerServiceProvider` binds and registers them correctly. In particular, bind `SameOldNick\BackupManager\Contracts\Responders\BackupMonitorsUiResponder` to a concrete `BackupMonitorsUiResponder` class (for example `VendorName\BackupManager\Responders\BackupMonitorsUiResponder`) using `$this->app->bind(...)` in the `register` method.
9. Verify the package now relies on the Spatie config provider integration and database-backed monitored backup configuration expected by v2.0.
10. Run focused validation for the upgrade, including relevant tests and a quick route or UI smoke check if the app exposes backup manager screens.

When you make changes, explain what you changed, call out any manual follow-up still required, and highlight anything that could not be upgraded automatically.
```

### Added

- Database-backed backup monitor configuration
- `backup_monitors` and backup monitor/filesystem pivot migrations
- Backup monitor CRUD HTTP controller and form requests
- Backup monitor model, factory, collection, and service layer
- UI responder contracts/stubs and responder DTOs for backup monitor screens
- Unit and feature tests for backup monitor configuration and controller behavior

### Fixed

- Added fallback and error handling for monitor configuration resolution
- Added fallback behavior for destination disk resolution
- Added default route configuration fallbacks
- Added error handling for backup and cleanup scheduling operations
- Added support for custom config fallbacks in backup manager configuration

### Refactored

- Refactored destination and backup service query filters
- Simplified disk resolution and provider lookup paths

## v1.1.1 - 2026-07-09

### Fixed

- `FilesystemConfiguration` slug is now automatically generated on create and kept in sync on save
- Backup destinations now use the stored `slug` to find the storage disk, fixing failures where the auto-generated slug didn't match the expected filesystem name
- Exposed `slug` in `FilesystemConfiguration` array representation

### Refactored

- Added `byDriverName` scope to `FilesystemConfiguration` and replaced magic number string manipulation in `DynamicFilesystemManager`

## v1.1.0 - 2026-07-06

### Added

- Configurable channel lease name and expiration (`config('backup-manager.channel_leases')`)
- `isValid` accessor on `FilesystemConfiguration` to check for missing morph classes
- Validation for cron expressions in backup schedules (invalid expressions are logged and skipped)
- Validation for backup types in backup schedules (invalid types are logged and skipped)
- Redirect responders for backup operations when the channel lease is missing
- Unit tests for `RelativePath` validation rule
- Unit tests for scheduling with invalid morph classes
- Added error messages for expired or unauthorized backup channel leases

### Fixed

- Scheduler no longer crashes when a `FilesystemConfiguration` references a non-existent morph class

### Refactored

- Channel lease expiration now derives from config instead of being hardcoded
- Moved `ScheduleTest` from `tests/Feature` to `tests/Unit`

## v1.0.0 - 2026-07-05

- Initial release of Laravel Backup Manager.
