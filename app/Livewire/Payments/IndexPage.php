<?php

declare(strict_types=1);

namespace App\Livewire\Payments;

use App\Models\Payment;
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

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('viewAny', Payment::class), 403);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->resetPage();
    }

    public function render(): View
    {
        $search = trim($this->search);

        $payments = Payment::query()
            ->with(['invoice.customer', 'receipt', 'receiver', 'voider', 'currency'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $paymentQuery) use ($search): void {
                    $paymentQuery->where('reference_number', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('payment_method', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('status', 'like', sprintf('%%%s%%', $search))
                        ->orWhereHas('invoice', function (Builder $invoiceQuery) use ($search): void {
                            $invoiceQuery->where('invoice_number', 'like', sprintf('%%%s%%', $search))
                                ->orWhereHas('customer', function (Builder $customerQuery) use ($search): void {
                                    $customerQuery->where('full_name', 'like', sprintf('%%%s%%', $search))
                                        ->orWhere('phone', 'like', sprintf('%%%s%%', $search));
                                });
                        })
                        ->orWhereHas('receipt', function (Builder $receiptQuery) use ($search): void {
                            $receiptQuery->where('receipt_number', 'like', sprintf('%%%s%%', $search));
                        });
                });
            })
            ->latest('payment_date')
            ->paginate(15);

        return view('livewire.payments.index-page', [
            'payments' => $payments,
        ]);
    }
}
