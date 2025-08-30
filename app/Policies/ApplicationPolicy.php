<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Application;

class ApplicationPolicy
{
    /** Lecture liste */
    public function viewAny(User $user): bool
    {
        // Lecture libre pour tous les utilisateurs authentifiés
        return $user !== null;
    }

    /** Lecture fiche */
    public function view(User $user, Application $application): bool
    {
        return true; // lecture libre
    }

    /** Création (admin only via permissions) */
    public function create(User $user): bool
    {
        return $user->can('applications.create') || $user->can('applications.*');
    }

    /** Mise à jour */
    public function update(User $user, Application $application): bool
    {
        return $user->can('applications.update') || $user->can('applications.*');
    }

    /** Suppression */
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
