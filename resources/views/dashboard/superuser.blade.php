<x-layouts.dashboard title="Superuser Overview">
    <div class="space-y-6">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Selamat Datang, {{ auth()->user()->name }}
            </h1>
            <p class="text-text-secondary-light mt-1">Berikut adalah ringkasan sistem hari ini.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
                class="bg-surface-light dark:bg-card-dark p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-blue-600 dark:text-blue-400">
                        <span class="material-icons-round">dataset</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-text-secondary-light">Total Tiket</p>
                        <h3 class="text-2xl font-bold text-text-light dark:text-text-dark">{{ $stats['total'] }}</h3>
                    </div>
                </div>
            </div>

            <div
                class="bg-surface-light dark:bg-card-dark p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-yellow-600 dark:text-yellow-400">
                        <span class="material-icons-round">hourglass_top</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-text-secondary-light">Menunggu</p>
                        <h3 class="text-2xl font-bold text-text-light dark:text-text-dark">{{ $stats['waiting'] }}</h3>
                    </div>
                </div>
            </div>

            <div
                class="bg-surface-light dark:bg-card-dark p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg text-purple-600 dark:text-purple-400">
                        <span class="material-icons-round">engineering</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-text-secondary-light">Diproses</p>
                        <h3 class="text-2xl font-bold text-text-light dark:text-text-dark">{{ $stats['progress'] }}</h3>
                    </div>
                </div>
            </div>

            <div
                class="bg-surface-light dark:bg-card-dark p-6 rounded-xl shadow-sm border border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-green-50 dark:bg-green-900/20 rounded-lg text-green-600 dark:text-green-400">
                        <span class="material-icons-round">check_circle</span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-text-secondary-light">Selesai</p>
                        <h3 class="text-2xl font-bold text-text-light dark:text-text-dark">{{ $stats['done'] }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div
                class="lg:col-span-2 bg-surface-light dark:bg-card-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="font-semibold text-text-light dark:text-text-dark">Tiket Terbaru</h2>
                    <a href="{{ route('tickets.index') }}" class="text-sm text-primary hover:underline">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs text-text-secondary-light uppercase bg-gray-50 dark:bg-gray-800/50">
                            <tr>
                                <th class="px-6 py-3">Kode</th>
                                <th class="px-6 py-3">Judul</th>
                                <th class="px-6 py-3">Pelapor</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($recentTickets as $ticket)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition">
                                    <td class="px-6 py-4 font-medium text-text-light dark:text-text-dark">
                                        {{ $ticket->ticket_code }}
                                    </td>
                                    <td class="px-6 py-4 text-text-secondary-light">
                                        {{ Str::limit($ticket->title, 30) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div
                                                class="size-6 rounded-full bg-primary/10 flex items-center justify-center text-xs font-bold text-primary">
                                                {{ substr($ticket->user->name, 0, 1) }}
                                            </div>
                                            <span
                                                class="text-text-light dark:text-text-dark">{{ $ticket->user->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @php
                                            $colors = [
                                                'waiting' =>
                                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                'progress' =>
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                                'done' =>
                                                    'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                'reject' =>
                                                    'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                            ];
                                        @endphp
                                        <span
                                            class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colors[$ticket->status->value] ?? 'bg-gray-100' }}">
                                            {{ ucfirst($ticket->status->value) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div
                class="bg-surface-light dark:bg-card-dark rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-6">
                <h2 class="font-semibold text-text-light dark:text-text-dark mb-4">Layanan Terpopuler</h2>
                <div class="space-y-4">
                    @foreach ($serviceStats as $service)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-text-secondary-light">{{ $service->name }}</span>
                                <span
                                    class="font-medium text-text-light dark:text-text-dark">{{ $service->tickets_count }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                                <div class="bg-primary h-2 rounded-full"
                                    style="width: {{ $stats['total'] > 0 ? ($service->tickets_count / $stats['total']) * 100 : 0 }}%">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
