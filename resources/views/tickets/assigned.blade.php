<x-layouts.dashboard title="Tiket Ditugaskan">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">Tiket Ditugaskan</h1>
            <p class="text-sm text-muted-light dark:text-muted-dark">Tiket yang ditugaskan kepada Anda</p>
        </div>
    </div>

    {{-- Filter tanpa Petugas --}}
    <x-tickets.assigned.filter :services="$services" />

    <div id="ticket-list-area"
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        @forelse ($tickets as $ticket)
            <x-tickets.index.item :ticket="$ticket" />
        @empty
            <div
                class="p-10 text-center text-sm text-muted-light dark:text-slate-400 flex flex-col items-center justify-center">
                <span class="material-icons-round text-4xl mb-2 text-gray-300 dark:text-slate-600">inbox</span>
                Tidak ada tiket yang ditugaskan ke Anda.
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $tickets->links() }}</div>

</x-layouts.dashboard>
