@props(['ticket'])

@php
    $statusColor = match ($ticket->status) {
        \App\Enums\TicketStatus::WAITING => 'text-yellow-600',
        \App\Enums\TicketStatus::PROGRESS => 'text-blue-600',
        \App\Enums\TicketStatus::DONE => 'text-emerald-600',
        \App\Enums\TicketStatus::REJECT => 'text-red-600',
    };

    $statusIcon = match ($ticket->status) {
        \App\Enums\TicketStatus::WAITING => 'radio_button_checked',
        \App\Enums\TicketStatus::PROGRESS => 'adjust',
        \App\Enums\TicketStatus::DONE => 'check_circle',
        \App\Enums\TicketStatus::REJECT => 'cancel',
    };

    $priorityIndo = match ($ticket->priority) {
        \App\Enums\TicketPriority::HIGH => 'Tinggi',
        \App\Enums\TicketPriority::MEDIUM => 'Sedang',
        \App\Enums\TicketPriority::LOW => 'Rendah',
        default => 'Normal',
    };

    $isClosed = in_array($ticket->status, [\App\Enums\TicketStatus::DONE, \App\Enums\TicketStatus::REJECT]);

    $actionVerb = match ($ticket->status) {
        \App\Enums\TicketStatus::WAITING, \App\Enums\TicketStatus::PROGRESS => 'membuka',
        \App\Enums\TicketStatus::DONE => 'selesai',
        \App\Enums\TicketStatus::REJECT => 'ditutup',
    };

    $timestamp = $isClosed ? $ticket->updated_at : $ticket->created_at;

    $displayName = $ticket->user
        ? $ticket->user->name
        : ($ticket->guestDetail
            ? $ticket->guestDetail->full_name
            : 'Pengguna');
@endphp

<div
    class="p-3 sm:px-4 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors flex items-start gap-3 group cursor-pointer relative border-b border-border-light dark:border-border-dark last:border-0">

    <div class="pt-0.5 shrink-0 relative z-10 pointer-events-none">
        <span class="material-icons-round text-[20px] {{ $statusColor }}" title="{{ $ticket->status->value }}">
            {{ $statusIcon }}
        </span>
    </div>

    <div class="grow min-w-0">

        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1 mb-1">
            <a href="{{ route('tickets.show', $ticket) }}"
                class="text-[15px] font-medium text-gray-900 dark:text-slate-100 hover:text-blue-600 dark:hover:text-blue-400 transition-colors leading-snug line-clamp-2 wrap-break-word before:absolute before:inset-0 before:z-0"
                title="Lihat Detail Tiket">
                {{ Str::limit(strip_tags($ticket->description), 100, '...') }}
            </a>

            <div class="flex flex-wrap gap-1 shrink-0 relative z-10 pointer-events-none">
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300 border border-transparent whitespace-nowrap">
                    {{ $ticket->service->name }}
                </span>
                <span
                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border border-transparent whitespace-nowrap
                    {{ $ticket->priority === \App\Enums\TicketPriority::HIGH ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                    {{ $ticket->priority === \App\Enums\TicketPriority::MEDIUM ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                    {{ $ticket->priority === \App\Enums\TicketPriority::LOW ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                    {{ $priorityIndo }}
                </span>
            </div>
        </div>

        <div
            class="text-xs text-muted-light dark:text-slate-400 leading-relaxed flex flex-wrap gap-1 items-center relative z-10 pointer-events-none">
            <span class="font-mono text-gray-500 shrink-0">#{{ $ticket->ticket_code }}</span>
            <span class="text-gray-400 shrink-0">·</span>

            <span class="font-medium text-gray-900 dark:text-slate-200 truncate max-w-25 sm:max-w-none align-bottom"
                title="{{ $displayName }}">
                {{ $displayName }}
            </span>

            <span class="text-muted-light dark:text-slate-500 shrink-0">
                {{ $actionVerb }} {{ $timestamp->diffForHumans() }}
            </span>
        </div>
    </div>

    <div class="shrink-0 flex items-center gap-3 self-start mt-0.5 pl-2 relative z-10">
        @if (is_null($ticket->assigned_to) && auth()->user()->role !== \App\Enums\UserRole::USER)
            <form method="POST" action="{{ route('tickets.assign.me', $ticket) }}">
                @csrf
                <button type="submit"
                    class="px-2.5 py-1 text-xs font-medium rounded-md shadow-sm bg-white border border-border-light text-text-light hover:border-secondary hover:text-secondary hover:bg-gray-50 dark:bg-slate-800 dark:border-slate-600 dark:text-slate-200 dark:hover:border-blue-400 transition-all whitespace-nowrap cursor-pointer relative z-20">
                    Ambil
                </button>
            </form>
        @elseif (!is_null($ticket->assigned_to))
            <div class="hidden sm:flex items-center" title="Ditugaskan ke {{ $ticket->assignee->name }}">
                <img src="{{ $ticket->assignee->avatar_path ? asset('storage/' . $ticket->assignee->avatar_path) : 'https://ui-avatars.com/api/?name=' . urlencode($ticket->assignee->name) }}"
                    alt="{{ $ticket->assignee->name }}"
                    class="w-6 h-6 rounded-full object-cover border border-border-light dark:border-slate-600 shadow-sm pointer-events-none" />
            </div>
        @endif

        @if ($ticket->comments_count > 0)
            <div
                class="flex items-center text-muted-light dark:text-slate-400 hover:text-secondary transition-colors pointer-events-none">
                <span class="material-icons-round text-[16px] mr-0.5">chat_bubble_outline</span>
                <span class="text-xs font-medium">{{ $ticket->comments_count }}</span>
            </div>
        @endif
    </div>
</div>
