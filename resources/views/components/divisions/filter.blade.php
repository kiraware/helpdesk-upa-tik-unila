<form method="GET" action="{{ route('divisions.index') }}" class="mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
        <div class="sm:col-span-8 lg:col-span-9 relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="material-icons-round text-gray-400">search</span>
            </div>

            <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama unit fungsi..."
                class="h-10 block w-full pl-10 pr-10 py-2 border border-border-light dark:border-border-dark rounded-lg leading-5 bg-surface-light dark:bg-slate-800 text-text-light dark:text-text-dark placeholder-muted-light dark:placeholder-muted-dark focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary sm:text-sm shadow-sm">

            @if (request('q'))
                <a href="{{ request()->fullUrlWithoutQuery('q') }}"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 transition-colors"
                    title="Hapus pencarian">
                    <span class="material-icons-round text-lg">close</span>
                </a>
            @endif
        </div>

        <div class="sm:col-span-4 lg:col-span-3 flex items-center">
            <button type="submit"
                class="w-full h-10 px-4 bg-secondary text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors shadow-sm">
                Cari Unit Fungsi
            </button>
        </div>
    </div>
</form>
