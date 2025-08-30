<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Report;

class ReportPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('reports.view');
    }

    public function view(User $user, Report $report): bool
    {
        return $user->can('reports.view');
    }

    /** Création/Mise à jour d’un rapport = lié à la capacité à mettre à jour l’incident.
     *  On laisse souvent le contrôleur vérifier l’IncidentPolicy::update/resolve.
     *  Ici, on autorise si l’utilisateur peut au moins “update.any/service/assigned”.
     */
    public function create(User $user): bool
    {
        return $user->can('incidents.update.any')
            || $user->can('incidents.update.service')
            || $user->can('incidents.update.assigned');
    }

    public function update(User $user, Report $report): bool
    {
        // même logique qu’en création
        return $this->create($user);
    }

    public function delete(User $user, Report $report): bool
    {
        // pas de suppression par défaut (à adapter)
        return false;
    }

    public function restore(User $user, Report $report): bool
    {
        return false;
    }

    public function forceDelete(User $user, Report $report): bool
    {
        return false;
    }
}
