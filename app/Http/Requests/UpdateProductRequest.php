<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenant = tenant();

        return [
            'product_category_id' => ['nullable', $tenant->exists('product_categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_sellable' => ['sometimes', 'boolean'],
            'buying_price' => ['nullable', 'numeric', 'min:0'],
            'base_price' => ['nullable', Rule::requiredIf($this->boolean('is_sellable', true)), 'numeric', 'min:0'],
        ];
    }
}
