<?php

declare(strict_types=1);

namespace App\Livewire\ItemCategories;

use App\Models\ItemCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $itemCategoryId;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;

    public function mount(ItemCategory $itemCategory): void
    {
        abort_unless(auth()->user()?->can('inventory-items.update'), 403);

        $this->itemCategoryId = $itemCategory->id;
        $this->name = $itemCategory->name;
        $this->description = $itemCategory->description ?? '';
        $this->is_active = $itemCategory->is_active;
    }

    protected function rules(): array
    {
        $itemCategory = ItemCategory::query()->find($this->itemCategoryId);

        return [
            'name' => ['required', 'string', 'max:255', tenant()->unique('item_categories', 'name')->ignore($itemCategory)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ];
    }

    public function update(): mixed
    {
        abort_unless(auth()->user()?->can('inventory-items.update'), 403);

        $this->validate();

        ItemCategory::query()->findOrFail($this->itemCategoryId)->update([
            'name' => trim($this->name),
            'description' => $this->description === '' ? null : trim($this->description),
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'Item category updated successfully.');

        return $this->redirectRoute('item-categories.index');
    }

    public function render(): View
    {
        return view('livewire.item_categories.edit-page');
    }
}

