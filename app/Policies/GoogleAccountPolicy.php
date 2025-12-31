<?php

namespace App\Policies;

use App\Models\GoogleAccount;
use App\Models\User;

class GoogleAccountPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, GoogleAccount $googleAccount): bool
    {
        return $user->id === $googleAccount->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, GoogleAccount $googleAccount): bool
    {
        return $user->id === $googleAccount->user_id;
    }

    public function delete(User $user, GoogleAccount $googleAccount): bool
    {
        return $user->id === $googleAccount->user_id;
    }

    public function restore(User $user, GoogleAccount $googleAccount): bool
    {
        return $user->id === $googleAccount->user_id;
    }

    public function forceDelete(User $user, GoogleAccount $googleAccount): bool
    {
        return $user->id === $googleAccount->user_id;
    }
}
