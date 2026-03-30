# Inventory Module Plan

This document defines the inventory approach we should adapt into this ERP.

The goal is to support a multipurpose ERP that can work for:

- Supermarkets
- Retail shops
- Farms
- Agro-input stores
- General trading businesses

Instead of copying the old system literally, we will keep the useful ideas and reshape them into a more general inventory architecture.

---

## Table of Contents

1. [Goals](#goals)
2. [Core Principles](#core-principles)
3. [Item Types](#item-types)
4. [Core Data We Should Track](#core-data-we-should-track)
5. [Recommended Inventory Fields](#recommended-inventory-fields)
6. [Batch and Expiry Tracking](#batch-and-expiry-tracking)
7. [Units, Variants, and Pack Sizes](#units-variants-and-pack-sizes)
8. [Stock Locations](#stock-locations)
9. [Inventory Transactions](#inventory-transactions)
10. [Inventory Flow](#inventory-flow)
11. [Validation Rules](#validation-rules)
12. [Reports and Dashboards](#reports-and-dashboards)
13. [Useful Extras to Include Early](#useful-extras-to-include-early)
14. [How This Fits the Current ERP](#how-this-fits-the-current-erp)
15. [Implementation Phases](#implementation-phases)

---

## Goals

The inventory module should:

- Track stock for physical items
- Ignore stock for services and non-stock items
- Support opening stock
- Support purchases and sales
- Support stock adjustments and transfers
- Support returns
- Support batch and expiry tracking
- Support multiple locations
- Support unit conversions and pack sizes
- Keep a clear stock history for reporting and audit

---

## Core Principles

### 1. Use a Stock Ledger

We should not rely only on a `stock_count` field being incremented and decremented directly.

The primary source of truth should be an inventory ledger or movement table, for example:

- `inventory_movements`

Each stock-affecting action should create a movement entry.

Examples:

- Purchase receipt -> stock in
- Sales issue -> stock out
- Stock adjustment gain -> stock in
- Stock adjustment loss -> stock out
- Transfer out -> stock out
- Transfer in -> stock in
- Sales return -> stock in
- Purchase return -> stock out
- Harvest -> stock in
- Internal consumption -> stock out

### 2. Keep Current Stock Easy to Read

For performance, we may still keep a cached quantity on the item or item-location record, but it should be derived from movements and not treated as the only truth.

### 3. Make Inventory Tracking Optional

Not every item in the ERP should affect stock.

Examples:

- Tailoring service -> no stock
- Consulting service -> no stock
- Product on shelf -> stock tracked
- Fertilizer -> stock tracked
- Animal feed -> stock tracked

### 4. Design for More Than One Industry

The structure should be generic enough to support:

- Supermarket stock
- Retail stock
- Farm inputs
- Farm harvest outputs
- Packaged goods
- Bulk goods
- Expiring goods

---

## Item Types

Each item should belong to one of these broad types:

- `service`
- `stock_item`
- `non_stock_item`
- `raw_material`
- `finished_good`
- `consumable`

At minimum, the system must clearly distinguish whether an item affects inventory.

Recommended booleans:

- `tracks_inventory`
- `is_sellable`
- `is_purchasable`
- `is_active`

Optional but useful:

- `allow_negative_stock`
- `is_serialized`

---

## Core Data We Should Track

For a reusable inventory module, we should track:

- Item identity
- Item category
- Unit of measure
- Stock tracking flag
- Opening stock
- Reorder threshold
- Reorder quantity
- Batch requirement
- Expiry requirement
- Stock location
- Stock movement history
- Supplier linkage
- Sales linkage
- Purchase linkage

---

## Recommended Inventory Fields

These fields can live on the `products` table or a future `items` table.

| Field | Type | Purpose |
| --- | --- | --- |
| `sku` | string | Internal stock code |
| `barcode` | string nullable | POS and scanner support |
| `item_type` | string | Service, stock item, raw material, finished good, etc. |
| `tracks_inventory` | boolean | Whether stock should be tracked |
| `is_sellable` | boolean | Whether item can be sold |
| `is_purchasable` | boolean | Whether item can be bought |
| `base_unit_id` | foreign key nullable | Primary inventory unit |
| `reorder_level` | decimal nullable | Low-stock warning threshold |
| `reorder_quantity` | decimal nullable | Suggested quantity to restock |
| `opening_stock_quantity` | decimal nullable | Starting stock |
| `opening_stock_date` | datetime nullable | When stock tracking begins |
| `has_variants` | boolean | Whether item has variants or pack sizes |
| `parent_item_id` | foreign key nullable | Parent item for variants |
| `allow_negative_stock` | boolean | Whether stock can go below zero |
| `has_expiry` | boolean | Whether the item expires |
| `requires_batch_tracking` | boolean | Whether batch numbers are mandatory |
| `is_serialized` | boolean | Whether unique serial tracking is needed |

Recommended notes:

- If `tracks_inventory = false`, stock-related fields can be null.
- If `has_expiry = true`, batch tracking should also be enforced.
- If `is_serialized = true`, serial numbers should be handled separately from simple quantity tracking.

---

## Batch and Expiry Tracking

Batch tracking is important for:

- Supermarket goods
- Medicines
- Agro-inputs
- Seeds
- Animal drugs
- Food products
- Perishable farm produce

### Key Rules

- Add a boolean field: `has_expiry`
- If `has_expiry = true`, then:
  - `requires_batch_tracking = true`
  - `batch_number` becomes compulsory on stock-in transactions
  - `expiry_date` becomes compulsory on stock-in transactions
- Stock should be traceable by batch
- Reports should show near-expiry and expired stock
- Sales should prefer FEFO where appropriate

FEFO means:

- First Expiry, First Out

This is usually better than plain FIFO for expiring goods.

### Suggested Batch Table

We should introduce a table such as:

- `inventory_batches`

Suggested fields:

| Field | Type | Purpose |
| --- | --- | --- |
| `id` | id | Primary key |
| `tenant_id` | string | Tenant scope |
| `product_id` | foreign key | Item being tracked |
| `location_id` | foreign key nullable | Where the batch is stored |
| `batch_number` | string | Supplier or internal batch reference |
| `expiry_date` | date nullable | Required if item expires |
| `manufactured_at` | date nullable | Useful for production and compliance |
| `received_at` | date nullable | When batch entered stock |
| `quantity_on_hand` | decimal | Current quantity for the batch |
| `cost_price` | decimal nullable | Batch cost if needed |
| `status` | string | Active, depleted, expired, quarantined |

### Batch-Level Rules

- Stock movements for expiring items should reference a batch
- Batch quantity should never go below zero unless negative stock is allowed
- Expired batches should be blocked from sale unless explicitly overridden
- Near-expiry items should be highlighted on the dashboard
- Batch-based adjustments should be supported for damaged or expired stock

---

## Units, Variants, and Pack Sizes

The old system used base products and sub-products with unit consumption. That idea is still useful, but we should generalize it.

### Units of Measure

We should support units like:

- pcs
- box
- carton
- kg
- g
- litre
- ml
- tray
- dozen
- bag

### Conversions

We should support conversion rules such as:

- 1 carton = 24 pcs
- 1 tray = 30 eggs
- 1 bag = 50 kg
- 1 bottle = 500 ml

This should live in a proper conversion structure rather than only one field on the item.

Suggested table:

- `unit_conversions`

### Variants and Pack Sizes

Useful examples:

- Sugar 1kg
- Sugar 500g
- Soda 300ml
- Soda 500ml
- Seed pack 1kg
- Seed pack 5kg

These can be modeled as:

- Parent item plus variants
- Or parent item plus packaging definitions

This is more flexible than the old base-product-only design.

---

## Stock Locations

A multipurpose ERP should support storing stock in different places.

Suggested table:

- `stock_locations`

Examples:

- Main store
- Branch store
- Warehouse
- Cold room
- Pharmacy shelf
- Farm store
- Field store

Useful fields:

- `name`
- `code`
- `location_type`
- `is_default`
- `is_active`

Location support is important for:

- Transfers between stores
- Per-location stock reports
- Damage or shrinkage tracking
- Branch-based selling

---

## Inventory Transactions

We should use a ledger table such as:

- `inventory_movements`

Suggested fields:

| Field | Type | Purpose |
| --- | --- | --- |
| `id` | id | Primary key |
| `tenant_id` | string | Tenant scope |
| `product_id` | foreign key | Item moved |
| `location_id` | foreign key nullable | Stock location |
| `batch_id` | foreign key nullable | Batch reference |
| `movement_type` | string | Purchase, sale, adjustment, transfer, return, harvest, consumption |
| `direction` | string | in or out |
| `quantity` | decimal | Movement quantity |
| `unit_id` | foreign key nullable | Unit used |
| `unit_conversion_rate` | decimal nullable | Conversion to base unit |
| `balance_after` | decimal nullable | Snapshot after movement |
| `unit_cost` | decimal nullable | Stock valuation support |
| `reference_type` | string nullable | Purchase, invoice, transfer, adjustment, etc. |
| `reference_id` | unsigned big integer nullable | Related record id |
| `movement_date` | datetime | Effective movement time |
| `notes` | text nullable | Reason or comments |
| `created_by` | foreign key nullable | User responsible |

### Movement Types We Should Support

- `opening_stock`
- `purchase_receipt`
- `sale_issue`
- `sales_return`
- `purchase_return`
- `adjustment_gain`
- `adjustment_loss`
- `transfer_out`
- `transfer_in`
- `damage`
- `wastage`
- `harvest`
- `production_output`
- `internal_consumption`

---

## Inventory Flow

### Stock In

Stock should increase from:

- Opening stock
- Purchase receipts
- Sales returns
- Positive adjustments
- Transfer in
- Harvest
- Production output

### Stock Out

Stock should reduce from:

- Sales
- Purchase returns
- Negative adjustments
- Transfer out
- Damage
- Wastage
- Internal consumption

### Important Rule

Inventory should move when the business event is confirmed, not merely drafted.

Examples:

- Draft invoice should not reduce stock
- Confirmed or posted sale should reduce stock
- Draft purchase should not increase stock
- Goods received should increase stock

This will keep stock accurate and prevent false balances.

---

## Validation Rules

These rules should be enforced at the form and business-logic levels.

### Item Rules

- If `tracks_inventory = true`, `base_unit_id` is required
- If `tracks_inventory = true`, `opening_stock_quantity` may be provided
- If `tracks_inventory = false`, inventory fields can be null
- If `has_variants = true`, the parent-child structure must be valid
- If `has_expiry = true`, `requires_batch_tracking` must be true

### Transaction Rules

- If item tracks inventory, quantity must be greater than zero
- If item has expiry, `batch_number` is required on stock-in
- If item has expiry, `expiry_date` is required on stock-in
- If item requires batch tracking, stock-out should reference a valid available batch
- If negative stock is not allowed, stock-out must fail when insufficient stock exists
- If item is serialized, serial numbers must be captured per unit movement

### Sales Rules

- Services should not create inventory movements
- Non-stock items should not reduce stock
- Stock items should reduce stock only after confirmation or posting
- Expired batches should not be sold without an override policy

---

## Reports and Dashboards

The inventory module should support:

- Current stock by item
- Current stock by location
- Current stock by batch
- Low-stock report
- Near-expiry report
- Expired stock report
- Stock movement history
- Stock card per item
- Batch history
- Inventory valuation
- Slow-moving items
- Fast-moving items
- Negative stock exceptions

### Suggested Dashboard Alerts

- Low stock
- Out of stock
- Near expiry
- Expired stock still on hand
- Negative stock detected
- Batch without expiry where expiry is required

---

## Useful Extras to Include Early

These are not all mandatory on day one, but they are worth planning for now.

### 1. SKU and Barcode Support

Important for:

- POS
- Supermarket scanning
- Faster stock counts

### 2. Supplier Support

Useful tables:

- `suppliers`
- `purchase_orders`
- `goods_receipts`

This allows stock to enter through proper purchasing flows.

### 3. Stock Adjustments

We should support:

- Physical count corrections
- Damage write-offs
- Expiry write-offs
- Theft or shrinkage

### 4. Stock Transfers

Important for multi-branch and multi-store setups.

### 5. Returns

We should support:

- Sales returns
- Purchase returns

### 6. Cycle Counts and Stock Takes

Useful for supermarkets and busy stores.

This can later support:

- Periodic counts
- Variance reports
- Approval of count adjustments

### 7. Valuation Support

Useful fields and logic for:

- Average cost
- FIFO
- Batch cost

This is important for reports and gross profit accuracy.

### 8. Serial Number Tracking

Useful for:

- Electronics
- Equipment
- Pumps
- Tools

This should be optional and only used where needed.

### 9. Reserved Stock

Very useful if the ERP later supports quotations, sales orders, or allocations.

Examples:

- `quantity_on_hand`
- `quantity_reserved`
- `quantity_available`

### 10. Approval and Audit Trail

High-risk inventory actions should be reviewable:

- Adjustments
- Backdated transactions
- Batch overrides
- Expiry overrides
- Negative stock overrides

This ties in well with the existing Spatie activity logging.

---

## How This Fits the Current ERP

The current ERP already has a `products` table, so that is the natural starting point.

However:

- The current product model is still a simple catalog
- The current invoice items are not yet linked to products
- The current order flow still contains tailoring-specific fields

Because of that, inventory should not be built around the tailoring order structure.

Instead, we should:

1. Extend products into proper stock-capable items
2. Add units, locations, movements, and batches
3. Link stock-affecting sale lines to products
4. Post inventory only when transactions are confirmed

---

## Implementation Phases

### Phase 1: Core Inventory Foundation

- Extend products with stock-related fields
- Add units of measure
- Add stock locations
- Add opening stock support
- Add inventory movements ledger
- Add basic current stock reports

### Phase 2: Expiry and Batch Control

- Add `has_expiry`
- Add `requires_batch_tracking`
- Add inventory batches
- Enforce compulsory batch number and expiry date where required
- Add near-expiry and expired stock reports

### Phase 3: Transaction Flows

- Add suppliers
- Add purchases and goods receipt
- Link inventory-aware sales lines to products
- Add stock adjustments
- Add stock transfers
- Add returns

### Phase 4: Advanced Flexibility

- Add variants and pack sizes
- Add conversion rules
- Add valuation support
- Add stock takes and cycle counts
- Add reserved stock
- Add serial tracking where needed

---

## Summary

The new inventory module should keep the good parts from the old system:

- Opening stock
- Stock alerts
- Unit handling
- Inventory reports

But it should improve the architecture by adding:

- Ledger-based stock tracking
- Stock locations
- Batch tracking
- Expiry enforcement
- Generic movement types
- Support for multiple industries

Most importantly:

- If an item expires, batch number and expiry date must be compulsory
- Inventory should move only on confirmed stock events
- The design should work for supermarkets, farms, shops, and similar businesses without being tied to one workflow
