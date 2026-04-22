<x-layouts.dashboard title="Histori Notifikasi">

    <div class="max-w-4xl mx-auto space-y-6">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h2 class="text-2xl font-bold text-text-light dark:text-text-dark">Notifikasi</h2>

            @if (auth()->user()->unreadNotifications->count() > 0)
                <form action="{{ route('notifications.markAll') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full sm:w-auto px-4 py-2 bg-white dark:bg-slate-800 border border-border-light dark:border-slate-700 rounded-lg text-sm shadow-sm hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors">
                        Tandai Semua Dibaca
                    </button>
                </form>
            @endif
        </div>

        <div
            class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
            <ul class="divide-y divide-border-light dark:divide-border-dark">

                @forelse($notifications as $notification)
                    <li
                        class="{{ $notification->read_at ? 'bg-transparent' : 'bg-blue-50/50 dark:bg-blue-900/10' }} hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">

                        <div class="p-4 sm:p-5">
                            <div class="flex items-start gap-3 sm:gap-4">

                                {{-- Icon --}}
                                <div class="shrink-0 mt-1">
                                    @if ($notification->data['type'] == 'success')
                                        <div
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400">
                                            <span class="material-icons-round text-lg sm:text-xl">check_circle</span>
                                        </div>
                                    @elseif($notification->data['type'] == 'error')
                                        <div
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center text-red-600 dark:text-red-400">
                                            <span class="material-icons-round text-lg sm:text-xl">error</span>
                                        </div>
                                    @else
                                        <div
                                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-blue-600 dark:text-blue-400">
                                            <span class="material-icons-round text-lg sm:text-xl">info</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 min-w-0">

                                    {{-- Title + Time (klik untuk redirect) --}}
                                    <a href="{{ route('notifications.read', $notification->id) }}" class="block">
                                        <div class="flex flex-row items-center justify-between gap-2 mb-1">
                                            <p
                                                class="text-sm font-semibold text-text-light dark:text-slate-200 truncate pr-2">
                                                {{ $notification->data['title'] }}
                                            </p>
                                            <span
                                                class="text-xs text-muted-light dark:text-slate-500 whitespace-nowrap shrink-0">
                                                {{ $notification->created_at->translatedFormat('d M, H:i') }}
                                            </span>
                                        </div>
                                    </a>

                                    {{-- Message --}}
                                    <div x-data="{ open: false }" class="text-sm text-muted-light dark:text-slate-400">

                                        <p x-ref="text" :class="open ? '' : 'line-clamp-2'"
                                            class="break-words transition-all duration-300">
                                            {{ $notification->data['message'] }}
                                        </p>

                                        {{-- Toggle hanya muncul jika benar-benar ke-clamp --}}
                                        <button x-show="$refs.text.scrollHeight > $refs.text.clientHeight"
                                            @click.stop="open = !open"
                                            class="mt-1 text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                            <span x-show="!open">Lihat selengkapnya</span>
                                            <span x-show="open">Sembunyikan</span>
                                        </button>

                                    </div>
                                </div>

                                {{-- Unread indicator --}}
                                @if (!$notification->read_at)
                                    <div class="shrink-0 self-center">
                                        <span class="block w-2.5 h-2.5 bg-blue-500 rounded-full"></span>
                                    </div>
                                @endif

                            </div>
                        </div>

                    </li>
                @empty
                    <div class="p-12 text-center text-muted-light dark:text-slate-500">
                        <span class="material-icons-round text-5xl mb-4 text-gray-200 dark:text-slate-700">
                            notifications_none
                        </span>
                        <p>Belum ada notifikasi.</p>
                    </div>
                @endforelse

            </ul>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>

    </div>
</x-layouts.dashboard>
