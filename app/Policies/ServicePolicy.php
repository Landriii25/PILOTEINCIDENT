<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('services.view') || $user->can('services.*');
    }

    public function view(User $user, Service $service): bool
    {
        if ($user->can('services.view')) {
            return true;
        }
        // Un superviseur peut voir son service :
        if ($user->hasRole('superviseur') && $user->service_id === $service->id) {
            return true;
        }
        return false;
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
}
