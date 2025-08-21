<?php

namespace App\AdminLte\Menu\Filters;

use Illuminate\Support\Facades\Auth;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;

class UnreadNotificationsCount implements FilterInterface
{
    public function transform($item)
    {
        // On cible UNIQUEMENT l’entrée de menu ayant 'key' => 'notifications'
        if (($item['key'] ?? null) !== 'notifications') {
            return $item;
        }

        $count = 0;
        if (Auth::check()) {
            $count = Auth::user()->unreadNotifications()->count();
        }

        if ($count > 0) {
            $item['label'] = (string) $count;     // AdminLTE veut une string
            $item['label_color'] = $item['label_color'] ?? 'danger';
        } else {
            unset($item['label']); // pas de badge s’il n’y a rien
        }

        return $item;
    }
}
