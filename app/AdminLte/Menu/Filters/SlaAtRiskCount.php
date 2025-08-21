<?php

namespace App\AdminLte\Menu\Filters;

use App\Models\Incident;
use Illuminate\Support\Facades\Auth;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class SlaAtRiskCount implements FilterInterface
{
    public function transform($item)
    {
        // Cibler l’item de menu marqué par la clé
        if (($item['key'] ?? null) !== 'incidents_sla') {
            return $item;
        }

        $count = 0;
        $user = Auth::user();

        if ($user) {
            // Base query: incidents en retard (due_at <= now) et non résolus
            $q = Incident::query()
                ->whereNull('resolved_at')
                ->whereNotNull('due_at')
                ->where('due_at', '<=', now());

            // Respecter le rôle (mêmes règles que le contrôleur)
            if ($user->hasRole('admin')) {
                // pas de filtre
            } elseif ($user->hasRole('superviseur') && $user->service_id) {
                $q->where('service_id', $user->service_id);
            } elseif ($user->hasRole('technicien')) {
                $q->where('technicien_id', $user->id);
            } else {
                // utilisateur simple
                $q->where('user_id', $user->id);
            }

            $count = $q->count();
        }

        if ($count > 0) {
            $item['label'] = (string) $count;               // AdminLTE veut une string
            $item['label_color'] = $item['label_color'] ?? 'danger';
        } else {
            unset($item['label']); // pas de badge si zéro
        }

        return $item;
    }
}
