<div class="mb-8">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Basic Details</h2>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define how this inventory item appears in your catalog and how it behaves in sales, purchasing, and stock operations.</p>
</div>

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div>
        <label for="product_category_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Item Category</label>
        <select
            id="product_category_id"
            wire:model="product_category_id"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
            <option value="">Select item category (optional)</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>
        @error('product_category_id')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="item_type" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Item Type <span class="text-red-500">*</span></label>
        <select
            id="item_type"
            wire:model.live="item_type"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
            @foreach($itemTypes as $itemTypeOption)
                <option value="{{ $itemTypeOption->value }}">{{ $itemTypeOption->label() }}</option>
            @endforeach
        </select>
        @error('item_type')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Inventory Item Name <span class="text-red-500">*</span></label>
        <input
            id="name"
            type="text"
            wire:model.blur="name"
            required
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
        @error('name')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="rounded-lg border border-dashed border-gray-300 p-4 dark:border-gray-600 md:col-span-2">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">SKU</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $sku ?: 'Will be generated automatically after the item is created.' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Barcode</p>
                <p class="mt-1 text-sm text-gray-900 dark:text-white">
                    @if($is_sellable)
                        {{ $barcode ?: 'Will be generated automatically for sellable items after save.' }}
                    @else
                        Not generated because this item is not marked for sale.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div>
        <label for="purchase_price" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Purchase Price</label>
        <input
            id="purchase_price"
            type="number"
            wire:model.blur="purchase_price"
            step="0.01"
            min="0"
            placeholder="0.00"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
        <p class="mt-1 text-xs text-gray-400">Optional. Use this as the default purchase or cost price.</p>
        @error('purchase_price')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="sale_price" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Sale Price @if($is_sellable)<span class="text-red-500">*</span>@endif</label>
        <input
            id="sale_price"
            type="number"
            wire:model.blur="sale_price"
            step="0.01"
            min="0"
            placeholder="0.00"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
        <p class="mt-1 text-xs text-gray-400">Required when the item can be sold.</p>
        @error('sale_price')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="parent_item_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Parent Item</label>
        <select
            id="parent_item_id"
            wire:model="parent_item_id"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        >
            <option value="">None</option>
            @foreach($parentProducts as $parentProduct)
                <option value="{{ $parentProduct->id }}">{{ $parentProduct->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-gray-400">Use this for variants or pack-size children.</p>
        @error('parent_item_id')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label for="description" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
        <textarea
            id="description"
            wire:model.blur="description"
            rows="3"
            class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
        ></textarea>
        <p class="mt-1 text-xs text-gray-400">Optional. Describe the inventory item.</p>
        @error('description')
            <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="my-8 border-t border-gray-200 pt-6 dark:border-gray-700">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Inventory Settings</h2>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Define whether this inventory item tracks stock and how future stock receipts, sales, and adjustments should behave.</p>
</div>

<div class="grid grid-cols-1 gap-5 md:grid-cols-2">
    <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700 md:col-span-2">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <label class="flex cursor-pointer items-center">
                <input
                    type="checkbox"
                    wire:model.live="tracks_inventory"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700"
                >
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Tracks inventory</span>
            </label>

            <label class="flex cursor-pointer items-center">
                <input
                    type="checkbox"
                    wire:model.live="is_sellable"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700"
                >
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can be sold</span>
            </label>

            <label class="flex cursor-pointer items-center">
                <input
                    type="checkbox"
                    wire:model="is_purchasable"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700"
                >
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Can be purchased</span>
            </label>

            <label class="flex cursor-pointer items-center">
                <input
                    type="checkbox"
                    wire:model="has_variants"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700"
                >
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Has variants or pack sizes</span>
            </label>

            <label class="flex cursor-pointer items-center">
                <input
                    type="checkbox"
                    wire:model="is_active"
                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700"
                >
                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
            </label>
        </div>
        @error('tracks_inventory')
            <p class="mt-2 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
        @enderror
    </div>

    @if($tracks_inventory)
        <div>
            <label for="base_unit_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Base Unit <span class="text-red-500">*</span></label>
            <select
                id="base_unit_id"
                wire:model="base_unit_id"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
                <option value="">Select unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->name }} ({{ $unit->abbreviation }})</option>
                @endforeach
            </select>
            @error('base_unit_id')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="reorder_level" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Reorder Level</label>
            <input
                id="reorder_level"
                type="number"
                wire:model.blur="reorder_level"
                step="0.01"
                min="0"
                placeholder="0.00"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            >
            @error('reorder_level')
                <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>

        <div class="rounded-lg border border-dashed border-gray-300 p-4 dark:border-gray-600 md:col-span-2">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <label class="flex cursor-pointer items-center">
                    <input
                        type="checkbox"
                        wire:model.live="has_expiry"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700"
                    >
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Item expires</span>
                </label>

                <label class="flex cursor-pointer items-center">
                    <input
                        type="checkbox"
                        wire:model="is_serialized"
                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700"
                    >
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Serialized item</span>
                </label>
            </div>

            @if($has_expiry)
                <p class="mt-3 text-xs text-amber-600 dark:text-amber-300">Expiry-controlled items will require batch number and expiry date when you later receive or open stock in the inventory module.</p>
            @else
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Inventory issues now always require available stock on hand. Negative stock is no longer allowed for inventory items.</p>
            @endif
        </div>
    @else
        <div class="rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-900/40 dark:text-gray-400 md:col-span-2">
            Inventory-only fields are hidden because this item does not track stock.
        </div>
    @endif
</div>
