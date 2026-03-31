<?php

declare(strict_types=1);

namespace App\Enums;

enum StockLocationType: string
{
    case Warehouse = 'warehouse';
    case Store = 'store';
    case ColdRoom = 'cold_room';
    case Shelf = 'shelf';
    case Farm = 'farm';
    case Field = 'field';

    public function label(): string
    {
        return match ($this) {
            self::Warehouse => 'Warehouse',
            self::Store => 'Store',
            self::ColdRoom => 'Cold Room',
            self::Shelf => 'Shelf',
            self::Farm => 'Farm',
            self::Field => 'Field',
        };
    }
}
