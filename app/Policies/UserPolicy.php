<?php

namespace App\Policies;

use App\Models\User as AppUser;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view') || $user->can('users.*');
    }

    public function view(User $user, AppUser $model): bool
    {
        return $user->can('users.view') || $user->can('users.*');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create') || $user->can('users.*');
    }

    public function update(User $user, AppUser $model): bool
    {
        return $user->can('users.update') || $user->can('users.*');
    }

    public function delete(User $user, AppUser $model): bool
    {
        return $user->can('users.delete') || $user->can('users.*');
    }
}
