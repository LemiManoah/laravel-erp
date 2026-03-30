<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Receipt {{ $receipt->receipt_number }}</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <style type="text/css">
            html {
                font-family: sans-serif;
                line-height: 1.15;
                margin: 0;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
                font-weight: 400;
                line-height: 1.5;
                color: #212529;
                background-color: #fff;
                font-size: 14px;
                margin: 20px auto;
                max-width: 900px;
            }

            h4 {
                margin-top: 0;
                margin-bottom: 0.5rem;
                font-weight: 500;
                line-height: 1.2;
                font-size: 1.5rem;
            }

            p {
                margin-top: 0;
                margin-bottom: 1rem;
            }

            table {
                border-collapse: collapse;
                width: 100%;
                color: #212529;
            }

            .table th,
            .table td {
                padding: 0.75rem;
                vertical-align: top;
            }

            .table thead th {
                vertical-align: bottom;
                border-bottom: 2px solid #dee2e6;
            }

            .table.table-details td {
                border-top: 1px solid #dee2e6;
            }

            .border-0 {
                border: none !important;
            }

            .text-right {
                text-align: right !important;
            }

            .text-uppercase {
                text-transform: uppercase !important;
            }

            .cool-gray {
                color: #6B7280;
            }

            .party-header {
                font-size: 1.5rem;
                font-weight: 400;
            }

            .total-amount {
                font-size: 18px;
                font-weight: 700;
            }

            .actions {
                margin-bottom: 16px;
                font-size: 14px;
            }

            .actions button,
            .actions a {
                padding: 8px 12px;
                border: 0;
                background: #111827;
                color: white;
                text-decoration: none;
                cursor: pointer;
                margin-right: 8px;
                border-radius: 4px;
                display: inline-block;
            }

            @media print {
                .actions {
                    display: none;
                }

                body {
                    margin: 12px;
                }
            }
        </style>
    </head>

    <body>
        <div class="actions">
            <button type="button" onclick="window.print()">Print</button>
            <a href="javascript:window.close()">Close</a>
        </div>

        <table class="table">
            <tbody>
                <tr>
                    <td class="border-0" width="70%">
                        <h4 class="text-uppercase">
                            <strong>Receipt {{ $receipt->receipt_number }}</strong>
                        </h4>
                        <p class="cool-gray">Issued for payment against invoice {{ $receipt->payment->invoice->invoice_number }}</p>
                    </td>
                    <td class="border-0">
                        <p>Date: <strong>{{ $receipt->issued_date->format('M d, Y') }}</strong></p>
                        <p>Receipt No: <strong>{{ $receipt->receipt_number }}</strong></p>
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th class="border-0 party-header" width="48.5%">From</th>
                    <th class="border-0" width="3%"></th>
                    <th class="border-0 party-header">To</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border-0">
                        <p><strong>{{ config('app.name') }}</strong></p>
                    </td>
                    <td class="border-0"></td>
                    <td class="border-0">
                        <p><strong>{{ $receipt->payment->invoice->customer->full_name }}</strong></p>
                        @if($receipt->payment->invoice->customer->address)
                            <p>Address: {{ $receipt->payment->invoice->customer->address }}</p>
                        @endif
                        @if($receipt->payment->invoice->customer->phone)
                            <p>Phone: {{ $receipt->payment->invoice->customer->phone }}</p>
                        @endif
                        @if($receipt->payment->invoice->customer->email)
                            <p>Email: {{ $receipt->payment->invoice->customer->email }}</p>
                        @endif
                    </td>
                </tr>
            </tbody>
        </table>

        <table class="table table-details">
            <thead>
                <tr>
                    <th class="border-0">Description</th>
                    <th class="border-0">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Invoice Number</td>
                    <td>{{ $receipt->payment->invoice->invoice_number }}</td>
                </tr>
                <tr>
                    <td>Payment Date</td>
                    <td>{{ $receipt->payment->payment_date->format('M d, Y') }}</td>
                </tr>
                <tr>
                    <td>Payment Method</td>
                    <td>{{ $receipt->payment->payment_method }}</td>
                </tr>
                <tr>
                    <td>Reference Number</td>
                    <td>{{ $receipt->payment->reference_number ?: 'N/A' }}</td>
                </tr>
                <tr>
                    <td>Received By</td>
                    <td>{{ $receipt->payment->receiver?->name ?: 'System' }}</td>
                </tr>
                <tr>
                    <td><strong>Amount Received</strong></td>
                    <td class="total-amount">
                        {{ $currencyFormatter->formatValue($receipt->payment->amount, 2, $receipt->payment->currency) }}
                    </td>
                </tr>
            </tbody>
        </table>

        @if($receipt->payment->notes)
            <p><strong>Notes</strong>: {!! nl2br(e($receipt->payment->notes)) !!}</p>
        @endif
    </body>
</html>
