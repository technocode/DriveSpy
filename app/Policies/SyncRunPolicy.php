<?php

namespace App\Policies;

use App\Models\SyncRun;
use App\Models\User;

class SyncRunPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SyncRun $syncRun): bool
    {
        return $user->id === $syncRun->googleAccount->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, SyncRun $syncRun): bool
    {
        return $user->id === $syncRun->googleAccount->user_id;
    }

    public function delete(User $user, SyncRun $syncRun): bool
    {
        return $user->id === $syncRun->googleAccount->user_id;
    }

    public function restore(User $user, SyncRun $syncRun): bool
    {
        return $user->id === $syncRun->googleAccount->user_id;
    }

    public function forceDelete(User $user, SyncRun $syncRun): bool
    {
        return $user->id === $syncRun->googleAccount->user_id;
    }
}
