<x-layouts.dashboard title="Manajemen Ticket">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Ticket Masuk
            </h1>
            <p class="text-sm text-muted-light dark:text-muted-dark">
                Daftar laporan dan permintaan pengguna
            </p>
        </div>
    </div>

    {{-- Search & Filter --}}
    <form method="GET" action="{{ route('tickets.index') }}" class="mb-6 space-y-3">

        {{-- Row 1: Search + Status + Priority --}}
        <div class="flex flex-col lg:flex-row gap-3">

            {{-- Search --}}
            <div class="relative flex-1">
                <span
                    class="absolute left-3 top-1/2 -translate-y-1/2
               material-icons-round text-base text-muted-light">
                    search
                </span>

                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Cari kode tiket / isi laporan..."
                    class="h-11 w-full pl-10 pr-4
            rounded-lg border border-border-light dark:border-border-dark
            bg-surface-light dark:bg-slate-800
            text-sm text-text-light dark:text-text-dark
            placeholder-muted-light dark:placeholder-muted-dark
            focus:ring-1 focus:ring-secondary focus:border-secondary
            shadow-sm">
            </div>

            {{-- Status --}}
            <div class="relative w-full sm:w-[180px]" x-data="{ open: false }">

                {{-- Trigger --}}
                <button type="button" @click="open = !open"
                    class="h-10 w-full flex items-center justify-between px-4
                    border border-border-light dark:border-border-dark
                    rounded-lg
                    bg-surface-light dark:bg-slate-800
                    text-sm text-text-light dark:text-text-dark
                    shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700/50">

                    <span class="flex items-center gap-2">
                        <span class="material-icons-round text-base text-muted-light">flag</span>

                        @php
                            $statusLabel = match (request('status')) {
                                'waiting' => 'Waiting',
                                'progress' => 'Progress',
                                'done' => 'Done',
                                'reject' => 'Reject',
                                default => 'Semua Status',
                            };
                        @endphp

                        {{ $statusLabel }}
                    </span>

                    <span class="material-icons-round text-base text-muted-light">expand_more</span>
                </button>

                {{-- Dropdown --}}
                <div x-show="open" x-transition x-cloak @click.outside="open = false"
                    class="absolute z-20 mt-2 w-full
                    rounded-xl overflow-hidden shadow-xl
                    border border-border-light dark:border-slate-700
                    bg-white/90 dark:bg-slate-800/90
                    backdrop-blur-md backdrop-saturate-150">

                    {{-- Semua --}}
                    <button type="submit" name="status" value=""
                        class="w-full text-left px-4 py-2.5 text-sm
                        hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                        {{ request('status') === null ? 'font-semibold text-secondary' : '' }}">
                        Semua Status
                    </button>

                    <div class="h-px bg-border-light dark:bg-slate-700/70"></div>

                    {{-- Waiting --}}
                    <button type="submit" name="status" value="waiting"
                        class="w-full text-left px-4 py-2.5 text-sm
                        hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                        {{ request('status') === 'waiting' ? 'font-semibold text-yellow-600 dark:text-yellow-400' : '' }}">
                        Waiting
                    </button>

                    {{-- Progress --}}
                    <button type="submit" name="status" value="progress"
                        class="w-full text-left px-4 py-2.5 text-sm
                        hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                        {{ request('status') === 'progress' ? 'font-semibold text-blue-600 dark:text-blue-400' : '' }}">
                        Progress
                    </button>

                    {{-- Done --}}
                    <button type="submit" name="status" value="done"
                        class="w-full text-left px-4 py-2.5 text-sm
                        hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                        {{ request('status') === 'done' ? 'font-semibold text-emerald-600 dark:text-emerald-400' : '' }}">
                        Done
                    </button>

                    {{-- Reject --}}
                    <button type="submit" name="status" value="reject"
                        class="w-full text-left px-4 py-2.5 text-sm
                        hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                        {{ request('status') === 'reject' ? 'font-semibold text-red-600 dark:text-red-400' : '' }}">
                        Reject
                    </button>
                </div>
            </div>

            {{-- Priority --}}
            <div class="relative w-full sm:w-[180px]" x-data="{ open: false }">

                {{-- Trigger --}}
                <button type="button" @click="open = !open"
                    class="h-10 w-full flex items-center justify-between px-4
                    border border-border-light dark:border-border-dark
                    rounded-lg
                    bg-surface-light dark:bg-slate-800
                    text-sm text-text-light dark:text-text-dark
                    shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700/50">

                    <span class="flex items-center gap-2">
                        <span class="material-icons-round text-base text-muted-light">priority_high</span>

                        @php
                            $priorityLabel = match (request('priority')) {
                                'high' => 'High',
                                'medium' => 'Medium',
                                'low' => 'Low',
                                default => 'Semua Prioritas',
                            };
                        @endphp

                        {{ $priorityLabel }}
                    </span>

                    <span class="material-icons-round text-base text-muted-light">expand_more</span>
                </button>

                {{-- Dropdown --}}
                <div x-show="open" x-transition x-cloak @click.outside="open = false"
                    class="absolute z-20 mt-2 w-full
                    rounded-xl overflow-hidden shadow-xl
                    border border-border-light dark:border-slate-700
                    bg-white/90 dark:bg-slate-800/90
                    backdrop-blur-md backdrop-saturate-150">

                    {{-- Semua --}}
                    <button type="submit" name="priority" value=""
                        class="w-full text-left px-4 py-2.5 text-sm
                        hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                        {{ request('priority') === null ? 'font-semibold text-secondary' : '' }}">
                        Semua Prioritas
                    </button>

                    <div class="h-px bg-border-light dark:bg-slate-700/70"></div>

                    {{-- High --}}
                    <button type="submit" name="priority" value="high"
                        class="w-full text-left px-4 py-2.5 text-sm
                        hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                        {{ request('priority') === 'high' ? 'font-semibold text-red-600 dark:text-red-400' : '' }}">
                        High
                    </button>

                    {{-- Medium --}}
                    <button type="submit" name="priority" value="medium"
                        class="w-full text-left px-4 py-2.5 text-sm
                        hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                        {{ request('priority') === 'medium' ? 'font-semibold text-yellow-600 dark:text-yellow-400' : '' }}">
                        Medium
                    </button>

                    {{-- Low --}}
                    <button type="submit" name="priority" value="low"
                        class="w-full text-left px-4 py-2.5 text-sm
                        hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                        {{ request('priority') === 'low' ? 'font-semibold text-gray-600 dark:text-gray-300' : '' }}">
                        Low
                    </button>
                </div>
            </div>
        </div>

        {{-- Row 2: Date Range + Assignee --}}
        <div class="flex flex-col sm:flex-row gap-3">

            {{-- Date Range --}}
            <div class="grid grid-cols-2 sm:flex gap-2 w-full sm:w-auto">

                <input type="date" name="start_date" value="{{ request('start_date') }}"
                    onchange="this.form.submit()"
                    class="h-11 w-full sm:w-[150px]
                        rounded-lg border border-border-light dark:border-border-dark
                        bg-surface-light dark:bg-slate-800
                        text-sm text-text-light dark:text-text-dark
                        focus:ring-1 focus:ring-secondary">

                <input type="date" name="end_date" value="{{ request('end_date') }}" onchange="this.form.submit()"
                    class="h-11 w-full sm:w-[150px]
                        rounded-lg border border-border-light dark:border-border-dark
                        bg-surface-light dark:bg-slate-800
                        text-sm text-text-light dark:text-text-dark
                        focus:ring-1 focus:ring-secondary">
            </div>

            {{-- Assignee --}}
            <div class="relative flex-1 sm:max-w-xs" x-data="{ open: false }">

                {{-- Trigger --}}
                <button type="button" @click="open = !open"
                    class="h-10 w-full flex items-center justify-between px-4
                    border border-border-light dark:border-border-dark
                    rounded-lg
                    bg-surface-light dark:bg-slate-800
                    text-sm text-text-light dark:text-text-dark
                    shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700/50">

                    <span class="flex items-center gap-2">
                        <span class="material-icons-round text-base text-muted-light">person</span>

                        @php
                            if (request('assigned_to') === 'unassigned') {
                                $assigneeLabel = 'Belum di-assign';
                            } elseif (request('assigned_to')) {
                                $assigneeLabel = optional($admins->firstWhere('id', request('assigned_to')))->name;
                            } else {
                                $assigneeLabel = 'Semua Assignee';
                            }
                        @endphp

                        {{ $assigneeLabel }}
                    </span>

                    <span class="material-icons-round text-base text-muted-light">expand_more</span>
                </button>

                {{-- Dropdown --}}
                <div x-show="open" x-transition x-cloak @click.outside="open = false"
                    class="absolute z-20 mt-2 w-full
                    rounded-xl overflow-hidden shadow-xl
                    border border-border-light dark:border-slate-700
                    bg-white/90 dark:bg-slate-800/90
                    backdrop-blur-md backdrop-saturate-150">

                    {{-- Semua --}}
                    <button type="submit" name="assigned_to" value=""
                        class="w-full text-left px-4 py-2.5 text-sm
                        hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                        {{ request('assigned_to') === null ? 'font-semibold text-secondary' : '' }}">
                        Semua Assignee
                    </button>

                    <div class="h-px bg-border-light dark:bg-slate-700/70"></div>

                    {{-- Unassigned --}}
                    <button type="submit" name="assigned_to" value="unassigned"
                        class="w-full text-left px-4 py-2.5 text-sm
                        hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                        {{ request('assigned_to') === 'unassigned' ? 'font-semibold text-red-600 dark:text-red-400' : '' }}">
                        Belum di-assign
                    </button>

                    <div class="h-px bg-border-light dark:bg-slate-700/70"></div>

                    {{-- Admin List --}}
                    @foreach ($admins as $admin)
                        <button type="submit" name="assigned_to" value="{{ $admin->id }}"
                            class="w-full text-left px-4 py-2.5 text-sm
                            hover:bg-gray-100/70 dark:hover:bg-slate-700/60
                            {{ request('assigned_to') == $admin->id ? 'font-semibold text-secondary' : '' }}">
                            {{ $admin->name }}
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

    </form>

    {{-- Ticket List --}}
    <div
        class="bg-surface-light dark:bg-surface-dark
                rounded-xl shadow-sm border border-border-light dark:border-border-dark
                divide-y divide-border-light dark:divide-border-dark">

        @forelse ($tickets as $ticket)
            <div class="p-4 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition group flex gap-4">

                {{-- Status Icon --}}
                <div class="pt-1">
                    @php
                        $statusIcon = match ($ticket->status) {
                            \App\Enums\TicketStatus::WAITING => 'radio_button_checked',
                            \App\Enums\TicketStatus::PROGRESS => 'adjust',
                            \App\Enums\TicketStatus::DONE => 'check_circle',
                            \App\Enums\TicketStatus::REJECT => 'cancel',
                        };
                    @endphp
                    <span
                        class="material-icons-round text-lg
                        {{ $ticket->status === \App\Enums\TicketStatus::WAITING ? 'text-yellow-500' : '' }}
                        {{ $ticket->status === \App\Enums\TicketStatus::PROGRESS ? 'text-blue-500' : '' }}
                        {{ $ticket->status === \App\Enums\TicketStatus::DONE ? 'text-emerald-500' : '' }}
                        {{ $ticket->status === \App\Enums\TicketStatus::REJECT ? 'text-red-500' : '' }}">
                        {{ $statusIcon }}
                    </span>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">

                    {{-- User Notes --}}
                    <p class="font-medium text-text-light dark:text-text-dark line-clamp-2 group-hover:text-secondary">
                        {{ Str::limit(strip_tags($ticket->user_notes), 120) }}
                    </p>

                    {{-- Meta --}}
                    <div class="mt-1 flex flex-wrap items-center gap-2 text-xs text-muted-light dark:text-muted-dark">

                        {{-- Ticket Code --}}
                        <span class="font-mono">#{{ $ticket->ticket_code }}</span>

                        <span>dibuat {{ $ticket->created_at->diffForHumans() }}</span>

                        <span>
                            oleh
                            @if ($ticket->user)
                                {{ $ticket->user->name }}
                            @elseif ($ticket->guestDetail)
                                {{ $ticket->guestDetail->full_name }} <span class="italic">(Guest)</span>
                            @else
                                -
                            @endif
                        </span>

                        {{-- Service Label --}}
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full
                                   bg-indigo-100 text-indigo-800
                                   dark:bg-indigo-900/30 dark:text-indigo-400">
                            {{ $ticket->service->name }}
                        </span>

                        {{-- Priority --}}
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full
                            {{ $ticket->priority === \App\Enums\TicketPriority::HIGH ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                            {{ $ticket->priority === \App\Enums\TicketPriority::MEDIUM ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                            {{ $ticket->priority === \App\Enums\TicketPriority::LOW ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                            {{ ucfirst($ticket->priority->value) }}
                        </span>
                    </div>
                </div>

                {{-- Right Actions --}}
                <div class="flex items-center gap-3">

                    {{-- Comment Count --}}
                    <div class="flex items-center text-muted-light dark:text-muted-dark text-sm">
                        <span class="material-icons-round text-sm mr-1">chat_bubble_outline</span>
                        {{ $ticket->comments_count ?? $ticket->comments()->count() }}
                    </div>

                    {{-- Assign --}}
                    @if (is_null($ticket->assigned_to))
                        <form method="POST" action="{{-- route('tickets.assign', $ticket) --}}">
                            @csrf
                            <button type="submit"
                                class="px-3 py-1.5 text-xs font-medium
                                       bg-secondary text-white rounded-lg hover:bg-blue-600">
                                Assign ke saya
                            </button>
                        </form>
                    @else
                        <span
                            class="px-3 py-1.5 text-xs rounded-lg
                                   bg-emerald-100 text-emerald-800
                                   dark:bg-emerald-900/30 dark:text-emerald-400">
                            {{ $ticket->assignee->name }}
                        </span>
                    @endif
                </div>
            </div>
        @empty
            <div class="p-10 text-center text-sm text-muted-light dark:text-muted-dark">
                Tidak ada ticket ditemukan.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $tickets->links() }}
    </div>

</x-layouts.dashboard>
