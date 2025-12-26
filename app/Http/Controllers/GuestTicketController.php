<?php

namespace App\Http\Controllers;

use App\Enums\IdentityType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Models\Service;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class GuestTicketController extends Controller
{
    public function create()
    {
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('guest-tickets.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 1. Validasi Data Diri (Wajib Upload Manual)
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'identity_number' => 'required|string|max:50',
            'entity_type' => ['required', new Enum(IdentityType::class)],

            // Limit 5MB (5120 KB)
            'photo_identity' => 'required|image|max:5120',
            'photo_selfie' => 'required|image|max:5120',

            // 2. Validasi Data Tiket
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
            'priority' => ['required', new Enum(TicketPriority::class)],
            'title' => 'required|string|max:100',
            'description' => 'required|string', // Trix Content (HTML)

            // Validasi attachments[] manual DIHAPUS karena pakai Trix
        ]);

        DB::transaction(function () use ($validated, $request) {
            // A. Buat Tiket Utama
            $ticket = Ticket::create([
                'user_id' => null, // Guest
                'service_id' => $validated['service_id'],
                'priority' => $validated['priority'],
                'title' => $validated['title'],
                'description' => $validated['description'], // HTML dari Trix
                'status' => TicketStatus::WAITING,
            ]);

            // B. Upload Foto Identitas & Selfie (Tetap Manual karena field khusus)
            // Simpan di folder guest-identities
            $identityPath = $request->file('photo_identity')->store('guest-identities', 'public');
            $selfiePath = $request->file('photo_selfie')->store('guest-selfies', 'public');

            // C. Simpan Detail Guest
            $ticket->guestDetail()->create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'identity_number' => $validated['identity_number'],
                'entity_type' => $validated['entity_type'],
                'photo_identity_path' => $identityPath,
                'photo_selfie_path' => $selfiePath,
            ]);

            // D. Logic Attachment Manual DIHAPUS (Step D sebelumnya)
        });

        return back()->with('success', 'Tiket berhasil dibuat! Silakan cek email Anda untuk notifikasi selanjutnya.');
    }

    /**
     * Handle upload file dari Trix Editor untuk Guest.
     * Menggunakan nama generik dan validasi 5MB.
     */
    public function storeEmbeddedFile(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:5120', // Max 5MB
                'mimes:jpg,jpeg,png,pdf,doc,docx,zip,rar',
            ],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Simpan di folder guest-editor-files
            $path = $file->store('guest-editor-files', 'public');

            return response()->json([
                'url' => Storage::url($path),
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
