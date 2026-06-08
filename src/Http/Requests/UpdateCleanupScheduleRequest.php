<?php

namespace SameOldNick\BackupManager\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use SameOldNick\BackupManager\Rules\CronExpression as CronExpressionRule;

class UpdateCleanupScheduleRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255',
            'cron_expression' => ['sometimes', 'string', new CronExpressionRule],
            'is_active' => 'sometimes|boolean',
        ];
    }
}
