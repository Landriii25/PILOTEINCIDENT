<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Service;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        // Lecture des services pour tous les rôles (utile pour listes / filtres)
        return $user !== null;
    }

    public function view(User $user, Service $service): bool
    {
        return $user !== null || $user->can('services.view') || $user->can('services.*');
    }

    public function create(User $user): bool
    {
        return $user->can('services.create') || $user->can('services.*');
    }

    public function update(User $user, Service $service): bool
    {
        return $user->can('services.update') || $user->can('services.*');
    }

    public function delete(User $user, Service $service): bool
    {
        return $user->can('services.delete') || $user->can('services.*');
    }

    public function restore(User $user, Service $service): bool
    {
        return $user->can('services.update') || $user->can('services.*');
    }

    public function forceDelete(User $user, Service $service): bool
    {
        return $user->can('services.delete') || $user->can('services.*');
    }
}
