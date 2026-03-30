<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;
use Illuminate\Support\Facades\Auth;

final readonly class CreateCustomerAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Customer
    {
        $customer = Customer::create([
            ...$data,
            'created_by' => Auth::id(),
        ]);

        return $customer;
    }
}
