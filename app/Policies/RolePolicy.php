<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    public function manage(User $user): bool
    {
        return $user->can('roles.manage') || $user->can('roles.*');
    }
}
