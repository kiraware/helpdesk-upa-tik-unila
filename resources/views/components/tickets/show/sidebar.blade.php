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

<div class="space-y-6" x-data="{ showModal: false, modalImage: '' }">
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

            {{-- Header --}}
            <div class="px-4 py-3 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50">
                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-light">
                    Detail Pelapor
                </h3>
            </div>

            <div class="p-4">
                {{-- Profile Section --}}
                <div class="flex items-center gap-3 mb-4">
                    {{-- Avatar --}}
                    <div class="shrink-0">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->guestDetail->full_name) }}"
                            class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-sm border border-border-light dark:border-border-dark shadow-sm object-cover">
                    </div>

                    {{-- Name --}}
                    <div class="flex flex-col min-w-0">
                        <span
                            class="text-sm font-bold text-text-light dark:text-text-dark wrap-break-word leading-tight">
                            {{ $ticket->guestDetail->full_name }}
                        </span>
                    </div>
                </div>

                {{-- Fields Detail --}}
                <div class="space-y-4 text-xs">
                    {{-- Email --}}
                    <div>
                        <p class="text-muted-light dark:text-muted-dark font-medium mb-0.5">Email</p>
                        <p class="text-text-light dark:text-text-dark font-medium break-all"
                            title="{{ $ticket->guestDetail->email }}">
                            {{ $ticket->guestDetail->email }}
                        </p>
                    </div>

                    {{-- ID Number --}}
                    <div>
                        <p class="text-muted-light dark:text-muted-dark font-medium mb-0.5">Nomor ID</p>
                        <p class="text-text-light dark:text-text-dark font-medium wrap-break-word font-mono">
                            {{ $ticket->guestDetail->identity_number }}
                        </p>
                    </div>

                    {{-- Entity / Identity --}}
                    <div>
                        <p class="text-muted-light dark:text-muted-dark font-medium mb-0.5">Identitas</p>
                        <p class="text-text-light dark:text-text-dark font-medium wrap-break-word">
                            {{ strtoupper($ticket->guestDetail->entity_type->value) }}
                        </p>
                    </div>
                </div>

                {{-- Action Buttons (Identity/Selfie) --}}
                <div class="mt-6 flex flex-row md:flex-col gap-2">
                    @if ($ticket->guestDetail->photo_identity_path)
                        {{-- Trigger Modal --}}
                        <button type="button"
                            @click="modalImage = '{{ asset('storage/' . $ticket->guestDetail->photo_identity_path) }}'; showModal = true"
                            class="w-full flex items-center justify-center md:justify-start py-2 px-3 bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-slate-700 border border-border-light dark:border-border-dark rounded-lg text-secondary hover:text-blue-700 transition-colors text-xs font-medium group cursor-pointer">
                            <span
                                class="material-icons-round text-sm mr-2 text-muted-light dark:text-muted-dark group-hover:text-secondary transition-colors">badge</span>
                            Kartu Identitas
                        </button>
                    @endif

                    @if ($ticket->guestDetail->photo_selfie_path)
                        {{-- Trigger Modal --}}
                        <button type="button"
                            @click="modalImage = '{{ asset('storage/' . $ticket->guestDetail->photo_selfie_path) }}'; showModal = true"
                            class="w-full flex items-center justify-center md:justify-start py-2 px-3 bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-slate-700 border border-border-light dark:border-border-dark rounded-lg text-secondary hover:text-blue-700 transition-colors text-xs font-medium group cursor-pointer">
                            <span
                                class="material-icons-round text-sm mr-2 text-muted-light dark:text-muted-dark group-hover:text-secondary transition-colors">face</span>
                            Selfie
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- MODAL PREVIEW GAMBAR --}}
    <div x-show="showModal" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        {{-- Close on click outside --}}
        <div class="absolute inset-0" @click="showModal = false"></div>

        <div
            class="relative bg-white dark:bg-surface-dark rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            {{-- Modal Header --}}
            <div class="flex justify-between items-center p-4 border-b border-border-light dark:border-border-dark">
                <h3 class="font-bold text-text-light dark:text-text-dark">Preview Gambar</h3>
                <button @click="showModal = false" class="text-muted-light hover:text-red-500 transition-colors">
                    <span class="material-icons-round">close</span>
                </button>
            </div>

            {{-- Modal Image Content --}}
            <div class="p-2 flex items-center justify-center bg-gray-100 dark:bg-slate-900 overflow-auto h-full">
                <img :src="modalImage" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-sm">
            </div>
        </div>
    </div>
</div>
