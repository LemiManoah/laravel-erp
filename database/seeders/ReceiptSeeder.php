<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Database\Seeder;

final class ReceiptSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->receipts() as $referenceNumber => $attributes) {
            $payment = Payment::query()
                ->where('reference_number', $referenceNumber)
                ->where('status', 'valid')
                ->first();

            if ($payment === null) {
                continue;
            }

            Receipt::query()->updateOrCreate(
                ['payment_id' => $payment->id],
                [
                    'receipt_number' => $attributes['receipt_number'],
                    'issued_date' => $attributes['issued_date'],
                ],
            );
        }
    }

    /**
     * @return array<string, array{receipt_number: string, issued_date: string}>
     */
    private function receipts(): array
    {
        return [
            'CC-1234567890' => [
                'receipt_number' => 'RCT-2026-001',
                'issued_date' => '2026-02-08',
            ],
            'BT-WEDDING-001' => [
                'receipt_number' => 'RCT-2026-002',
                'issued_date' => '2026-01-09',
            ],
            'CASH-002' => [
                'receipt_number' => 'RCT-2026-003',
                'issued_date' => '2026-01-29',
            ],
            'CC-9876543210' => [
                'receipt_number' => 'RCT-2026-004',
                'issued_date' => '2026-02-23',
            ],
            'BT-SUIT-002' => [
                'receipt_number' => 'RCT-2026-005',
                'issued_date' => '2026-03-10',
            ],
        ];
    }
}
