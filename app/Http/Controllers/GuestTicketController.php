<?php

namespace App\Http\Controllers;

use App\Channels\WhatsAppChannel;
use App\Enums\IdentityType;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Helpers\ImageSanitizer;
use App\Helpers\OffHoursHelper;
use App\Models\Department;
use App\Models\Service;
use App\Models\ServiceReplyTemplate;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Rules\SafeFile;
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

        if (! $ticket) {
            return back()->withInput()->with('error', 'Tiket tidak ditemukan dengan kode tersebut.');
        }

        if ($ticket->user_id) {

            if (! $user) {
                return back()->withInput()->with('error', 'Tiket tidak ditemukan.');
            }

            if ($user->role === UserRole::USER) {
                if ($ticket->user_id === $user->id) {
                    return redirect()->route('tickets.show', $ticket);
                }

                return back()->withInput()->with('error', 'Tiket tidak ditemukan.');
            }

            if (in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER])) {
                return redirect()->route('tickets.show', $ticket);
            }

        } else {

            return redirect()->route('guest.tracking.show', $ticket->ticket_code);
        }

        return back()->withInput()->with('error', 'Akses ditolak.');
    }

    /**
     * Menampilkan detail tiket berdasarkan ticket_code.
     */
    public function show(Request $request, Ticket $ticket)
    {
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

        $admins = User::whereIn('role', ['admin', 'superuser'])->get(['id', 'name', 'avatar_path']);

        $services = Service::where('is_active', true)
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
            ->get(['id', 'name']);

        // Query template jawaban untuk admin/superuser yang login
        $replyTemplate = null;
        if (auth()->check() && in_array(auth()->user()->role, [UserRole::ADMIN, UserRole::SUPERUSER])) {
            $replyTemplate = ServiceReplyTemplate::where('service_id', $ticket->service_id)
                ->where('user_id', auth()->id())
                ->value('template');
        }

        return view('guest-tickets.show', compact('ticket', 'admins', 'services', 'replyTemplate'));
    }

    public function create()
    {
        $services = Service::where('is_active', true)
            ->where('show_to_guest', true)
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
            ->get();

        $departments = Department::orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")->get(['id', 'name']);

        return view('guest-tickets.create', compact('services', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:50',
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
            'phone' => 'nullable|string|regex:/^[0-9]+$/|max:20',
            'identity_number' => 'required|string|regex:/^[0-9]+$/|max:32',
            'department_id' => 'required|exists:departments,id',
            'other_department' => [
                'nullable',
                'string',
                'max:150',
                function ($attribute, $value, $fail) use ($request) {
                    $dept = Department::find($request->department_id);
                    if ($dept && strtolower($dept->name) === 'lainnya' && empty($value)) {
                        $fail('Nama Fakultas / Unit Kerja wajib diisi jika memilih Lainnya.');
                    }
                },
            ],
            'entity_type' => ['required', new Enum(IdentityType::class)],
            'photo_identity' => ['required', 'image', 'max:2048', new SafeFile],
            'photo_selfie' => ['required', 'image', 'max:2048', new SafeFile],

            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(function ($query) {
                    return $query->where('is_active', true)->where('show_to_guest', true);
                }),
            ],
            'priority' => ['required', new Enum(TicketPriority::class)],
            'description' => 'required|string|min:20',
            'g-recaptcha-response' => ['required', new ValidRecaptcha],
        ]);

        $service = Service::find($validated['service_id']);
        if ($service) {
            $activeTicket = Ticket::whereHas('guestDetail', function ($query) use ($validated) {
                $query->where('identity_number', $validated['identity_number']);
            })
                ->where('service_id', $service->id)
                ->whereNotIn('status', [TicketStatus::DONE->value, TicketStatus::REJECT->value])
                ->exists();

            if ($activeTicket) {
                return back()
                    ->withInput()
                    ->with('error', "Anda masih memiliki tiket dengan layanan {$service->name} yang sedang aktif (belum selesai). Silakan tunggu tiket tersebut diselesaikan sebelum membuat tiket baru.");
            }
        }

        $ticket = DB::transaction(function () use ($validated, $request) {
            $newTicket = Ticket::create([
                'user_id' => null, // Guest
                'service_id' => $validated['service_id'],
                'priority' => $validated['priority'],
                'description' => $validated['description'],
                'status' => TicketStatus::WAITING,
            ]);

            $identityPath = $request->file('photo_identity')->store('guest-identities', 'public');
            $selfiePath = $request->file('photo_selfie')->store('guest-selfies', 'public');

            ImageSanitizer::sanitize(storage_path('app/public/'.$identityPath), $request->file('photo_identity')->getClientOriginalExtension());
            ImageSanitizer::sanitize(storage_path('app/public/'.$selfiePath), $request->file('photo_selfie')->getClientOriginalExtension());

            $newTicket->guestDetail()->create([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'identity_number' => $validated['identity_number'],
                'department_id' => $validated['department_id'],
                'other_department' => $validated['other_department'] ?? null,
                'entity_type' => $validated['entity_type'],
                'photo_identity_path' => $identityPath,
                'photo_selfie_path' => $selfiePath,
            ]);

            return $newTicket;
        });

        TicketAttachment::whereNull('ticket_id')
            ->where('created_at', '>=', now()->subDay())
            ->select(['id', 'ticket_id', 'path'])
            ->get()
            ->each(function ($attachment) use ($ticket) {
                if (str_contains($ticket->description, $attachment->url)) {
                    $attachment->update(['ticket_id' => $ticket->id]);
                }
            });

        $admins = User::whereIn('role', [UserRole::ADMIN->value, UserRole::SUPERUSER->value])->get();
        $titleAdmin = 'Tiket Baru';
        $messageAdmin = "Terdapat tiket baru (*#{$ticket->ticket_code}*) untuk layanan *{$ticket->service->name}* dari *".$validated['full_name'].'*. Mohon segera ditinjau.';

        Notification::send($admins, new SystemNotification(
            $titleAdmin,
            $messageAdmin,
            route('tickets.show', $ticket),
            'info',
            ['database']
        ));

        $titleGuest = 'Laporan Anda Berhasil Diterima';
        $messageGuest = "Halo *{$validated['full_name']}*, laporan Anda terkait layanan *{$ticket->service->name}* telah berhasil kami terima dan simpan dengan kode tiket *#{$ticket->ticket_code}*. Silakan klik tautan di bawah ini untuk melihat detail dan memantau status penanganan tiket Anda secara berkala.";
        $guestChannels = ['mail'];
        $guestNotification = Notification::route('mail', $validated['email']);

        if (! empty($validated['phone'])) {
            $guestNotification->route(WhatsAppChannel::class, $validated['phone']);
            $guestChannels[] = WhatsAppChannel::class;
        }

        if (OffHoursHelper::isOutsideWorkingHours()) {
            $messageGuest .= "\n\n*Catatan:* Tiket Anda dibuat di luar jam kerja/hari libur. Pengerjaan tiket akan dilakukan pada hari dan jam kerja operasional (Senin-Kamis: 08.00-16.00 WIB, Jumat: 08.00-16.30 WIB).";
        }

        $guestNotification->notify(new SystemNotification(
            $titleGuest,
            $messageGuest,
            route('guest.tracking.show', $ticket->ticket_code),
            'info',
            $guestChannels
        ));

        $successMessage = 'Tiket berhasil dibuat! Silakan simpan Kode Tiket atau URL ini untuk memantau perkembangan.';
        if (OffHoursHelper::isOutsideWorkingHours()) {
            $successMessage .= ' Pengerjaan tiket akan dilakukan pada hari dan jam kerja operasional.';
        }

        return redirect()
            ->route('guest.tracking.show', $ticket->ticket_code)
            ->with('success', $successMessage);
    }

    /**
     * Handle upload file dari Trix Editor untuk Guest.
     * Menggunakan nama generik dan validasi 2MB.
     */
    public function storeEmbeddedFile(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png,pdf', new SafeFile],
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('ticket-attachments', 'public');

            ImageSanitizer::sanitize(storage_path('app/public/'.$path), $file->getClientOriginalExtension());

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
