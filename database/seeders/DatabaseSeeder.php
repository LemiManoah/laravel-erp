<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            TenantSeeder::class,
        ]);

        Tenant::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->each(function (Tenant $tenant): void {
                tenancy()->initialize($tenant);

                try {
                    $this->call(TenantAppSeeder::class);
                } finally {
                    tenancy()->end();
                }
            });
    }
}
