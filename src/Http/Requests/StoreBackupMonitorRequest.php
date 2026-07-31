<?php

namespace SameOldNick\BackupManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;

class StoreBackupMonitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'destination_ids' => 'sometimes|array|min:1',
            'destination_ids.*' => [
                'integer',
                Rule::exists(FilesystemConfiguration::class, 'id')->where(
                    'is_active',
                    true,
                ),
            ],
            'maximum_age_in_days' => ['nullable', 'integer', 'min:1'],
            'maximum_storage_in_megabytes' => ['nullable', 'integer', 'min:1'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }
}
