<?php

declare(strict_types=1);

namespace App\Enums;

enum InventoryBatchStatus: string
{
    case Active = 'active';
    case Depleted = 'depleted';
    case Expired = 'expired';
    case Quarantined = 'quarantined';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Depleted => 'Depleted',
            self::Expired => 'Expired',
            self::Quarantined => 'Quarantined',
        };
    }
}
