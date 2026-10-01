# Changelog

All notable changes will be documented in this file.

## v3.0.0 - 2026-10-01

### Breaking

- `CleanupSchedulesUiResponder::renderCreateCleanupSchedule()` now receives a `CreateCleanupScheduleViewData` argument, so existing responder implementations must accept it
- Removed `BackupSchedulesService::getAvailableDestinations()` in favour of `BackupDestinationsService::getAvailableDestinations()`

### Added

- Added `CleanupJob` and `CleanupRunner`, so cleanups can be dispatched as queueable jobs (optionally targeting specific disks) instead of only running the `backup:clean` command
- Added configurable queue options for backup and cleanup jobs (`config/backup-manager.php` → `jobs.backup` and `jobs.cleanup`: connection, queue, group, delay, middleware, and after-commit)
- Cleanup schedules can now be linked to specific filesystem configurations and run only against those disks; the scheduler resolves the attached, active and valid destinations to disk names
- Cleanup schedule create and edit screens now support choosing destinations, and `CreateCleanupScheduleData`/`UpdateCleanupScheduleData` accept `destination_ids` (validated as an array of selectable destination ids, capped at 255). An empty array is valid and attaches nothing, so a schedule can be returned to the app's configured destinations; omitting the key on update leaves the current selection untouched
- Added `selectedDestinationIds` to `EditBackupScheduleViewData` and `EditCleanupScheduleViewData`, so responder implementations receive the destinations currently attached to the schedule instead of having to re-derive them
- Added the `SelectableDestinationId` validation rule: a submitted destination id must be active, or already attached to the schedule being updated
- Added `BackupRunner`/`CleanupRunner` and backup/cleanup job support for an explicit `disks` list, including `PerformBackupService::dispatchBackupJob()` for scoped backups
- Added `FilesystemConfigurationCollection::getActive()`, `getValid()`, `getActiveAndValid()` and `toDiskNames()` helpers, plus `BackupDestinationsService::getAvailableDestinations()`
- Added `createBackupDestinations()` to the abstract `Runner` so the backup and cleanup runners resolve destinations through the same path
- Added `NoValidDisksException` for runs that are given an explicitly empty disk list
- Added PHPStan configuration (`phpstan.neon.dist`) and memory limits to the `analyse` and `lint` composer scripts

### Fixed

- Fixed backup and cleanup schedules being skipped silently when queue options are configured (the scheduler read `jobs.*.name` instead of `jobs.*.queue`, and unguarded option lookups raised errors that were swallowed and logged without a stack trace)
- Fixed cleanup schedule destination selections being ignored because `destination_ids` was missing from the store and update request validation
- Fixed the cleanup schedule edit screen dropping a destination from the picker once it had been deactivated, which silently detached it on the next save
- Fixed the backup schedule edit screen never being able to offer an attached destination that had been deactivated, because its list was already filtered down to active destinations before the filter ran
- Fixed a schedule whose attached destination had since been deactivated being impossible to update at all: the store and update requests rejected the id the edit screen had pre-selected, so even an unrelated change such as the cron expression failed validation. An id that is already attached is now accepted, while a destination that was never attached still has to be active
- Bounded `destination_ids` to 255 entries, so a single form post cannot fan out into an unbounded number of destination lookups
- Fixed the Inertia responder stubs pre-selecting every active destination on the schedule edit screens instead of the attached ones, which would attach destinations the user never chose
- Fixed schedules whose attached destinations are all inactive or invalid quietly falling back to every disk: the run is now skipped and logged instead
- Fixed an explicitly empty `disks` array falling back to every destination; it now fails with `NoValidDisksException`
- Fixed channel authenticators being dropped by route caching: they are now registered at runtime, so `php artisan optimize` no longer makes every `/broadcasting/auth` request answer 403
- `BackupFile::file_exists` is now cached and `meta` reuses the same storage lookup instead of querying the disk twice per serialization
- Closures passed to `BackupFactory::failed()` and `BackupFileFactory::uploadedFile()` are now resolved with the factory instance
- Constrained `dragonmantank/cron-expression` to `^3.6`

### Refactored

- Cleanup schedules now schedule `CleanupJob` instead of the background `backup:clean` command
- Both schedule controllers resolve available destinations through `BackupDestinationsService`
- Moved `createBackupDestinations()` from `BackupRunner` into the base `Runner`
- Documented dynamic model attributes in `BackupFile`, `BackupMonitor`, `FilesystemConfigurationSFTP` and the schedule models for static analysis
- Added runner tests covering the `null`, empty and explicit disk cases, and feature tests for cleanup schedule destination assignment and page payloads

### Documentation

- Fixed the demo repository link in the README
- Documented the schedule view data (including `selectedDestinationIds`) in the React UI guide and corrected the stale `CleanupSchedulesUiResponder` signature

### Upgrade Notes

- This is a major release (`3.0.0`) containing breaking changes: raise the `sameoldnick/laravel-backup-manager` constraint in `composer.json` to `^3.0` before updating, or run `composer require sameoldnick/laravel-backup-manager:^3.0`
- Publish and run the new `cleanup_schedule_filesystem_configuration` migration: `php artisan vendor:publish --tag=backup-manager-migrations --force`, then `php artisan migrate`
- Update your `CleanupSchedulesUiResponder` implementation so `renderCreateCleanupSchedule()` accepts the new `CreateCleanupScheduleViewData $data` argument (see the updated responder stubs)
- Update the app's cleanup schedule create and edit UI to offer destination selection: build the picker from the `configurations` collection now passed to `renderCreateCleanupSchedule()` and `renderEditCleanupSchedule()`, and submit the chosen ids as `destination_ids` on store and update. Until this is done the new disk scoping cannot be reached from the frontend, and every cleanup keeps running against all disks
- Decide what the app's schedule forms send when nothing is picked: an empty `destination_ids` array attaches nothing and falls back to the destinations configured for the app, while omitting the key on update leaves the existing selection as it is. On the edit screen, do not send the key at all if the user did not touch the picker
- Pre-select the attached destinations on the cleanup schedule (and backup schedule) edit screens from `$data->selectedDestinationIds`, not by filtering `$data->configurations` on `is_active`: that collection also contains active destinations that are not attached, and it keeps attached destinations that have since been deactivated so they stay visible in the picker (dropping one silently detaches it on save)
- Replace `BackupSchedulesService::getAvailableDestinations()` calls with `BackupDestinationsService::getAvailableDestinations()`
- Re-publish the package config to pick up the new `jobs` options (`php artisan vendor:publish --tag=backup-manager-config --force`)
- Cleanup schedules are now dispatched as jobs rather than run as a command, so a queue worker is required when the default queue connection is not `sync`

### Upgrade Prompt

Use this prompt to upgrade an existing Laravel app from Laravel Backup Manager 2.0.x to 3.0.0:

```text
Upgrade this Laravel application from Laravel Backup Manager (`sameoldnick/laravel-backup-manager`) 2.0.x to 3.0.0.

Follow this process:

1. Inspect `composer.json`, `composer.lock`, `config/backup-manager.php`, `bootstrap/providers.php`, `app/Providers/BackupManagerServiceProvider.php`, `database/migrations`, and every class that implements `SameOldNick\\BackupManager\\Contracts\\Responders\\CleanupSchedulesUiResponder` for the app's existing Laravel Backup Manager integration.

2. Raise the package constraint in `composer.json` to `^3.0` and run `composer update sameoldnick/laravel-backup-manager` (or `composer require sameoldnick/laravel-backup-manager:^3.0`). The constraint must be raised explicitly: Composer will not cross a major version on its own, so a plain `composer update` leaves the app on 2.x.

    After updating, inspect the installed package metadata with `composer show sameoldnick/laravel-backup-manager --format=json` and verify its PHP requirement. If the upgraded package requires a newer PHP version than the application declares, update the root PHP constraint in `composer.json` and refresh `composer.lock` before continuing.

3. Publish the latest package migrations with `php artisan vendor:publish --tag=backup-manager-migrations --force`, then look for a published copy of `0001_01_01_000020_create_cleanup_schedule_filesystem_configuration_table.php` (the `cleanup_schedule_filesystem_configuration` pivot table).

4. Do not create duplicate migrations. If that migration was published under a new timestamp while a copy already existed, delete the newly copied file and keep the original. Never rename or delete a migration file for a table that already exists in the app.

5. Before running `php artisan migrate`, inspect both the existing database tables and the `migrations` table. If package tables already exist but their original migration records are missing, reconcile the migration records by recording the existing original migration filenames as applied. Do not rerun migrations that would recreate existing tables, and do not rename or delete their migration files.

    Then run `php artisan migrate` and confirm the `cleanup_schedule_filesystem_configuration` table exists with `cleanup_schedule_id` and `filesystem_configuration_id` columns.

6. Update the app's `CleanupSchedulesUiResponder` implementation so `renderCreateCleanupSchedule(CreateCleanupScheduleViewData $data)` accepts the new argument, importing `SameOldNick\\BackupManager\\DataTransferObjects\\Responders\\Schedules\\CleanupSchedules\\CreateCleanupScheduleViewData`. This change is required: the previous zero-argument signature is a fatal declaration error against the updated contract.

7. Give the app's cleanup schedule create and edit screens a destination picker: render the options from the `configurations` collection now passed to `renderCreateCleanupSchedule()` and `renderEditCleanupSchedule()`, and post the selected ids as `destination_ids` on store and update (an array of active `filesystem_configurations` ids). Without this, the new disk scoping cannot be reached from the frontend, and every cleanup keeps running against all disks.

8. Pre-select the attached destinations on the cleanup schedule and backup schedule edit screens from the view data's `selectedDestinationIds`, not by filtering the view data's `configurations` on `is_active`: that collection also contains active destinations that are not attached, and it keeps attached destinations that have since been deactivated so they stay visible in the picker. Dropping one silently detaches it on save.

9. Re-publish or manually update `config/backup-manager.php` so it includes the new `jobs.backup` and `jobs.cleanup` sections (`connection`, `queue`, `group`, `delay`, `middleware`, `after_commit`), without altering existing route, channel lease, or fallback values.

10. Replace any calls to the removed `BackupSchedulesService::getAvailableDestinations()` with `BackupDestinationsService::getAvailableDestinations()`.

11. Account for the scheduling change: cleanup schedules now dispatch `SameOldNick\\BackupManager\\Jobs\\CleanupJob` instead of running the `backup:clean` command, so a queue worker must be running unless the default queue connection is `sync`.

12. Account for the disk scoping change: a cleanup schedule with destinations attached now cleans only those disks, and a schedule whose attached destinations are all inactive or invalid is skipped and logged (`No valid disks found for cleanup schedule`) instead of falling back to every disk. Review the app's cleanup schedules and their destination state after upgrading.

13. Run focused validation for the upgrade: the app's test suite, a route or UI smoke check of the Backup Manager screens (especially the cleanup schedule create and edit screens, which must post `destination_ids` and keep attached-but-inactive destinations visible), and a queue worker check for cleanup jobs.

When you make changes, explain what you changed, call out any manual follow-up still required, and highlight anything that could not be upgraded automatically.
```

## v2.0.2 - 2026-08-12

### Breaking

- Renamed backup channel lease exceptions to `ChannelLeaseNotFoundException` and `ChannelLeaseUnauthorizedException`, and updated the backup controller error handling to match

### Added

- Introduced `AbstractChannelLeaseService` to share the lease lifecycle logic between backup and destination test services
- Added `BackupDestinationTestRunAlreadyExistsException` for duplicate backup destination test runs

### Fixed

- Backup start now maps missing, unauthorized, and duplicate lease states to the correct HTTP responses

### Refactored

- Consolidated the backup and destination test services onto a shared abstract lease workflow

### Documentation

- Clarified the composer constraint guidance in the upgrade notes
- Added README badges for package visibility

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
2. Update composer.json to require the package with a `^2.0` constraint if it is not already installed.
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
