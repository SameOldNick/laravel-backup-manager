<?php

namespace SameOldNick\BackupManager\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Translation\PotentiallyTranslatedString;

class Slugified implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || empty($value)) {
            $fail('backup-manager::validation.slugified');
        }

        // Check if the value is a valid slug (lowercase letters, numbers, and hyphens)
        if (Str::slug($value) !== $value) {
            $fail('backup-manager::validation.slugified');
        }
    }
}
