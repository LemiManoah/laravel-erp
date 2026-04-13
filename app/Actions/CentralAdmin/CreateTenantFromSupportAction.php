<?php

declare(strict_types=1);

namespace App\Actions\CentralAdmin;

use App\Actions\User\CreateUserAction;
use App\Models\Tenant;
use Database\Seeders\TenantBootstrapSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final readonly class CreateTenantFromSupportAction
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function handle(array $attributes): Tenant
    {
        $currentTenant = tenancy()->initialized ? tenant() : null;

        return DB::transaction(function () use ($attributes, $currentTenant): Tenant {
            if ($currentTenant !== null) {
                tenancy()->end();
            }

            try {
                $tenant = Tenant::create([
                    'name' => $attributes['name'],
                    'slug' => $attributes['slug'],
                    'email' => $attributes['email'] ?: null,
                    'phone' => $attributes['phone'] ?: null,
                    'is_active' => (bool) ($attributes['is_active'] ?? true),
                ]);

                $tenant->domains()->create([
                    'domain' => Str::lower((string) $attributes['primary_domain']),
                ]);

                tenancy()->initialize($tenant);

                app(TenantBootstrapSeeder::class)->run();

                app(CreateUserAction::class)->handle([
                    'name' => $attributes['admin_name'],
                    'email' => $attributes['admin_email'],
                    'phone' => $attributes['admin_phone'] ?: null,
                    'password' => $attributes['admin_password'],
                    'is_active' => true,
                    'theme_preference' => 'system',
                    'roles' => ['Admin'],
                ]);

                return $tenant->load('domains');
            } finally {
                tenancy()->end();

                if ($currentTenant !== null) {
                    tenancy()->initialize($currentTenant);
                }
            }
        });
    }
}
