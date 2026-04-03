<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateItemCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenant = tenant();

        return [
            'name' => ['required', 'string', 'max:255', $tenant->unique('item_categories', 'name')->ignore($this->route('itemCategory'))],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
