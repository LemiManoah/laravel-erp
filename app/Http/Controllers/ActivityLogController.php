<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

final readonly class ActivityLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:activity-logs.view'),
        ];
    }

    public function index(): View
    {
        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->latest()
            ->paginate();

        return view('activity_logs.index', compact('activities'));
    }
}
