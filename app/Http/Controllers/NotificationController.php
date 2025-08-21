<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Liste des notifications (facultatif si tu n’utilises que le dropdown).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        // Non lues en premier, puis lues
        $unread = $user->unreadNotifications()->latest()->paginate(10, ['*'], 'unread_page');
        $read   = $user->readNotifications()->latest()->paginate(10, ['*'], 'read_page');

        // Si tu as une vue notifications.index, décommente la ligne suivante :
        // return view('notifications.index', compact('unread','read'));

        // Sinon, on renvoie un JSON simple (utile pour debug/API)
        return view('notifications.index', compact('unread', 'read'));
    }

    /**
     * Marquer TOUTES les notifications comme lues.
     */
    public function markAllRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        // Répondre intelligemment si c’est un appel AJAX
        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Toutes les notifications ont été marquées comme lues.']);
        }

        return back()->with('success', 'Toutes les notifications ont été marquées comme lues.');
    }

    /**
     * Marquer UNE notification comme lue.
     */
    public function markRead(Request $request, string $id)
    {
        $user = $request->user();
        $n = $user->notifications()->where('id', $id)->firstOrFail(); // garantie : appartient bien à l’utilisateur

        if ($n->read_at === null) {
            $n->markAsRead();
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Notification marquée comme lue.');
    }

    /**
     * Accéder à la notification, la marquer lue et rediriger vers l’URL cible.
     * Utilisé par le lien du dropdown (cloche).
     */
    public function go(Request $request, string $id)
    {
        $user = $request->user();
        $n = $user->notifications()->where('id', $id)->firstOrFail();

        // Marque comme lue si nécessaire
        if (is_null($n->read_at)) {
            $n->markAsRead();
        }

        // Récupérer l’URL stockée dans la notification
        $url = data_get($n->data, 'url');

        // Sécurise : si pas d’URL, on revient en arrière
        if (!$url) {
            return back()->with('warning', 'La notification ne contient pas de lien de destination.');
        }

        return redirect($url);
    }
}
