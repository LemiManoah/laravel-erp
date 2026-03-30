<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenant = tenant();

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                $tenant->unique('expense_categories', 'name')->ignore($this->expense_category),
            ],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
