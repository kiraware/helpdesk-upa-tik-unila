<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class TicketCommentController extends Controller
{
    /**
     * Menyimpan komentar baru untuk tiket.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate(['message' => 'required|string']);

        $user = $request->user();

        // 1. KEAMANAN: Cek Otorisasi
        // Jika user adalah 'USER' biasa (bukan Admin/Superuser),
        // dia HANYA boleh komen di tiket miliknya sendiri.
        if ($user->role === UserRole::USER && $ticket->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki akses ke tiket ini.');
        }

        DB::transaction(function () use ($request, $ticket, $user) {
            // 2. Simpan Komentar (User ID pasti ada)
            $ticket->comments()->create([
                'user_id' => $user->id,
                'message' => $request->message,
            ]);

            $isStaff = in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

            // Jika yang komen adalah Staff, DAN tiket belum ada yang pegang
            if ($isStaff && is_null($ticket->assigned_to)) {
                $ticket->update([
                    'assigned_to' => $user->id,
                    'assigned_at' => now(),
                    'status' => TicketStatus::PROGRESS,
                ]);
            }
        });

        $isStaff = in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

        if ($isStaff) {
            // A. STAFF MEMBALAS -> Notifikasi ke User Pemilik Tiket
            if ($ticket->user && $ticket->user_id !== $user->id) {
                $ticket->user->notify(new SystemNotification(
                    'Balasan Baru pada Tiket',
                    "{$user->name} membalas tiket #{$ticket->ticket_code}.",
                    route('tickets.show', $ticket),
                    'info'
                ));
            } elseif ($ticket->guestDetail) {
                Notification::route('mail', $ticket->guestDetail->email)
                    ->route('whatsapp', $ticket->guestDetail->phone)
                    ->notify(new SystemNotification(
                        'Balasan Baru pada Tiket',
                        "{$user->name} membalas tiket #{$ticket->ticket_code}.",
                        route('guest.tracking.show', $ticket->ticket_code),
                        'info'
                    ));
            }
        } else {
            // B. USER MEMBALAS -> Notifikasi ke Petugas

            if ($ticket->assigned_to) {
                // Jika sudah ada petugas, kirim ke petugasnya saja
                if ($ticket->assigned_to !== $user->id) {
                    $ticket->assignee->notify(new SystemNotification(
                        'Balasan User',
                        "{$user->name} membalas tiket #{$ticket->ticket_code} yang Anda tangani.",
                        route('tickets.show', $ticket),
                        'info'
                    ));
                }
            } else {
                // Jika BELUM ada petugas (Unassigned), kirim ke SEMUA Admin
                $admins = User::whereIn('role', [UserRole::ADMIN, UserRole::SUPERUSER])->get();
                Notification::send($admins, new SystemNotification(
                    'Balasan User (Unassigned)',
                    "{$user->name} membalas tiket #{$ticket->ticket_code}. Belum ada petugas.",
                    route('tickets.show', $ticket),
                    'warning'
                ));
            }
        }

        return back()->with('success', 'Komentar berhasil dikirim.');
    }

    /**
     * Handle upload file Trix Editor.
     */
    public function storeEmbeddedFile(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:jpg,jpeg,png,pdf,doc,docx,zip'],
        ]);

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('trix-attachments', 'public');

            return response()->json(['url' => Storage::url($path)]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
