<?php

declare(strict_types=1);

namespace App\Enums;

enum InventoryDirection: string
{
    case In = 'in';
    case Out = 'out';

    public function multiplier(): int
    {
        return $this === self::In ? 1 : -1;
    }

    public function label(): string
    {
        return $this === self::In ? 'Stock In' : 'Stock Out';
    }
}
