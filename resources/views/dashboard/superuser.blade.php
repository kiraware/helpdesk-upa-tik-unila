<x-layouts.dashboard title="Dashboard">
    <div class="space-y-6">
        {{-- HEADER --}}
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Selamat Datang, {{ auth()->user()->name }}
            </h1>
            <p class="text-muted-light dark:text-muted-dark mt-1">Berikut adalah ringkasan sistem hari ini.</p>
        </div>

        {{-- STATS GRID --}}
        <div id="stats-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

            {{-- Card 1: Total --}}
            <div
                class="bg-surface-light dark:bg-surface-dark p-5 lg:p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center gap-3 lg:gap-4 lg:row-span-2">

                {{-- Mengubah warna bg dan text menjadi INDIGO --}}
                <div
                    class="shrink-0 p-3 lg:p-4 bg-indigo-50 dark:bg-indigo-500/10 rounded-lg lg:rounded-xl text-indigo-600 dark:text-indigo-400">
                    <span class="material-icons-round text-2xl lg:text-4xl">dataset</span>
                </div>

                <div class="flex-1">
                    <p class="text-sm lg:text-base font-medium text-muted-light dark:text-muted-dark">
                        Total Tiket
                    </p>

                    <h3
                        class="text-xl lg:text-3xl xl:text-4xl font-bold text-primary dark:text-white wrap-break-word leading-tight mt-0.5 lg:mt-1">
                        {{ number_format($stats['total'], 0, ',', '.') }}
                    </h3>
                </div>
            </div>

            {{-- Card 2: Waiting --}}
            <div
                class="bg-surface-light dark:bg-surface-dark p-5 xl:p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center gap-3 xl:gap-4">
                <div
                    class="shrink-0 p-3 bg-yellow-50 dark:bg-yellow-500/10 rounded-lg text-yellow-600 dark:text-yellow-400">
                    <span class="material-icons-round">hourglass_top</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-muted-light dark:text-muted-dark">Menunggu</p>
                    <h3
                        class="text-xl xl:text-2xl font-bold text-text-light dark:text-text-dark wrap-break-word leading-tight mt-0.5">
                        {{ number_format($stats['waiting'], 0, ',', '.') }}
                    </h3>
                </div>
            </div>

            {{-- Card 3: Progress --}}
            <div
                class="bg-surface-light dark:bg-surface-dark p-5 xl:p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center gap-3 xl:gap-4">

                {{-- Mengubah warna bg dan text menjadi BIRU --}}
                <div class="shrink-0 p-3 bg-blue-50 dark:bg-blue-500/10 rounded-lg text-blue-600 dark:text-blue-400">
                    <span class="material-icons-round">engineering</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-muted-light dark:text-muted-dark">Diproses</p>
                    <h3
                        class="text-xl xl:text-2xl font-bold text-text-light dark:text-text-dark wrap-break-word leading-tight mt-0.5">
                        {{ number_format($stats['progress'], 0, ',', '.') }}
                    </h3>
                </div>
            </div>

            {{-- Card 4: Done --}}
            <div
                class="bg-surface-light dark:bg-surface-dark p-5 xl:p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center gap-3 xl:gap-4">
                <div
                    class="shrink-0 p-3 bg-green-50 dark:bg-green-500/10 rounded-lg text-green-600 dark:text-green-400">
                    <span class="material-icons-round">check_circle</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-muted-light dark:text-muted-dark">Selesai</p>
                    <h3
                        class="text-xl xl:text-2xl font-bold text-text-light dark:text-text-dark wrap-break-word leading-tight mt-0.5">
                        {{ number_format($stats['done'], 0, ',', '.') }}
                    </h3>
                </div>
            </div>

            {{-- Card 5: Reject --}}
            <div
                class="bg-surface-light dark:bg-surface-dark p-5 xl:p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark flex items-center gap-3 xl:gap-4">
                <div class="shrink-0 p-3 bg-red-50 dark:bg-red-500/10 rounded-lg text-red-600 dark:text-red-400">
                    <span class="material-icons-round">cancel</span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-muted-light dark:text-muted-dark">Ditolak</p>
                    <h3
                        class="text-xl xl:text-2xl font-bold text-text-light dark:text-text-dark wrap-break-word leading-tight mt-0.5">
                        {{ number_format($stats['reject'], 0, ',', '.') }}
                    </h3>
                </div>
            </div>

        </div>

        {{-- MAIN CONTENT GRID --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- SECTION: TIKET TERBARU --}}
            <div class="lg:col-span-2 space-y-4">
                <div class="flex justify-between items-center px-1">
                    <h2 class="font-semibold text-text-light dark:text-text-dark">Tiket Terbaru</h2>
                    <a href="{{ route('tickets.index') }}" class="text-sm text-secondary hover:underline">Lihat
                        Semua</a>
                </div>

                {{-- List Container --}}
                <div id="ticket-container"
                    class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
                    @forelse ($recentTickets as $ticket)
                        <x-tickets.index.item :ticket="$ticket" />
                    @empty
                        <div class="p-8 text-center text-muted-light dark:text-muted-dark">
                            <p>Belum ada tiket yang masuk.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- SECTION: LAYANAN TERPOPULER --}}
            <div id="services-container"
                class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6 h-fit">
                <h2 class="font-semibold text-text-light dark:text-text-dark mb-4">Layanan Terpopuler</h2>
                <div class="space-y-5">
                    @foreach ($serviceStats as $service)
                        <div>
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="text-muted-light dark:text-muted-dark">{{ $service->name }}</span>
                                <span
                                    class="font-bold text-text-light dark:text-text-dark">{{ $service->tickets_count }}</span>
                            </div>
                            {{-- Progress Bar Background --}}
                            <div class="w-full bg-gray-100 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
                                {{-- Progress Bar Fill --}}
                                <div class="bg-secondary h-2 rounded-full"
                                    style="width: {{ $stats['total'] > 0 ? ($service->tickets_count / $stats['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if ($serviceStats->isEmpty())
                        <p class="text-sm text-muted-light dark:text-muted-dark text-center italic">Belum ada data
                            statistik.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>

<script>
    setInterval(() => {
        fetch(window.location.href)
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newList = doc.getElementById('ticket-container');
                if (newList) document.getElementById('ticket-container').innerHTML = newList.innerHTML;

                const newStats = doc.getElementById('stats-container');
                if (newStats) document.getElementById('stats-container').innerHTML = newStats.innerHTML;

                const newServices = doc.getElementById('services-container');
                if (newServices) document.getElementById('services-container').innerHTML = newServices
                    .innerHTML;
            })
            .catch(err => console.error('Gagal refresh:', err));
    }, 10000);
</script>
