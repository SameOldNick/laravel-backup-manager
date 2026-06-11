---
name: backup-manager-development
description: Build and work with Laravel Backup Manager features, including database-driven schedules, dynamic storage destinations, on-demand backups, front-end responder scaffolding, broadcasting, and testing.
---

# Laravel Backup Manager Development

## When to use this skill

Use this skill when:

- Creating or modifying backup schedules, cleanup schedules, or storage destinations
- Triggering backups programmatically or via controllers
- Scaffolding or customizing UI responder classes with `backup-manager:install`
- Working with `BackupRun` status tracking or real-time broadcasting
- Writing tests for backup manager features (commands, runners, services)
- Configuring Spatie Backup settings that interact with this package
- Debugging why `config('backup.backup.destination.disks')` returns stale values

## Features

### Scaffolding responders

Scaffold stack-specific responder classes and a service provider into the host application:

```bash
php artisan backup-manager:install --stack=inertia
```

| Option                | Purpose                                                                  |
| --------------------- | ------------------------------------------------------------------------ |
| `--stack=`            | `inertia` (returns Inertia page props) or `custom` (empty method bodies) |
| `--path=`             | Destination directory (default: `app/BackupManager`)                     |
| `--app-namespace=`    | Root namespace (default: `App\`)                                         |
| `--force`             | Overwrite existing files without prompting                               |
| `--skip-provider`     | Skip generating `App\Providers\BackupManagerServiceProvider`             |
| `--skip-registration` | Skip auto-registering the provider in `bootstrap/providers.php`          |

The command replaces the `VendorName\` placeholder namespace with the target namespace, copies 6 responder stubs from `resources/stubs/stacks/{stack}/`, and generates a service provider that binds each responder contract to its implementation.

### Creating a storage destination

`FilesystemConfiguration` is polymorphic — create the base record, then attach the type-specific config:

```php
use SameOldNick\BackupManager\Models\FilesystemConfiguration;

// Local disk
$destination = FilesystemConfiguration::create([
    'name' => 'Local Backups',
    'disk_type' => 'local',
    'is_active' => true,
]);
$destination->localConfiguration()->create([
    'root' => storage_path('app/backups'),
]);

// FTP disk
$destination = FilesystemConfiguration::create([
    'name' => 'FTP Server',
    'disk_type' => 'ftp',
    'is_active' => true,
]);
$destination->ftpConfiguration()->create([
    'host' => 'ftp.example.com',
    'username' => 'backup',
    'password' => 'secret',
    'port' => 21,
    'ssl' => true,
    'passive' => true,
]);

// SFTP disk
$destination = FilesystemConfiguration::create([
    'name' => 'SFTP Server',
    'disk_type' => 'sftp',
    'is_active' => true,
]);
$destination->sftpConfiguration()->create([
    'host' => 'sftp.example.com',
    'username' => 'backup',
    'password' => 'secret',
    'port' => 22,
    'root' => '/backups',
]);
```

The `driver_name` accessor generates the disk name from the slug. Only active destinations are returned to Spatie Backup. Use `BackupDestinationsService` for higher-level CRUD operations.

### Creating a backup schedule

```php
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Models\BackupSchedule;

$schedule = BackupSchedule::create([
    'name' => 'Daily Full Backup',
    'type' => BackupTypes::Full,
    'cron_expression' => '0 2 * * *',
    'is_active' => true,
]);

// Link to one or more destinations
$schedule->filesystemConfigurations()->attach($destinationId);
// Or sync multiple: $schedule->filesystemConfigurations()->sync([1, 2, 3]);
```

Backup types: `BackupTypes::Full`, `BackupTypes::Files`, `BackupTypes::Databases`. Cron shortcuts like `@daily` are transformed by `TransformsCronExpression`. The `BackupScheduler` reads active schedules and registers them with Laravel's task scheduler. Schedules without linked destinations fall back to the default disk resolution from config.

The `BackupSchedule` model appends `next_run` (computed from the cron expression).

### Creating a cleanup schedule

```php
use SameOldNick\BackupManager\Models\CleanupSchedule;

CleanupSchedule::create([
    'name' => 'Daily Cleanup',
    'cron_expression' => '0 3 * * *',
    'is_active' => true,
]);
```

Cleanup schedules don't link to specific destinations — they use whatever disks are active in the database. The cleanup strategy (keep daily/weekly/monthly/yearly) is configured in `config/backup.php` under `cleanup.default_strategy`.

### Triggering an on-demand backup

The backup flow has two steps — initialize, then start:

```php
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Services\PerformBackupService;

$service = app(PerformBackupService::class);

// Step 1: Open a broadcasting channel lease for real-time updates
$lease = $service->openBackupChannel($uuid, $user);

// Step 2: Dispatch the backup job (creates a BackupRun record)
$backupRun = $service->dispatchBackupJob($lease, BackupTypes::Full, $user);

// For idempotent dispatch (safe to call multiple times):
$backupRun = $service->dispatchBackupJobOnce($lease, BackupTypes::Full, $user, $uuid);
// Returns null if a BackupRun already exists for this UUID
```

`BackupRun` status flow: `Pending` → `Running` → `Successful` | `Failed`. The `create()` factory on `BackupRunner` sets callbacks that update the `BackupRun` model.

**Important**: `BackupRun` uses UUIDs and has `started_at`/`completed_at` fields that are **not in `$fillable`**. When testing callbacks that update these fields, use `Model::unguard()` or direct property assignment.

### Implementing a custom UI responder

When using `--stack=custom`, implement the contract methods for your front-end:

```php
namespace App\BackupManager\Responders;

use SameOldNick\BackupManager\Contracts\Responders\BackupsUiResponder as Contract;
use SameOldNick\BackupManager\DataTransferObjects\Responders\Backups\BackupsListViewData;

class BackupsUiResponder implements Contract
{
    public function renderBackupsList(BackupsListViewData $data)
    {
        return Inertia::render('backups/index', $data->toArray());
    }
}
```

Responder contracts are in `SameOldNick\BackupManager\Contracts\Responders\`. Each method receives a typed DTO from `SameOldNick\BackupManager\DataTransferObjects\Responders\`. The Inertia stack returns `Inertia::render()` calls; the custom stack has empty method bodies.

### Real-time broadcasting

Backup progress is broadcast on private channels (`backups.{uuid}`):

```php
// Server: Channel access is managed via ChannelAccessManager (cache-backed)
$lease = $service->openBackupChannel($uuid, $user);
// $lease->channelId is 'backups.{uuid}'
// $lease gives the front-end access to subscribe

// Front-end: Subscribe using the lease credentials
// The BackupJob sends status updates during execution
```

Channel access uses `CacheStore` (application cache), not the database.

### Working with the Spatie Backup config

The package overrides `destination.disks` with database records. Always resolve `Config` from the container:

```php
// Correct — returns database-driven disks
$config = app(\Spatie\Backup\Config\Config::class);
$disks = $config->backup->destination->disks;

// Wrong — returns whatever is in config/backup.php (stale)
$disks = config('backup.backup.destination.disks');
```

The override chain: `ServiceProvider::rebindSpatieBackupConfig()` → `DatabaseConfigProvider` → `DatabaseBackupConfigProvider` → `DatabaseDestinationConfigProvider` → `BackupDatabaseConfigurationProvider::getDisks()` → queries active `FilesystemConfiguration` records.

## Testing

### Test setup

The test suite uses Orchestra Testbench with SQLite in-memory. Extend `SameOldNick\BackupManager\Tests\TestCase` which provides `RefreshDatabase`, `WithLaravelMigrations`, and `WithWorkbench`.

```php
class MyTest extends TestCase
{
    // RefreshDatabase, WithLaravelMigrations, WithWorkbench are already applied
}
```

### Testing the install command

Mock `Filesystem` to intercept writes and control destination file existence. Use partial mocks (`Mockery::mock(new Filesystem)`) to let source stub reads pass through to the real filesystem while capturing `put()` calls. Run the command directly via `Command::run()` with `ArrayInput` + `BufferedOutput` — add `--no-interaction` to the input definition since the command checks for it in `promptForOverwrite()`.

```php
$filesystem = Mockery::mock(new Filesystem);
$filesystem->shouldReceive('put')->andReturnUsing(function ($path, $content) use (&$written) {
    $written[$path] = $content;
});
$filesystem->shouldReceive('ensureDirectoryExists')->andReturn();
// Delegate exists() and get() to real filesystem for stub sources

$command = new InstallBackupManager($filesystem);
$command->setLaravel($this->app);
$definition = $command->getDefinition();
$definition->addOption(new InputOption('no-interaction', null, InputOption::VALUE_NONE));
$command->run(new ArrayInput(['--stack' => 'inertia'], $definition), new BufferedOutput);
```

### Testing BackupRunner

Mock `SpatieBackupJob` to prevent actual backups. Use a partial mock of `BackupRunner` to override `createBackupJob()`:

```php
$mockJob = Mockery::mock(SpatieBackupJob::class);
$mockJob->shouldReceive('run')->once(); // success
// or: $mockJob->shouldReceive('run')->once()->andThrow(new Exception); // failure

$runner = Mockery::mock(BackupRunner::class, [
    null, // onStartedCallback
    fn () => $successCalled = true,  // onSuccessCallback
    fn ($e) => $failedCalled = true,  // onFailedCallback
    fn () => $completedCalled = true, // onCompletedCallback
])->makePartial();
$runner->shouldReceive('createBackupJob')->andReturn($mockJob);
$runner->run(Mockery::mock(Config::class));
```

**Known behavior**: `onStartedCallback` is accepted but never invoked in `run()`.

### Testing create() factory

Use reflection to extract callbacks and invoke them directly. Remember that `started_at` and `completed_at` are not fillable — unguard the model:

```php
$backupRun = BackupRun::factory()->create(['status' => BackupRunStatus::Pending]);
$runner = BackupRunner::create($backupRun);

$reflection = new \ReflectionClass($runner);
$prop = $reflection->getProperty('onStartedCallback');
$prop->setAccessible(true);
$callback = $prop->getValue($runner);

Model::unguard();
call_user_func($callback);
Model::reguard();

$backupRun->refresh();
$this->assertEquals(BackupRunStatus::Running, $backupRun->status);
```
