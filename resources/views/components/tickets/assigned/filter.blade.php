@props(['services'])

<form method="GET" action="{{ route('tickets.assigned') }}" class="mb-6 flex flex-col gap-3">
    <button type="submit" class="hidden"></button>

    <input type="hidden" name="service" id="input-service" value="{{ request('service') }}">
    <input type="hidden" name="status" id="input-status" value="{{ request('status') }}">
    <input type="hidden" name="priority" id="input-priority" value="{{ request('priority') }}">

    {{-- Search Bar --}}
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

    {{-- Grid: Layanan, Status, Prioritas (tanpa Petugas) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

        {{-- Filter Layanan --}}
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

        {{-- Filter Status --}}
        <div class="relative w-full" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-3 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark shadow-sm">
                <span class="truncate">{{ request('status') ? ucfirst(request('status')) : 'Semua Status' }}</span>
                <span class="material-icons-round text-base text-muted-light">expand_more</span>
            </button>
            <div x-show="open" @click.away="open = false"
                class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-border-light dark:border-border-dark py-1"
                style="display: none;">
                <button type="button" onclick="document.getElementById('input-status').value=''; this.form.submit()"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 text-text-light dark:text-text-dark">
                    Semua Status
                </button>
                @foreach (\App\Enums\TicketStatus::cases() as $status)
                    <button type="button"
                        onclick="document.getElementById('input-status').value='{{ $status->value }}'; this.form.submit()"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 {{ request('status') == $status->value ? 'font-bold text-secondary' : 'text-text-light dark:text-text-dark' }}">
                        {{ ucfirst($status->value) }}
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Filter Prioritas --}}
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
    </div>

    {{-- Date Filter --}}
    <div class="grid grid-cols-2 gap-3">
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
                class="absolute right-3 top-1/2 -translate-y-1/2 material-icons-round text-base text-muted-light pointer-events-none">calendar_today</span>
        </div>
    </div>
</form>
