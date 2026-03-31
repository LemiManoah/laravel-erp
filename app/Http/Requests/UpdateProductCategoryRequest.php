<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenant = tenant();

        return [
            'name' => ['required', 'string', 'max:255', $tenant->unique('product_categories', 'name')->ignore($this->route('productCategory'))],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
