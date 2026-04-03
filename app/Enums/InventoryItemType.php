<?php

declare(strict_types=1);

namespace App\Enums;

enum InventoryItemType: string
{
    case Service = 'service';
    case StockItem = 'stock_item';
    case NonStockItem = 'non_stock_item';
    case RawMaterial = 'raw_material';
    case FinishedGood = 'finished_good';
    case Consumable = 'consumable';

    public function label(): string
    {
        return match ($this) {
            self::Service => 'Service',
            self::StockItem => 'Stock Item',
            self::NonStockItem => 'Non-Stock Item',
            self::RawMaterial => 'Raw Material',
            self::FinishedGood => 'Finished Good',
            self::Consumable => 'Consumable',
        };
    }

    public function tracksInventoryByDefault(): bool
    {
        return ! in_array($this, [self::Service, self::NonStockItem], true);
    }
}
