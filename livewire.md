# Livewire Migration Plan

This document outlines how we can convert this ERP from controller-driven Blade pages to Livewire while preserving the current styles, layout, and overall user experience.

The goal is not to redesign the application.

The goal is to keep the existing UI system and make Livewire power the interaction layer underneath it.

---

## Objectives

- Adopt Livewire for interactive pages and forms
- Keep the current visual design intact
- Reuse the existing Blade layout and UI components
- Avoid a risky all-at-once rewrite
- Migrate module by module with clear checkpoints
- Keep tenant behavior, permissions, and activity logging working throughout

---

## Current State

The application is already well-positioned for a Livewire migration because:

- Most pages already render inside the shared app layout
- Styling is already componentized through Blade components and Tailwind classes
- Navigation, header, sidebar, buttons, forms, and modals are already reusable
- The app uses standard resource controllers and route groups, which makes page-by-page replacement straightforward

Important constraints from the current app:

- Multi-tenancy must continue to work on tenant routes
- Authorization and permissions must continue to work on every page
- Spatie activity logging must continue to capture meaningful changes
- The existing look and layout must remain the same
- Print views should generally remain regular Blade pages unless there is a strong reason to make them reactive

---

## Migration Principles

### 1. Preserve the Layout System

Livewire components should render inside the same shared layout already used by the application.

That means we should keep using:

- `resources/views/components/layouts/app.blade.php`
- the existing header and sidebar partials
- the existing form, button, modal, and table styling patterns

Livewire should fit the current UI, not replace it.

### 2. Migrate Page by Page

We should not rewrite everything in one pass.

Instead:

- convert one module at a time
- establish patterns
- reuse those patterns for the next modules

### 3. Start With High-Value Pages

The first Livewire targets should be pages where it brings clear benefit:

- searchable indexes
- dynamic create/edit forms
- pages with inline actions
- pages with filters, totals, or dependent fields

### 4. Keep Business Logic Out of the Components

Livewire components should coordinate the UI, validation, and user actions.

Core business logic should remain in:

- actions
- services
- models
- policies

This prevents the components from becoming too large or too fragile.

### 5. Maintain Feature Parity During Migration

We should avoid changing workflows while converting them unless the change is intentional.

For each page, the first goal is:

- same route behavior
- same permissions
- same data
- same styling
- same user-facing outcome

---

## Recommended Livewire Structure

Suggested component organization:

- `app/Livewire/Dashboard`
- `app/Livewire/Customers`
- `app/Livewire/Orders`
- `app/Livewire/Invoices`
- `app/Livewire/Payments`
- `app/Livewire/Products`
- `app/Livewire/Expenses`
- `app/Livewire/Reports`
- `app/Livewire/Settings`
- `app/Livewire/Inventory`

Suggested component naming pattern:

- `IndexPage`
- `CreatePage`
- `EditPage`
- `ShowPage`
- `Form`
- `Table`
- `Filters`
- `Modal`

Examples:

- `App\Livewire\Invoices\IndexPage`
- `App\Livewire\Invoices\CreatePage`
- `App\Livewire\Payments\IndexPage`
- `App\Livewire\Inventory\StockDashboardPage`

This keeps each module predictable and easy to navigate.

---

## What Should Stay Blade-Only

Some views should remain standard Blade for now:

- print pages
- PDF-oriented views
- simple authentication screens if there is no UX value in making them reactive
- static report print layouts

Possible examples from the current app:

- receipt print pages
- invoice print pages
- report print layouts

---

## Milestones

## Milestone 1: Livewire Foundation

### Goal

Install and wire Livewire into the app without altering the current look.

### Tasks

- Install Livewire
- Publish Livewire assets and configuration if needed
- Confirm Livewire scripts and styles are properly loaded through the shared layout
- Ensure Livewire pages can render inside the current app layout
- Define base conventions for component folders, naming, and route usage
- Confirm Livewire works correctly inside tenant routes
- Confirm Alpine interactions already used in the layout continue to work

### Deliverables

- Livewire installed and bootstrapped
- Shared layout ready for Livewire pages
- One simple test component rendered successfully in the app shell
- A documented component structure we will follow across the project

### Success Criteria

- A Livewire page can render within the existing sidebar/header layout
- Styling remains unchanged
- Tenant identification still works
- Existing navigation still behaves correctly

---

## Milestone 2: Shared UI Patterns for Livewire

### Goal

Create reusable patterns so we do not rebuild forms and tables differently in every module.

### Tasks

- Define a standard page shell for Livewire index, create, edit, and show screens
- Reuse current Blade components for buttons, inputs, selects, alerts, cards, tables, and modals
- Create a standard Livewire search/filter/pagination pattern
- Create a standard flash message and validation error pattern
- Define how permission checks will be applied in Livewire pages
- Define how activity logging hooks into mutations triggered from components

### Deliverables

- A standard index-page pattern
- A standard form-page pattern
- A standard modal action pattern
- A standard search + pagination pattern

### Success Criteria

- New Livewire pages can be built using repeatable structure
- The UI remains visually consistent with existing Blade pages
- We reduce duplication before module-by-module migration starts

---

## Milestone 3: Pilot Module Conversion

### Goal

Convert one contained module end-to-end to validate the migration approach.

### Recommended Pilot

Products or Customers.

Why:

- lower workflow risk than invoices
- common CRUD patterns
- useful as a template for the rest of the app

### Tasks

- Convert index page to Livewire
- Convert create page to Livewire
- Convert edit page to Livewire
- Keep route names and permission behavior aligned
- Reuse the existing form styling and page layout
- Keep validation and save logic clean

### Deliverables

- One module fully working in Livewire
- Established patterns for list, form, validation, and redirects/notifications

### Success Criteria

- Users can view, create, and edit records without visual regression
- Search and pagination still work
- Activity logging still records changes
- Permissions still protect access correctly

---

## Milestone 4: Index Pages and Searchable Screens

### Goal

Convert the app's list-heavy pages where Livewire gives immediate UX value.

### Priority Pages

- products index
- customers index
- payments index
- activity logs index
- expenses index
- users index
- roles index
- currencies index
- payment methods index

### Tasks

- Add debounced search where helpful
- Add Livewire pagination
- Preserve filters and sorting
- Preserve current table styling
- Preserve action buttons and route affordances

### Deliverables

- Searchable and paginated Livewire index pages

### Success Criteria

- Page reloads are reduced for filtering and search
- Visual layout remains unchanged
- Query string behavior is clear and stable where needed

---

## Milestone 5: Core Transaction Forms

### Goal

Convert the dynamic business forms where Livewire adds the most value.

### Priority Pages

- order create/edit
- invoice create/edit
- payment recording workflows
- expense create/edit

### Why This Matters

These pages contain the most interaction:

- repeating line items
- totals
- dependent dropdowns
- live validation
- modal actions

### Tasks

- Convert line-item forms to Livewire state
- Keep calculation logic consistent with current actions/services
- Preserve currency-aware formatting
- Preserve validations
- Maintain issue/cancel/void restrictions and confirmations
- Ensure state resets correctly after save or cancel

### Deliverables

- Dynamic transaction forms powered by Livewire

### Success Criteria

- Totals update smoothly
- Validation errors display inline without breaking layout
- Existing business rules remain intact
- No regression in payment, invoice, or order workflows

---

## Milestone 6: Reports and Read-Heavy Screens

### Goal

Convert interactive report pages where filtering benefits from Livewire, while keeping print outputs Blade-based.

### Priority Pages

- sales report
- payments report
- expenses report
- outstanding balances
- customer statement
- profit and loss filters

### Tasks

- Make report filters reactive
- Keep printable views on standard routes
- Maintain existing report table layout
- Preserve totals and currency formatting

### Deliverables

- Interactive Livewire report filters
- Existing print routes preserved

### Success Criteria

- Report filtering becomes faster and more fluid
- Print layout remains reliable

---

## Milestone 7: Inventory Module in Livewire

### Goal

Build the upcoming inventory module with Livewire from the start instead of creating a controller-first version and migrating later.

### Why This Is a Good Fit

Inventory will benefit heavily from:

- search
- filters
- batch handling
- expiry warnings
- line-based stock transactions
- real-time totals and balance previews

### Candidate Inventory Components

- item index
- item form
- stock dashboard
- batch dashboard
- opening stock form
- goods receipt page
- stock adjustment page
- stock transfer page
- expiry alerts page
- stock card page

### Success Criteria

- Inventory launches directly with the reactive patterns established earlier
- Expiry and batch workflows feel natural and fast
- Per-location and per-batch stock views are easy to use

---

## Milestone 8: Settings, Dashboards, and Polish

### Goal

Migrate lower-priority interactive screens and smooth out UX patterns across the app.

### Candidate Areas

- dashboard widgets
- settings pages
- profile update flows
- password update flows
- lightweight admin tools

### Tasks

- Standardize loading states
- Standardize empty states
- Standardize inline validation
- Standardize confirmation dialogs
- Review accessibility and keyboard behavior

### Deliverables

- More consistent Livewire UX across the application

### Success Criteria

- The app feels cohesive rather than half-migrated
- Repeated patterns are shared and maintainable

---

## Cross-Cutting Concerns

These must be checked throughout every milestone.

### Multi-Tenancy

- All queries must remain tenant-scoped
- Livewire routes and requests must initialize tenancy correctly
- No component should accidentally leak central or cross-tenant data

### Authorization

- Components must respect the same permissions and policies as controllers
- Unauthorized actions must fail cleanly

### Activity Logging

- Livewire-triggered create, update, void, cancel, and delete actions must still produce meaningful Spatie activity logs

### Validation

- Validation rules should remain centralized where possible
- Livewire validation should stay aligned with existing request rules and business rules

### Performance

- Paginated lists should remain efficient
- Heavy report queries should be optimized before becoming reactive
- Avoid unnecessary re-rendering on large forms

### Testing

- Feature tests should cover migrated pages
- Component tests should cover Livewire interactions
- Tenant-aware behavior should be explicitly tested

---

## Route Strategy

We can migrate routes in one of two ways.

### Option A: Replace Controller Endpoints Gradually

Use Livewire components directly in routes as each page is migrated.

Good for:

- index pages
- create/edit pages
- self-contained modules

### Option B: Hybrid Blade Host Pages

Keep the route pointing to a Blade view that mounts a Livewire component inside the existing page.

Good for:

- more complex migrations
- pages where we want less route churn during the transition

### Recommendation

Start with the hybrid approach where it reduces risk, then move to direct component routing once the patterns are stable.

---

## Suggested Order of Work

Recommended sequence:

1. Install and wire Livewire
2. Create shared Livewire UI patterns
3. Convert Products or Customers as the pilot module
4. Convert searchable index pages
5. Convert transaction-heavy forms
6. Convert interactive report filters
7. Build inventory directly in Livewire
8. Finish polish and remaining low-risk pages

---

## Risks and How We Manage Them

### Risk: Visual Regression

Mitigation:

- keep current Blade layout
- reuse current components
- avoid redesign while migrating

### Risk: Business Logic Drifting Into Components

Mitigation:

- keep actions/services for domain logic
- keep components focused on UI state and orchestration

### Risk: Tenant or Permission Bugs

Mitigation:

- test migrated pages under tenant routes
- verify permission gates on each component action

### Risk: Large Dynamic Forms Becoming Hard to Maintain

Mitigation:

- split into smaller nested components where helpful
- keep totals and state handling explicit

### Risk: Mixed Architecture Becoming Messy

Mitigation:

- define conventions early
- migrate in clear module batches
- remove obsolete controller/view patterns after each module stabilizes

---

## Definition of Done Per Migrated Module

A module should only be considered complete when:

- the page uses Livewire successfully
- the layout and styling match the current app
- permissions still work
- tenancy still works
- activity logging still works
- validation still works
- pagination/search/filter behavior is correct
- tests exist or are updated
- dead controller/view code for that page is cleaned up when safe

---

## Immediate Next Step

Once Livewire is installed, the best first implementation step is:

1. wire Livewire into the shared app layout
2. create one small proof-of-concept component inside the current shell
3. convert the `products` module or `customers` module as the pilot

That will give us the reusable pattern for the rest of the ERP and for the new inventory module.
