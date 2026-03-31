<?php

declare(strict_types=1);

namespace App\Enums;

enum InventoryMovementType: string
{
    case OpeningStock = 'opening_stock';
    case PurchaseReceipt = 'purchase_receipt';
    case SaleIssue = 'sale_issue';
    case SalesReturn = 'sales_return';
    case PurchaseReturn = 'purchase_return';
    case AdjustmentGain = 'adjustment_gain';
    case AdjustmentLoss = 'adjustment_loss';
    case TransferOut = 'transfer_out';
    case TransferIn = 'transfer_in';
    case Damage = 'damage';
    case Wastage = 'wastage';
    case Harvest = 'harvest';
    case ProductionOutput = 'production_output';
    case InternalConsumption = 'internal_consumption';

    public function label(): string
    {
        return match ($this) {
            self::OpeningStock => 'Opening Stock',
            self::PurchaseReceipt => 'Purchase Receipt',
            self::SaleIssue => 'Sale Issue',
            self::SalesReturn => 'Sales Return',
            self::PurchaseReturn => 'Purchase Return',
            self::AdjustmentGain => 'Adjustment Gain',
            self::AdjustmentLoss => 'Adjustment Loss',
            self::TransferOut => 'Transfer Out',
            self::TransferIn => 'Transfer In',
            self::Damage => 'Damage',
            self::Wastage => 'Wastage',
            self::Harvest => 'Harvest',
            self::ProductionOutput => 'Production Output',
            self::InternalConsumption => 'Internal Consumption',
        };
    }

    public function direction(): InventoryDirection
    {
        return match ($this) {
            self::OpeningStock,
            self::PurchaseReceipt,
            self::SalesReturn,
            self::AdjustmentGain,
            self::TransferIn,
            self::Harvest,
            self::ProductionOutput => InventoryDirection::In,
            self::SaleIssue,
            self::PurchaseReturn,
            self::AdjustmentLoss,
            self::TransferOut,
            self::Damage,
            self::Wastage,
            self::InternalConsumption => InventoryDirection::Out,
        };
    }

    public function isTransfer(): bool
    {
        return in_array($this, [self::TransferOut, self::TransferIn], true);
    }
}
