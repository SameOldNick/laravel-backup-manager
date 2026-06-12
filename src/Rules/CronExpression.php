<?php

namespace SameOldNick\BackupManager\Rules;

use Closure;
use Cron\CronExpression as CronCronExpression;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class CronExpression implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! CronCronExpression::isValidExpression($value)) {
            $fail('backup-manager::validation.cron_expression_invalid');
        }
    }
}
