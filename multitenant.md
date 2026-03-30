# Multi-Tenant ERP System Plan

## Overview

This document outlines the strategy to convert the current single-tenant ERP system into a multi-tenant architecture. Multi-tenancy allows multiple organizations (tenants) to use the same application instance while keeping their data isolated from each other.

## Current Architecture Analysis

The current ERP system is single-tenant with the following entities:

- Users (authentication, roles via Spatie)
- Customers, Products, Product Categories
- Orders, Order Items
- Invoices, Invoice Items
- Payments, Payment Methods
- Expenses, Expense Categories
- Receipts, Currencies, Measurements

---

## Tenant Identification Strategies

### Recommended: Subdomain-Based Tenancy

**Approach:** Each tenant accesses the system via a unique subdomain (e.g., `companyA.erpapp.com`, `companyB.erpapp.com`)

**Pros:**

- Clean URL separation
- Easy to implement with Laravel
- Good for SaaS deployment

**Implementation:**

1. Configure DNS wildcard subdomain (\*.yourdomain.com → your server)
2. Extract subdomain in middleware
3. Map subdomain to tenant in database

**Alternative: Path-Based Tenancy**

- URL format: `yourdomain.com/tenantSlug/...`
- Easier for non-wildcard DNS setups

**Alternative: Header-Based Tenancy**

- Use X-Tenant-ID header for API-only implementations

---

## Database Architecture Options

### Option A: Shared Database, Shared Schema (Recommended)

All tenants share the same database with a `tenant_id` column on every data table.

**Pros:**

- Simple migrations
- Easy cross-tenant reporting (if needed)
- Lower infrastructure costs
- Simpler backup/restore

**Cons:**

- Risk of data leakage if not properly scoped
- Requires careful query scoping

**Schema Changes:**

```php
// Example: Add tenant_id to all tables
$table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
```

### Option B: Separate Databases per Tenant

Each tenant gets their own database.

**Pros:**

- Complete data isolation
- Easier compliance (GDPR, etc.)
- Tenant-specific backups

**Cons:**

- Complex migrations (must run on all databases)
- More infrastructure management
- Cross-tenant queries require additional tooling

### Option C: Separate Schemas

Same database, different schemas per tenant. (Less common in Laravel)

---

## Implementation Plan

### Phase 1: Core Infrastructure

#### 1.1 Create Tenant Model and Migration

```php
// database/migrations/xxxx_xx_xx_create_tenants_table.php
Schema::create('tenants', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique(); // URL-friendly identifier
    $table->string('subdomain')->unique()->nullable();
    $table->string('domain')->unique()->nullable();
    $table->string('email')->nullable();
    $table->string('phone')->nullable();
    $table->text('address')->nullable();
    $table->string('logo_path')->nullable();
    $table->string('primary_color')->default('#000000');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### 1.2 Create Tenant Scoping Trait

```php
// app/Traits/ScopesToTenant.php
namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ScopesToTenant
{
    public static function bootScopesToTenant()
    {
        static::creating(function ($model) {
            if (session()->has('tenant_id')) {
                $model->tenant_id = session('tenant_id');
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            $builder->where('tenant_id', session('tenant_id'));
        });
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }
}
```

#### 1.3 Create Tenant Middleware

```php
// app/Http/Middleware/TenantScope.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Tenant;

class TenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = explode('.', $host)[0];

        // For development/local: check tenant from config or session
        if (app()->environment('local')) {
            if ($request->has('tenant_id')) {
                session(['tenant_id' => $request->tenant_id]);
            }
        }

        $tenant = Tenant::where('subdomain', $subdomain)
            ->orWhere('domain', $host)
            ->first();

        if (!$tenant) {
            return response()->json(['error' => 'Tenant not found'], 404);
        }

        if (!$tenant->is_active) {
            return response()->json(['error' => 'Tenant is inactive'], 403);
        }

        session(['tenant_id' => $tenant->id, 'tenant' => $tenant]);

        return $next($request);
    }
}
```

#### 1.4 Update User Model

Add tenant relationship and tenant_id:

```php
// app/Models/User.php
class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, ScopesToTenant;

    protected $fillable = [
        'tenant_id', // Add this
        'name',
        'email',
        // ... rest
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

### Phase 2: Update All Models

#### 2.1 Apply Tenant Scope to All Data Models

Add `ScopesToTenant` trait to every tenant-specific model:

- Customer
- Product, ProductCategory
- Order, OrderItem
- Invoice, InvoiceItem
- Payment, PaymentMethod
- Expense, ExpenseCategory
- Receipt
- Currency
- Measurement

#### 2.2 Global Scope Override

Update base queries to respect tenant context:

```php
// In each model
protected static function boot()
{
    parent::boot();

    static::addGlobalScope('tenant', function (Builder $builder) {
        $tenantId = session('tenant_id');
        if ($tenantId) {
            $builder->where('tenant_id', $tenantId);
        }
    });
}
```

### Phase 3: Tenant Management Features

#### 3.1 Tenant Admin Panel

Create pages to:

- View all tenants
- Create/edit/deactivate tenants
- Set tenant-specific settings (branding, features)
- View tenant-specific reports

#### 3.2 Tenant Settings

Add tenant configuration table:

```php
Schema::create('tenant_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
    $table->string('key');
    $table->text('value')->nullable();
    $table->timestamps();

    $table->unique(['tenant_id', 'key']);
});
```

Settings might include:

- Currency preference
- Date format
- Tax rate
- Invoice number prefix
- Logo/branding

### Phase 4: Authentication & Authorization

#### 4.1 Update Authentication Flow

- Login: Filter users by tenant_id
- Registration: Create user under current tenant
- Password reset: Scope to tenant

#### 4.2 Role/Permission Isolation

Spatie permissions should be tenant-scoped:

- Same permission names but different roles per tenant
- OR use tenant_id on permission tables

### Phase 5: Data Seeding & Tenant Initialization

#### 5.1 Seed Data Per Tenant

Create seeder that runs per tenant:

- Default expense categories
- Default payment methods
- Default measurements
- Default currency

### Phase 6: API Considerations

#### 6.1 API Token Scoping

API tokens should be tied to a tenant:

- Add tenant_id to personal_access_tokens table
- Filter token queries by tenant

#### 6.2 Tenant Context in API

```php
// In API requests
$request->headers->set('X-Tenant-ID', $tenantId);
```

---

## Configuration Changes

### .env additions

```
TENANT_MODE=subdomain  # or: path, header
DEFAULT_DOMAIN=erpapp.com
```

### config/app.php

```php
'tenant' => [
    'mode' => env('TENANT_MODE', 'subdomain'),
    'default_domain' => env('DEFAULT_DOMAIN', 'erpapp.com'),
],
```

---

## Testing Strategy

1. **Unit Tests**: Test trait behavior
2. **Integration Tests**: Test tenant isolation
3. **Feature Tests**: Test tenant-specific flows
4. **Multi-Tenant Tests**: Create tests that verify data doesn't leak between tenants

---

## Migration Steps Summary

1. Create tenants table
2. Add tenant_id FK to users table
3. Create tenant_settings table
4. Create ScopesToTenant trait
5. Create TenantScope middleware
6. Add tenant_id to all data tables
7. Apply trait to all models
8. Update authentication flow
9. Create tenant management UI
10. Run migrations with tenant_id for existing data

---

## Packages to Consider

- **spatie/laravel-multitenancy** - Popular package handling many edge cases
- **tymon/jwt-auth** - For API authentication with tenant scoping

---

## Key Considerations

1. **Super Admin**: Create a way to bypass tenant scoping for super admins
2. **Data Migration**: Existing data needs tenant_id populated (use a "master" tenant)
3. **Queues**: Tenant context must be carried to queued jobs
4. **File Storage**: Tenant-specific file paths (e.g., `tenants/{id}/uploads/`)
5. **Email**: Tenant-specific email settings/from addresses
6. **Logging**: Include tenant_id in log context
