<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Actions\Profile\UpdateProfileAction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class ProfilePage extends Component
{
    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('settings.profile.update'), 403);

        $user = auth()->user();
        $this->name = $user->name;
        $this->email = $user->email;
    }

    protected function rules(): array
    {
        $user = auth()->user();

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', tenant()->unique('users', 'email')->ignore($user)],
        ];
    }

    public function save(UpdateProfileAction $action): void
    {
        abort_unless(auth()->user()?->can('settings.profile.update'), 403);

        $this->validate();

        $action->handle(auth()->user(), [
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('status', 'Profile updated successfully');
    }

    public function deleteAccount(): mixed
    {
        $user = auth()->user();

        Auth::logout();
        $user->delete();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return $this->redirectRoute('tenant.home');
    }

    public function render(): View
    {
        return view('livewire.settings.profile-page');
    }
}
