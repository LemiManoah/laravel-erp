<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Actions\Password\UpdatePasswordAction;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class PasswordPage extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('settings.password.update'), 403);
    }

    protected function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function save(UpdatePasswordAction $action): void
    {
        abort_unless(auth()->user()?->can('settings.password.update'), 403);

        $this->validate();

        $action->handle(auth()->user(), $this->password);

        $this->reset(['current_password', 'password', 'password_confirmation']);
        session()->flash('status', 'password-updated');
    }

    public function render(): View
    {
        return view('livewire.settings.password-page');
    }
}
