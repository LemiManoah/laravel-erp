<?php

declare(strict_types=1);

namespace App\Livewire\Users;

use App\Actions\User\CreateUserAction;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class CreatePage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedRoles = [];
    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('users.create'), 403);
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', tenant()->unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'selectedRoles' => ['required', 'array', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }

    public function save(CreateUserAction $action): mixed
    {
        abort_unless(auth()->user()?->can('users.create'), 403);

        $this->validate();

        $action->handle([
            'name' => trim($this->name),
            'email' => trim($this->email),
            'phone' => $this->phone === '' ? null : trim($this->phone),
            'password' => $this->password,
            'roles' => $this->selectedRoles,
            'is_active' => $this->is_active,
        ]);

        session()->flash('success', 'User created successfully.');

        return $this->redirectRoute('users.index');
    }

    public function render(): View
    {
        return view('livewire.users.create-page', [
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }
}
