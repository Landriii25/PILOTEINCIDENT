<?php

namespace App\AdminLte\Menu\Filters;

use App\Models\Incident;
use Illuminate\Support\Facades\Auth;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class SlaBadgesCount implements FilterInterface
{
    public function transform($item)
    {
        $user = Auth::user();

        if (!$user) {
            return $item;
        }

        // Requête de base
        $q = Incident::query()
            ->whereNull('resolved_at')
            ->whereNotNull('due_at');

        // Filtrage par rôle
        if ($user->hasRole('admin')) {
            // pas de filtre
        } elseif ($user->hasRole('superviseur') && $user->service_id) {
            $q->where('service_id', $user->service_id);
        } elseif ($user->hasRole('technicien')) {
            $q->where('technicien_id', $user->id);
        } else {
            $q->where('user_id', $user->id);
        }

        // Selon la clé de menu
        if (($item['key'] ?? null) === 'incidents_sla_overdue') {
            $count = (clone $q)
                ->where('due_at', '<=', now())
                ->count();
        } elseif (($item['key'] ?? null) === 'incidents_sla_soon') {
            $count = (clone $q)
                ->whereBetween('due_at', [now(), now()->addHours(1)])
                ->count();
        } else {
            return $item; // pas concerné
        }

        if ($count > 0) {
            $item['label'] = (string) $count;
            // Couleur déjà définie dans config/menu
        } else {
            unset($item['label']); // pas de badge si zéro
        }

        return $item;
    }
}
