<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statement' => ['sometimes', 'string', 'max:1000'],
            'is_completed' => ['sometimes', 'boolean'],
            'task_date' => ['sometimes', 'date'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
