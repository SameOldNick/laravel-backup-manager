# Changelog

All notable changes will be documented in this file.

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
