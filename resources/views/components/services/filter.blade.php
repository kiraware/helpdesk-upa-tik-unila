<form method="GET" action="{{ route('services.index') }}" class="mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">

        {{-- Search --}}
        <div class="sm:col-span-8 lg:col-span-9 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-icons-round text-gray-400">search</span>
            </div>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama layanan..."
                class="h-10 block w-full pl-10 pr-3 py-2 border border-border-light dark:border-border-dark rounded-lg leading-5 bg-surface-light dark:bg-slate-800 text-text-light dark:text-text-dark placeholder-muted-light dark:placeholder-muted-dark focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary sm:text-sm shadow-sm">
        </div>

        {{-- Filter Status --}}
        <div class="sm:col-span-4 lg:col-span-3 relative" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                class="h-10 w-full flex items-center justify-between px-4 py-2 border border-border-light dark:border-border-dark rounded-lg bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700/50 focus:outline-none">
                <span class="flex items-center gap-2">
                    <span class="material-icons-round text-base text-muted-light">filter_alt</span>
                    @php
                        $statusLabel = match (request('status')) {
                            '1' => 'Aktif',
                            '0' => 'Non Aktif',
                            default => 'Semua Status',
                        };
                    @endphp
                    {{ $statusLabel }}
                </span>
                <span class="material-icons-round text-base text-muted-light">expand_more</span>
            </button>

            <div x-show="open" x-transition x-cloak @click.outside="open = false"
                class="absolute z-20 mt-2 w-full rounded-xl overflow-hidden shadow-xl border border-border-light dark:border-slate-700 bg-white/90 dark:bg-slate-800/90 backdrop-blur-md backdrop-saturate-150">
                <button type="submit" name="status" value=""
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === null ? 'font-semibold text-secondary' : '' }}">Semua
                    Status</button>
                <div class="h-px bg-border-light dark:bg-slate-700/70"></div>
                <button type="submit" name="status" value="1"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === '1' ? 'font-semibold text-emerald-600 dark:text-emerald-400' : '' }}">Aktif</button>
                <button type="submit" name="status" value="0"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 {{ request('status') === '0' ? 'font-semibold text-red-600 dark:text-red-400' : '' }}">Non
                    Aktif</button>
            </div>
        </div>
    </div>
</form>
