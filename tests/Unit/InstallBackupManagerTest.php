<?php

namespace SameOldNick\BackupManager\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use InvalidArgumentException;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use SameOldNick\BackupManager\Commands\InstallBackupManager;
use SameOldNick\BackupManager\Tests\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\BufferedOutput;

class InstallBackupManagerTest extends TestCase
{
    /** @var array<string, string> */
    private array $writtenFiles = [];

    private array $expectedProviderFiles = [
        'BackupManagerServiceProvider.php',
    ];

    private array $expectedResponderFiles = [
        'BackupDestinationsUiResponder.php',
        'BackupDestinationTestUiResponder.php',
        'BackupSchedulesUiResponder.php',
        'BackupsUiResponder.php',
        'PerformBackupUiResponder.php',
        'CleanupSchedulesUiResponder.php',
        'SchedulesUiResponder.php',
    ];

    private Filesystem $realFilesystem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->writtenFiles = [];
        $this->realFilesystem = new Filesystem;
    }

    // ──── Stack Validation ───────────────────────────────────────────

    #[Test]
    public function it_rejects_an_unsupported_stack(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported stack [unknown]. Supported stacks: inertia, custom.');

        $filesystem = $this->createFilesystemMock();

        $this->runCommand($filesystem, ['--stack' => 'unknown']);
    }

    // ──── File Generation ────────────────────────────────────────────

    #[Test]
    public function it_generates_all_responder_files_for_the_inertia_stack(): void
    {
        $filesystem = $this->createFilesystemMock();

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--skip-registration' => true,
        ]);

        foreach ($this->expectedResponderFiles as $file) {
            $this->assertArrayHasKeyEndingWith(
                $file,
                $this->writtenFiles,
                "Expected {$file} to be generated."
            );
        }
    }

    #[Test]
    public function it_generates_all_responder_files_for_the_custom_stack(): void
    {
        $filesystem = $this->createFilesystemMock();

        $this->runCommand($filesystem, [
            '--stack' => 'custom',
            '--skip-registration' => true,
        ]);

        foreach ($this->expectedResponderFiles as $file) {
            $this->assertArrayHasKeyEndingWith(
                $file,
                $this->writtenFiles,
                "Expected {$file} to be generated for custom stack."
            );
        }
    }

    // ──── Existing File Handling ─────────────────────────────────────

    #[Test]
    public function it_skips_existing_destination_files_when_not_forced(): void
    {
        $filesystem = $this->createFilesystemMock(destinationsExist: true);

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--skip-registration' => true,
        ]);

        // No files should be written because they all "already exist"
        $responderWrites = array_filter(
            array_keys($this->writtenFiles),
            fn (string $path) => str_contains($path, 'Responders') || str_contains($path, 'BackupManagerServiceProvider')
        );

        $this->assertCount(0, $responderWrites, 'No responder or provider files should be written when destinations exist.');
    }

    #[Test]
    public function it_overwrites_existing_files_when_forced(): void
    {
        $filesystem = $this->createFilesystemMock(destinationsExist: true);

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--force' => true,
            '--skip-registration' => true,
        ]);

        // All files should be written despite destinations existing
        $responderWrites = array_filter(
            array_keys($this->writtenFiles),
            fn (string $path) => str_contains($path, 'BackupManager/Responders')
                || str_contains($path, 'BackupManagerServiceProvider')
        );

        $this->assertCount(
            count($this->expectedResponderFiles) + count($this->expectedProviderFiles),
            $responderWrites,
            'All responders + provider should be overwritten when forced.'
        );
    }

    // ──── Provider Generation ────────────────────────────────────────

    #[Test]
    public function it_generates_the_service_provider_by_default(): void
    {
        $filesystem = $this->createFilesystemMock();

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--skip-registration' => true,
        ]);

        $this->assertArrayHasKeyEndingWith(
            'BackupManagerServiceProvider.php',
            $this->writtenFiles,
            'Expected the service provider to be generated.'
        );
    }

    #[Test]
    public function it_skips_provider_generation_when_skip_provider_option_is_set(): void
    {
        $filesystem = $this->createFilesystemMock();

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--skip-provider' => true,
            '--skip-registration' => true,
        ]);

        $providerWrites = array_filter(
            array_keys($this->writtenFiles),
            fn (string $path) => str_contains($path, 'BackupManagerServiceProvider')
        );

        $this->assertCount(0, $providerWrites, 'Provider should not be generated when --skip-provider is set.');
    }

    // ──── Bootstrap Registration ─────────────────────────────────────

    #[Test]
    public function it_registers_the_provider_in_bootstrap_providers_php(): void
    {
        $providersContent = "<?php\n\nreturn [\n    App\\Providers\\AppServiceProvider::class,\n];\n";
        $filesystem = $this->createFilesystemMock(bootstrapProvidersContent: $providersContent);

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
        ]);

        $bootstrapWrite = $this->getWrittenFileEndingWith('bootstrap/providers.php');

        $this->assertNotNull($bootstrapWrite, 'Expected bootstrap/providers.php to be updated.');
        $this->assertStringContainsString(
            'App\\Providers\\BackupManagerServiceProvider::class',
            $bootstrapWrite,
            'Expected the provider class to be registered in bootstrap/providers.php.'
        );
    }

    #[Test]
    public function it_does_not_duplicate_provider_registration(): void
    {
        $providersContent = "<?php\n\nreturn [\n    App\\Providers\\AppServiceProvider::class,\n    App\\Providers\\BackupManagerServiceProvider::class,\n];\n";
        $filesystem = $this->createFilesystemMock(bootstrapProvidersContent: $providersContent);

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
        ]);

        $bootstrapWrite = $this->getWrittenFileEndingWith('bootstrap/providers.php');

        $this->assertNull($bootstrapWrite, 'bootstrap/providers.php should not be written when provider is already registered.');

        // Verify only one occurrence in the original content
        $occurrences = substr_count($providersContent, 'BackupManagerServiceProvider::class');
        $this->assertSame(1, $occurrences, 'Provider class should appear exactly once.');
    }

    #[Test]
    public function it_skips_registration_when_skip_registration_option_is_set(): void
    {
        $providersContent = "<?php\n\nreturn [\n    App\\Providers\\AppServiceProvider::class,\n];\n";
        $filesystem = $this->createFilesystemMock(bootstrapProvidersContent: $providersContent);

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--skip-registration' => true,
        ]);

        $bootstrapWrite = $this->getWrittenFileEndingWith('bootstrap/providers.php');

        $this->assertNull($bootstrapWrite, 'bootstrap/providers.php should not be written when --skip-registration is set.');
    }

    // ──── Namespace Transform ────────────────────────────────────────

    #[Test]
    public function it_replaces_vendor_namespace_with_application_namespace(): void
    {
        $filesystem = $this->createFilesystemMock();

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--skip-registration' => true,
        ]);

        foreach ($this->writtenFiles as $path => $content) {
            $this->assertStringNotContainsString(
                'VendorName\\',
                $content,
                "File {$path} should not contain the placeholder VendorName namespace."
            );
        }
    }

    #[Test]
    public function it_uses_the_custom_app_namespace_when_provided(): void
    {
        $filesystem = $this->createFilesystemMock();

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--app-namespace' => 'Acme\\Blog',
            '--skip-registration' => true,
        ]);

        $responderFile = $this->getWrittenFileEndingWith('BackupDestinationsUiResponder.php');

        $this->assertNotNull($responderFile, 'Expected a responder file to be written.');
        $this->assertStringContainsString(
            'namespace Acme\\Blog\\BackupManager\\Responders',
            $responderFile,
            'Expected the custom namespace to be applied.'
        );
    }

    // ──── Custom Path ────────────────────────────────────────────────

    #[Test]
    public function it_writes_files_to_a_custom_path(): void
    {
        $filesystem = $this->createFilesystemMock();

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--path' => 'app/CustomBackup',
            '--skip-registration' => true,
        ]);

        $responderWrites = array_filter(
            array_keys($this->writtenFiles),
            fn (string $path) => str_contains($path, 'CustomBackup/Responders')
        );

        $this->assertCount(
            count($this->expectedResponderFiles),
            $responderWrites,
            'All 6 responders should be written to the custom path.'
        );
    }

    // ──── Exit Code ──────────────────────────────────────────────────

    #[Test]
    public function it_returns_success_exit_code(): void
    {
        $filesystem = $this->createFilesystemMock();

        $exitCode = $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--skip-registration' => true,
        ]);

        $this->assertSame(0, $exitCode, 'Command should return a success exit code.');
    }

    // ──── Output Messaging ───────────────────────────────────────────

    #[Test]
    public function it_displays_generated_and_skipped_counts(): void
    {
        $providersContent = "<?php\n\nreturn [\n    App\\Providers\\AppServiceProvider::class,\n];\n";
        $filesystem = $this->createFilesystemMock(bootstrapProvidersContent: $providersContent);

        $output = $this->runCommand($filesystem, [
            '--stack' => 'inertia',
        ], returnOutput: true);

        $this->assertStringContainsString('Generated', $output);
        $this->assertStringContainsString('Skipped', $output);
        $this->assertStringContainsString('Registering service provider', $output);
        $this->assertStringContainsString('Registered', $output);
    }

    #[Test]
    public function it_shows_next_steps_message(): void
    {
        $filesystem = $this->createFilesystemMock();

        $output = $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--skip-registration' => true,
        ], returnOutput: true);

        $this->assertStringContainsString('Next steps', $output);
    }

    // ──── Source File Missing ────────────────────────────────────────

    #[Test]
    public function it_throws_when_a_source_stub_is_missing(): void
    {
        $filesystem = Mockery::mock(new Filesystem);

        // Make exists() return false for stubs paths
        $filesystem->shouldReceive('exists')
            ->andReturnUsing(function (string $path): bool {
                return false;
            });

        $filesystem->shouldReceive('put')->andReturn();
        $filesystem->shouldReceive('ensureDirectoryExists')->andReturn();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to scaffold missing source file');

        $this->runCommand($filesystem, [
            '--stack' => 'inertia',
            '--skip-registration' => true,
        ]);
    }

    // ──── Helpers ────────────────────────────────────────────────────

    /**
     * Run the InstallBackupManager command with the given filesystem and input options.
     *
     * @param  array<string, mixed>  $options
     * @return int|string Exit code, or full output string when $returnOutput is true
     */
    private function runCommand(Filesystem $filesystem, array $options = [], bool $returnOutput = false): int|string
    {
        $command = new InstallBackupManager($filesystem);
        $command->setLaravel($this->app);

        // Add --no-interaction to the definition so promptForOverwrite() can check it
        $definition = $command->getDefinition();
        $definition->addOption(new InputOption('no-interaction', null, InputOption::VALUE_NONE));

        $input = new ArrayInput($options, $definition);
        $input->setInteractive(false);

        $output = new BufferedOutput;

        $exitCode = $command->run($input, $output);

        if ($returnOutput) {
            return $output->fetch();
        }

        return $exitCode;
    }

    /**
     * Create a filesystem mock that delegates to the real filesystem
     * for stub sources while intercepting writes.
     *
     * @param  bool  $destinationsExist  Whether destination files should appear to exist
     * @param  ?string  $bootstrapProvidersContent  Content for bootstrap/providers.php (null = file doesn't exist)
     */
    private function createFilesystemMock(
        bool $destinationsExist = false,
        ?string $bootstrapProvidersContent = null,
    ): Filesystem {
        $realFs = $this->realFilesystem;
        $providersPath = base_path('bootstrap/providers.php');

        /** @var Filesystem $filesystem */
        $filesystem = Mockery::mock(new Filesystem);

        $filesystem->shouldReceive('put')
            ->andReturnUsing(function (string $path, string $content): void {
                $this->writtenFiles[$this->normalizePath($path)] = $content;
            });

        $filesystem->shouldReceive('ensureDirectoryExists')->andReturn();

        $filesystem->shouldReceive('exists')
            ->andReturnUsing(function (string $path) use ($realFs, $destinationsExist, $providersPath, $bootstrapProvidersContent): bool {
                // Control bootstrap/providers.php existence
                if ($path === $providersPath) {
                    return $bootstrapProvidersContent !== null;
                }

                // Control destination file existence
                if ($destinationsExist && (str_contains($path, 'BackupManager') || str_contains($path, 'CustomBackup'))) {
                    return true;
                }

                // Delegate to real filesystem for stub sources
                return $realFs->exists($path);
            });

        $filesystem->shouldReceive('get')
            ->andReturnUsing(function (string $path) use ($realFs, $providersPath, $bootstrapProvidersContent): string {
                if ($path === $providersPath && $bootstrapProvidersContent !== null) {
                    return $bootstrapProvidersContent;
                }

                return $realFs->get($path);
            });

        return $filesystem;
    }

    /**
     * Assert that the written files array contains a key ending with the given suffix.
     */
    private function assertArrayHasKeyEndingWith(string $suffix, array $array, string $message = ''): void
    {
        foreach (array_keys($array) as $key) {
            if (str_ends_with($key, $suffix)) {
                $this->addToAssertionCount(1);

                return;
            }
        }

        $keys = implode("\n", array_keys($array));
        $this->fail($message ?: "Failed asserting that array has key ending with '{$suffix}'.\nExisting keys:\n{$keys}");
    }

    /**
     * Get the written content for a file whose path ends with the given suffix.
     */
    private function getWrittenFileEndingWith(string $suffix): ?string
    {
        foreach ($this->writtenFiles as $path => $content) {
            if (str_ends_with($path, $suffix)) {
                return $content;
            }
        }

        return null;
    }

    /**
     * Normalize a path for consistent key comparison.
     */
    private function normalizePath(string $path): string
    {
        return str_replace('\\', '/', $path);
    }
}
