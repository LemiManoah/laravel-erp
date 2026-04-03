<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Appearance\UpdateAppearanceAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppearanceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class AppearanceController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.appearance.update', only: ['update']),
        ];
    }

    public function update(UpdateAppearanceRequest $request, UpdateAppearanceAction $action): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('updateAppearance', $user);

        $action->handle($user, $request->validated());

        return back();
    }
}
