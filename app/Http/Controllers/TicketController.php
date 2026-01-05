<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Enums\UserRole;
use App\Models\Configuration;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        $services = Service::where('is_active', true)
            ->orderBy('name')
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
                $qq->where('ticket_code', 'like', "%{$request->q}%")
                    ->orWhere('title', 'like', "%{$request->q}%")
                    ->orWhere('description', 'like', "%{$request->q}%");
            }))

            ->when($request->status, fn ($q) => $q->where('status', $request->status))

            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority))

            ->when($request->service_id, fn ($q) => $q->where('service_id', $request->service_id))

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
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('tickets.index', compact('tickets', 'admins', 'services'));
    }

    public function create()
    {
        // Hanya 'user' yang boleh akses
        if (auth()->user()->role !== UserRole::USER) {
            abort(403, 'Hanya pengguna (User) yang dapat membuat tiket baru.');
        }

        // Ambil data Service untuk dropdown
        $services = Service::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('tickets.create', compact('services'));
    }

    public function show(Ticket $ticket)
    {
        // Mencegah User A mengakses URL tiket milik User B secara manual
        $user = auth()->user();
        if ($user->role === UserRole::USER) {
            // Jika tiket ini bukan punya dia, tampilkan 403 Forbidden
            if ($ticket->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki akses ke tiket ini.');
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

        return view('tickets.show', compact('ticket', 'admins'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->role !== UserRole::USER) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'service_id' => [
                'required',
                Rule::exists('services', 'id')->where(function ($query) {
                    return $query->where('is_active', true);
                }),
            ],
            'priority' => ['required', new Enum(\App\Enums\TicketPriority::class)],
            'title' => 'required|string|max:100',
            'description' => 'required|string',
        ]);

        DB::transaction(function () use ($validated) {
            $ticket = Ticket::create([
                'user_id' => auth()->id(),
                'service_id' => $validated['service_id'],
                'priority' => $validated['priority'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'status' => TicketStatus::WAITING,
            ]);
        });

        return redirect()->route('tickets.index')
            ->with('success', 'Tiket berhasil dibuat. Tim kami akan segera meninjaunya.');
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate(['title' => ['required', 'string', 'max:100']]);

        $ticket->update(['title' => $validated['title']]);

        return redirect()->route('tickets.show', $ticket->uuid)
            ->with('success', 'Judul tiket berhasil diperbarui.');
    }

    public function assignMe(Ticket $ticket)
    {
        // Pastikan User Biasa tidak bisa mengakses fungsi ini
        if (auth()->user()->role === \App\Enums\UserRole::USER) {
            abort(403, 'Anda tidak memiliki izin untuk mengambil tiket ini.');
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

        return back()->with('success', 'Tiket berhasil ditugaskan ke Anda.');
    }

    /**
     * Menutup tiket (Selesai / Tolak).
     * Jika belum ada petugas, otomatis assign ke yang menutup.
     */
    public function close(Request $request, Ticket $ticket)
    {
        $user = auth()->user();

        // 1. Cek Permission (Hanya Admin/Superuser)
        if (! in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER])) {
            abort(403, 'Anda tidak memiliki izin untuk menutup tiket.');
        }

        // 2. Validasi Input Status (Hanya boleh DONE atau REJECT)
        $validated = $request->validate([
            'status' => ['required', new Enum(TicketStatus::class)],
        ]);

        // Pastikan status yang dikirim valid untuk penutupan
        if (! in_array($validated['status'], [TicketStatus::DONE->value, TicketStatus::REJECT->value])) {
            return back()->with('error', 'Status tidak valid untuk penutupan tiket.');
        }

        DB::transaction(function () use ($ticket, $validated, $user) {
            $updateData = [
                'status' => $validated['status'],
                'closed_at' => now(),
            ];

            // 3. Auto-assign jika belum ada petugas
            if (is_null($ticket->assigned_to)) {
                $updateData['assigned_to'] = $user->id;
                $updateData['assigned_at'] = now();
            }

            $ticket->update($updateData);
        });

        $statusLabel = $validated['status'] === TicketStatus::DONE->value ? 'diselesaikan' : 'ditolak';

        return back()->with('success', "Tiket berhasil {$statusLabel}.");
    }

    /**
     * Generate PDF Surat Tugas
     */
    public function printAssignment(Ticket $ticket)
    {
        // 1. Cek Permission
        $user = auth()->user();
        $isStaff = in_array($user->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

        // Hanya Admin/Superuser atau Petugas ybs yang boleh cetak
        if (! $isStaff && $ticket->assigned_to !== $user->id) {
            abort(403, 'Anda tidak memiliki izin untuk mencetak surat tugas ini.');
        }

        // 2. Pastikan tiket sudah ada petugasnya
        if (! $ticket->assigned_to) {
            return back()->with('error', 'Tiket belum memiliki petugas.');
        }

        $ticket->load([
            'assignee.division',
            'service',
            'user',
            'guestDetail',
        ]);

        // 3. Data Kepala UPA TIK
        $config = Configuration::first();
        $kepalaUpa = [
            'name' => $config->upa_head_name,
            'nip' => $config->upa_head_nip,
            'jabatan' => $config->upa_head_position,
        ];

        // 4. Generate PDF
        $pdf = Pdf::loadView('tickets.pdf.assignment_letter', compact('ticket', 'kepalaUpa'))
            ->setPaper('a4', 'portrait');

        // Stream (tampil di browser) atau Download
        return $pdf->stream('Surat_Tugas_'.$ticket->ticket_code.'.pdf');
    }
}
