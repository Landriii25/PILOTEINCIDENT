<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function before(User $user, string $ability)
    {
        // Vérifie si l'utilisateur a le droit de tout faire
        if ($user->hasRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        // Tout le monde peut consulter la liste
        return true;
    }

    public function view(User $user, Application $application): bool
    {
        return true; // lecture libre
    }

    public function create(User $user): bool
    {
        return $user->can('applications.create') || $user->can('applications.*');
    }

    public function update(User $user, Application $application): bool
    {
        return $user->can('applications.update') || $user->can('applications.*');
    }

    public function delete(User $user, Application $application): bool
    {
        return $user->can('applications.delete') || $user->can('applications.*');
    }

    public function restore(User $user, Application $application): bool
    {
        return $user->can('applications.restore') || $user->can('applications.*');
    }

    public function forceDelete(User $user, Application $application): bool
    {
        return $user->can('applications.forceDelete') || $user->can('applications.*');
    }
}
