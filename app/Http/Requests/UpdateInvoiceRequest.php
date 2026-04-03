<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
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
            'order_id' => ['nullable', $tenant->exists('orders', 'id')],
            'stock_location_id' => ['nullable', $tenant->exists('stock_locations', 'id')],
            'currency_id' => ['required', $tenant->exists('currencies', 'id')],
            'invoice_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:invoice_date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => ['nullable', $tenant->exists('inventory_items', 'id')],
            'items.*.item_name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
        ];
    }
}

