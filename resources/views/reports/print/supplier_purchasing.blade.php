@extends('reports.print.layout')

@section('title', 'Supplier Purchasing Report')

@section('content')
    <div class="header">
        <div>
            <h1>Supplier Purchasing Report</h1>
            <p class="meta">{{ $start_date }} to {{ $end_date }}</p>
        </div>
    </div>

    <div class="cards">
        <div class="card"><div class="label">Suppliers</div><div class="value">{{ $summary['suppliers_count'] }}</div></div>
        <div class="card"><div class="label">Ordered</div><div class="value">{{ $currencyFormatter->formatValue($summary['ordered_amount'], 2) }}</div></div>
        <div class="card"><div class="label">Received</div><div class="value">{{ $currencyFormatter->formatValue($summary['received_amount'], 2) }}</div></div>
        <div class="card"><div class="label">Returned</div><div class="value">{{ $currencyFormatter->formatValue($summary['returned_amount'], 2) }}</div></div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Supplier</th>
                <th class="text-right">Orders</th>
                <th class="text-right">Receipts</th>
                <th class="text-right">Returns</th>
                <th class="text-right">Ordered</th>
                <th class="text-right">Received</th>
                <th class="text-right">Returned</th>
                <th class="text-right">Net Purchased</th>
            </tr>
        </thead>
        <tbody>
            @forelse($supplier_rows as $row)
                <tr>
                    <td>{{ $row['supplier']->name }}</td>
                    <td class="text-right">{{ $row['orders_count'] }}</td>
                    <td class="text-right">{{ $row['receipts_count'] }}</td>
                    <td class="text-right">{{ $row['returns_count'] }}</td>
                    <td class="text-right">{{ $currencyFormatter->formatValue($row['ordered_amount'], 2) }}</td>
                    <td class="text-right">{{ $currencyFormatter->formatValue($row['received_amount'], 2) }}</td>
                    <td class="text-right">{{ $currencyFormatter->formatValue($row['returned_amount'], 2) }}</td>
                    <td class="text-right">{{ $currencyFormatter->formatValue($row['net_purchased_amount'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="muted">No supplier purchasing activity found for this period.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="section">
        <h2 class="section-title">Recent Purchase Receipts</h2>
        <table>
            <thead>
                <tr>
                    <th>Receipt</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent_receipts as $receipt)
                    <tr>
                        <td>{{ $receipt->receipt_number }}</td>
                        <td>{{ $receipt->supplier->name }}</td>
                        <td>{{ $receipt->receipt_date->format('M d, Y') }}</td>
                        <td class="text-right">{{ $currencyFormatter->formatValue($receipt->subtotal_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted">No receipts found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="section">
        <h2 class="section-title">Recent Purchase Returns</h2>
        <table>
            <thead>
                <tr>
                    <th>Return</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recent_returns as $purchaseReturn)
                    <tr>
                        <td>{{ $purchaseReturn->return_number }}</td>
                        <td>{{ $purchaseReturn->supplier->name }}</td>
                        <td>{{ $purchaseReturn->return_date->format('M d, Y') }}</td>
                        <td class="text-right">{{ $currencyFormatter->formatValue($purchaseReturn->subtotal_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="muted">No returns found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
