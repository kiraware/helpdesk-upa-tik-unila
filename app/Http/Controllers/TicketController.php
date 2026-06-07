<?php

namespace App\Http\Controllers;

use App\Channels\WhatsAppChannel;
use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Helpers\OffHoursHelper;
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
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
            ->get(['id', 'name']);

        $tickets = Ticket::query()
            ->with([
                'user:id,name,avatar_path',
                'service:id,name',
                'assignee:id,name,avatar_path',
                'guestDetail:id,ticket_id,full_name',
            ])
            ->withCount('comments')
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

            // ORDERING STRATEGY
            //
            // Tujuan: Tiket yang paling butuh perhatian muncul paling atas.
            //
            // 1. Status (aktif dulu, selesai belakangan):
            //    waiting(0) → progress(1) → done(2) → reject(3)
            //
            // 2. Priority dalam status aktif (waiting/progress):
            //    high(0) → medium(1) → low(2)
            //    Tiket closed tidak perlu diurutkan berdasarkan priority.
            //
            // 3. Waktu buat:
            //    - Tiket aktif  → terlama di atas (sudah nunggu paling lama)
            //    - Tiket closed → terbaru di atas (riwayat terkini lebih relevan)
            // -------------------------------------------------------------------------
            ->orderByRaw("
                CASE status
                    WHEN 'waiting'  THEN 0
                    WHEN 'progress' THEN 1
                    WHEN 'done'     THEN 2
                    WHEN 'reject'   THEN 3
                    ELSE 4
                END ASC
            ")
            ->orderByRaw("
                CASE
                    WHEN status IN ('waiting', 'progress') THEN
                        CASE priority
                            WHEN 'high'   THEN 0
                            WHEN 'medium' THEN 1
                            WHEN 'low'    THEN 2
                            ELSE 3
                        END
                    ELSE 0
                END ASC
            ")
            ->orderByRaw("
                CASE
                    WHEN status IN ('waiting', 'progress') THEN EXTRACT(EPOCH FROM created_at)
                    ELSE NULL
                END ASC NULLS LAST
            ")
            ->orderBy('created_at', 'desc')

            ->paginate(10)
            ->withQueryString();

        $admins = User::whereIn('role', ['admin', 'superuser'])
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
            ->get(['id', 'name', 'avatar_path']);

        return view('tickets.index', compact('tickets', 'admins', 'services'));
    }

    public function create()
    {
        if (auth()->user()->role !== UserRole::USER) {
            return redirect()->route('tickets.index')->with('error', 'Hanya pengguna (User) yang dapat membuat tiket baru.');
        }

        $services = Service::where('is_active', true)
            ->where('show_to_user', true)
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
            ->get();

        return view('tickets.create', compact('services'));
    }

    public function show(Ticket $ticket)
    {
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

        $admins = User::whereIn('role', ['admin', 'superuser'])->get(['id', 'name', 'avatar_path']);

        $services = Service::where('is_active', true)
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
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

        $service = Service::find($validated['service_id']);
        if ($service) {
            $activeTicket = Ticket::where('user_id', auth()->id())
                ->where('service_id', $service->id)
                ->whereNotIn('status', [TicketStatus::DONE->value, TicketStatus::REJECT->value])
                ->exists();

            if ($activeTicket) {
                return back()
                    ->withInput()
                    ->with('error', "Anda masih memiliki tiket dengan layanan {$service->name} yang sedang aktif (belum selesai). Silakan tunggu tiket tersebut diselesaikan sebelum membuat tiket baru.");
            }
        }

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

        TicketAttachment::whereNull('ticket_id')
            ->where('created_at', '>=', now()->subDay())
            ->select(['id', 'ticket_id', 'path'])
            ->get()
            ->each(function ($attachment) use ($ticket) {
                if (str_contains($ticket->description, $attachment->url)) {
                    $attachment->update(['ticket_id' => $ticket->id]);
                }
            });

        $successMessage = 'Tiket berhasil dibuat. Tim kami akan segera meninjaunya.';
        if (OffHoursHelper::isOutsideWorkingHours()) {
            $successMessage .= ' Pengerjaan tiket akan dilakukan pada hari dan jam kerja operasional (Senin-Kamis: 08.00-16.00 WIB, Jumat: 08.00-16.30 WIB).';
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', $successMessage);
    }

    public function assignMe(Ticket $ticket)
    {
        if (auth()->user()->role === UserRole::USER) {
            return back()->with('error', 'Anda tidak memiliki izin untuk mengambil tiket ini.');
        }

        if ($ticket->assigned_to) {
            return back()->with('error', 'Tiket sudah ditugaskan.');
        }

        $ticket->update([
            'assigned_to' => auth()->id(),
            'assigned_at' => now(),
            'status' => TicketStatus::PROGRESS,
        ]);

        $adminName = auth()->user()->name;

        $title = 'Tiket Diproses';
        $message = "Tiket Anda (*#{$ticket->ticket_code}*) untuk layanan *{$ticket->service->name}* saat ini telah berstatus *Sedang Diproses* dan ditangani oleh petugas kami (*{$adminName}*). Silakan klik tautan di bawah ini untuk memantau perkembangan penanganan tiket Anda.";

        $channels = ['database', 'mail', WhatsAppChannel::class];

        if ($ticket->user) {
            $ticket->user->notify(new SystemNotification(
                $title,
                $message,
                route('tickets.show', $ticket),
                'info',
                $channels
            ));
        } elseif ($ticket->guestDetail) {
            Notification::route('mail', $ticket->guestDetail->email)
                ->route(WhatsAppChannel::class, $ticket->guestDetail->phone)
                ->notify(new SystemNotification(
                    $title,
                    $message,
                    route('guest.tracking.show', $ticket->ticket_code),
                    'info',
                    ['mail', WhatsAppChannel::class]
                ));
        }

        return back()->with('success', 'Tiket berhasil ditugaskan ke Anda.');
    }

    public function updateAssignee(Request $request, Ticket $ticket)
    {
        $request->validate([
            'assigned_to' => [
                'nullable',
                Rule::exists('users', 'id')->whereIn('role', [UserRole::ADMIN->value, UserRole::SUPERUSER->value]),
            ],
        ]);

        $user = auth()->user();

        if (! in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER])) {
            return back()->with('error', 'Hanya Admin dan Superuser yang dapat merubah petugas secara manual.');
        }

        if (! in_array($ticket->status, [TicketStatus::WAITING, TicketStatus::PROGRESS])) {
            return back()->with('error', 'Petugas tidak dapat diubah pada tiket yang sudah ditutup.');
        }

        if ($ticket->assigned_to == $request->assigned_to) {
            return back()->with('info', 'Tidak ada perubahan pada petugas tiket.');
        }

        $ticket->assigned_to = $request->assigned_to;
        $ticket->assigned_at = $request->assigned_to ? now() : null;

        if (is_null($request->assigned_to) && $ticket->status === TicketStatus::PROGRESS) {
            $ticket->status = TicketStatus::WAITING;
        } elseif (! is_null($request->assigned_to) && $ticket->status === TicketStatus::WAITING) {
            $ticket->status = TicketStatus::PROGRESS;
        }

        $ticket->save();

        if ($request->assigned_to) {
            $ticket->load('assignee');

            if ($ticket->assignee && $ticket->assignee->id !== $user->id) {

                $title = 'Penugasan Tiket Baru';
                $message = "Tiket *#{$ticket->ticket_code}* (Layanan: *{$ticket->service->name}*) telah ditugaskan kepada Anda oleh *{$user->name}*. Mohon segera periksa detail tiket melalui tautan di bawah ini dan mulai proses penanganan.";

                $channels = ['database', 'mail', WhatsAppChannel::class];

                $ticket->assignee->notify(new SystemNotification(
                    $title,
                    $message,
                    route('tickets.show', $ticket),
                    'info',
                    $channels
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

        if (! in_array($ticket->status, [TicketStatus::WAITING, TicketStatus::PROGRESS])) {
            return back()->with('error', 'Prioritas tidak dapat diubah pada tiket yang sudah ditutup.');
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
                return back()->with('error', 'Anda tidak memiliki akses untuk mengubah prioritas tiket ini.');
            }
        } else {
            return back()->with('error', 'Anda tidak memiliki hak akses.');
        }

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

        if (! in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER])) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menutup tiket.');
        }

        if (! is_null($ticket->assigned_to) && $ticket->assigned_to !== $user->id) {
            return back()->with('error', 'Anda tidak dapat menutup tiket yang sedang ditugaskan kepada staff lain.');
        }

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

        $statusLabel = $validated['status'] === TicketStatus::DONE->value ? 'Diselesaikan' : 'Ditolak';
        $type = $validated['status'] === TicketStatus::DONE->value ? 'success' : 'error';

        $title = "Tiket {$statusLabel}";
        $message = "Tiket Anda (*#{$ticket->ticket_code}*) terkait layanan *{$ticket->service->name}* telah dinyatakan *{$statusLabel}* oleh petugas kami (*{$user->name}*). Silakan klik tautan di bawah ini untuk melihat detail penyelesaian dan mohon luangkan waktu Anda untuk mengisi survei kepuasan layanan kami.";

        $channels = ['database', 'mail', WhatsAppChannel::class];

        if ($ticket->user) {
            $ticket->user->notify(new SystemNotification(
                $title,
                $message,
                route('tickets.show', $ticket).'#survey-section',
                $type,
                $channels
            ));
        } elseif ($ticket->guestDetail) {
            Notification::route('mail', $ticket->guestDetail->email)
                ->route(WhatsAppChannel::class, $ticket->guestDetail->phone)
                ->notify(new SystemNotification(
                    $title,
                    $message,
                    route('guest.tracking.show', $ticket->ticket_code).'#survey-section',
                    $type,
                    ['mail', WhatsAppChannel::class]
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

    public function waiting(Request $request)
    {
        $user = auth()->user();

        $services = Service::where('is_active', true)
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
            ->get(['id', 'name']);

        $admins = User::whereIn('role', ['admin', 'superuser'])
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
            ->get(['id', 'name', 'avatar_path']);

        $tickets = Ticket::query()
            ->with(['user:id,name,avatar_path', 'service:id,name', 'assignee:id,name,avatar_path', 'guestDetail:id,ticket_id,full_name'])
            ->withCount('comments')
            ->where('status', TicketStatus::WAITING) // ← hard-coded, tidak bisa diubah via filter
            ->when($request->q, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('ticket_code', 'ilike', "%{$request->q}%")
                    ->orWhere('description', 'ilike', "%{$request->q}%");
            }))
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
                $start = $request->start_date ? now()->parse($request->start_date)->startOfDay() : null;
                $end = $request->end_date ? now()->parse($request->end_date)->endOfDay() : null;
                if ($start && $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                } elseif ($start) {
                    $q->where('created_at', '>=', $start);
                } elseif ($end) {
                    $q->where('created_at', '<=', $end);
                }
            })
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 WHEN 'low' THEN 2 ELSE 3 END ASC")
            ->orderBy('created_at', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('tickets.waiting', compact('tickets', 'services', 'admins'));
    }

    public function assigned(Request $request)
    {
        $user = auth()->user();

        $services = Service::where('is_active', true)
            ->orderByRaw("CASE WHEN LOWER(name) = 'lainnya' THEN 1 ELSE 0 END ASC, LOWER(name) ASC")
            ->get(['id', 'name']);

        $tickets = Ticket::query()
            ->with(['user:id,name,avatar_path', 'service:id,name', 'assignee:id,name,avatar_path', 'guestDetail:id,ticket_id,full_name'])
            ->withCount('comments')
            ->where('assigned_to', $user->id) // ← hard-coded ke user login
            ->when($request->q, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('ticket_code', 'ilike', "%{$request->q}%")
                    ->orWhere('description', 'ilike', "%{$request->q}%");
            }))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))
            ->when($request->service, fn ($q) => $q->where('service_id', $request->service))
            ->when($request->start_date || $request->end_date, function ($q) use ($request) {
                $start = $request->start_date ? now()->parse($request->start_date)->startOfDay() : null;
                $end = $request->end_date ? now()->parse($request->end_date)->endOfDay() : null;
                if ($start && $end) {
                    $q->whereBetween('created_at', [$start, $end]);
                } elseif ($start) {
                    $q->where('created_at', '>=', $start);
                } elseif ($end) {
                    $q->where('created_at', '<=', $end);
                }
            })
            ->orderByRaw("
            CASE status
                WHEN 'waiting'  THEN 0
                WHEN 'progress' THEN 1
                WHEN 'done'     THEN 2
                WHEN 'reject'   THEN 3
                ELSE 4
            END ASC
        ")
            ->orderByRaw("
            CASE WHEN status IN ('waiting','progress')
                THEN CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 WHEN 'low' THEN 2 ELSE 3 END
                ELSE 0
            END ASC
        ")
            ->orderBy('created_at', 'asc')
            ->paginate(10)
            ->withQueryString();

        return view('tickets.assigned', compact('tickets', 'services'));
    }
}
