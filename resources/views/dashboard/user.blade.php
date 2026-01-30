<x-layouts.dashboard title="Dashboard Saya">
    <div class="space-y-8">
        {{-- SECTION 1: Header Gradient --}}
        <div
            class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-gradient-to-r from-primary to-blue-600 p-6 rounded-2xl text-white shadow-lg">
            <div>
                <h1 class="text-2xl font-bold">Halo, {{ auth()->user()->name }}!</h1>
                <p class="text-blue-100 mt-1">Mengalami kendala IT? Ajukan tiket sekarang agar kami bisa membantu.</p>
            </div>
            <a href="{{ route('tickets.create') }}"
                class="bg-white text-primary hover:bg-gray-50 px-5 py-2.5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                <span class="material-icons-round">add</span>
                Buat Tiket Baru
            </a>
        </div>

        {{-- SECTION 2: Statistik --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
                class="p-4 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-sm">
                <p class="text-sm text-muted-light dark:text-muted-dark mb-1">Tiket Aktif</p>
                <p class="text-2xl font-bold text-primary dark:text-white">{{ $myStats['active'] }}</p>
            </div>
            <div
                class="p-4 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-sm">
                <p class="text-sm text-muted-light dark:text-muted-dark mb-1">Tiket Selesai</p>
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $myStats['completed'] }}</p>
            </div>
        </div>

        {{-- SECTION 3: Riwayat Terkini (Updated) --}}
        <div>
            <h2 class="text-lg font-semibold text-text-light dark:text-text-dark mb-4">Riwayat Terkini</h2>

            {{-- Container List --}}
            <div
                class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
                @forelse($myRecentTickets as $ticket)
                    {{-- Menggunakan komponen Item yang sudah ada --}}
                    {{-- Komponen ini otomatis menangani Dark Mode & Logika Role --}}
                    <x-tickets.index.item :ticket="$ticket" />
                @empty
                    {{-- Empty State --}}
                    <div class="p-8 text-center">
                        <div
                            class="inline-flex items-center justify-center size-16 rounded-full bg-gray-100 dark:bg-border-dark mb-4 text-muted-light dark:text-muted-dark">
                            <span class="material-icons-round text-3xl">inbox</span>
                        </div>
                        <h3 class="text-text-light dark:text-text-dark font-medium">Belum ada tiket</h3>
                        <p class="text-muted-light dark:text-muted-dark text-sm mt-1">
                            Anda belum membuat tiket bantuan apapun.
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.dashboard>
