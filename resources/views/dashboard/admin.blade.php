<x-layouts.dashboard title="Admin Dashboard">
    <div class="space-y-6">
        {{-- SECTION 1: Stats Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Card: Belum Ditugaskan --}}
            <div
                class="bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 p-6 rounded-xl flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-red-700 dark:text-red-400">Belum Ditugaskan</h3>
                    <p class="text-sm text-red-600/80 dark:text-red-400/70">Tiket perlu staff segera</p>
                </div>
                <div class="text-3xl font-black text-red-600 dark:text-red-400">
                    {{ $stats['unassigned'] }}
                </div>
            </div>

            {{-- Card: Tugas Saya --}}
            <div
                class="bg-blue-50 dark:bg-blue-500/10 border border-blue-100 dark:border-blue-500/20 p-6 rounded-xl flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-blue-700 dark:text-blue-400">Tugas Saya</h3>
                    <p class="text-sm text-blue-600/80 dark:text-blue-400/70">Tiket dalam pengerjaan Anda</p>
                </div>
                <div class="text-3xl font-black text-blue-600 dark:text-blue-400">
                    {{ $stats['my_tasks'] }}
                </div>
            </div>
        </div>

        {{-- SECTION 2: Priority Queue List --}}
        <div class="space-y-4">
            {{-- Header --}}
            <div class="flex justify-between items-center px-1">
                <h2 class="font-semibold text-text-light dark:text-text-dark">Antrian Prioritas</h2>
                <a href="{{ route('tickets.index') }}" class="text-sm text-secondary hover:underline">
                    Lihat Semua
                </a>
            </div>

            {{-- List Container --}}
            <div
                class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
                <div class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse($priorityTickets as $ticket)
                        {{-- Component Item --}}
                        <x-tickets.index.item :ticket="$ticket" />
                    @empty
                        <div class="p-8 text-center text-muted-light dark:text-muted-dark">
                            <span class="material-icons-round text-4xl mb-2 text-gray-300 dark:text-gray-600">
                                task_alt
                            </span>
                            <p>Tidak ada tiket mendesak saat ini. Kerja bagus!</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
