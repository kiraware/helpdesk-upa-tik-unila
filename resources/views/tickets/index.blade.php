<x-layouts.dashboard title="Manajemen Tiket">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Tiket Masuk
            </h1>
            <p class="text-sm text-muted-light dark:text-muted-dark">
                Daftar laporan dan permintaan pengguna
            </p>
        </div>
    </div>

    {{-- Search & Filter Component --}}
    {{-- Kita passing $admins karena dibutuhkan untuk dropdown filter --}}
    <x-tickets.index.filter :admins="$admins" />

    {{-- Ticket List Container --}}
    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">

        @forelse ($tickets as $ticket)
            {{-- Ticket Item Component --}}
            <x-tickets.index.item :ticket="$ticket" />
        @empty
            <div
                class="p-10 text-center text-sm text-muted-light dark:text-slate-400 flex flex-col items-center justify-center">
                <span class="material-icons-round text-4xl mb-2 text-gray-300 dark:text-slate-600">inbox</span>
                Tidak ada tiket ditemukan.
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $tickets->links() }}
    </div>

</x-layouts.dashboard>
