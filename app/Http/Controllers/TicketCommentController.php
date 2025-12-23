<?php

namespace App\Http\Controllers;

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
        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,pdf,doc,docx,zip', // Max 20MB
        ]);

        DB::transaction(function () use ($request, $ticket) {
            // 1. Simpan Komentar (Gunakan clean() jika pakai HTML Purifier)
            $comment = $ticket->comments()->create([
                'user_id' => auth()->id(),
                'message' => $request->message, // Data dari Trix sudah berupa HTML
            ]);

            // 2. Simpan Attachment (File lampiran biasa)
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('comments/'.$ticket->uuid, 'public');

                    $comment->attachments()->create([
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }
        });

        return back()->with('success', 'Komentar berhasil dikirim.');
    }

    /**
     * Handle upload gambar via Drag & Drop di Trix Editor.
     * Return JSON URL untuk ditampilkan di editor.
     */
    public function uploadEditorImage(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Simpan di folder khusus gambar editor
            $path = $file->store('editor-images', 'public');

            return response()->json([
                'url' => Storage::url($path),
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
