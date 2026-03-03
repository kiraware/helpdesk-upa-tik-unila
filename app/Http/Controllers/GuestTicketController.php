<?php

namespace App\Http\Controllers;

use App\Enums\IdentityType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Rules\ValidTurnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class GuestTicketController extends Controller
{
    /**
     * Menampilkan form pencarian tiket.
     */
    public function index()
    {
        return view('guest-tickets.index');
    }

    /**
     * Menangani logika pencarian tiket (Search Logic).
     */
    public function search(Request $request)
    {
        $request->validate([
            'ticket_code' => 'required|string',
        ]);

        $code = $request->ticket_code;
        $user = auth()->user();

        $ticket = Ticket::where('ticket_code', $code)->first();

        // Skenario 1: Tiket tidak ditemukan di sistem
        if (! $ticket) {
            return back()->with('error', 'Tiket tidak ditemukan dengan kode tersebut.');
        }

        // Cek apakah Tiket dibuat oleh User (Internal) atau Guest
        if ($ticket->user_id) {
            // --- TIKET USER ---

            // Jika yang mencari adalah Guest (tidak login)
            if (! $user) {
                // Skenario 2 (Variant Guest): Anggap tidak ada demi privasi
                return back()->with('error', 'Tiket tidak ditemukan.');
            }

            // Jika role USER
            if ($user->role === UserRole::USER) {
                // Skenario 3: User adalah pemilik tiket
                if ($ticket->user_id === $user->id) {
                    return redirect()->route('tickets.show', $ticket->uuid);
                }

                // Skenario 2 (Variant User Lain): User bukan pemilik tiket
                return back()->with('error', 'Tiket tidak ditemukan.');
            }

            // Skenario 4: Admin/Superuser mencari tiket User
            if (in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER])) {
                return redirect()->route('tickets.show', $ticket->uuid);
            }

        } else {
            // --- TIKET GUEST ---

            // Skenario 5: Admin/Superuser mencari tiket Guest -> Redirect ke Guest View
            // (Juga mencakup Guest biasa mencari tiket Guest)
            return redirect()->route('guest.tracking.show', $ticket->ticket_code);
        }

        return back()->with('error', 'Akses ditolak.');
    }

    /**
     * Menampilkan detail tiket berdasarkan ticket_code.
     */
    public function show(Request $request, Ticket $ticket)
    {
        // Security Check: Pastikan tiket ini memang tiket Guest (tidak punya user_id)
        // Atau jika ingin user biasa juga bisa ditracking, hapus pengecekan ini.
        // Disini kita asumsikan tracking publik hanya untuk tiket guest demi privasi user internal.
        if ($ticket->user_id) {
            abort(403, 'Tiket ini terdaftar sebagai tiket user internal. Silakan login untuk melihat.');
        }

        $ticket->load([
            'service',
            'assignee',
            'guestDetail',
            'comments.user',
            'comments.attachments',
        ]);

        return view('guest-tickets.show', compact('ticket'));
    }

    public function create()
    {
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get();

        $departments = Department::orderBy('name')->get();

        return view('guest-tickets.create', compact('services', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // 1. Validasi Data Diri
            'full_name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:20|regex:/^([0-9\s\-\+\(\)]*)$/',
            'identity_number' => 'required|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'entity_type' => ['required', new Enum(IdentityType::class)],
            'photo_identity' => 'required|image|max:5120',
            'photo_selfie' => 'required|image|max:5120',

            // 2. Validasi Data Tiket
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where('is_active', true),
            ],
            'priority' => ['required', new Enum(TicketPriority::class)],
            'title' => 'required|string|max:100',
            'description' => 'required|string',
            'cf-turnstile-response' => ['required', new ValidTurnstile],
        ]);

        $ticket = DB::transaction(function () use ($validated, $request) {
            // A. Buat Tiket Utama
            $newTicket = Ticket::create([
                'user_id' => null, // Guest
                'service_id' => $validated['service_id'],
                'priority' => $validated['priority'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'status' => TicketStatus::WAITING,
            ]);

            // B. Upload Foto Identitas & Selfie
            $identityPath = $request->file('photo_identity')->store('guest-identities', 'public');
            $selfiePath = $request->file('photo_selfie')->store('guest-selfies', 'public');

            // C. Simpan Detail Guest
            $newTicket->guestDetail()->create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'identity_number' => $validated['identity_number'],
                'department_id' => $validated['department_id'],
                'entity_type' => $validated['entity_type'],
                'photo_identity_path' => $identityPath,
                'photo_selfie_path' => $selfiePath,
            ]);

            return $newTicket;
        });

        // 1. Notifikasi ke Admin & Superuser
        $admins = User::whereIn('role', [UserRole::ADMIN, UserRole::SUPERUSER])->get();
        Notification::send($admins, new SystemNotification(
            'Tiket Baru (Tamu)',
            "Tamu ({$validated['full_name']}) membuat tiket baru: {$validated['title']}",
            route('tickets.show', $ticket->uuid),
            'info'
        ));

        // 2. Notifikasi ke Guest
        Notification::route('mail', $validated['email'])
            ->route('whatsapp', $validated['phone'])
            ->notify(new SystemNotification(
                'Tiket Berhasil Dibuat',
                "Halo {$validated['full_name']}, laporan Anda telah kami terima dengan Kode Tiket: {$ticket->ticket_code}. Silakan pantau perkembangannya melalui link berikut.",
                route('guest.tracking.show', $ticket->ticket_code),
                'success'
            ));

        return redirect()
            ->route('guest.tracking.show', $ticket->ticket_code)
            ->with('success', 'Tiket berhasil dibuat! Silakan simpan Kode Tiket atau URL ini untuk memantau perkembangan.');
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
                'mimes:jpg,jpeg,png,pdf,doc,docx,zip',
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
