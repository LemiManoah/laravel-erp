<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Actions\Password\UpdatePasswordAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

final readonly class PasswordController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:settings.password.update', only: ['update']),
        ];
    }

    public function update(UpdatePasswordRequest $request, UpdatePasswordAction $action): RedirectResponse
    {
        $user = $request->user();
        $this->authorize('updatePassword', $user);

        $action->handle($user, $request->validated('password'));

        return back()->with('status', 'password-updated');
    }
}
