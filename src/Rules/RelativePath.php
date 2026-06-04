<?php

namespace SameOldNick\BackupManager\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class RelativePath implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || $value === '') {
            return;
        }

        if (
            str_starts_with($value, '/') ||
            str_starts_with($value, '\\') ||
            preg_match('/^[a-zA-Z]:[\\\\\/]/', $value) === 1
        ) {
            $fail('backup-manager::validation.relative_path');

            return;
        }

        $segments = preg_split('/[\\\\\/]+/', $value) ?: [];

        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                $fail('backup-manager::validation.relative_path_traversal');

                return;
            }
        }
    }
}
