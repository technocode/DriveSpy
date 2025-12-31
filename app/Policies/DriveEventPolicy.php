<?php

namespace App\Policies;

use App\Models\DriveEvent;
use App\Models\User;

class DriveEventPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DriveEvent $driveEvent): bool
    {
        return $user->id === $driveEvent->googleAccount->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DriveEvent $driveEvent): bool
    {
        return $user->id === $driveEvent->googleAccount->user_id;
    }

    public function delete(User $user, DriveEvent $driveEvent): bool
    {
        return $user->id === $driveEvent->googleAccount->user_id;
    }

    public function restore(User $user, DriveEvent $driveEvent): bool
    {
        return $user->id === $driveEvent->googleAccount->user_id;
    }

    public function forceDelete(User $user, DriveEvent $driveEvent): bool
    {
        return $user->id === $driveEvent->googleAccount->user_id;
    }
}
