<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Incident;

class IncidentPolicy
{
    /** Liste */
    public function viewAny(User $user): bool
    {
        // Tout le monde peut voir la liste ; le contrôle de portée (own/assigned/service/any) se fait au niveau contrôleur requête
        return $user !== null;
    }

    /** Voir un incident (contrôle fin par portée) */
    public function view(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.view.any')) {
            return true;
        }

        if ($user->can('incidents.view.service') && $user->service_id && $incident->service_id === $user->service_id) {
            return true;
        }

        if ($user->can('incidents.view.assigned') && $incident->technicien_id === $user->id) {
            return true;
        }

        if ($user->can('incidents.view.own') && $incident->user_id === $user->id) {
            return true;
        }

        return false;
    }

    /** Créer */
    public function create(User $user): bool
    {
        return $user->can('incidents.create');
    }

    /** Mettre à jour */
    public function update(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.update.any')) {
            return true;
        }

        if ($user->can('incidents.update.service') && $user->service_id && $incident->service_id === $user->service_id) {
            return true;
        }

        if ($user->can('incidents.update.assigned') && $incident->technicien_id === $user->id) {
            return true;
        }

        return false;
    }

    /** Assigner */
    public function assign(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.assign.any')) {
            return true;
        }
        if ($user->can('incidents.assign.service') && $user->service_id && $incident->service_id === $user->service_id) {
            return true;
        }
        // pickup (= s’auto‑assigner)
        if ($user->can('incidents.assign.pickup') && ($incident->technicien_id === null || $incident->technicien_id === $user->id)) {
            return true;
        }
        return false;
    }

    /** Résoudre */
    public function resolve(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.resolve.any')) {
            return true;
        }
        if ($user->can('incidents.resolve.service') && $user->service_id && $incident->service_id === $user->service_id) {
            return true;
        }
        if ($user->can('incidents.resolve.assigned') && $incident->technicien_id === $user->id) {
            return true;
        }
        return false;
    }

    /** Clôturer */
    public function close(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.close.any')) {
            return true;
        }
        if ($user->can('incidents.close.own') && $incident->user_id === $user->id) {
            return true;
        }
        return false;
    }

    /** Ré‑ouvrir */
    public function reopen(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.reopen.any')) {
            return true;
        }
        if ($user->can('incidents.reopen.own') && $incident->user_id === $user->id) {
            return true;
        }
        return false;
    }

    /** Suppressions avancées (optionnel) */
    public function delete(User $user, Incident $incident): bool
    {
        return $user->can('incidents.delete');
    }
    public function restore(User $user, Incident $incident): bool
    {
        return $user->can('incidents.restore');
    }
    public function forceDelete(User $user, Incident $incident): bool
    {
        return $user->can('incidents.forceDelete');
    }
}
