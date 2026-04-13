<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;

final readonly class UpdateProfileAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $user, array $data): User
    {
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->forceFill([
                'email_verified_at' => null,
            ]);
        }

        $user->save();

        return $user;
    }
}
