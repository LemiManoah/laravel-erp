<?php

declare(strict_types=1);

namespace App\Livewire\Products\Concerns;

use App\Models\Product;

trait InteractsWithProductForm
{
    public ?string $product_category_id = null;

    public string $name = '';

    public ?string $description = null;

    public ?string $base_price = null;

    public bool $is_active = true;

    /**
     * @return array<string, mixed>
     */
    protected function productRules(): array
    {
        $tenant = tenant();

        return [
            'product_category_id' => ['nullable', $tenant->exists('product_categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'base_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    protected function fillFromProduct(Product $product): void
    {
        $this->product_category_id = $product->product_category_id === null ? null : (string) $product->product_category_id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->base_price = $product->base_price === null ? null : (string) $product->base_price;
        $this->is_active = $product->is_active;
    }

    /**
     * @return array<string, mixed>
     */
    protected function productPayload(): array
    {
        $description = $this->description === null ? null : trim($this->description);
        $basePrice = $this->base_price === null ? null : trim($this->base_price);

        return [
            'product_category_id' => filled($this->product_category_id) ? (int) $this->product_category_id : null,
            'name' => trim($this->name),
            'description' => $description === '' ? null : $description,
            'base_price' => $basePrice === '' ? null : $basePrice,
            'is_active' => $this->is_active,
        ];
    }
}
