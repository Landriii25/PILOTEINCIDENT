<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        // Deux paginateurs séparés (avec noms de page distincts)
        $unread = $request->user()
            ->unreadNotifications()
            ->latest()
            ->paginate(10, ['*'], 'unread_page');

        $read = $request->user()
            ->readNotifications()
            ->latest()
            ->paginate(10, ['*'], 'read_page');

        return view('notifications.index', compact('unread', 'read'));
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();
        return back()->with('success', 'Toutes les notifications sont lues.');
    }

    public function markRead(string $id, Request $request)
    {
        $n = $request->user()->notifications()->findOrFail($id);
        $n->markAsRead();
        return back()->with('success', 'Notification lue.');
    }

    public function go(string $id, Request $request)
    {
        $n = $request->user()->notifications()->findOrFail($id);
        $n->markAsRead();
        $url = $n->data['url'] ?? route('notifications.index');
        return redirect($url);
    }
}
