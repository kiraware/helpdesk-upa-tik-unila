<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    /**
     * Menampilkan semua histori notifikasi
     */
    public function index()
    {
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Tandai notifikasi sebagai "sudah dibaca" lalu redirect ke URL tujuan
     */
    public function markAsReadAndRedirect($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        $url = $notification->data['url'] ?? route('dashboard');

        return redirect($url);
    }

    /**
     * Tandai semua sebagai sudah dibaca
     */
    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('success', 'Semua notifikasi telah ditandai sudah dibaca.');
    }

    public function fetchJson()
    {
        $user = auth()->user();

        $unreadNotifs = $user->unreadNotifications()->take(5)->get()->map(function ($notif) {
            return [
                'id' => $notif->id,
                'data' => $notif->data,
                'created_at' => $notif->created_at->diffForHumans(),
                'read_url' => route('notifications.read', $notif->id),
            ];
        });

        return response()->json([
            'unreadCount' => $user->unreadNotifications()->count(),
            'notifications' => $unreadNotifs,
        ]);
    }
}
