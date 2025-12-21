<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $tickets = Ticket::query()
            ->with([
                'user',
                'service',
                'assignee',
                'guestDetail',
            ])

            ->withCount('comments')

            ->when($request->q, fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('ticket_code', 'like', "%{$request->q}%")
                    ->orWhere('title', 'like', "%{$request->q}%")
                    ->orWhere('description', 'like', "%{$request->q}%");
            })
            )

            ->when($request->status, fn ($q) => $q->where('status', $request->status)
            )

            ->when($request->priority, fn ($q) => $q->where('priority', $request->priority)
            )

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

        return view('tickets.index', compact('tickets', 'admins'));
    }

    public function show(Ticket $ticket)
    {
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
        $validated = $request->validate(['name' => 'required|string|max:255|unique:tickets']);

        Ticket::create(['name' => $validated['name']]);

        return redirect()->route('tickets.index')->with('success', 'Tiket berhasil ditambahkan.');
    }

    public function update(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:100',
                Rule::unique('tickets', 'title')->ignore($ticket->id),
            ],
        ]);

        $ticket->update(['title' => $validated['title']]);

        return redirect()->route('tickets.show', $ticket->uuid)
            ->with('success', 'Judul tiket berhasil diperbarui.');
    }

    public function assignMe(Ticket $ticket)
    {
        // Cegah overwrite
        if ($ticket->assigned_to) {
            return back()->with('error', 'Ticket sudah ditugaskan.');
        }

        $ticket->update([
            'assigned_to' => auth()->id(),
            'assigned_at' => now(),
        ]);

        return back()->with('success', 'Ticket berhasil ditugaskan ke Anda.');
    }

    public function storeComment(Request $request, Ticket $ticket)
    {
        $request->validate([
            'message' => 'required|string',
            'attachments.*' => 'nullable|file|max:20480|mimes:jpg,jpeg,png,pdf,doc,docx,zip', // Max 20MB
        ]);

        DB::transaction(function () use ($request, $ticket) {
            // 1. Simpan Komentar
            $comment = $ticket->comments()->create([
                'user_id' => auth()->id(),
                'message' => $request->message,
            ]);

            // 2. Simpan Attachment (jika ada)
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
}
