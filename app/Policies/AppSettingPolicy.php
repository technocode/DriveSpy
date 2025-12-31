<?php

namespace App\Policies;

use App\Models\AppSetting;
use App\Models\User;

class AppSettingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, AppSetting $appSetting): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, AppSetting $appSetting): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, AppSetting $appSetting): bool
    {
        return $user->is_admin;
    }

    public function restore(User $user, AppSetting $appSetting): bool
    {
        return $user->is_admin;
    }

    public function forceDelete(User $user, AppSetting $appSetting): bool
    {
        return $user->is_admin;
    }
}
