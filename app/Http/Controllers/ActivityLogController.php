<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

final readonly class ActivityLogController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:activity-logs.view'),
        ];
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $activities = Activity::query()
            ->with(['causer', 'subject'])
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $activityQuery) use ($search): void {
                    $activityQuery->where('description', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('event', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('log_name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('subject_type', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('subject_id', 'like', sprintf('%%%s%%', $search))
                        ->orWhereHas('causer', function (Builder $causerQuery) use ($search): void {
                            $causerQuery->where('name', 'like', sprintf('%%%s%%', $search))
                                ->orWhere('email', 'like', sprintf('%%%s%%', $search));
                        });
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('activity_logs.index', compact('activities', 'search'));
    }
}
