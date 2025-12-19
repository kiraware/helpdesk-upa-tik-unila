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
                divide-y divide-border-light dark:divide-border-dark overflow-hidden">

        @forelse ($tickets as $ticket)
            {{-- Container --}}
            <div
                class="p-3 sm:px-4 hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors flex items-start gap-3 group cursor-pointer relative">

                {{-- 1. Icon Status (Kiri) --}}
                <div class="pt-0.5 shrink-0">
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
                    @endphp
                    <span class="material-icons-round text-[20px] {{ $statusColor }}"
                        title="{{ $ticket->status->value }}">
                        {{ $statusIcon }}
                    </span>
                </div>

                {{-- 2. Main Content (Tengah) --}}
                <div class="grow min-w-0">

                    {{-- Header Row: Judul + Badges --}}
                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1 mb-1">

                        {{-- Judul Tiket (PERBAIKAN WARNA DARK MODE DISINI) --}}
                        {{-- Menggunakan dark:text-slate-100 (putih) dan dark:group-hover:text-blue-400 (biru muda) --}}
                        <h3
                            class="text-[16px] font-semibold 
                                   text-gray-900 dark:text-slate-100 
                                   group-hover:text-blue-600 dark:group-hover:text-blue-400 
                                   transition-colors leading-snug wrap-break-word">
                            {{ Str::limit(strip_tags($ticket->user_notes), 80) }}
                        </h3>

                        {{-- Badges Wrapper --}}
                        <div class="flex flex-wrap gap-1">
                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium 
                                       bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300 border border-transparent whitespace-nowrap">
                                {{ $ticket->service->name }}
                            </span>

                            <span
                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border border-transparent whitespace-nowrap
                                {{ $ticket->priority === \App\Enums\TicketPriority::HIGH ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400' : '' }}
                                {{ $ticket->priority === \App\Enums\TicketPriority::MEDIUM ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400' : '' }}
                                {{ $ticket->priority === \App\Enums\TicketPriority::LOW ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                                {{ ucfirst($ticket->priority->value) }}
                            </span>
                        </div>
                    </div>

                    {{-- Meta Info Row (Bawah) --}}
                    <div
                        class="text-xs text-muted-light dark:text-slate-400 leading-relaxed flex flex-wrap gap-1 items-center">
                        <span class="font-mono text-gray-500">#{{ $ticket->ticket_code }}</span>
                        <span class="text-gray-400">·</span>
                        <span>{{ $ticket->created_at->diffForHumans() }}</span>
                        <span class="hidden sm:inline">oleh</span>
                        <span
                            class="font-medium text-gray-700 dark:text-slate-300 truncate max-w-[100px] sm:max-w-none">
                            @if ($ticket->user)
                                {{ $ticket->user->name }}
                            @elseif ($ticket->guestDetail)
                                {{ $ticket->guestDetail->full_name }} (Guest)
                            @else
                                -
                            @endif
                        </span>
                    </div>
                </div>

                {{-- 3. Right Actions (Kanan) --}}
                <div class="shrink-0 flex items-center gap-3 self-start mt-0.5 pl-2">

                    {{-- Assign Action / Status --}}
                    @if (is_null($ticket->assigned_to))
                        <form method="POST" action="{{ route('tickets.assign.me', $ticket) }}">
                            @csrf
                            <button type="submit"
                                class="px-2.5 py-1 text-xs font-medium rounded-md shadow-sm
                                bg-white border border-border-light text-text-light
                                hover:border-secondary hover:text-secondary hover:bg-gray-50
                                dark:bg-slate-800 dark:border-slate-600 dark:text-slate-200 dark:hover:border-blue-400 transition-all whitespace-nowrap">
                                Assign
                            </button>
                        </form>
                    @else
                        {{-- (PERBAIKAN FOTO PROFIL ASSIGNEE DISINI) --}}
                        <div class="hidden sm:flex items-center" title="Assigned to {{ $ticket->assignee->name }}">
                            <img src="{{ $ticket->assignee->photo
                                ? asset('storage/' . $ticket->assignee->photo)
                                : 'https://ui-avatars.com/api/?name=' . urlencode($ticket->assignee->name) }}"
                                alt="{{ $ticket->assignee->name }}"
                                class="w-6 h-6 rounded-full object-cover 
                                       border border-border-light dark:border-slate-600 
                                       shadow-sm" />
                        </div>
                    @endif

                    {{-- Comment Count --}}
                    @if ($ticket->comments_count > 0)
                        <div
                            class="flex items-center text-muted-light dark:text-slate-400 hover:text-secondary transition-colors">
                            <span class="material-icons-round text-[16px] mr-0.5">chat_bubble_outline</span>
                            <span class="text-xs font-medium">{{ $ticket->comments_count }}</span>
                        </div>
                    @endif
                </div>

            </div>
        @empty
            <div
                class="p-10 text-center text-sm text-muted-light dark:text-slate-400 flex flex-col items-center justify-center">
                <span class="material-icons-round text-4xl mb-2 text-gray-300 dark:text-slate-600">inbox</span>
                Tidak ada ticket ditemukan.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $tickets->links() }}
    </div>

</x-layouts.dashboard>
