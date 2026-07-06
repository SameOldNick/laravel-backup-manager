# Changelog

All notable changes will be documented in this file.

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
