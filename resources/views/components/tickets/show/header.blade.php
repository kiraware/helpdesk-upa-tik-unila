@props(['ticket'])

@php
    $statusColors = match ($ticket->status) {
        \App\Enums\TicketStatus::WAITING => [
            'bg' => 'bg-yellow-100 dark:bg-yellow-900/30',
            'text' => 'text-yellow-700 dark:text-yellow-400',
            'icon' => 'radio_button_checked',
        ],
        \App\Enums\TicketStatus::PROGRESS => [
            'bg' => 'bg-blue-100 dark:bg-blue-900/30',
            'text' => 'text-blue-700 dark:text-blue-400',
            'icon' => 'adjust',
        ],
        \App\Enums\TicketStatus::DONE => [
            'bg' => 'bg-emerald-100 dark:bg-emerald-900/30',
            'text' => 'text-emerald-700 dark:text-emerald-400',
            'icon' => 'check_circle',
        ],
        \App\Enums\TicketStatus::REJECT => [
            'bg' => 'bg-red-100 dark:bg-red-900/30',
            'text' => 'text-red-700 dark:text-red-400',
            'icon' => 'cancel',
        ],
    };

    $creatorName = $ticket->user
        ? $ticket->user->name
        : ($ticket->guestDetail
            ? $ticket->guestDetail->full_name
            : 'Guest');

    $isClosed = in_array($ticket->status, [\App\Enums\TicketStatus::DONE, \App\Enums\TicketStatus::REJECT]);
    $isGuestTicket = is_null($ticket->user_id);
    $currentUser = auth()->user();

    // Logic Edit Title
    $canEditTitle = false;
    if (!$isClosed && $currentUser) {
        // Cek apakah user yang login adalah pembuat tiket
        $isOwner = $currentUser->id === $ticket->user_id;

        // Cek apakah user yang login memiliki role Admin atau Superuser
        $isStaff = in_array($currentUser->role, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::SUPERUSER]);

        // Bisa edit jika dia pemilik tiket ATAU dia adalah staff (Admin/Superuser)
        $canEditTitle = $isOwner || $isStaff;
    }

    // Logic Close Ticket (Hanya Staff yang di-assign atau jika tiket belum ada petugasnya)
    $canCloseTicket =
        $currentUser &&
        in_array($currentUser->role, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::SUPERUSER]) &&
        !$isClosed &&
        ($ticket->assigned_to === $currentUser->id || is_null($ticket->assigned_to));
@endphp

<div class="border-b border-border-light dark:border-border-dark pb-6 mb-6" x-data="{
    isEditing: false,
    focusInput() { $nextTick(() => { $refs.titleInput.focus() }); }
}">
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">

        {{-- Title & Meta --}}
        <div class="flex-1 min-w-0 w-full">
            <form id="update-title-form" action="{{ route('tickets.update_title', $ticket) }}" method="POST"
                class="min-w-0 w-full">
                @csrf @method('PATCH')
                <div class="mb-2 min-h-10 flex items-center w-full">
                    <h1 x-show="!isEditing"
                        class="text-2xl sm:text-3xl font-bold text-text-light dark:text-text-dark leading-tight break-all wrap-break-word whitespace-normal w-full">
                        {{ $ticket->title }}
                        <span
                            class="text-xl sm:text-2xl font-light text-muted-light dark:text-muted-dark ml-2 inline-block whitespace-nowrap">
                            #{{ $ticket->ticket_code }}
                        </span>
                    </h1>
                    @if ($canEditTitle)
                        <div x-show="isEditing" class="w-full flex items-center gap-2" x-cloak>
                            <input x-ref="titleInput" type="text" name="title"
                                value="{{ old('title', $ticket->title) }}"
                                class="w-full px-3 py-2 text-sm font-normal rounded-lg border border-secondary/50 focus:border-secondary focus:ring-2 focus:ring-secondary/20 bg-white dark:bg-slate-800 text-text-light dark:text-text-dark transition-all"
                                required>
                        </div>
                    @endif
                </div>
            </form>

            {{-- Meta Pills --}}
            <div class="flex flex-wrap items-center gap-3 text-sm text-muted-light dark:text-muted-dark mt-1">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium {{ $statusColors['bg'] }} {{ $statusColors['text'] }} border border-transparent">
                    <span class="material-icons-round text-base">{{ $statusColors['icon'] }}</span>
                    {{ ucfirst($ticket->status->value) }}
                </span>
                <span class="hidden sm:inline">&bull;</span>
                <span class="flex flex-wrap items-center gap-1">
                    <span class="font-semibold text-text-light dark:text-text-dark break-all">
                        {{ $creatorName }}
                    </span>
                    <span class="hidden sm:inline whitespace-nowrap">membuka tiket ini</span>
                    <span class="whitespace-nowrap">{{ $ticket->created_at->diffForHumans() }}</span>
                </span>
                <span class="hidden sm:inline">&bull;</span>
                <span class="whitespace-nowrap">{{ $ticket->comments->count() }} komentar</span>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2 shrink-0 w-full md:w-auto mb-2 md:mb-0 order-first md:order-last">

            {{-- VIEW MODE BUTTONS --}}
            <div x-show="!isEditing" class="flex flex-wrap items-center gap-2 w-full md:w-auto">

                {{-- 1. Tombol Edit --}}
                @if ($canEditTitle)
                    <button type="button" @click="isEditing = true; focusInput()"
                        class="flex-1 md:flex-none px-4 py-2 bg-secondary hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center justify-center whitespace-nowrap">
                        Edit
                    </button>
                @endif

                {{-- 2. Tombol Close Ticket --}}
                @if ($canCloseTicket)
                    <div class="relative flex-1 md:flex-none" x-data="{ open: false }">
                        <button type="button" @click="open = !open" @click.outside="open = false"
                            class="w-full md:w-auto flex items-center justify-center gap-1 px-4 py-2 bg-white dark:bg-surface-dark hover:bg-gray-50 dark:hover:bg-slate-700 border border-border-light dark:border-border-dark text-text-light dark:text-text-dark text-sm font-medium rounded-lg shadow-sm transition-colors whitespace-nowrap">
                            <span>Tutup Tiket</span>
                            <span class="material-icons-round text-base">expand_more</span>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open" x-transition x-cloak
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-xl z-50 overflow-hidden">

                            {{-- Option: Selesai --}}
                            <form action="{{ route('tickets.close', $ticket) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="done">
                                <button type="submit"
                                    class="w-full text-left px-4 py-3 text-sm text-text-light dark:text-text-dark hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors">
                                    <span class="material-icons-round text-emerald-600">check_circle</span>
                                    Selesai
                                </button>
                            </form>

                            <div class="border-t border-border-light dark:border-border-dark"></div>

                            {{-- Option: Tolak --}}
                            <form action="{{ route('tickets.close', $ticket) }}" method="POST">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="reject">
                                <button type="submit"
                                    class="w-full text-left px-4 py-3 text-sm text-text-light dark:text-text-dark hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors">
                                    <span class="material-icons-round text-red-600">cancel</span>
                                    Tolak
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

            </div>

            {{-- EDIT MODE BUTTONS --}}
            @if ($canEditTitle)
                <div x-show="isEditing" class="flex items-center gap-2 w-full md:w-auto" x-cloak>
                    <button type="button" @click="isEditing = false"
                        class="flex-1 md:flex-none px-4 py-2 text-sm font-medium text-center text-red-600 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:hover:bg-red-900/30 rounded-lg transition-colors border border-transparent">
                        Batal
                    </button>
                    <button type="submit" form="update-title-form"
                        class="flex-1 md:flex-none px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center justify-center">
                        Simpan
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
