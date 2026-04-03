<div>
    <div class="mb-6">
        <a href="{{ route('invoices.index') }}" class="mb-2 inline-block text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
            <i class="fas fa-arrow-left mr-1"></i> Back to Invoices
        </a>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Create New Invoice</h1>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300">
            Please correct the highlighted errors and try again.
        </div>
    @endif

    <div class="space-y-6">
        {{-- Settings Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h2 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Settings</h2>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Customer <span class="text-red-500">*</span></label>
                    <select wire:model.live="customer_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->full_name }}</option>
                        @endforeach
                    </select>
                    @error('customer_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Currency <span class="text-red-500">*</span></label>
                    <select wire:model.live="currency_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->code }} - {{ $currency->name }}</option>
                        @endforeach
                    </select>
                    @error('currency_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Date <span class="text-red-500">*</span></label>
                    <input type="date" wire:model.blur="invoice_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('invoice_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Due Date</label>
                    <input type="date" wire:model.blur="due_date" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    @error('due_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Linked Order</label>
                    <select wire:model.live="order_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">No linked order</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}">{{ $order->order_number }}</option>
                        @endforeach
                    </select>
                    @error('order_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Location</label>
                    <select wire:model.live="stock_location_id" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <option value="">Default location</option>
                        @foreach($stockLocations as $location)
                            <option value="{{ $location->id }}">{{ $location->name }}</option>
                        @endforeach
                    </select>
                    @error('stock_location_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Invoice Items Card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900 dark:text-white">Invoice Items</h2>
                @error('items') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="w-44 px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Inventory Item</th>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Item Name <span class="text-red-500">*</span></th>
                            <th class="px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Description</th>
                            <th class="w-20 px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Qty</th>
                            <th class="w-28 px-3 py-2 text-left text-xs font-medium uppercase text-gray-500">Unit Price</th>
                            <th class="w-28 px-3 py-2 text-right text-xs font-medium uppercase text-gray-500">Total</th>
                            <th class="w-10 px-3 py-2"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($items as $index => $item)
                            <tr wire:key="item-{{ $index }}">
                                <td class="px-3 py-2">
                                    <select wire:model.live="items.{{ $index }}.product_id" class="w-full rounded-md border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                        <option value="">Custom item</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}{{ $product->sku ? ' ('.$product->sku.')' : '' }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" wire:model.blur="items.{{ $index }}.item_name" class="w-full rounded-md border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    @error("items.$index.item_name") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-3 py-2">
                                    <input type="text" wire:model.blur="items.{{ $index }}.description" class="w-full rounded-md border border-gray-300 px-2 py-1 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" wire:model.live="items.{{ $index }}.quantity" min="1" class="w-full rounded-md border border-gray-300 px-2 py-1 text-center text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    @error("items.$index.quantity") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number" wire:model.live="items.{{ $index }}.unit_price" min="0" step="0.01" class="w-full rounded-md border border-gray-300 px-2 py-1 text-right text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    @error("items.$index.unit_price") <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-3 py-2 text-right text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $currencyFormatter->formatValue(((float) ($item['quantity'] ?? 0)) * ((float) ($item['unit_price'] ?? 0)), 2) }}
                                </td>
                                <td class="px-3 py-2 text-right">
                                    <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="button" wire:click="addItem" class="mt-4 rounded bg-gray-100 px-3 py-1 text-sm text-gray-700 transition hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                <i class="fas fa-plus mr-1"></i> Add Item
            </button>
        </div>

        {{-- Bottom Row: Notes + Summary --}}
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <label class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Invoice Notes</label>
                <textarea wire:model.blur="notes" rows="4" placeholder="Terms, bank details, etc." class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"></textarea>
            </div>

            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-medium text-gray-900 dark:text-white">Summary</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between text-gray-500">
                        <span>Subtotal</span>
                        <span>{{ $currencyFormatter->formatValue($this->subtotal, 2) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-gray-500">
                        <span>Discount</span>
                        <input type="number" wire:model.live="discount_amount" step="0.01" min="0" class="w-24 rounded border border-gray-300 px-2 py-1 text-right text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    @error('discount_amount') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3 text-gray-500 dark:border-gray-700">
                        <span>Tax</span>
                        <input type="number" wire:model.live="tax_amount" step="0.01" min="0" class="w-24 rounded border border-gray-300 px-2 py-1 text-right text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    @error('tax_amount') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                    <div class="flex justify-between pt-1 text-lg font-bold text-gray-900 dark:text-white">
                        <span>Total</span>
                        <span>{{ $currencyFormatter->formatValue($this->total, 2) }}</span>
                    </div>
                </div>
                <button type="button" wire:click="save" class="mt-6 w-full rounded-md bg-blue-600 px-4 py-2 font-semibold text-white transition hover:bg-blue-700">
                    Save Invoice
                </button>
                <a href="{{ route('invoices.index') }}" class="mt-2 block text-center text-sm text-gray-500 hover:text-gray-700">Cancel</a>
            </div>
        </div>
    </div>
</div>
