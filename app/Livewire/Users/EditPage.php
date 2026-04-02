<?php

declare(strict_types=1);

namespace App\Livewire\Users;

use App\Actions\User\UpdateUserAction;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class EditPage extends Component
{
    #[Locked]
    public int $userId;

    public string $name = '';
    public string $email = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public array $selectedRoles = [];
    public bool $is_active = true;

    public function mount(User $user): void
    {
        abort_unless(auth()->user()?->can('users.update'), 403);

        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
        $this->is_active = $user->is_active;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
    }

    protected function rules(): array
    {
        $user = User::query()->find($this->userId);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', tenant()->unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'selectedRoles' => ['required', 'array', 'min:1'],
            'is_active' => ['boolean'],
        ];
    }

    public function update(UpdateUserAction $action): mixed
    {
        abort_unless(auth()->user()?->can('users.update'), 403);

        $this->validate();

        $user = User::query()->findOrFail($this->userId);

        try {
            $action->handle($user, [
                'name' => trim($this->name),
                'email' => trim($this->email),
                'phone' => $this->phone === '' ? null : trim($this->phone),
                'password' => $this->password === '' ? null : $this->password,
                'roles' => $this->selectedRoles,
                'is_active' => $this->is_active,
            ]);
        } catch (ValidationException $e) {
            foreach ($e->errors() as $field => $messages) {
                $this->addError($field === 'roles' ? 'selectedRoles' : $field, $messages[0]);
            }

            return null;
        }

        session()->flash('success', 'User updated successfully.');

        return $this->redirectRoute('users.index');
    }

    public function render(): View
    {
        return view('livewire.users.edit-page', [
            'roles' => Role::query()->orderBy('name')->pluck('name'),
        ]);
    }
}
