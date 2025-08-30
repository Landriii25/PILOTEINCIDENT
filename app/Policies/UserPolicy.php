<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view') || $user->can('users.*');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view') || $user->can('users.*');
    }

    public function create(User $user): bool
    {
        return $user->can('users.create') || $user->can('users.*');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.update') || $user->can('users.*');
    }

    public function delete(User $user, User $model): bool
    {
        // empêcher de se supprimer soi‑même, même si autorisé
        if ($user->id === $model->id) {
            return false;
        }
        return $user->can('users.delete') || $user->can('users.*');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('users.update') || $user->can('users.*');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->can('users.delete') || $user->can('users.*');
    }
}
