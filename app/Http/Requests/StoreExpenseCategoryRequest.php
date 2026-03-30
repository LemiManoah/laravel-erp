<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenant = tenant();

        return [
            'name' => ['required', 'string', 'max:255', $tenant->unique('expense_categories', 'name')],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
