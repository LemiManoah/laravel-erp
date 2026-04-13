<?php

declare(strict_types=1);

use App\Models\Currency;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function (): void {
    $this->supportUser = User::factory()->create([
        'is_support' => true,
    ]);

    grantTestPermissions($this->supportUser, ['platform.tenants.manage']);

    $this->actingAs($this->supportUser);
});

it('allows a support user to view the support dashboard and tenant directory', function (): void {
    $this->get('/admin')->assertOk();
    $this->get('/admin/tenants')->assertOk();
});

it('creates a tenant with its first domain and first admin account from the support console', function (): void {
    $response = $this->post('/admin/tenants', [
        'name' => 'Northwind Demo',
        'slug' => 'northwind-demo',
        'email' => 'hello@northwind.localhost',
        'phone' => '+256701234567',
        'primary_domain' => 'northwind.localhost',
        'is_active' => '1',
        'admin_name' => 'Northwind Admin',
        'admin_email' => 'admin@northwind.localhost',
        'admin_phone' => '+256709999999',
        'admin_password' => 'password123',
        'admin_password_confirmation' => 'password123',
    ]);

    $response->assertRedirect(route('support.tenants.index'));

    $tenant = Tenant::query()->where('slug', 'northwind-demo')->firstOrFail();

    expect($tenant->name)->toBe('Northwind Demo');
    expect($tenant->domains()->where('domain', 'northwind.localhost')->exists())->toBeTrue();

    tenancy()->end();
    tenancy()->initialize($tenant);

    try {
        $admin = User::query()->where('email', 'admin@northwind.localhost')->first();

        expect($admin)->not->toBeNull();
        expect($admin?->hasRole('Admin'))->toBeTrue();
    } finally {
        tenancy()->end();
        tenancy()->initialize($this->tenant);
    }
});

it('updates tenant settings and the primary domain from the support console', function (): void {
    $managedTenant = Tenant::create([
        'name' => 'Managed Tenant',
        'slug' => 'managed-tenant',
        'email' => 'hello@managed.localhost',
        'phone' => '+256700000123',
        'is_active' => true,
    ]);
    $managedTenant->domains()->create(['domain' => 'managed.localhost']);

    $response = $this->put(route('support.tenants.update', $managedTenant), [
        'name' => 'Managed Tenant Updated',
        'slug' => 'managed-tenant-updated',
        'email' => 'support@managed.localhost',
        'phone' => '+256700000321',
        'primary_domain' => 'managed-updated.localhost',
        'is_active' => '1',
    ]);

    $response->assertRedirect(route('support.tenants.index'));

    $managedTenant->refresh();

    expect($managedTenant->name)->toBe('Managed Tenant Updated');
    expect($managedTenant->slug)->toBe('managed-tenant-updated');
    expect($managedTenant->email)->toBe('support@managed.localhost');
    expect($managedTenant->domains()->where('domain', 'managed-updated.localhost')->exists())->toBeTrue();
});

it('suspends and reactivates tenants from the support console', function (): void {
    $response = $this->post(route('support.tenants.suspend', $this->tenant));
    $response->assertRedirect(route('support.tenants.index'));

    expect($this->tenant->fresh()->is_active)->toBeFalse();

    $response = $this->post(route('support.tenants.reactivate', $this->tenant));
    $response->assertRedirect(route('support.tenants.index'));

    expect($this->tenant->fresh()->is_active)->toBeTrue();
});

it('blocks normal tenant usage when suspended but still allows support-console access', function (): void {
    $this->tenant->forceFill(['is_active' => false])->save();

    $normalUser = User::factory()->create([
        'is_support' => false,
    ]);

    $this->actingAs($normalUser);
    $this->get('/dashboard')->assertForbidden();

    $this->actingAs($this->supportUser);
    $this->get('/admin')->assertOk();
});

it('re-runs core setup maintenance for a tenant', function (): void {
    Currency::query()->delete();

    expect(Currency::query()->count())->toBe(0);

    $response = $this->post(route('support.tenants.maintenance.bootstrap', $this->tenant));

    $response->assertRedirect(route('support.tenants.edit', $this->tenant));
    expect(Currency::query()->count())->toBeGreaterThan(0);
});

it('refreshes the demo baseline for a tenant', function (): void {
    $response = $this->post(route('support.tenants.maintenance.demo-refresh', $this->tenant));

    $response->assertRedirect(route('support.tenants.edit', $this->tenant));
    expect(User::query()->where('email', 'admin@suits.com')->exists())->toBeTrue();
});
