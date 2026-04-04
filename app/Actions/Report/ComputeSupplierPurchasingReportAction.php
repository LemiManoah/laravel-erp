<?php

declare(strict_types=1);

namespace App\Actions\Report;

use App\Models\PurchaseReceipt;
use App\Models\PurchaseReturn;
use App\Models\Supplier;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final readonly class ComputeSupplierPurchasingReportAction
{
    /**
     * @return array<string, mixed>
     */
    public function handle(?int $supplierId, ?string $startDate, ?string $endDate): array
    {
        $start = $startDate ? Carbon::parse($startDate) : Carbon::now()->startOfMonth();
        $end = $endDate ? Carbon::parse($endDate) : Carbon::now()->endOfMonth();

        $suppliers = Supplier::query()
            ->when($supplierId !== null, fn ($query) => $query->whereKey($supplierId))
            ->with([
                'purchaseOrders' => fn ($query) => $query
                    ->whereDate('order_date', '>=', $start->toDateString())
                    ->whereDate('order_date', '<=', $end->toDateString())
                    ->latest('order_date'),
                'purchaseReceipts' => fn ($query) => $query
                    ->whereDate('receipt_date', '>=', $start->toDateString())
                    ->whereDate('receipt_date', '<=', $end->toDateString())
                    ->latest('receipt_date'),
                'purchaseReturns' => fn ($query) => $query
                    ->whereDate('return_date', '>=', $start->toDateString())
                    ->whereDate('return_date', '<=', $end->toDateString())
                    ->latest('return_date'),
            ])
            ->orderBy('name')
            ->get();

        $supplierRows = $suppliers->map(function (Supplier $supplier): array {
            $orderedAmount = (float) $supplier->purchaseOrders->sum('subtotal_amount');
            $receivedAmount = (float) $supplier->purchaseReceipts->sum('subtotal_amount');
            $returnedAmount = (float) $supplier->purchaseReturns->sum('subtotal_amount');

            return [
                'supplier' => $supplier,
                'orders_count' => $supplier->purchaseOrders->count(),
                'receipts_count' => $supplier->purchaseReceipts->count(),
                'returns_count' => $supplier->purchaseReturns->count(),
                'ordered_amount' => $orderedAmount,
                'received_amount' => $receivedAmount,
                'returned_amount' => $returnedAmount,
                'net_purchased_amount' => $receivedAmount - $returnedAmount,
            ];
        })->values();

        return [
            'suppliers' => Supplier::query()->orderBy('name')->get(),
            'selected_supplier_id' => $supplierId,
            'supplier_rows' => $supplierRows,
            'recent_receipts' => $this->recentReceipts($supplierRows),
            'recent_returns' => $this->recentReturns($supplierRows),
            'summary' => [
                'suppliers_count' => $supplierRows->count(),
                'orders_count' => $supplierRows->sum('orders_count'),
                'receipts_count' => $supplierRows->sum('receipts_count'),
                'returns_count' => $supplierRows->sum('returns_count'),
                'ordered_amount' => $supplierRows->sum('ordered_amount'),
                'received_amount' => $supplierRows->sum('received_amount'),
                'returned_amount' => $supplierRows->sum('returned_amount'),
                'net_purchased_amount' => $supplierRows->sum('net_purchased_amount'),
            ],
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $supplierRows
     * @return Collection<int, PurchaseReceipt>
     */
    private function recentReceipts(Collection $supplierRows): Collection
    {
        return $supplierRows
            ->flatMap(function (array $row): Collection {
                return $row['supplier']->purchaseReceipts->map(function (PurchaseReceipt $receipt) use ($row): PurchaseReceipt {
                    $receipt->setRelation('supplier', $row['supplier']);

                    return $receipt;
                });
            })
            ->sortByDesc('receipt_date')
            ->take(10)
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $supplierRows
     * @return Collection<int, PurchaseReturn>
     */
    private function recentReturns(Collection $supplierRows): Collection
    {
        return $supplierRows
            ->flatMap(function (array $row): Collection {
                return $row['supplier']->purchaseReturns->map(function (PurchaseReturn $purchaseReturn) use ($row): PurchaseReturn {
                    $purchaseReturn->setRelation('supplier', $row['supplier']);

                    return $purchaseReturn;
                });
            })
            ->sortByDesc('return_date')
            ->take(10)
            ->values();
    }
}
