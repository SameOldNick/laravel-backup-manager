# Changelog

All notable changes will be documented in this file.

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
