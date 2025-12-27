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
     * Menyimpan komentar baru untuk tiket tertentu.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate(['message' => 'required|string']);

        DB::transaction(function () use ($request, $ticket) {
            $user = auth()->user();

            $comment = $ticket->comments()->create([
                'user_id' => $user ? $user->id : null,
                'message' => $request->message,
            ]);

            // Cek apakah user adalah Admin atau Superuser
            // Kita gunakan ->value karena kolom role di DB kemungkinan string ('admin', 'superuser')
            $isAuthorized = $user && in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

            // Jika User berwenang DAN Tiket belum ada petugasnya
            if ($isAuthorized && is_null($ticket->assigned_to)) {
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
     * Handle upload file (Gambar/Dokumen) via Drag & Drop di Trix Editor.
     * Return JSON URL untuk ditampilkan di editor.
     */
    public function storeEmbeddedFile(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:5120',
                'mimes:jpg,jpeg,png,pdf,doc,docx,zip,rar',
            ],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Simpan di folder khusus gambar editor
            $path = $file->store('trix-attachments', 'public');

            return response()->json([
                'url' => Storage::url($path),
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
