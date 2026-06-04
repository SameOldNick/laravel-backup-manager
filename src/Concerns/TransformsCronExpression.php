<?php

namespace SameOldNick\BackupManager\Concerns;

use Cron\CronExpression;

trait TransformsCronExpression
{
    /**
     * Transforms cron expression to a more human readable format if it's a preset, otherwise returns the expression as is.
     * For example, transforms '@daily' to '0 0 * * *'
     */
    protected function transformCronExpression(?string $expression): ?string
    {
        // If the expression is null or empty, return null
        if (is_null($expression) || $expression === '') {
            return null;
        }

        if (str_starts_with($expression, '@')) {
            $presets = CronExpression::getAliases();

            $expression = $presets[$expression] ?? $expression;
        }

        // Otherwise, return the expression as is
        return $expression;
    }
}
