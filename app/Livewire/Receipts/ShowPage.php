<?php

declare(strict_types=1);

namespace App\Livewire\Receipts;

use App\Models\Receipt;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ShowPage extends Component
{
    public Receipt $receipt;

    public function mount(Receipt $receipt): void
    {
        abort_unless(auth()->user()?->can('receipts.view'), 403);
        abort_unless(auth()->user()?->can('view', $receipt), 403);

        $this->receipt = $receipt->load(['payment.invoice.customer', 'payment.receiver', 'payment.currency']);
    }

    public function render(): View
    {
        return view('livewire.receipts.show-page');
    }
}
