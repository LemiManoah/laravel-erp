<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tenant = tenant();

        return [
            'customer_id' => ['required', $tenant->exists('customers', 'id')],
            'currency_id' => ['required', $tenant->exists('currencies', 'id')],
            'order_date' => 'required|date',
            'promised_delivery_date' => 'nullable|date|after_or_equal:order_date',
            'priority' => 'required|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', $tenant->exists('products', 'id')],
            'items.*.garment_type' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.style_notes' => 'nullable|string',
            'items.*.fabric_details' => 'nullable|string',
        ];
    }
}
