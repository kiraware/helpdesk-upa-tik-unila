<form method="GET" action="{{ route('survey-questions.index') }}" class="mb-6 flex flex-col gap-3">
    <button type="submit" class="hidden"></button>

    <input type="hidden" name="status" id="input-status" value="{{ request('status') }}">

    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">

        <div class="relative sm:col-span-3">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-icons-round text-base text-muted-light">search</span>
            </div>

            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari aspek atau pertanyaan..."
                class="w-full pl-10 pr-10 py-3 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark placeholder-muted-light dark:placeholder-muted-dark focus:ring-1 focus:ring-secondary focus:border-secondary shadow-sm transition-all">

            @if (request('q'))
                <a href="{{ request()->fullUrlWithoutQuery('q') }}"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-muted-light hover:text-red-500 transition-colors"
                    title="Hapus pencarian">
                    <span class="material-icons-round text-lg">close</span>
                </a>
            @endif
        </div>

        <div class="relative w-full" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                class="w-full flex items-center justify-between px-3 py-3 rounded-lg border border-border-light dark:border-border-dark bg-surface-light dark:bg-slate-800 text-sm text-text-light dark:text-text-dark shadow-sm">
                <span class="truncate font-medium">
                    @if (request('status') === '1')
                        Aktif
                    @elseif(request('status') === '0')
                        Non Aktif
                    @else
                        Semua Status
                    @endif
                </span>
                <span class="material-icons-round text-base text-muted-light">expand_more</span>
            </button>
            <div x-show="open" @click.away="open = false" x-cloak
                class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-border-light dark:border-border-dark py-1"
                style="display: none;">
                <button type="button" onclick="document.getElementById('input-status').value=''; this.form.submit()"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 text-text-light dark:text-text-dark">Semua
                    Status</button>
                <button type="button" onclick="document.getElementById('input-status').value='1'; this.form.submit()"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 {{ request('status') === '1' ? 'font-bold text-secondary' : 'text-text-light dark:text-text-dark' }}">Aktif</button>
                <button type="button" onclick="document.getElementById('input-status').value='0'; this.form.submit()"
                    class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 {{ request('status') === '0' ? 'font-bold text-secondary' : 'text-text-light dark:text-text-dark' }}">Non
                    Aktif</button>
            </div>
        </div>

    </div>
</form>
