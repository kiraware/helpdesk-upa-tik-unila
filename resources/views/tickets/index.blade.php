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
    <form method="GET" action="{{ route('tickets.index') }}" class="mb-6 flex flex-col gap-3">

        {{-- 1. Search Bar (Full Width) --}}
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-icons-round text-base text-muted-light">search</span>
            </div>

            <input type="text" name="q" value="{{ request('q') }}"
                placeholder="Cari kode tiket / isi laporan..."
                class="w-full pl-10 pr-4 py-3
                rounded-lg border border-border-light dark:border-border-dark
                bg-surface-light dark:bg-slate-800
                text-sm text-text-light dark:text-text-dark
                placeholder-muted-light dark:placeholder-muted-dark
                focus:ring-1 focus:ring-secondary focus:border-secondary
                shadow-sm transition-all">
        </div>

        {{-- 2. Grid Baris Tengah (Status, Priority, Dates) --}}
        {{-- Desktop: 4 Kolom sejajar --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

            {{-- Status Dropdown --}}
            <div class="relative w-full" x-data="{ open: false }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-3
                    border border-border-light dark:border-border-dark
                    rounded-lg
                    bg-surface-light dark:bg-slate-800
                    text-sm text-text-light dark:text-text-dark
                    shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700/50">

                    <span class="flex items-center gap-2 truncate">
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

                <div x-show="open" x-transition x-cloak @click.outside="open = false"
                    class="absolute z-20 mt-1 w-full min-w-[180px]
                    rounded-xl overflow-hidden shadow-xl
                    border border-border-light dark:border-slate-700
                    bg-white/95 dark:bg-slate-800/95
                    backdrop-blur-md">

                    <button type="submit" name="status" value=""
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === null ? 'font-semibold text-secondary' : '' }}">Semua
                        Status</button>
                    <div class="h-px bg-border-light dark:bg-slate-700/70"></div>
                    <button type="submit" name="status" value="waiting"
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === 'waiting' ? 'font-semibold text-yellow-600' : '' }}">Waiting</button>
                    <button type="submit" name="status" value="progress"
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === 'progress' ? 'font-semibold text-blue-600' : '' }}">Progress</button>
                    <button type="submit" name="status" value="done"
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === 'done' ? 'font-semibold text-emerald-600' : '' }}">Done</button>
                    <button type="submit" name="status" value="reject"
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === 'reject' ? 'font-semibold text-red-600' : '' }}">Reject</button>
                </div>
            </div>

            {{-- Priority Dropdown --}}
            <div class="relative w-full" x-data="{ open: false }">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-3 py-3
                    border border-border-light dark:border-border-dark
                    rounded-lg
                    bg-surface-light dark:bg-slate-800
                    text-sm text-text-light dark:text-text-dark
                    shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700/50">

                    <span class="flex items-center gap-2 truncate">
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

                <div x-show="open" x-transition x-cloak @click.outside="open = false"
                    class="absolute z-20 mt-1 w-full min-w-[180px]
                    rounded-xl overflow-hidden shadow-xl
                    border border-border-light dark:border-slate-700
                    bg-white/95 dark:bg-slate-800/95
                    backdrop-blur-md">

                    <button type="submit" name="priority" value=""
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('priority') === null ? 'font-semibold text-secondary' : '' }}">Semua
                        Prioritas</button>
                    <div class="h-px bg-border-light dark:bg-slate-700/70"></div>
                    <button type="submit" name="priority" value="high"
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('priority') === 'high' ? 'font-semibold text-red-600' : '' }}">High</button>
                    <button type="submit" name="priority" value="medium"
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('priority') === 'medium' ? 'font-semibold text-yellow-600' : '' }}">Medium</button>
                    <button type="submit" name="priority" value="low"
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('priority') === 'low' ? 'font-semibold text-gray-600' : '' }}">Low</button>
                </div>
            </div>

            {{-- Start Date --}}
            <div class="relative w-full">
                <input type="text" name="start_date" value="{{ request('start_date') }}"
                    onfocus="(this.type='date')" onblur="(this.value ? this.type='date' : this.type='text')"
                    placeholder="Tanggal Awal" onchange="this.form.submit()"
                    class="w-full px-3 py-3
                    rounded-lg border border-border-light dark:border-border-dark
                    bg-surface-light dark:bg-slate-800
                    text-sm text-text-light dark:text-text-dark
                    placeholder-muted-light dark:placeholder-muted-dark
                    focus:ring-1 focus:ring-secondary focus:border-secondary shadow-sm">
                <span
                    class="absolute right-3 top-1/2 -translate-y-1/2 material-icons-round text-base text-muted-light pointer-events-none">calendar_today</span>
            </div>

            {{-- End Date --}}
            <div class="relative w-full">
                <input type="text" name="end_date" value="{{ request('end_date') }}" onfocus="(this.type='date')"
                    onblur="(this.value ? this.type='date' : this.type='text')" placeholder="Tanggal Akhir"
                    onchange="this.form.submit()"
                    class="w-full px-3 py-3
                    rounded-lg border border-border-light dark:border-border-dark
                    bg-surface-light dark:bg-slate-800
                    text-sm text-text-light dark:text-text-dark
                    placeholder-muted-light dark:placeholder-muted-dark
                    focus:ring-1 focus:ring-secondary focus:border-secondary shadow-sm">
                <span
                    class="absolute right-3 top-1/2 -translate-y-1/2 material-icons-round text-base text-muted-light pointer-events-none">event</span>
            </div>

        </div>

        {{-- 3. Assignee  --}}
        <div class="relative w-full" x-data="{ open: false }">

            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-3
                border border-border-light dark:border-border-dark
                rounded-lg
                bg-surface-light dark:bg-slate-800
                text-sm text-text-light dark:text-text-dark
                shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700/50">

                <span class="flex items-center gap-2 truncate">
                    <span class="material-icons-round text-base text-muted-light">person</span>
                    @php
                        if (request('assigned_to') === 'me') {
                            $assigneeLabel = 'Ditugaskan ke Saya';
                        } elseif (request('assigned_to') === 'unassigned') {
                            $assigneeLabel = 'Belum Ditugaskan';
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

            {{-- Dropdown Menu --}}
            {{-- Pastikan z-index tinggi (z-50) agar menimpa tabel dibawahnya --}}
            <div x-show="open" x-transition x-cloak @click.outside="open = false"
                class="absolute z-50 mt-1 w-full left-0
                rounded-xl overflow-hidden shadow-xl
                border border-border-light dark:border-slate-700
                bg-white/95 dark:bg-slate-800/95
                backdrop-blur-md">

                <div class="max-h-60 overflow-y-auto"> {{-- Scroll jika list assignee panjang --}}
                    <button type="submit" name="assigned_to" value=""
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('assigned_to') === null ? 'font-semibold text-secondary' : '' }}">Semua
                        Assignee</button>
                    <div class="h-px bg-border-light dark:bg-slate-700/70"></div>
                    <button type="submit" name="assigned_to" value="me"
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('assigned_to') === 'me' ? 'font-semibold text-secondary' : '' }}">Ditugaskan
                        ke saya</button>
                    <button type="submit" name="assigned_to" value="unassigned"
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('assigned_to') === 'unassigned' ? 'font-semibold text-red-600' : '' }}">Belum
                        ditugaskan</button>
                    <div class="h-px bg-border-light dark:bg-slate-700/70"></div>
                    @foreach ($admins as $admin)
                        <button type="submit" name="assigned_to" value="{{ $admin->id }}"
                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('assigned_to') == $admin->id ? 'font-semibold text-secondary' : '' }}">
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
                    @if ($ticket->comments_count > 0)
                        <div class="flex items-center text-muted-light dark:text-muted-dark text-sm">
                            <span class="material-icons-round text-sm mr-1">chat_bubble_outline</span>
                            {{ $ticket->comments_count }}
                        </div>
                    @endif

                    {{-- Assign --}}
                    @if (is_null($ticket->assigned_to))
                        <form method="POST" action="{{ route('tickets.assign.me', $ticket) }}">
                            @csrf
                            <button type="submit"
                                class="px-3 py-1.5 text-xs font-medium
                                    bg-secondary text-white rounded-lg
                                    hover:bg-blue-600 transition">
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
