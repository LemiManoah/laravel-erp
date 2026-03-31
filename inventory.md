# Inventory Module Plan

This document defines the inventory approach for this ERP after moving away from product-level stock fields.

The goal is to support:

- Supermarkets
- Retail shops
- Farms
- Agro-input stores
- General trading businesses

## Core Direction

We now separate inventory into three layers:

1. `products`
2. `product_prices`
3. `inventory_stocks`

This means:

- Products are the catalog or master item record
- Default prices live outside the product table
- Stock lives outside the product table
- Expiry details live only on stock rows, not on products

## Product Layer

The `products` table should answer:

- What is this item?
- Does it track inventory?
- Can it be sold?
- Can it be purchased?
- Does it expire?
- What unit does it use?

Recommended product fields:

- `product_category_id`
- `sku`
- `barcode`
- `item_type`
- `tracks_inventory`
- `is_sellable`
- `is_purchasable`
- `base_unit_id`
- `reorder_level`
- `reorder_quantity`
- `has_variants`
- `parent_item_id`
- `allow_negative_stock`
- `has_expiry`
- `is_serialized`
- `name`
- `description`
- `is_active`

What should not live on `products` anymore:

- `quantity_on_hand`
- `opening_stock_quantity`
- `opening_stock_date`
- `opening_batch_number`
- `opening_expiry_date`
- `buying_price`
- `selling_price`

## Default Pricing Layer

Use a `product_prices` table for one default price row per product for now.

Suggested fields:

- `product_id`
- `selling_price`
- `buying_price`

Rules:

- One default selling price per product for now
- One default buying price per product for now
- Transaction lines can override the default selling price
- Future price lists can be added later without redesigning products

## Stock Layer

Use an `inventory_stocks` table instead of `inventory_batches`.

This is important because:

- Not every product has batches
- Not every product expires
- We still want one stock row per product and location for normal items

Suggested fields:

- `product_id`
- `location_id`
- `batch_number` nullable
- `expiry_date` nullable
- `received_at` nullable
- `quantity_on_hand`
- `unit_cost` nullable
- `notes` nullable

Rules:

- For non-expiry items:
  - keep one stock row per product per location
  - `batch_number` stays null
  - `expiry_date` stays null
- For expiry items:
  - batch number is required when stock is received
  - expiry date is required when stock is received
  - multiple stock rows may exist for the same product in the same location because each batch is separate

## Inventory Movements

Use `inventory_movements` as the ledger.

Suggested fields:

- `product_id`
- `location_id`
- `inventory_stock_id` nullable
- `movement_type`
- `direction`
- `quantity`
- `unit_id`
- `unit_conversion_rate`
- `balance_after`
- `unit_cost`
- `reference_type`
- `reference_id`
- `movement_date`
- `notes`
- `created_by`

Movement types:

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

## Operational Rules

### Product Creation

When creating a product:

- create the product record
- create the default price record
- do not create stock automatically

### Recording Stock

Stock should be recorded separately in the inventory module.

That means:

- opening stock is entered as an inventory movement
- receipts are entered as inventory movements
- transfers move quantity between stock rows

### Expiry Handling

If `has_expiry = true` on the product:

- stock-in must capture `batch_number`
- stock-in must capture `expiry_date`
- stock-out should issue from a specific stock row
- sales should prefer FEFO

If `has_expiry = false`:

- batch tracking is not required
- use a single stock row per product per location

## Why This Design Is Better

Benefits:

- product setup becomes simpler
- stock is no longer mixed into the product catalog
- expiry logic applies only where needed
- default prices are separated cleanly from stock
- batch-heavy businesses and normal retail businesses can use the same design

Tradeoff:

- queries become slightly more relational
- current stock should be treated as derived from `inventory_stocks` and `inventory_movements`

That tradeoff is worth it for flexibility and correctness.

## Current Build Plan

### Phase 1

- keep products as catalog records only
- add default price records in `product_prices`
- add `inventory_stocks`
- add inventory movement ledger
- add stock locations and units
- add dedicated operator pages for receiving stock and making stock adjustments

Status:

- completed

### Phase 2

- enforce expiry-only stock rows with batch details
- add near-expiry and expired stock monitoring
- use FEFO for sales issue on expiry items
- keep generic ledger entry as an advanced fallback, not the primary operator flow

Status:

- completed for stock monitoring and FEFO-based issue flow
- generic movement entry remains available as the fallback page

### Phase 3

- purchasing and goods receipt
- stock adjustments
- transfers
- returns
- richer reporting by product, location, and stock row

Status:

- completed for receipts, adjustments, transfers, inventory status reporting, and stock card reporting
- completed for suppliers and purchase receipts
- completed for purchase orders
- completed for receipt-against-order flow
- completed for purchase returns
- completed for supplier purchasing reports
- dedicated sales return workflows remain optional if we want a separate operator flow beyond the existing stock movement engine

## What Exists Now

The current inventory module now includes:

- products as catalog records with default pricing
- units of measure
- stock locations
- inventory stocks
- inventory movements ledger
- inventory monitoring dashboard
- receive stock flow
- stock adjustment flow
- transfer flow
- inventory status report
- stock card report
- suppliers
- purchase orders
- purchase receipts
- receipt-against-order flow
- purchase returns
- supplier purchasing report

## Next Recommended Milestone

The current inventory and procurement plan is complete for the intended first release shape.

Recommended next build items if we expand further:

- supplier balances or payable visibility later if finance expands
- dedicated sales returns if we want a separate return screen instead of relying on stock movement tools
- stock counts / stock takes
- reserved stock
- approval flow for adjustments and transfers

## Summary

The chosen direction is:

- `products` for the item master
- `product_prices` for default prices
- `inventory_stocks` for on-hand stock rows
- `inventory_movements` for the ledger

And the key business rule is:

- only expiry-controlled items need batch-style stock rows
