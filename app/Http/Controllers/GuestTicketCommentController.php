<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Rules\ValidTurnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GuestTicketCommentController extends Controller
{
    /**
     * Menyimpan balasan komentar khusus dari Guest.
     */
    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'cf-turnstile-response' => ['required', new ValidTurnstile],
        ]);

        $ticket->comments()->create([
            'user_id' => null, // Guest
            'message' => $request->message,
        ]);

        return back()->with('success', 'Balasan berhasil dikirim.');
    }

    /**
     * Handle upload file (Gambar/Dokumen) via Trix Editor khusus Guest.
     */
    public function storeEmbeddedFile(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:5120', // Limit 5MB
                'mimes:jpg,jpeg,png,pdf,doc,docx,zip',
            ],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Kita simpan di folder terpisah agar mudah dimanage jika perlu dibersihkan
            $path = $file->store('guest-trix-attachments', 'public');

            return response()->json([
                'url' => Storage::url($path),
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
