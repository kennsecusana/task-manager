<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'statement' => ['required', 'string', 'max:1000'],
            'task_date' => ['required', 'date'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
