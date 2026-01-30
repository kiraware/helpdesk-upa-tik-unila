<x-layouts.dashboard title="Admin Dashboard">
    <div class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
                class="bg-red-50 dark:bg-red-900/10 border border-red-100 dark:border-red-900/30 p-6 rounded-xl flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-red-700 dark:text-red-400">Belum Ditugaskan</h3>
                    <p class="text-sm text-red-600/80 dark:text-red-400/70">Tiket perlu staff segera</p>
                </div>
                <div class="text-3xl font-black text-red-600 dark:text-red-400">
                    {{ $stats['unassigned'] }}
                </div>
            </div>

            <div
                class="bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-900/30 p-6 rounded-xl flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-bold text-blue-700 dark:text-blue-400">Tugas Saya</h3>
                    <p class="text-sm text-blue-600/80 dark:text-blue-400/70">Tiket dalam pengerjaan Anda</p>
                </div>
                <div class="text-3xl font-black text-blue-600 dark:text-blue-400">
                    {{ $stats['my_tasks'] }}
                </div>
            </div>
        </div>

        <div
            class="bg-surface-light dark:bg-card-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-800">
            <div
                class="p-6 border-b border-gray-100 dark:border-gray-700 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-text-light dark:text-text-dark">Antrian Prioritas</h2>
                    <p class="text-sm text-text-secondary-light">Tangani tiket dengan prioritas tinggi atau belum
                        ditugaskan.</p>
                </div>
                <a href="{{ route('tickets.index') }}"
                    class="btn-primary px-4 py-2 rounded-lg text-sm bg-primary text-white hover:bg-primary/90 transition">
                    Kelola Semua Tiket
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-text-secondary-light uppercase bg-gray-50 dark:bg-gray-800/50">
                        <tr>
                            <th class="px-6 py-4">Tiket</th>
                            <th class="px-6 py-4">Prioritas</th>
                            <th class="px-6 py-4">Pelapor</th>
                            <th class="px-6 py-4">Status Penugasan</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($priorityTickets as $ticket)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition group">
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span
                                            class="font-medium text-text-light dark:text-text-dark">{{ $ticket->title }}</span>
                                        <span class="text-xs text-text-secondary-light">#{{ $ticket->ticket_code }} •
                                            {{ $ticket->created_at->diffForHumans() }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($ticket->priority->value === 'high')
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                            <span class="material-icons-round text-[14px]">priority_high</span>
                                            High
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                            {{ ucfirst($ticket->priority->value) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="size-8 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 dark:from-gray-700 dark:to-gray-600 flex items-center justify-center text-xs font-bold">
                                            {{ substr($ticket->user->name, 0, 1) }}
                                        </div>
                                        <span
                                            class="text-text-light dark:text-text-dark">{{ $ticket->user->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if ($ticket->assigned_to)
                                        <span class="text-text-secondary-light flex items-center gap-1">
                                            <span class="material-icons-round text-[16px]">person</span>
                                            {{ $ticket->assignee->name }}
                                        </span>
                                    @else
                                        <span
                                            class="text-orange-500 flex items-center gap-1 text-xs font-medium bg-orange-50 dark:bg-orange-900/20 px-2 py-1 rounded w-fit">
                                            <span class="material-icons-round text-[14px]">warning</span>
                                            Belum Ada
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('tickets.show', $ticket->uuid) }}"
                                        class="text-primary hover:text-primary/80 font-medium text-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-text-secondary-light">
                                    Tidak ada tiket mendesak saat ini. Kerja bagus!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
