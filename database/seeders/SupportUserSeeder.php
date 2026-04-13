<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class SupportUserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->first();

        if ($tenant === null) {
            return;
        }

        $user = User::withoutTenancy()->firstOrNew([
            'tenant_id' => $tenant->id,
            'email' => 'support@localhost',
        ]);

        if (! $user->exists) {
            $user->password = Hash::make('password');
        }

        $user->fill([
            'name' => 'Platform Support',
            'phone' => '+256700000001',
            'is_active' => true,
            'is_support' => true,
            'theme_preference' => 'system',
        ]);
        $user->email_verified_at = now();
        $user->assignRole('Admin'); 

        if (! $user->exists || $user->isDirty()) {
            $user->save();
        }

        if (! $user->can('platform.tenants.manage')) {
            $user->givePermissionTo('platform.tenants.manage');
        }
    }
}
