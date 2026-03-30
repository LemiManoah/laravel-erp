<?php

declare(strict_types=1);

namespace App\Actions\Customer;

use App\Models\Customer;

final readonly class UpdateCustomerAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer;
    }
}
