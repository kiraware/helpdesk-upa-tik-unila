<?php

namespace App\Http\Controllers;

use App\Channels\WhatsAppChannel;
use App\Enums\IdentityType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Rules\ValidRecaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
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

        $code = strtoupper($request->ticket_code);
        $user = auth()->user();

        $ticket = Ticket::where('ticket_code', $code)->first();

        // Skenario 1: Tiket tidak ditemukan di sistem
        if (! $ticket) {
            return back()->withInput()->with('error', 'Tiket tidak ditemukan dengan kode tersebut.');
        }

        // Cek apakah Tiket dibuat oleh User (Internal) atau Guest
        if ($ticket->user_id) {
            // --- TIKET USER ---

            // Jika yang mencari adalah Guest (tidak login)
            if (! $user) {
                // Skenario 2 (Variant Guest): Anggap tidak ada demi privasi
                return back()->withInput()->with('error', 'Tiket tidak ditemukan.');
            }

            // Jika role USER
            if ($user->role === UserRole::USER) {
                // Skenario 3: User adalah pemilik tiket
                if ($ticket->user_id === $user->id) {
                    return redirect()->route('tickets.show', $ticket);
                }

                // Skenario 2 (Variant User Lain): User bukan pemilik tiket
                return back()->withInput()->with('error', 'Tiket tidak ditemukan.');
            }

            // Skenario 4: Admin/Superuser mencari tiket User
            if (in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER])) {
                return redirect()->route('tickets.show', $ticket);
            }

        } else {
            // --- TIKET GUEST ---

            // Skenario 5: Admin/Superuser mencari tiket Guest -> Redirect ke Guest View
            // (Juga mencakup Guest biasa mencari tiket Guest)
            return redirect()->route('guest.tracking.show', $ticket->ticket_code);
        }

        return back()->withInput()->with('error', 'Akses ditolak.');
    }

    /**
     * Menampilkan detail tiket berdasarkan ticket_code.
     */
    public function show(Request $request, Ticket $ticket)
    {
        // Security Check: Pastikan tiket ini memang tiket Guest (tidak punya user_id)
        if ($ticket->user_id) {
            return redirect('/')->with('error', 'Tiket ini terdaftar sebagai tiket user internal. Silakan login untuk melihat.');
        }

        $ticket->load([
            'service',
            'assignee',
            'guestDetail',
            'comments.user',
            'comments.attachments',
        ]);

        $admins = User::whereIn('role', ['admin', 'superuser'])->get();

        $services = Service::where('is_active', true)
            ->orderByRaw('LOWER(name) ASC')
            ->get(['id', 'name']);

        return view('guest-tickets.show', compact('ticket', 'admins', 'services'));
    }

    public function create()
    {
        $services = Service::where('is_active', true)
            ->where('show_to_guest', true)
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
            'email' => [
                'required',
                'email',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (preg_match('/@([a-z0-9-]+\.)*unila\.ac\.id$/i', $value)) {
                        $fail('Email dari domain unila.ac.id tidak diperbolehkan.');
                    }
                },
            ],
            'phone' => 'nullable|string|max:20|regex:/^([0-9\s\-\+\(\)]*)$/',
            'identity_number' => 'required|string|max:50',
            'department_id' => 'required|exists:departments,id',
            'entity_type' => ['required', new Enum(IdentityType::class)],
            'photo_identity' => 'required|image|max:2048',
            'photo_selfie' => 'required|image|max:2048',

            // 2. Validasi Data Tiket
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(function ($query) {
                    return $query->where('is_active', true)->where('show_to_guest', true);
                }),
            ],
            'priority' => ['required', new Enum(TicketPriority::class)],
            'description' => 'required|string',
            'g-recaptcha-response' => ['required', new ValidRecaptcha],
        ]);

        $ticket = DB::transaction(function () use ($validated, $request) {
            // A. Buat Tiket Utama
            $newTicket = Ticket::create([
                'user_id' => null, // Guest
                'service_id' => $validated['service_id'],
                'priority' => $validated['priority'],
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

        $unassignedAttachments = TicketAttachment::whereNull('ticket_id')->get();

        foreach ($unassignedAttachments as $attachment) {
            if (str_contains($ticket->description, $attachment->url)) {
                $attachment->update(['ticket_id' => $ticket->id]);
            }
        }

        // 1. NOTIFIKASI KE ADMIN & SUPERUSER
        $admins = User::whereIn('role', [UserRole::ADMIN, UserRole::SUPERUSER])->get();
        $titleAdmin = 'Laporan Baru dari Tamu';
        $messageAdmin = "Terdapat laporan baru dari tamu (*{$validated['full_name']}*) dengan kode tiket *#{$ticket->ticket_code}* pada layanan *{$ticket->service->name}*. Laporan ini memiliki prioritas *{$ticket->priority->value}*. Mohon segera tinjau detail laporan ini dan tentukan petugas untuk menindaklanjutinya.";
        $channels = ['database', 'mail', WhatsAppChannel::class];

        Notification::send($admins, new SystemNotification(
            $titleAdmin,
            $messageAdmin,
            route('tickets.show', $ticket),
            'info',
            $channels
        ));

        // 2. NOTIFIKASI KE GUEST
        $titleGuest = 'Laporan Anda Berhasil Diterima';
        $messageGuest = "Halo *{$validated['full_name']}*, laporan Anda terkait layanan *{$ticket->service->name}* telah berhasil kami terima dan simpan dengan kode tiket *#{$ticket->ticket_code}*. Silakan klik tautan di bawah ini untuk melihat detail dan memantau status penanganan tiket Anda secara berkala.";
        $guestChannels = ['mail'];
        $guestNotification = Notification::route('mail', $validated['email']);

        // Cek apakah nomor telepon diisi oleh tamu
        if (! empty($validated['phone'])) {
            $guestNotification->route(WhatsAppChannel::class, $validated['phone']);
            $guestChannels[] = WhatsAppChannel::class;
        }

        $guestNotification->notify(new SystemNotification(
            $titleGuest,
            $messageGuest,
            route('guest.tracking.show', $ticket->ticket_code),
            'info',
            $guestChannels
        ));

        return redirect()
            ->route('guest.tracking.show', $ticket->ticket_code)
            ->with('success', 'Tiket berhasil dibuat! Silakan simpan Kode Tiket atau URL ini untuk memantau perkembangan.');
    }

    /**
     * Handle upload file dari Trix Editor untuk Guest.
     * Menggunakan nama generik dan validasi 2MB.
     */
    public function storeEmbeddedFile(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png,pdf,doc,docx,zip'],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('ticket-attachments', 'public');

            $attachment = TicketAttachment::create([
                'ticket_id' => null,
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);

            return response()->json([
                'url' => $attachment->url,
            ]);
        }

        return response()->json(['error' => 'No file uploaded'], 400);
    }
}
