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

    // 1. Cek apakah tiket sudah ditutup
    $isClosed = in_array($ticket->status, [\App\Enums\TicketStatus::DONE, \App\Enums\TicketStatus::REJECT]);

    // 2. Cek apakah ini tiket buatan Guest (user_id null)
    $isGuestTicket = is_null($ticket->user_id);

    // 3. Ambil user yang sedang login (bisa null jika Guest)
    $currentUser = auth()->user();

    // LOGIKA PENENTUAN HAK EDIT JUDUL
    $canEditTitle = false;

    if (!$isClosed) {
        if ($currentUser) {
            // Jika User Login
            if ($currentUser->role === \App\Enums\UserRole::USER) {
                // Role User: HANYA boleh edit jika ini BUKAN tiket guest (artinya tiket miliknya sendiri)
                $canEditTitle = !$isGuestTicket;
            } else {
                // Role Admin/Superuser: Boleh edit semua
                $canEditTitle = true;
            }
        } else {
            // Jika Guest (Tidak Login): Tidak boleh edit
            $canEditTitle = false;
        }
    }
@endphp

<div class="border-b border-border-light dark:border-border-dark pb-6 mb-6" x-data="{
    isEditing: false,
    focusInput() { $nextTick(() => { $refs.titleInput.focus() }); }
}">
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">

        {{-- Title & Meta --}}
        <div class="flex-1 min-w-0 w-full">

            <form id="update-title-form" action="{{ route('tickets.update', $ticket->uuid) }}" method="POST"
                class="min-w-0 w-full">
                @csrf
                @method('PUT')
                <div class="mb-2 min-h-10 flex items-center w-full">
                    {{-- MODE BACA --}}
                    <h1 x-show="!isEditing"
                        class="text-2xl sm:text-3xl font-bold text-text-light dark:text-text-dark leading-tight break-all wrap-break-word w-full">
                        {{ $ticket->title }}
                        <span
                            class="text-xl sm:text-2xl font-light text-muted-light dark:text-muted-dark ml-2 inline-block whitespace-nowrap">
                            #{{ $ticket->ticket_code }}
                        </span>
                    </h1>

                    {{-- MODE EDIT --}}
                    {{-- Hanya render input jika diperbolehkan edit --}}
                    @if ($canEditTitle)
                        <div x-show="isEditing" class="w-full flex items-center gap-2" x-cloak>
                            {{-- Input Judul --}}
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

                {{-- Nama User --}}
                <span class="flex flex-wrap items-center gap-1">
                    <span class="font-semibold text-text-light dark:text-text-dark break-all whitespace-normal">
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
        <div class="flex items-center gap-2 shrink-0 w-full md:w-auto mb-2 md:mb-0 order-first md:order-last">

            {{-- VIEW MODE BUTTONS --}}
            <div x-show="!isEditing" class="flex items-center gap-2 w-full md:w-auto">

                {{-- Tombol EDIT: Gunakan variabel $canEditTitle yang sudah kita buat --}}
                @if ($canEditTitle)
                    <button type="button" @click="isEditing = true; focusInput()"
                        class="flex-1 md:flex-none px-4 py-2 bg-secondary hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center justify-center">
                        Edit
                    </button>
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
