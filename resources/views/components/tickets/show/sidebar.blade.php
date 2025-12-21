@props(['ticket'])

@php
    $isClosed = in_array($ticket->status, [\App\Enums\TicketStatus::DONE, \App\Enums\TicketStatus::REJECT]);
    $prioColor = match ($ticket->priority) {
        \App\Enums\TicketPriority::HIGH => 'text-red-600',
        \App\Enums\TicketPriority::MEDIUM => 'text-yellow-600',
        \App\Enums\TicketPriority::LOW => 'text-gray-600',
        \App\Enums\TicketPriority::DEFAULT => 'text-gray-600', // Fallback
    };
@endphp

<div class="space-y-6">
    {{-- ASSIGNEE --}}
    <div
        class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm">
        <div
            class="px-4 py-3 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50 flex justify-between items-center">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-light">Petugas</h3>
            @if (is_null($ticket->assigned_to) && !$isClosed)
                <form method="POST" action="{{ route('tickets.assign.me', $ticket->uuid) }}">
                    @csrf
                    <button type="submit" class="text-xs text-secondary hover:underline">Ambil Tiket</button>
                </form>
            @endif
        </div>
        <div class="p-4">
            @if ($ticket->assignee)
                <div class="flex items-center gap-3">
                    <img src="{{ $ticket->assignee->photo ? asset('storage/' . $ticket->assignee->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($ticket->assignee->name) }}"
                        class="w-8 h-8 rounded-full">
                    <div>
                        <p class="text-sm font-medium text-text-light dark:text-text-dark">{{ $ticket->assignee->name }}
                        </p>
                        <p class="text-xs text-muted-light">Ditugaskan
                            {{ $ticket->assigned_at ? $ticket->assigned_at->diffForHumans() : '-' }}</p>
                    </div>
                </div>
            @else
                <div class="text-sm text-muted-light italic">Belum ada petugas</div>
            @endif
        </div>
    </div>

    {{-- INFO --}}
    <div
        class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm">
        <div class="px-4 py-3 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-light">Informasi</h3>
        </div>
        <div class="p-4 space-y-4">
            <div>
                <p class="text-xs text-muted-light mb-1">Layanan</p>
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                    {{ $ticket->service->name }}
                </span>
            </div>
            <div>
                <p class="text-xs text-muted-light mb-1">Prioritas</p>
                <div class="flex items-center gap-1 text-sm font-medium {{ $prioColor }}">
                    <span class="material-icons-round text-base">flag</span>
                    {{ ucfirst($ticket->priority->value) }}
                </div>
            </div>
        </div>
    </div>

    {{-- GUEST DETAILS --}}
    @if (!$ticket->user_id && $ticket->guestDetail)
        <div
            class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50">
                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-light">Detail Pelapor</h3>
            </div>
            <div class="p-4">
                <div class="flex items-center gap-3 mb-4">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->guestDetail->full_name) }}"
                        class="w-10 h-10 rounded-full border border-border-light dark:border-border-dark">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-text-light dark:text-text-dark truncate"
                            title="{{ $ticket->guestDetail->full_name }}">{{ $ticket->guestDetail->full_name }}</p>
                        <p class="text-xs text-muted-light truncate" title="{{ $ticket->guestDetail->email }}">
                            {{ $ticket->guestDetail->email }}</p>
                    </div>
                </div>
                <div class="space-y-3 text-sm">
                    <div
                        class="flex justify-between items-center py-2 border-t border-dashed border-border-light dark:border-border-dark">
                        <span class="text-muted-light text-xs">Identitas</span>
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">{{ strtoupper($ticket->guestDetail->entity_type->value) }}</span>
                    </div>
                    <div
                        class="flex justify-between items-center py-2 border-t border-dashed border-border-light dark:border-border-dark">
                        <span class="text-muted-light text-xs">Nomor ID</span>
                        <span
                            class="font-mono text-text-light dark:text-text-dark font-medium">{{ $ticket->guestDetail->identity_number }}</span>
                    </div>
                </div>
                <div class="mt-4 pt-2 border-t border-border-light dark:border-border-dark">
                    <p class="text-xs font-semibold text-muted-light mb-2">Lampiran Identitas</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach ([['path' => $ticket->guestDetail->photo_identity_path, 'label' => 'KTP', 'icon' => 'visibility'], ['path' => $ticket->guestDetail->photo_selfie_path, 'label' => 'Selfie', 'icon' => 'face']] as $doc)
                            <a href="{{ asset('storage/' . $doc['path']) }}" target="_blank"
                                class="relative group block aspect-4/3 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 border border-border-light dark:border-border-dark">
                                <img src="{{ asset('storage/' . $doc['path']) }}"
                                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110">
                                <div
                                    class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white">
                                    <span class="material-icons-round text-xl mb-1">{{ $doc['icon'] }}</span>
                                    <span
                                        class="text-[10px] uppercase font-bold tracking-wider">{{ $doc['label'] }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
