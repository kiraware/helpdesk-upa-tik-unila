<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    /**
     * Menampilkan semua histori notifikasi
     */
    public function index()
    {
        // Ambil semua notifikasi user, paginate 20 per halaman
        $notifications = auth()->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Tandai notifikasi sebagai "sudah dibaca" lalu redirect ke URL tujuan
     */
    public function markAsReadAndRedirect($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        // Tandai sudah dibaca
        $notification->markAsRead();

        // Ambil URL dari data notifikasi, default ke dashboard jika null
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
}
