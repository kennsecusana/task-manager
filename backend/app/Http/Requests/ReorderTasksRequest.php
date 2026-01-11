<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderTasksRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tasks' => ['required', 'array'],
            'tasks.*.id' => ['required', 'integer', 'exists:tasks,id'],
            'tasks.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'tasks.*.id.exists' => 'One or more task IDs are invalid.',
        ];
    }
}
