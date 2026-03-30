<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

trait LogsModelActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults()
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->logExcept([
                'password',
                'remember_token',
            ])
            ->setDescriptionForEvent(fn (string $eventName): string => sprintf('%s %s', class_basename($this), $eventName));

        if ($this->getFillable() !== []) {
            $options->logFillable();
        } else {
            $options->logAll();
        }

        return $options;
    }
}
