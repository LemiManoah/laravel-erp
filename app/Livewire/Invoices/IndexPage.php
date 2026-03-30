<?php

declare(strict_types=1);

namespace App\Livewire\Invoices;

use App\Actions\Invoice\SyncInvoiceStatusesAction;
use App\Models\Invoice;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class IndexPage extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('viewAny', Invoice::class), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->status = '';
        $this->resetPage();
    }

    public function render(): View
    {
        app(SyncInvoiceStatusesAction::class)->handle();

        $search = trim($this->search);

        $invoices = Invoice::query()
            ->with('customer')
            ->when($this->status !== '', static fn (Builder $query) => $query->where('status', $this->status))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $invoiceQuery) use ($search): void {
                    $invoiceQuery->where('invoice_number', 'like', sprintf('%%%s%%', $search))
                        ->orWhereHas('customer', function (Builder $customerQuery) use ($search): void {
                            $customerQuery->where('full_name', 'like', sprintf('%%%s%%', $search))
                                ->orWhere('phone', 'like', sprintf('%%%s%%', $search));
                        });
                });
            })
            ->latest('invoice_date')
            ->paginate(10);

        return view('livewire.invoices.index-page', [
            'invoices' => $invoices,
        ]);
    }
}
