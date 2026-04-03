<div>
    <div class="header">
        <div>
            <h1>Inventory Status Report</h1>
            <p class="meta">Generated on {{ now()->format('M d, Y H:i') }}</p>
        </div>
    </div>

    <div class="cards">
        <div class="card"><div class="label">Tracked Products</div><div class="value">{{ $summary['tracked_products'] }}</div></div>
        <div class="card"><div class="label">Low Stock</div><div class="value">{{ $summary['low_stock_products'] }}</div></div>
        <div class="card"><div class="label">Near Expiry</div><div class="value">{{ $summary['near_expiry_rows'] }}</div></div>
        <div class="card"><div class="label">Expired Rows</div><div class="value">{{ $summary['expired_rows'] }}</div></div>
    </div>

    <h2>Low Stock Products</h2>
    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">On Hand</th>
                <th class="text-right">Reorder Level</th>
            </tr>
        </thead>
        <tbody>
            @forelse($low_stock_products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td class="text-right">{{ number_format((float) $product->quantity_on_hand, 2) }}</td>
                    <td class="text-right">{{ number_format((float) $product->reorder_level, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" class="muted">No low stock products.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Stock By Location</h2>
    <table>
        <thead>
            <tr>
                <th>Location</th>
                <th class="text-right">Products</th>
                <th class="text-right">Stock Rows</th>
                <th class="text-right">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @forelse($stock_by_location as $row)
                <tr>
                    <td>{{ $row['location']->name }}</td>
                    <td class="text-right">{{ $row['tracked_products'] }}</td>
                    <td class="text-right">{{ $row['stock_rows'] }}</td>
                    <td class="text-right">{{ number_format((float) $row['total_quantity'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="4" class="muted">No location stock found.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Expiry Watch</h2>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th>Product</th>
                <th>Stock Row</th>
                <th class="text-right">Qty</th>
                <th>Expiry</th>
            </tr>
        </thead>
        <tbody>
            @forelse($near_expiry_stocks as $stock)
                <tr>
                    <td>Near expiry</td>
                    <td>{{ $stock->product->name }}</td>
                    <td>{{ $stock->batch_number ?? 'Standard stock row' }}{{ $stock->location ? ' ('.$stock->location->name.')' : '' }}</td>
                    <td class="text-right">{{ number_format((float) $stock->quantity_on_hand, 2) }}</td>
                    <td>{{ $stock->expiry_date?->format('M d, Y') }}</td>
                </tr>
            @empty
            @endforelse

            @forelse($expired_stocks as $stock)
                <tr>
                    <td>Expired</td>
                    <td>{{ $stock->product->name }}</td>
                    <td>{{ $stock->batch_number ?? 'Standard stock row' }}{{ $stock->location ? ' ('.$stock->location->name.')' : '' }}</td>
                    <td class="text-right">{{ number_format((float) $stock->quantity_on_hand, 2) }}</td>
                    <td>{{ $stock->expiry_date?->format('M d, Y') }}</td>
                </tr>
            @empty
                @if($near_expiry_stocks->isEmpty())
                    <tr><td colspan="5" class="muted">No expiry-risk stock rows.</td></tr>
                @endif
            @endforelse
        </tbody>
    </table>
</div>
