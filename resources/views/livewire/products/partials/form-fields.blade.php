<div class="mb-5">
    <label for="product_category_id" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Category</label>
    <select
        id="product_category_id"
        wire:model="product_category_id"
        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
    >
        <option value="">Select Category (optional)</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>
    @error('product_category_id')
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="mb-5">
    <label for="name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Product Name *</label>
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

<div class="mb-5">
    <label for="base_price" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Base Price</label>
    <input
        id="base_price"
        type="number"
        wire:model.blur="base_price"
        step="0.01"
        min="0"
        placeholder="0.00"
        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
    >
    <p class="mt-1 text-xs text-gray-400">Optional. Default price for this product.</p>
    @error('base_price')
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="mb-5">
    <label for="description" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Description</label>
    <textarea
        id="description"
        wire:model.blur="description"
        rows="3"
        class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
    ></textarea>
    <p class="mt-1 text-xs text-gray-400">Optional. Describe the product details.</p>
    @error('description')
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>

<div class="mb-6">
    <label class="flex cursor-pointer items-center">
        <input
            type="checkbox"
            wire:model="is_active"
            class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 dark:border-gray-600 dark:bg-gray-700"
        >
        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active (can be selected when creating orders)</span>
    </label>
    @error('is_active')
        <p class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
