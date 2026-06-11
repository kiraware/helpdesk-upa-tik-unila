@props(['services', 'admins'])

<form method="GET" action="{{ route('tickets.waiting') }}" class="mb-6 flex flex-col gap-3">
    <button type="submit" class="hidden"></button>

    <input type="hidden" name="service" id="input-service" value="{{ request('service') }}">
    <input type="hidden" name="priority" id="input-priority" value="{{ request('priority') }}">
    <input type="hidden" name="assigned_to" id="input-assigned_to" value="{{ request('assigned_to') }}">
    <input type="hidden" name="entity" id="input-entity" value="{{ request('entity') }}">

    <div class="relative w-full">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <span class="material-icons-round text-base text-muted-light">search</span>
        </div>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode tiket / isi laporan..."
            class="w-full pl-10 pr-10 py-3 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark placeholder-muted-light dark:placeholder-muted-dark focus:ring-1 focus:ring-secondary focus:border-secondary shadow-sm transition-all">
        @if (request('q'))
            <a href="{{ request()->fullUrlWithoutQuery('q') }}"
                class="absolute inset-y-0 right-0 pr-3 flex items-center text-muted-light hover:text-red-500 transition-colors">
                <span class="material-icons-round text-lg">close</span>
            </a>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">

        <div class="relative w-full" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-3 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark shadow-sm">
                <span
                    class="truncate">{{ $services->firstWhere('id', request('service'))?->name ?? 'Semua Layanan' }}</span>
                <span class="material-icons-round text-base text-muted-light">expand_more</span>
            </button>
            <div x-show="open" @click.away="open = false"
                class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-border-light dark:border-border-dark py-1 max-h-60 overflow-y-auto"
                style="display: none;">
                <button type="button" onclick="document.getElementById('input-service').value=''; this.form.submit()"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 text-text-light dark:text-text-dark">
                    Semua Layanan
                </button>
                @foreach ($services as $service)
                    <button type="button"
                        onclick="document.getElementById('input-service').value='{{ $service->id }}'; this.form.submit()"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 {{ request('service') == $service->id ? 'font-bold text-secondary' : 'text-text-light dark:text-text-dark' }}">
                        {{ $service->name }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="relative w-full" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-3 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark shadow-sm">
                <span class="truncate">
                    {{ request('entity') ? ucfirst(request('entity')) : 'Semua Entitas' }}
                </span>
                <span class="material-icons-round text-base text-muted-light">expand_more</span>
            </button>
            <div x-show="open" @click.away="open = false"
                class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-border-light dark:border-border-dark py-1"
                style="display: none;">

                <button type="button" onclick="document.getElementById('input-entity').value=''; this.form.submit()"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 text-text-light dark:text-text-dark">
                    Semua Entitas
                </button>

                @foreach (['mahasiswa', 'dosen', 'tendik', 'karyawan', 'tamu', 'lainnya'] as $entity)
                    <button type="button"
                        onclick="document.getElementById('input-entity').value='{{ $entity }}'; this.form.submit()"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 {{ request('entity') == $entity ? 'font-bold text-secondary' : 'text-text-light dark:text-text-dark' }}">
                        {{ ucfirst($entity) }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="relative w-full" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-3 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark shadow-sm">
                <span
                    class="truncate">{{ request('priority') ? ucfirst(request('priority')) : 'Semua Prioritas' }}</span>
                <span class="material-icons-round text-base text-muted-light">expand_more</span>
            </button>
            <div x-show="open" @click.away="open = false"
                class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-border-light dark:border-border-dark py-1"
                style="display: none;">
                <button type="button" onclick="document.getElementById('input-priority').value=''; this.form.submit()"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 text-text-light dark:text-text-dark">
                    Semua Prioritas
                </button>
                @foreach (\App\Enums\TicketPriority::cases() as $priority)
                    <button type="button"
                        onclick="document.getElementById('input-priority').value='{{ $priority->value }}'; this.form.submit()"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 {{ request('priority') == $priority->value ? 'font-bold text-secondary' : 'text-text-light dark:text-text-dark' }}">
                        {{ ucfirst($priority->value) }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="relative w-full" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-3 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark shadow-sm">
                <span class="truncate flex items-center gap-2">
                    @php
                        $selectedAdmin = $admins->firstWhere('id', request('assigned_to'));
                    @endphp
                    @if ($selectedAdmin)
                        <img src="{{ $selectedAdmin->avatar_path
                            ? asset('storage/' . $selectedAdmin->avatar_path)
                            : 'https://ui-avatars.com/api/?name=' . urlencode($selectedAdmin->name) }}"
                            class="w-5 h-5 rounded-full object-cover shrink-0 border border-border-light dark:border-slate-600">
                        {{ $selectedAdmin->name }}
                    @else
                        Semua Petugas
                    @endif
                </span>
                <span class="material-icons-round text-base text-muted-light">expand_more</span>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak
                class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-border-light dark:border-border-dark py-1 max-h-60 overflow-y-auto"
                style="display: none;">
                <button type="button"
                    onclick="document.getElementById('input-assigned_to').value=''; this.form.submit()"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors {{ request('assigned_to') == '' ? 'font-bold text-secondary bg-gray-50 dark:bg-slate-700/50' : 'text-text-light dark:text-text-dark' }}">
                    <span class="material-icons-round text-[20px] text-gray-400">group</span>
                    <span>Semua Petugas</span>
                </button>
                <div class="border-t border-border-light dark:border-border-dark my-1"></div>
                @foreach ($admins as $admin)
                    <button type="button"
                        onclick="document.getElementById('input-assigned_to').value='{{ $admin->id }}'; this.form.submit()"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors {{ request('assigned_to') == $admin->id ? 'font-bold text-secondary bg-gray-50 dark:bg-slate-700/50' : 'text-text-light dark:text-text-dark' }}">
                        <img src="{{ $admin->avatar_path
                            ? asset('storage/' . $admin->avatar_path)
                            : 'https://ui-avatars.com/api/?name=' . urlencode($admin->name) }}"
                            class="w-5 h-5 rounded-full object-cover shrink-0 border border-border-light dark:border-slate-600">
                        <span class="truncate">{{ $admin->name }}</span>
                        @if (request('assigned_to') == $admin->id)
                            <span
                                class="material-icons-round text-[14px] ml-auto text-blue-600 dark:text-blue-400">check</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        <div class="relative w-full">
            <input type="text" name="start_date" value="{{ request('start_date') }}"
                onfocus="(this.type='date')" onblur="(this.value ? this.type='date' : this.type='text')"
                placeholder="Tanggal Awal" onchange="this.form.submit()"
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
                class="absolute right-3 top-1/2 -translate-y-1/2 material-icons-round text-base text-muted-light pointer-events-none">calendar_today</span>
        </div>
    </div>
</form>
