<?php

namespace SameOldNick\BackupManager\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Models\FilesystemConfiguration;
use SameOldNick\BackupManager\Rules\RelativePath;
use SameOldNick\BackupManager\Rules\Slugified;

class StoreBackupDestinationRequest extends FormRequest
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
        return [
            'enabled' => 'required|boolean',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(FilesystemConfiguration::class),
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique(FilesystemConfiguration::class),
                new Slugified,
            ],
            'type' => 'required|in:local,ftp,sftp',
            'host' => 'nullable|required_if:type,ftp,sftp|string|max:255',
            'port' => 'nullable|required_if:type,ftp,sftp|integer|min:1|max:65535',
            'auth_type' => 'nullable|required_if:type,sftp|string|in:password,key',
            'username' => [
                'nullable',
                Rule::requiredIf(fn () => $this->type === 'ftp' || $this->type === 'sftp'),
                'string',
                'max:255',
            ],
            'password' => [
                'nullable',
                Rule::requiredIf(fn () => $this->type === 'ftp' || ($this->type === 'sftp' && $this->auth_type === 'password')),
                'string',
                'max:255',
            ],
            'root' => [
                'nullable',
                Rule::requiredIf(fn () => $this->type === 'local'),
                'string',
                'max:255',
                ...($this->type === 'local' ? [new RelativePath] : []),
            ],
            'private_key' => [
                'nullable',
                Rule::requiredIf(fn () => $this->type === 'sftp' && $this->auth_type === 'key'),
                'string',
            ],
            'passphrase' => 'nullable|string|max:255',
            'extra' => 'nullable|array',
        ];
    }
}
