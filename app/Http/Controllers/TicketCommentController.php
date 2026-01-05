<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
