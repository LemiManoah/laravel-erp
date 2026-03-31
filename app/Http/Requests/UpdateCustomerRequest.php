<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');
        $tenant = tenant();

        return [
            'full_name' => 'required|string|max:255',
            'phone' => ['required', 'string', $tenant->unique('customers', 'phone')->ignore($customer)],
            'alternative_phone' => 'nullable|string',
            'email' => ['nullable', 'email', $tenant->unique('customers', 'email')->ignore($customer)],
            'address' => 'nullable|string',
            'gender' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'notes' => 'nullable|string',
        ];
    }
}
