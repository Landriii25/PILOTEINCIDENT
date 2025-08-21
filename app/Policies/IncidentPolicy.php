<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class IncidentPolicy
{
    /**
     * Voir n’importe quel incident (listing global)
     */
    public function viewAny(User $user): bool
    {
        // admin via Gate::before
        return $user->can('incidents.view.any')
            || $user->can('incidents.view.service')
            || $user->can('incidents.view.assigned')
            || $user->can('incidents.view.own');
    }

    /**
     * Voir un incident particulier
     */
    public function view(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.view.any')) {
            return true;
        }

        if ($user->can('incidents.view.service')) {
            // superviseur : incidents de son service
            return $user->service_id && $incident->service_id === $user->service_id;
        }

        if ($user->can('incidents.view.assigned')) {
            // technicien : incidents qui lui sont assignés
            return $incident->technicien_id === $user->id;
        }

        if ($user->can('incidents.view.own')) {
            // utilisateur : ses propres incidents
            return $incident->user_id === $user->id;
        }

        return false;
    }

    /**
     * Créer un incident (admin/superviseur/utilisateur)
     */
    public function create(User $user): bool
    {
        return $user->can('incidents.create');
    }

    /**
     * Mettre à jour un incident
     */
    public function update(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.update.any')) {
            return true;
        }

        if ($user->can('incidents.update.service')) {
            return $user->service_id && $incident->service_id === $user->service_id;
        }

        if ($user->can('incidents.update.assigned')) {
            return $incident->technicien_id === $user->id;
        }

        return false;
    }

    /**
     * Assigner / Réassigner un incident
     */
    public function assign(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.assign.any')) {
            return true;
        }

        if ($user->can('incidents.assign.service')) {
            return $user->service_id && $incident->service_id === $user->service_id;
        }

        // technicien “pickup” (s’auto‑assigner) si permission dédiée
        if ($user->can('incidents.assign.pickup')) {
            // Ex : autoriser le pickup si pas de technicien ou même service
            return is_null($incident->technicien_id)
                && ($user->service_id && $incident->service_id === $user->service_id);
        }

        return false;
    }

    /**
     * Résoudre (passer à “Résolu”)
     */
    public function resolve(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.resolve.any')) {
            return true;
        }

        if ($user->can('incidents.resolve.service')) {
            return $user->service_id && $incident->service_id === $user->service_id;
        }

        if ($user->can('incidents.resolve.assigned')) {
            return $incident->technicien_id === $user->id;
        }

        return false;
    }

    /**
     * Clôturer (close) — seulement admin OU créateur (selon règle)
     */
    public function close(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.close.any')) {
            return true;
        }

        if ($user->can('incidents.close.own')) {
            return $incident->user_id === $user->id;
        }

        return false;
    }

    /**
     * Ré-ouvrir vers le même tech (reopen)
     */
    public function reopen(User $user, Incident $incident): bool
    {
        if ($user->can('incidents.reopen.any')) {
            return true;
        }

        if ($user->can('incidents.reopen.own')) {
            return $incident->user_id === $user->id;
        }

        return false;
    }

    /**
     * Supprimer un incident (souvent réservé à admin)
     */
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
