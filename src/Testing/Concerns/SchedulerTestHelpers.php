<?php

namespace SameOldNick\BackupManager\Testing\Concerns;

use SameOldNick\BackupManager\BackupScheduler;

trait SchedulerTestHelpers
{
    /**
     * Creates a spy instance of the BackupScheduler class that records scheduled jobs and commands.
     *
     * @return object An instance of the anonymous class that extends BackupScheduler and records scheduled jobs and commands
     */
    protected function makeSchedulerSpy(): object
    {
        return new class extends BackupScheduler
        {
            public array $jobs = [];

            public array $commands = [];

            protected function scheduleJob(object $job, string $expression)
            {
                $this->jobs[] = [
                    'job' => $job,
                    'expression' => $expression,
                ];

                return null;
            }

            protected function scheduleCommand(string $command, string $expression)
            {
                $this->commands[] = [
                    'command' => $command,
                    'expression' => $expression,
                ];

                return null;
            }
        };
    }

    /**
     * Asserts that the expected jobs were scheduled by the BackupScheduler.
     *
     * @param  callable  $callback  A callback function that receives the array of scheduled jobs for making assertions
     */
    protected function assertSchedulerJobs(callable $callback): void
    {
        $scheduler = $this->makeSchedulerSpy();

        $scheduler->scheduleBackup();
        $scheduler->scheduleCleanup();

        $callback($scheduler->jobs);
    }

    /**
     * Asserts that the expected commands were scheduled by the BackupScheduler.
     *
     * @param  callable  $callback  A callback function that receives the array of scheduled commands for making assertions
     */
    protected function assertSchedulerCommands(callable $callback): void
    {
        $scheduler = $this->makeSchedulerSpy();

        $scheduler->scheduleBackup();
        $scheduler->scheduleCleanup();

        $callback($scheduler->commands);
    }
}
