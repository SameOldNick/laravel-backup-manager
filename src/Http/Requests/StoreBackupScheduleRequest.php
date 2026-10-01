<?php

namespace SameOldNick\BackupManager\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use SameOldNick\BackupManager\Enums\BackupTypes;
use SameOldNick\BackupManager\Rules\CronExpression as CronExpressionRule;
use SameOldNick\BackupManager\Rules\SelectableDestinationId;

class StoreBackupScheduleRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'type' => [
                'required',
                'string',
                Rule::enum(BackupTypes::class),
            ],
            'cron_expression' => ['required', 'string', new CronExpressionRule],
            'is_active' => 'sometimes|boolean',
            'destination_ids' => 'sometimes|array|max:255',
            'destination_ids.*' => [
                'integer',
                new SelectableDestinationId,
            ],
        ];
    }
}
