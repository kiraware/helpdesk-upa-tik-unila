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
@endphp

<div class="border-b border-border-light dark:border-border-dark pb-6 mb-6" x-data="{
    isEditing: false,
    focusInput() { $nextTick(() => { $refs.titleInput.focus() }); }
}">
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
        {{-- Title & Meta --}}
        <div class="flex-1 min-w-0">
            <form id="update-title-form" action="{{ route('tickets.update', $ticket->uuid) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-2 min-h-[40px] flex items-center">
                    {{-- MODE BACA --}}
                    <h1 x-show="!isEditing"
                        class="text-2xl sm:text-3xl font-bold text-text-light dark:text-text-dark leading-tight wrap-break-word">
                        {{ $ticket->title }}
                        <span
                            class="text-xl sm:text-2xl font-light text-muted-light dark:text-muted-dark ml-2 inline-block whitespace-nowrap">
                            #{{ $ticket->ticket_code }}
                        </span>
                    </h1>
                    {{-- MODE EDIT --}}
                    <div x-show="isEditing" class="w-full flex items-center gap-2" x-cloak>
                        <input x-ref="titleInput" type="text" name="title"
                            value="{{ old('title', $ticket->title) }}"
                            class="w-full px-3 py-2 text-sm font-normal rounded-lg border border-secondary/50 focus:border-secondary focus:ring-2 focus:ring-secondary/20 bg-white dark:bg-slate-800 text-text-light dark:text-text-dark transition-all"
                            required>
                        <span class="text-sm font-normal text-muted-light whitespace-nowrap">
                            #{{ $ticket->ticket_code }}
                        </span>
                    </div>
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
                <span class="flex items-center gap-1 truncate">
                    <span class="font-semibold text-text-light dark:text-text-dark">{{ $creatorName }}</span>
                    <span class="hidden sm:inline">membuka tiket ini</span>
                    <span>{{ $ticket->created_at->diffForHumans() }}</span>
                </span>
                <span class="hidden sm:inline">&bull;</span>
                <span>{{ $ticket->comments->count() }} komentar</span>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-2 shrink-0 self-start md:self-auto min-w-[180px] justify-end">
            <div x-show="!isEditing" class="flex items-center gap-2">
                <a href="{{ route('tickets.index') }}"
                    class="px-3 py-2 text-sm font-medium text-muted-light hover:text-text-light dark:text-muted-dark dark:hover:text-text-dark transition-colors">
                    Kembali
                </a>
                @if (!$isClosed)
                    <button type="button" @click="isEditing = true; focusInput()"
                        class="px-4 py-2 bg-secondary hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center justify-center">
                        Edit
                    </button>
                @endif
            </div>
            <div x-show="isEditing" class="flex items-center gap-2" x-cloak>
                <button type="button" @click="isEditing = false"
                    class="px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition-colors">
                    Batal
                </button>
                <button type="submit" form="update-title-form"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors flex items-center justify-center">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>
