<?php

namespace App\Http\Controllers;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Configuration;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\User;
use App\Notifications\SystemNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $services = Service::where('is_active', true)
            ->orderByRaw('LOWER(name) ASC')
            ->get(['id', 'name']);

        $tickets = Ticket::query()
            ->with([
                'user',
                'service',
                'assignee',
                'guestDetail',
            ])
            ->withCount('comments')

            // Jika user yang login role-nya 'user', batasi query hanya ke tiket miliknya.
            ->when($user && $user->role === UserRole::USER, function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })

            ->when($request->q, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('ticket_code', 'ilike', "%{$request->q}%")
                    ->orWhere('description', 'ilike', "%{$request->q}%");
            }))

            ->when($request->status, fn ($q) => $q->where('status', $request->status))

            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))

            ->when($request->service, fn ($q) => $q->where('service_id', $request->service))

            ->when($request->assigned_to, function ($q) use ($request) {
                match ($request->assigned_to) {
                    'unassigned' => $q->whereNull('assigned_to'),
                    'me' => $q->where('assigned_to', auth()->id()),
                    default => $q->where('assigned_to', $request->assigned_to),
                };
            })

            ->when($request->start_date || $request->end_date, function ($q) use ($request) {
                $start = $request->start_date
                    ? now()->parse($request->start_date)->startOfDay()
                    : null;

                $end = $request->end_date
                    ? now()->parse($request->end_date)->endOfDay()
                    : null;

                if ($start && $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                } elseif ($start) {
                    $q->where('created_at', '>=', $start);
                } elseif ($end) {
                    $q->where('created_at', '<=', $end);
                }
            })

            ->latest()
            ->paginate(10)
            ->withQueryString();

        $admins = User::whereIn('role', ['admin', 'superuser'])
            ->orderByRaw('LOWER(name) ASC')
            ->get(['id', 'name']);

        return view('tickets.index', compact('tickets', 'admins', 'services'));
    }

    public function create()
    {
        if (auth()->user()->role !== UserRole::USER) {
            return redirect()->route('tickets.index')->with('error', 'Hanya pengguna (User) yang dapat membuat tiket baru.');
        }

        $services = Service::where('is_active', true)
            ->where('show_to_user', true)
            ->orderByRaw('LOWER(name) ASC')
            ->get();

        return view('tickets.create', compact('services'));
    }

    public function show(Ticket $ticket)
    {
        // Mencegah User A mengakses URL tiket milik User B secara manual
        $user = auth()->user();
        if ($user->role === UserRole::USER) {
            if ($ticket->user_id !== $user->id) {
                return redirect()->route('tickets.index')->with('error', 'Anda tidak memiliki akses ke tiket ini.');
            }
        }

        $ticket->load([
            'user',
            'service',
            'assignee',
            'guestDetail',
            'attachments',
            'comments.user',
            'comments.attachments',
        ]);

        $admins = User::whereIn('role', ['admin', 'superuser'])->get();

        $services = Service::where('is_active', true)
            ->orderByRaw('LOWER(name) ASC')
            ->get(['id', 'name']);

        return view('tickets.show', compact('ticket', 'admins', 'services'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== UserRole::USER) {
            return redirect()->route('tickets.index')->with('error', 'Tindakan tidak diizinkan.');
        }

        $validated = $request->validate([
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(function ($query) {
                    return $query->where('is_active', true)->where('show_to_user', true);
                }),
            ],
            'priority' => ['required', new Enum(TicketPriority::class)],
            'description' => 'required|string',
        ]);

        $ticket = DB::transaction(function () use ($validated) {
            $ticket = Ticket::create([
                'user_id' => auth()->id(),
                'service_id' => $validated['service_id'],
                'priority' => $validated['priority'],
                'description' => $validated['description'],
                'status' => TicketStatus::WAITING,
            ]);

            return $ticket;
        });

        $unassignedAttachments = TicketAttachment::whereNull('ticket_id')->get();

        foreach ($unassignedAttachments as $attachment) {
            if (str_contains($ticket->description, $attachment->url)) {
                $attachment->update(['ticket_id' => $ticket->id]);
            }
        }

        $admins = User::whereIn('role', [UserRole::ADMIN, UserRole::SUPERUSER])->get();

        Notification::send($admins, new SystemNotification(
            'Tiket Baru Masuk',
            auth()->user()->name." membuat tiket baru: #{$ticket->ticket_code}.",
            route('tickets.show', $ticket),
            'info'
        ));

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Tiket berhasil dibuat. Tim kami akan segera meninjaunya.');
    }

    public function assignMe(Ticket $ticket)
    {
        // Pastikan User Biasa tidak bisa mengakses fungsi ini
        if (auth()->user()->role === \App\Enums\UserRole::USER) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengambil tiket ini.');
        }

        // Cegah overwrite jika sudah ada petugas
        if ($ticket->assigned_to) {
            return back()->with('error', 'Tiket sudah ditugaskan.');
        }

        $ticket->update([
            'assigned_to' => auth()->id(),
            'assigned_at' => now(),
            'status' => TicketStatus::PROGRESS,
        ]);

        $adminName = auth()->user()->name;

        if ($ticket->user) {
            $ticket->user->notify(new SystemNotification(
                'Tiket Sedang Diproses',
                "Tiket #{$ticket->ticket_code} kini sedang ditangani oleh ".$adminName.'.',
                route('tickets.show', $ticket),
                'info'
            ));
        } elseif ($ticket->guestDetail) {
            Notification::route('mail', $ticket->guestDetail->email)
                ->route('whatsapp', $ticket->guestDetail->phone)
                ->notify(new SystemNotification(
                    'Tiket Sedang Diproses',
                    "Tiket Anda (#{$ticket->ticket_code}) kini sedang ditangani oleh staff kami ({$adminName}).",
                    route('guest.tracking.show', $ticket->ticket_code),
                    'info'
                ));
        }

        return back()->with('success', 'Tiket berhasil ditugaskan ke Anda.');
    }

    public function updateAssignee(Request $request, Ticket $ticket)
    {
        // Validasi input, pastikan user ID yang dikirim adalah admin/superuser
        $request->validate([
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->whereIn('role', [UserRole::ADMIN->value, UserRole::SUPERUSER->value]),
            ],
        ]);

        $user = auth()->user();

        // Keamanan tambahan: Hanya Superuser yang boleh merubah petugas secara manual
        if ($user->role !== UserRole::SUPERUSER) {
            return back()->with('error', 'Hanya Superuser yang dapat merubah petugas secara manual.');
        }

        // Syarat: Status tiket masih waiting atau progress
        if (! in_array($ticket->status, [TicketStatus::WAITING, TicketStatus::PROGRESS])) {
            return back()->with('error', 'Petugas tidak dapat diubah pada tiket yang sudah ditutup.');
        }

        // Jika nilai dari form sama dengan nilai di database, hentikan proses (skip update)
        if ($ticket->assigned_to == $request->assigned_to) {
            return back()->with('info', 'Tidak ada perubahan pada petugas tiket.');
        }

        // Update data petugas & timestamp HANYA jika ada perubahan
        $ticket->assigned_to = $request->assigned_to;
        $ticket->assigned_at = $request->assigned_to ? now() : null;

        // Auto-update status
        if (is_null($request->assigned_to) && $ticket->status === TicketStatus::PROGRESS) {
            $ticket->status = TicketStatus::WAITING;
        } elseif (! is_null($request->assigned_to) && $ticket->status === TicketStatus::WAITING) {
            $ticket->status = TicketStatus::PROGRESS;
        }

        $ticket->save();

        // Kirim notifikasi HANYA jika form tidak dikosongkan
        if ($request->assigned_to) {
            $ticket->load('assignee');

            if ($ticket->assignee && $ticket->assignee->id !== $user->id) {
                $ticket->assignee->notify(new SystemNotification(
                    'Penugasan Tiket Baru',
                    "Anda telah ditugaskan untuk menangani tiket #{$ticket->ticket_code} oleh {$user->name}.",
                    route('tickets.show', $ticket),
                    'info'
                ));
            }
        }

        return back()->with('success', 'Petugas tiket berhasil diperbarui.');
    }

    public function updateService(Request $request, Ticket $ticket)
    {
        $request->validate([
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(fn ($q) => $q->where('is_active', true)),
            ],
        ]);

        $user = auth()->user();

        // 1. Status harus waiting / progress
        if (! in_array($ticket->status, [TicketStatus::WAITING, TicketStatus::PROGRESS])) {
            return back()->with('error', 'Layanan tidak dapat diubah pada tiket yang sudah ditutup.');
        }

        $isSuperuser = $user->role === UserRole::SUPERUSER;
        $isAdmin = $user->role === UserRole::ADMIN;

        if ($isSuperuser) {
            if (is_null($ticket->assigned_to)) {
                $ticket->assigned_to = $user->id;
                $ticket->assigned_at = now();
            }
        } elseif ($isAdmin) {
            if (is_null($ticket->assigned_to)) {
                $ticket->assigned_to = $user->id;
                $ticket->assigned_at = now();
            } elseif ($ticket->assigned_to !== $user->id) {
                return back()->with('error', 'Anda tidak memiliki akses untuk mengubah layanan tiket ini.');
            }
        } else {
            return back()->with('error', 'Anda tidak memiliki hak akses.');
        }

        $ticket->service_id = $request->service_id;
        $ticket->save();

        return back()->with('success', 'Layanan tiket berhasil diperbarui.');
    }

    public function updatePriority(Request $request, Ticket $ticket)
    {
        $request->validate([
            'priority' => ['required', Rule::enum(TicketPriority::class)],
        ]);

        $user = auth()->user();

        // 1. Syarat: Status tiket masih waiting atau progress
        if (! in_array($ticket->status, [TicketStatus::WAITING, TicketStatus::PROGRESS])) {
            return back()->with('error', 'Prioritas tidak dapat diubah pada tiket yang sudah ditutup.');
        }

        // 2. Syarat Otorisasi & Auto-assign
        $isSuperuser = $user->role === UserRole::SUPERUSER;
        $isAdmin = $user->role === UserRole::ADMIN;

        if ($isSuperuser) {
            // Superuser bisa mengedit kapan saja.
            // Jika belum ada petugas, otomatis jadikan dia petugas (opsional, tapi disarankan)
            if (is_null($ticket->assigned_to)) {
                $ticket->assigned_to = $user->id;
                $ticket->assigned_at = now();
            }
        } elseif ($isAdmin) {
            // Admin hanya bisa mengedit jika tiket belum ada petugasnya ATAU tiket miliknya
            if (is_null($ticket->assigned_to)) {
                $ticket->assigned_to = $user->id;
                $ticket->assigned_at = now();
            } elseif ($ticket->assigned_to !== $user->id) {
                return back()->with('error', 'Anda tidak memiliki akses untuk mengubah prioritas tiket ini.');
            }
        } else {
            return back()->with('error', 'Anda tidak memiliki hak akses.');
        }

        // Update priority
        $ticket->priority = $request->priority;
        $ticket->save();

        return back()->with('success', 'Prioritas tiket berhasil diperbarui.');
    }

    /**
     * Menutup tiket (Selesai / Tolak).
     * Jika belum ada petugas, otomatis assign ke yang menutup.
     */
    public function close(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        // 1. Cek apakah user adalah staff (Admin / Superuser)
        if (! in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER])) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menutup tiket.');
        }

        // 2. Cek apakah tiket sudah di-assign ke orang lain
        if (! is_null($ticket->assigned_to) && $ticket->assigned_to !== $user->id) {
            return back()->with('error', 'Anda tidak dapat menutup tiket yang sedang ditugaskan kepada staff lain.');
        }

        // 3. Minimal ada 1 komentar dari staf
        $hasStaffComment = $ticket->comments()->whereHas('user', function ($query) {
            $query->whereIn('role', [UserRole::ADMIN->value, UserRole::SUPERUSER->value]);
        })->exists();

        if (! $hasStaffComment) {
            return back()->with('error', 'Tidak dapat menutup tiket. Harus ada minimal 1 balasan/komentar dari petugas sebelum tiket dapat ditutup.');
        }

        $validated = $request->validate([
            'status' => ['required', new Enum(TicketStatus::class)],
        ]);

        if (! in_array($validated['status'], [TicketStatus::DONE->value, TicketStatus::REJECT->value])) {
            return back()->with('error', 'Status tidak valid untuk penutupan tiket.');
        }

        DB::transaction(function () use ($ticket, $validated, $user) {
            $updateData = [
                'status' => $validated['status'],
                'closed_at' => now(),
            ];

            if (is_null($ticket->assigned_to)) {
                $updateData['assigned_to'] = $user->id;
                $updateData['assigned_at'] = now();
            }

            $ticket->update($updateData);
        });

        $statusLabel = $validated['status'] === TicketStatus::DONE->value ? 'diselesaikan' : 'ditolak';
        $type = $validated['status'] === TicketStatus::DONE->value ? 'success' : 'error';

        if ($ticket->user) {
            $ticket->user->notify(new SystemNotification(
                "Tiket #{$ticket->ticket_code} ".ucfirst($statusLabel),
                "Tiket Anda telah {$statusLabel} oleh {$user->name}. Mohon luangkan waktu untuk mengisi survei kepuasan pada halaman detail tiket.",
                route('tickets.show', $ticket),
                $type
            ));
        } elseif ($ticket->guestDetail) {
            Notification::route('mail', $ticket->guestDetail->email)
                ->route('whatsapp', $ticket->guestDetail->phone)
                ->notify(new SystemNotification(
                    "Tiket #{$ticket->ticket_code} ".ucfirst($statusLabel),
                    "Tiket Anda telah {$statusLabel} oleh staff {$user->name}. Mohon luangkan waktu untuk mengisi survei kepuasan pada halaman detail tiket.",
                    route('guest.tracking.show', $ticket->ticket_code),
                    $type
                ));
        }

        return back()->with('success', "Tiket berhasil {$statusLabel}.");
    }

    public function printAssignment(Ticket $ticket)
    {
        $user = auth()->user();
        $isStaff = in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

        if (! $isStaff && $ticket->assigned_to !== $user->id) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mencetak surat tugas ini.');
        }

        if (! $ticket->assigned_to) {
            return back()->with('error', 'Tiket belum memiliki petugas.');
        }

        $ticket->load([
            'assignee.division',
            'service',
            'user',
            'guestDetail',
        ]);

        $config = Configuration::first();
        $kepalaUpa = [
            'name' => $config->upa_head_name,
            'nip' => $config->upa_head_nip,
            'jabatan' => $config->upa_head_position,
        ];

        $pdf = Pdf::loadView('tickets.pdf.assignment_letter', compact('ticket', 'kepalaUpa'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Surat_Tugas_'.$ticket->ticket_code.'.pdf');
    }

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
