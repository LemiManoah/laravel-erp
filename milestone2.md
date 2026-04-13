# Milestone 2 Plan

This file tracks Milestone 2 implementation work for the SaaS platform layer.

Milestone 2 goal from [report.md](./report.md):

- build the central platform and tenant-management layer
- allow support-led administration of tenants
- move tenant provisioning away from seed-only operation

## Scope

Milestone 2 focuses on:

- support-user platform access
- support tenant-management dashboard
- tenant directory and operational visibility
- tenant onboarding and setup
- tenant settings management from the platform side
- tenant suspension and reactivation
- tenant maintenance operations

## Sub-Milestones

- [x] M2.1 Create a separate Milestone 2 execution plan and progress tracker.
- [x] M2.2 Add support-user-only access to the new tenant-management module.
- [x] M2.3 Build the initial support tenant-management dashboard and tenant directory.
- [x] M2.4 Add tenant creation flow with domain assignment.
- [x] M2.5 Add central tenant edit/settings management.
- [x] M2.6 Add tenant suspension and reactivation controls.
- [x] M2.7 Add tenant maintenance actions for platform operations.
- [x] M2.8 Add platform-side tests for the support tenant-management module.

## What M2.2 And M2.3 Include

Implemented in this first slice:

- dedicated support login flow on the central app
- support-flag and permission-based middleware for the tenant-management area
- central dashboard route group on central domains only
- initial dashboard stats for tenants and domains
- tenant directory page for operational visibility
- seeded support user with `is_support` plus `platform.tenants.manage`

## Next Recommended Build Order

1. Run and fix the local platform test suite

## What M2.4 Adds

Implemented in this slice:

- support-side tenant creation form
- first-domain assignment during tenant onboarding
- tenant bootstrap seeding for core setup data
- first tenant admin account creation with the `Admin` role

## What M2.5 Adds

Implemented in this slice:

- support-side tenant edit screen
- tenant profile updates for name, slug, contact details, and active status
- primary domain update flow with uniqueness protection

## What M2.6 Adds

Implemented in this slice:

- suspend and reactivate actions in the tenant directory
- suspend and reactivate controls on the tenant edit screen
- tenant activity middleware that blocks suspended-tenant app access for normal users
- support-route bypass for support users so they can still reactivate suspended tenants

## What M2.7 Adds

Implemented in this slice:

- support-side maintenance actions on the tenant edit screen
- core setup repair by re-running the tenant bootstrap seeder
- demo baseline refresh by re-running the tenant app seeder

## What M2.8 Adds

Implemented in this slice:

- support-console feature coverage for access, create, edit, suspend, reactivate, and maintenance flows
- suspended-tenant access checks for normal users versus support users

## Better Later

The current starting point is intentionally simple:

- support users live in the main `users` table
- support access is controlled by `is_support` plus `platform.tenants.manage`
- tenant management is accessed on the current tenant domain under `/admin`

Once the workflow is stable, a better next step would be:

- a dedicated `Support` role instead of direct permission assignment
- audited tenant switching or impersonation instead of broad silent access
- a dedicated support domain instead of sharing the tenant app host

## Seeded Support User

Current default seeded support credentials:

- email: `support@localhost`
- password: `password`

This account is intended for local development only and should be replaced or environment-driven before any production use.
