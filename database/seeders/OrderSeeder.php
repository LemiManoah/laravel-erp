<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Currency;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Seeder;

final class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $userIds = User::query()->pluck('id', 'email');
        $customerIds = Customer::query()->pluck('id', 'customer_code');
        $currencyIds = Currency::query()->pluck('id', 'code');

        foreach ($this->orders() as $record) {
            $items = $record['items'];
            $customerCode = $record['customer_code'];
            $currencyCode = $record['currency_code'];
            $assignedToEmail = $record['assigned_to_email'];
            $createdByEmail = $record['created_by_email'];
            unset($record['items']);
            unset($record['customer_code']);
            unset($record['currency_code']);
            unset($record['assigned_to_email']);
            unset($record['created_by_email']);

            $order = Order::query()->updateOrCreate(
                ['order_number' => $record['order_number']],
                [
                    ...$record,
                    'customer_id' => $customerIds[$customerCode] ?? null,
                    'currency_id' => $currencyIds[$currencyCode] ?? null,
                    'assigned_to' => $userIds[$assignedToEmail] ?? null,
                    'created_by' => $userIds[$createdByEmail] ?? null,
                ],
            );

            foreach ($items as $item) {
                OrderItem::query()->updateOrCreate(
                    [
                        'order_id' => $order->id,
                        'garment_type' => $item['garment_type'],
                        'description' => $item['description'],
                    ],
                    $item,
                );
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function orders(): array
    {
        return [
            [
                'order_number' => 'ORD-2026-001',
                'customer_code' => 'CUST001',
                'currency_code' => 'UGX',
                'order_date' => '2026-01-29',
                'promised_delivery_date' => '2026-02-28',
                'actual_completion_date' => '2026-03-02',
                'status' => 'completed',
                'priority' => 'normal',
                'notes' => 'Classic navy business suit.',
                'assigned_to_email' => 'robert.tailor@suits.com',
                'created_by_email' => 'admin@suits.com',
                'items' => [
                    [
                        'garment_type' => 'suit',
                        'description' => 'Navy business suit with pinstripes',
                        'quantity' => 1,
                        'unit_price' => 1200.00,
                        'style_notes' => 'Classic two-button design, slim fit.',
                        'fabric_details' => 'Wool blend, 95% wool, 5% elastane.',
                        'color' => 'Navy blue with white pinstripes',
                        'lining_details' => 'Silk lining in navy blue.',
                        'button_details' => 'Horn buttons',
                        'monogram_text' => 'JA',
                        'urgent_flag' => false,
                    ],
                ],
            ],
            [
                'order_number' => 'ORD-2026-002',
                'customer_code' => 'CUST002',
                'currency_code' => 'USD',
                'order_date' => '2026-02-13',
                'promised_delivery_date' => '2026-03-15',
                'actual_completion_date' => null,
                'status' => 'in_progress',
                'priority' => 'high',
                'notes' => 'Power suit collection for board meetings.',
                'assigned_to_email' => 'susan.designer@suits.com',
                'created_by_email' => 'admin@suits.com',
                'items' => [
                    [
                        'garment_type' => 'suit',
                        'description' => 'Charcoal grey power suit',
                        'quantity' => 2,
                        'unit_price' => 1500.00,
                        'style_notes' => 'Executive cut, shoulder pads.',
                        'fabric_details' => 'Premium wool, 100% merino.',
                        'color' => 'Charcoal grey',
                        'lining_details' => 'Silk lining in burgundy.',
                        'button_details' => 'Mother of pearl buttons',
                        'monogram_text' => 'SM',
                        'urgent_flag' => true,
                    ],
                    [
                        'garment_type' => 'blouse',
                        'description' => 'White silk blouses',
                        'quantity' => 3,
                        'unit_price' => 200.00,
                        'style_notes' => 'French cuffs, collarless design.',
                        'fabric_details' => '100% silk.',
                        'color' => 'White',
                        'lining_details' => 'No lining',
                        'button_details' => 'Fabric covered buttons',
                        'monogram_text' => '',
                        'urgent_flag' => true,
                    ],
                ],
            ],
            [
                'order_number' => 'ORD-2026-003',
                'customer_code' => 'CUST003',
                'currency_code' => 'UGX',
                'order_date' => '2025-12-30',
                'promised_delivery_date' => '2026-01-29',
                'actual_completion_date' => '2026-01-31',
                'status' => 'completed',
                'priority' => 'normal',
                'notes' => 'Wedding tuxedo and formal wear.',
                'assigned_to_email' => 'robert.tailor@suits.com',
                'created_by_email' => 'admin@suits.com',
                'items' => [
                    [
                        'garment_type' => 'tuxedo',
                        'description' => 'Black tuxedo with tails',
                        'quantity' => 1,
                        'unit_price' => 2500.00,
                        'style_notes' => 'Traditional formal wear.',
                        'fabric_details' => 'Wool barathea, 100% wool.',
                        'color' => 'Black',
                        'lining_details' => 'Black satin lining.',
                        'button_details' => 'Black satin covered buttons',
                        'monogram_text' => 'MC',
                        'urgent_flag' => false,
                    ],
                    [
                        'garment_type' => 'vest',
                        'description' => 'Matching waistcoat',
                        'quantity' => 1,
                        'unit_price' => 300.00,
                        'style_notes' => 'Double-breasted design.',
                        'fabric_details' => 'Same as tuxedo.',
                        'color' => 'Black',
                        'lining_details' => 'Black satin.',
                        'button_details' => 'Satin covered buttons',
                        'monogram_text' => '',
                        'urgent_flag' => false,
                    ],
                ],
            ],
            [
                'order_number' => 'ORD-2026-004',
                'customer_code' => 'CUST004',
                'currency_code' => 'UGX',
                'order_date' => '2026-03-10',
                'promised_delivery_date' => '2026-04-09',
                'actual_completion_date' => null,
                'status' => 'pending',
                'priority' => 'normal',
                'notes' => 'Modern fashion-forward collection.',
                'assigned_to_email' => 'susan.designer@suits.com',
                'created_by_email' => 'admin@suits.com',
                'items' => [
                    [
                        'garment_type' => 'suit',
                        'description' => 'Modern slim-fit suit',
                        'quantity' => 1,
                        'unit_price' => 1100.00,
                        'style_notes' => 'Contemporary cut, narrow lapels.',
                        'fabric_details' => 'Cotton-linen blend.',
                        'color' => 'Light grey',
                        'lining_details' => 'Contrast patterned lining.',
                        'button_details' => 'Modern metal buttons',
                        'monogram_text' => 'ER',
                        'urgent_flag' => false,
                    ],
                ],
            ],
            [
                'order_number' => 'ORD-2026-005',
                'customer_code' => 'CUST005',
                'currency_code' => 'UGX',
                'order_date' => '2026-03-20',
                'promised_delivery_date' => '2026-04-19',
                'actual_completion_date' => null,
                'status' => 'pending',
                'priority' => 'low',
                'notes' => 'Seasonal wardrobe update.',
                'assigned_to_email' => 'robert.tailor@suits.com',
                'created_by_email' => 'admin@suits.com',
                'items' => [
                    [
                        'garment_type' => 'suit',
                        'description' => 'Seasonal business suit',
                        'quantity' => 2,
                        'unit_price' => 950.00,
                        'style_notes' => 'Classic fit, versatile styling.',
                        'fabric_details' => 'Tropical weight wool.',
                        'color' => 'Medium grey',
                        'lining_details' => 'Standard polyester lining.',
                        'button_details' => 'Plastic buttons',
                        'monogram_text' => 'DT',
                        'urgent_flag' => false,
                    ],
                ],
            ],
        ];
    }
}
