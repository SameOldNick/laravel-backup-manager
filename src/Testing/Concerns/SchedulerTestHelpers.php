<?php

namespace SameOldNick\BackupManager\Testing\Concerns;

use SameOldNick\BackupManager\BackupScheduler;

trait SchedulerTestHelpers
{
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

    protected function assertSchedulerJobs(callable $callback): void
    {
        $scheduler = $this->makeSchedulerSpy();

        $scheduler->scheduleBackup();
        $scheduler->scheduleCleanup();

        $callback($scheduler->jobs);
    }

    protected function assertSchedulerCommands(callable $callback): void
    {
        $scheduler = $this->makeSchedulerSpy();

        $scheduler->scheduleBackup();
        $scheduler->scheduleCleanup();

        $callback($scheduler->commands);
    }
}
