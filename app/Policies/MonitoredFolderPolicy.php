<?php

namespace App\Policies;

use App\Models\MonitoredFolder;
use App\Models\User;

class MonitoredFolderPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MonitoredFolder $monitoredFolder): bool
    {
        return $user->id === $monitoredFolder->googleAccount->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MonitoredFolder $monitoredFolder): bool
    {
        return $user->id === $monitoredFolder->googleAccount->user_id;
    }

    public function delete(User $user, MonitoredFolder $monitoredFolder): bool
    {
        return $user->id === $monitoredFolder->googleAccount->user_id;
    }

    public function restore(User $user, MonitoredFolder $monitoredFolder): bool
    {
        return $user->id === $monitoredFolder->googleAccount->user_id;
    }

    public function forceDelete(User $user, MonitoredFolder $monitoredFolder): bool
    {
        return $user->id === $monitoredFolder->googleAccount->user_id;
    }
}
