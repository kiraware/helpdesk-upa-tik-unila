@props(['admins'])

<form method="GET" action="{{ route('tickets.index') }}" class="mb-6 flex flex-col gap-3">

    {{-- 1. Search Bar --}}
    <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <span class="material-icons-round text-base text-muted-light">search</span>
        </div>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode tiket / isi laporan..."
            class="w-full pl-10 pr-4 py-3 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark placeholder-muted-light dark:placeholder-muted-dark focus:ring-1 focus:ring-secondary focus:border-secondary shadow-sm transition-all">
    </div>

    {{-- 2. Grid Baris Tengah --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

        {{-- Status Dropdown --}}
        <div class="relative w-full" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-3 border border-border-light dark:border-border-dark rounded-lg bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700/50">
                <span class="flex items-center gap-2 truncate">
                    <span class="material-icons-round text-base text-muted-light">flag</span>
                    @php
                        $statusLabel = match (request('status')) {
                            'waiting' => 'Menunggu',
                            'progress' => 'Diproses',
                            'done' => 'Selesai',
                            'reject' => 'Ditolak',
                            default => 'Semua Status',
                        };
                    @endphp
                    {{ $statusLabel }}
                </span>
                <span class="material-icons-round text-base text-muted-light">expand_more</span>
            </button>
            <div x-show="open" x-transition x-cloak @click.outside="open = false"
                class="absolute z-20 mt-1 w-full min-w-[180px] rounded-xl overflow-hidden shadow-xl border border-border-light dark:border-slate-700 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md">
                <button type="submit" name="status" value=""
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === null ? 'font-semibold text-secondary' : '' }}">Semua
                    Status</button>
                <div class="h-px bg-border-light dark:bg-slate-700/70"></div>
                <button type="submit" name="status" value="waiting"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === 'waiting' ? 'font-semibold text-yellow-600' : '' }}">Menunggu</button>
                <button type="submit" name="status" value="progress"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === 'progress' ? 'font-semibold text-blue-600' : '' }}">Diproses</button>
                <button type="submit" name="status" value="done"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === 'done' ? 'font-semibold text-emerald-600' : '' }}">Selesai</button>
                <button type="submit" name="status" value="reject"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === 'reject' ? 'font-semibold text-red-600' : '' }}">Ditolak</button>
            </div>
        </div>

        {{-- Priority Dropdown --}}
        <div class="relative w-full" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-3 border border-border-light dark:border-border-dark rounded-lg bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700/50">
                <span class="flex items-center gap-2 truncate">
                    <span class="material-icons-round text-base text-muted-light">priority_high</span>
                    @php
                        $priorityLabel = match (request('priority')) {
                            'high' => 'Tinggi',
                            'medium' => 'Sedang',
                            'low' => 'Rendah',
                            default => 'Semua Prioritas',
                        };
                    @endphp
                    {{ $priorityLabel }}
                </span>
                <span class="material-icons-round text-base text-muted-light">expand_more</span>
            </button>
            <div x-show="open" x-transition x-cloak @click.outside="open = false"
                class="absolute z-20 mt-1 w-full min-w-[180px] rounded-xl overflow-hidden shadow-xl border border-border-light dark:border-slate-700 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md">
                <button type="submit" name="priority" value=""
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('priority') === null ? 'font-semibold text-secondary' : '' }}">Semua
                    Prioritas</button>
                <div class="h-px bg-border-light dark:bg-slate-700/70"></div>
                <button type="submit" name="priority" value="high"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('priority') === 'high' ? 'font-semibold text-red-600' : '' }}">Tinggi</button>
                <button type="submit" name="priority" value="medium"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('priority') === 'medium' ? 'font-semibold text-yellow-600' : '' }}">Sedang</button>
                <button type="submit" name="priority" value="low"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('priority') === 'low' ? 'font-semibold text-gray-600' : '' }}">Rendah</button>
            </div>
        </div>

        {{-- Date Pickers --}}
        <div class="relative w-full">
            <input type="text" name="start_date" value="{{ request('start_date') }}" onfocus="(this.type='date')"
                onblur="(this.value ? this.type='date' : this.type='text')" placeholder="Tanggal Awal"
                onchange="this.form.submit()"
                class="w-full px-3 py-3 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark placeholder-muted-light dark:placeholder-muted-dark focus:ring-1 focus:ring-secondary focus:border-secondary shadow-sm">
            <span
                class="absolute right-3 top-1/2 -translate-y-1/2 material-icons-round text-base text-muted-light pointer-events-none">calendar_today</span>
        </div>
        <div class="relative w-full">
            <input type="text" name="end_date" value="{{ request('end_date') }}" onfocus="(this.type='date')"
                onblur="(this.value ? this.type='date' : this.type='text')" placeholder="Tanggal Akhir"
                onchange="this.form.submit()"
                class="w-full px-3 py-3 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark placeholder-muted-light dark:placeholder-muted-dark focus:ring-1 focus:ring-secondary focus:border-secondary shadow-sm">
            <span
                class="absolute right-3 top-1/2 -translate-y-1/2 material-icons-round text-base text-muted-light pointer-events-none">event</span>
        </div>
    </div>

    {{-- 3. Assignee Dropdown --}}
    <div class="relative w-full" x-data="{ open: false }">
        <button type="button" @click="open = !open"
            class="w-full flex items-center justify-between px-3 py-3 border border-border-light dark:border-border-dark rounded-lg bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700/50">
            <span class="flex items-center gap-2 truncate">
                <span class="material-icons-round text-base text-muted-light">person</span>
                @php
                    $assigneeLabel = match (true) {
                        request('assigned_to') === 'me' => 'Ditugaskan ke Saya',
                        request('assigned_to') === 'unassigned' => 'Belum Ditugaskan',
                        !empty(request('assigned_to')) => optional($admins->firstWhere('id', request('assigned_to')))
                            ->name,
                        default => 'Semua Petugas',
                    };
                @endphp
                {{ $assigneeLabel }}
            </span>
            <span class="material-icons-round text-base text-muted-light">expand_more</span>
        </button>

        <div x-show="open" x-transition x-cloak @click.outside="open = false"
            class="absolute z-50 mt-1 w-full left-0 rounded-xl overflow-hidden shadow-xl border border-border-light dark:border-slate-700 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md">
            <div class="max-h-60 overflow-y-auto">
                <button type="submit" name="assigned_to" value=""
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('assigned_to') === null ? 'font-semibold text-secondary' : '' }}">Semua
                    Petugas</button>
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
