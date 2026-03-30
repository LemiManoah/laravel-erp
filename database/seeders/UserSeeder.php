<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()
            ->whereIn('name', ['Admin', 'Sales', 'Accountant', 'Tailor'])
            ->get()
            ->keyBy('name');

        foreach ($this->users() as $attributes) {
            $roleName = $attributes['role'];
            $emailVerifiedAt = $attributes['email_verified_at'];
            unset($attributes['role']);
            unset($attributes['email_verified_at']);

            $user = User::query()->firstOrNew(['email' => $attributes['email']]);

            if (! $user->exists) {
                $user->password = Hash::make('password');
            }

            $user->fill($attributes);
            $user->email_verified_at = $emailVerifiedAt;

            if (! $user->exists || $user->isDirty()) {
                $user->save();
            }

            $role = $roles->get($roleName);

            if ($role !== null) {
                $user->syncRoles([$role]);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function users(): array
    {
        return [
            [
                'role' => 'Admin',
                'name' => 'System Administrator',
                'email' => 'admin@suits.com',
                'phone' => '+254700000000',
                'is_active' => true,
                'theme_preference' => 'light',
                'email_verified_at' => '2026-01-01 09:00:00',
                'last_login_at' => '2026-03-29 08:15:00',
            ],
            [
                'role' => 'Sales',
                'name' => 'John Salesman',
                'email' => 'john.sales@suits.com',
                'phone' => '+254711111111',
                'is_active' => true,
                'theme_preference' => 'light',
                'email_verified_at' => '2026-01-03 09:00:00',
                'last_login_at' => '2026-03-28 11:40:00',
            ],
            [
                'role' => 'Sales',
                'name' => 'Mary Seller',
                'email' => 'mary.seller@suits.com',
                'phone' => '+254722222222',
                'is_active' => true,
                'theme_preference' => 'light',
                'email_verified_at' => '2026-01-04 09:00:00',
                'last_login_at' => '2026-03-27 16:10:00',
            ],
            [
                'role' => 'Accountant',
                'name' => 'James Accountant',
                'email' => 'james.accountant@suits.com',
                'phone' => '+254733333333',
                'is_active' => true,
                'theme_preference' => 'light',
                'email_verified_at' => '2026-01-05 09:00:00',
                'last_login_at' => '2026-03-29 07:50:00',
            ],
            [
                'role' => 'Accountant',
                'name' => 'Patricia Finance',
                'email' => 'patricia.finance@suits.com',
                'phone' => '+254744444444',
                'is_active' => true,
                'theme_preference' => 'light',
                'email_verified_at' => '2026-01-06 09:00:00',
                'last_login_at' => '2026-03-26 14:20:00',
            ],
            [
                'role' => 'Tailor',
                'name' => 'Robert Tailor',
                'email' => 'robert.tailor@suits.com',
                'phone' => '+254755555555',
                'is_active' => true,
                'theme_preference' => 'light',
                'email_verified_at' => '2026-01-07 09:00:00',
                'last_login_at' => '2026-03-28 17:35:00',
            ],
            [
                'role' => 'Tailor',
                'name' => 'Susan Designer',
                'email' => 'susan.designer@suits.com',
                'phone' => '+254766666666',
                'is_active' => true,
                'theme_preference' => 'light',
                'email_verified_at' => '2026-01-08 09:00:00',
                'last_login_at' => '2026-03-25 13:05:00',
            ],
            [
                'role' => 'Sales',
                'name' => 'Former Employee',
                'email' => 'former.employee@suits.com',
                'phone' => '+254777777777',
                'is_active' => false,
                'theme_preference' => 'light',
                'email_verified_at' => '2026-01-09 09:00:00',
                'last_login_at' => '2026-02-15 12:00:00',
            ],
        ];
    }
}
