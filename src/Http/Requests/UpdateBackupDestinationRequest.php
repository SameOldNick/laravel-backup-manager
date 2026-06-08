<?php

namespace SameOldNick\BackupManager\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Rules\RelativePath;
use SameOldNick\BackupManager\Rules\Slugified;

class UpdateBackupDestinationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var FilesystemConfiguration $destination */
        $destination = $this->route('destination');

        return [
            'enabled' => ['sometimes', 'boolean'],
            'name' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique(FilesystemConfiguration::class)->ignore($destination),
            ],
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:255',
                Rule::unique(FilesystemConfiguration::class)->ignore($destination),
                new Slugified,
            ],
            'host' => ['sometimes', 'string', 'max:255'],
            'port' => ['sometimes', 'integer', 'min:1', 'max:65535'],
            'username' => ['sometimes', 'string', 'max:255'],
            'auth_type' => ['sometimes', 'nullable', 'string', 'in:password,key'],
            'password' => [
                'sometimes',
                'string',
                'max:255',
                'required_with:confirm_password',
            ],
            'confirm_password' => [
                'sometimes',
                'string',
                'required_with:password',
                'same:password',
            ],
            'private_key' => ['sometimes', 'string'],
            'passphrase' => ['sometimes', 'nullable', 'string', 'max:255'],
            'root' => [
                'sometimes',
                'string',
                'max:255',
                ...($destination->disk_type === 'local' ? [new RelativePath] : []),
            ],
            'extra' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
