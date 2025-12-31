<?php

namespace App\Policies;

use App\Models\DriveItem;
use App\Models\User;

class DriveItemPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, DriveItem $driveItem): bool
    {
        return $user->id === $driveItem->googleAccount->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, DriveItem $driveItem): bool
    {
        return $user->id === $driveItem->googleAccount->user_id;
    }

    public function delete(User $user, DriveItem $driveItem): bool
    {
        return $user->id === $driveItem->googleAccount->user_id;
    }

    public function restore(User $user, DriveItem $driveItem): bool
    {
        return $user->id === $driveItem->googleAccount->user_id;
    }

    public function forceDelete(User $user, DriveItem $driveItem): bool
    {
        return $user->id === $driveItem->googleAccount->user_id;
    }
}
