<?php

namespace SameOldNick\BackupManager\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBackupMonitorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'disks' => ['sometimes', 'array', 'min:1'],
            'disks.*' => ['string'],
            'maximum_age_in_days' => ['nullable', 'integer', 'min:1'],
            'maximum_storage_in_megabytes' => ['nullable', 'integer', 'min:1'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }
}