<?php

declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Invoice\SyncInvoiceStatusesAction;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

abstract class ReportPage extends Component
{
    protected function renderReport(string $view, string $title, array $data = []): View
    {
        $prefix = $this->isPrintMode() ? 'reports.print' : 'reports';

        $layout = $this->isPrintMode()
            ? 'components.layouts.report-print'
            : 'components.layouts.app';

        return view(sprintf('%s.%s', $prefix, $view), $data)
            ->layout($layout, ['title' => $title]);
    }

    protected function validatedDateRange(): array
    {
        $validated = validator(request()->query(), [
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ])->validate();

        return [
            'start_date' => $validated['start_date'] ?? now()->startOfMonth()->toDateString(),
            'end_date' => $validated['end_date'] ?? now()->endOfMonth()->toDateString(),
        ];
    }

    protected function validatedCustomerStatementFilters(): array
    {
        $tenant = tenant();

        if ($tenant === null) {
            throw ValidationException::withMessages([
                'tenant' => 'No tenant context is available.',
            ]);
        }

        $validated = validator(request()->query(), [
            'customer_id' => ['nullable', $tenant->exists('customers', 'id')],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ])->validate();

        return [
            'customer_id' => $this->optionalInteger($validated, 'customer_id'),
            'start_date' => $validated['start_date'] ?? now()->startOfMonth()->toDateString(),
            'end_date' => $validated['end_date'] ?? now()->endOfMonth()->toDateString(),
        ];
    }

    protected function validatedStockCardFilters(): array
    {
        $tenant = tenant();

        if ($tenant === null) {
            throw ValidationException::withMessages([
                'tenant' => 'No tenant context is available.',
            ]);
        }

        $validated = validator(request()->query(), [
            'inventory_item_id' => ['nullable', $tenant->exists('inventory_items', 'id')],
            'location_id' => ['nullable', $tenant->exists('stock_locations', 'id')],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ])->validate();

        return [
            'inventory_item_id' => $this->optionalInteger($validated, 'inventory_item_id'),
            'location_id' => $this->optionalInteger($validated, 'location_id'),
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
        ];
    }

    protected function validatedSupplierPurchasingFilters(): array
    {
        $validated = validator(request()->query(), [
            'supplier_id' => ['nullable', 'integer'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
        ])->validate();

        return [
            'supplier_id' => $this->optionalInteger($validated, 'supplier_id'),
            'start_date' => $validated['start_date'] ?? now()->startOfMonth()->toDateString(),
            'end_date' => $validated['end_date'] ?? now()->endOfMonth()->toDateString(),
        ];
    }

    protected function syncInvoiceStatuses(): void
    {
        app(SyncInvoiceStatusesAction::class)->handle();
    }

    protected function isPrintMode(): bool
    {
        return str_ends_with((string) request()->route()?->getName(), '.print');
    }

    protected function optionalInteger(array $validated, string $key): ?int
    {
        if (! array_key_exists($key, $validated) || $validated[$key] === null) {
            return null;
        }

        return (int) $validated[$key];
    }
}

