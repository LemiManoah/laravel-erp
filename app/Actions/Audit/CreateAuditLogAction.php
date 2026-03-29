<?php

declare(strict_types=1);

namespace App\Actions\Audit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

final readonly class CreateAuditLogAction
{
    public function handle(
        string $actionType,
        Model|string $entity,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $reason = null,
    ): Activity {
        $request = request();
        $properties = [];
        
        if ($oldValues) {
            $properties['old'] = (array) $oldValues;
        }
        
        if ($newValues) {
            $properties['attributes'] = (array) $newValues;
        }

        $activity = activity()
            ->causedBy(Auth::user())
            ->event($actionType)
            ->withProperties($properties)
            ->tap(function (Activity $activity) use ($request, $reason) {
                // Determine a sensible description if reason isn't provided
                $activity->description = $reason ?? str($activity->event)->replace('.', ' ')->ucfirst()->toString();
                
                // Track metadata in properties if needed
                $activity->properties = $activity->properties->merge([
                    'ip_address' => $request?->ip(),
                    'user_agent' => $request?->userAgent()
                ]);
            });

        if ($entity instanceof Model) {
            $activity->performedOn($entity);
        }

        return $activity->log($reason ?? "{$actionType} recorded");
    }
}
