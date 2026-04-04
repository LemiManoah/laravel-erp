<?php

declare(strict_types=1);

namespace App\Livewire\Inventory\Receipts;

use App\Actions\Inventory\RecordInventoryMovementAction;
use App\Enums\InventoryMovementType;
use App\Models\InventoryItem;
use App\Models\StockLocation;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $receipt_type = 'opening_stock';

    public string $location_id = '';

    public string $movement_date = '';

    public string $notes = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $items = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('inventory-movements.create'), 403);

        $this->movement_date = now()->format('Y-m-d\TH:i');
        $this->receipt_type = InventoryMovementType::OpeningStock->value;
        $this->items = [$this->blankItem()];
    }

    protected function rules(): array
    {
        $tenant = tenant();

        return [
            'receipt_type' => ['required', Rule::in($this->allowedReceiptTypes()->pluck('value')->all())],
            'location_id' => ['required', $tenant->exists('stock_locations', 'id')],
            'movement_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', $tenant->exists('inventory_items', 'id')],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.batch_number' => ['nullable', 'string', 'max:255'],
            'items.*.expiry_date' => ['nullable', 'date'],
            'items.*.notes' => ['nullable', 'string'],
        ];
    }

    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->items === []) {
            $this->items[] = $this->blankItem();
        }
    }

    public function updatedItems($value, ?string $key = null): void
    {
        if ($key === null) {
            return;
        }

        [$index, $field] = explode('.', $key, 2);
        $index = (int) $index;

        if ($field !== 'inventory_item_id') {
            return;
        }

        $inventoryItem = InventoryItem::query()->with('defaultPrice')->find((int) $value);

        if ($inventoryItem !== null && blank($this->items[$index]['unit_cost'])) {
            $this->items[$index]['unit_cost'] = $inventoryItem->purchase_price ?? '';
        }

        if ($inventoryItem !== null && ! $inventoryItem->has_expiry) {
            $this->items[$index]['batch_number'] = '';
            $this->items[$index]['expiry_date'] = '';
        }
    }

    public function save(RecordInventoryMovementAction $recordInventoryMovement): mixed
    {
        abort_unless(auth()->user()?->can('inventory-movements.create'), 403);

        $this->validate();
        $this->validateInventoryItems();

        $movementType = InventoryMovementType::from($this->receipt_type);
        $lineItems = collect($this->items)->map(function (array $item): array {
            return [
                'inventory_item_id' => (int) $item['inventory_item_id'],
                'quantity' => (float) $item['quantity'],
                'unit_cost' => $item['unit_cost'] !== '' ? (float) $item['unit_cost'] : null,
                'batch_number' => trim((string) ($item['batch_number'] ?? '')),
                'expiry_date' => $item['expiry_date'] ?? '',
                'notes' => trim((string) ($item['notes'] ?? '')),
            ];
        });

        DB::transaction(function () use ($lineItems, $movementType, $recordInventoryMovement): void {
            foreach ($lineItems as $item) {
                $inventoryItem = InventoryItem::query()->findOrFail($item['inventory_item_id']);

                $recordInventoryMovement->handle($inventoryItem, $movementType, $item['quantity'], [
                    'location_id' => (int) $this->location_id,
                    'batch_number' => $item['batch_number'] !== '' ? $item['batch_number'] : null,
                    'expiry_date' => $item['expiry_date'] !== '' ? $item['expiry_date'] : null,
                    'received_at' => substr($this->movement_date, 0, 10),
                    'movement_date' => $this->movement_date,
                    'unit_cost' => $item['unit_cost'],
                    'notes' => $item['notes'] !== ''
                        ? $item['notes']
                        : ($this->notes !== '' ? trim($this->notes) : null),
                ]);
            }
        });

        session()->flash('success', 'Stock receipt recorded successfully.');

        return $this->redirectRoute('inventory.stocks.index');
    }

    public function render(): View
    {
        return view('livewire.inventory.receipts.create-page', [
            'inventoryItems' => InventoryItem::query()->stockTracked()->active()->with('defaultPrice')->orderBy('name')->get(),
            'locations' => StockLocation::query()->active()->ordered()->get(),
            'receiptTypes' => $this->allowedReceiptTypes(),
            'total' => collect($this->items)->sum(
                fn (array $item): float => ((float) ($item['quantity'] ?: 0)) * ((float) ($item['unit_cost'] ?: 0))
            ),
        ]);
    }

    private function validateInventoryItems(): void
    {
        $messages = [];

        foreach ($this->items as $index => $item) {
            $inventoryItem = InventoryItem::query()->find((int) ($item['inventory_item_id'] ?? 0));

            if ($inventoryItem === null) {
                continue;
            }

            if (! $inventoryItem->tracks_inventory) {
                $messages["items.$index.inventory_item_id"] = 'Selected inventory item does not track inventory.';
            }

            if ($inventoryItem->has_expiry) {
                if (blank($item['batch_number'] ?? null)) {
                    $messages["items.$index.batch_number"] = 'Batch number is required for expiring items.';
                }

                if (blank($item['expiry_date'] ?? null)) {
                    $messages["items.$index.expiry_date"] = 'Expiry date is required for expiring items.';
                }
            }
        }

        if ($messages !== []) {
            throw ValidationException::withMessages($messages);
        }
    }

    /**
     * @return array<string, string>
     */
    private function blankItem(): array
    {
        return [
            'inventory_item_id' => '',
            'quantity' => '',
            'unit_cost' => '',
            'batch_number' => '',
            'expiry_date' => '',
            'notes' => '',
        ];
    }

    private function allowedReceiptTypes()
    {
        return collect([
            InventoryMovementType::OpeningStock,
            InventoryMovementType::PurchaseReceipt,
            InventoryMovementType::SalesReturn,
            InventoryMovementType::AdjustmentGain,
            InventoryMovementType::Harvest,
            InventoryMovementType::ProductionOutput,
        ]);
    }
}

