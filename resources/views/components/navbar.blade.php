<header
    class="sticky top-0 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8
           bg-surface-light dark:bg-surface-dark 
           border-b border-border-light dark:border-border-dark 
           shadow-sm z-40">

    {{-- PEMISAH BAWAH HALUS --}}
    <div
        class="pointer-events-none absolute inset-x-0 bottom-0 h-px
               bg-linear-to-r from-transparent via-gray-300/70 dark:via-slate-600/60 to-transparent
               blur-[0.5px]">
    </div>

    {{-- KIRI: Toggle Sidebar & Title --}}
    <div class="flex items-center gap-3">
        <button @click="sidebarOpen = !sidebarOpen"
            class="md:hidden text-muted-light dark:text-slate-400
                   hover:text-primary transition-colors focus:outline-none">
            <span class="material-icons-round">menu</span>
        </button>

        <h1 class="hidden md:block text-xl font-semibold text-text-light dark:text-text-dark">
            {{ $title ?? 'Dasbor' }}
        </h1>
    </div>

    {{-- KANAN: Notifikasi & Profil --}}
    <div class="flex items-center space-x-2 sm:space-x-4">

        {{-- === TOMBOL NOTIFIKASI === --}}
        <div class="relative" x-data="{ notifOpen: false }">
            @php
                // Ambil 5 notifikasi belum dibaca terbaru
                $unreadNotifs = auth()->user()->unreadNotifications()->take(5)->get();
                $unreadCount = auth()->user()->unreadNotifications()->count();
            @endphp

            <button @click="notifOpen = !notifOpen"
                class="relative p-2 rounded-full text-muted-light dark:text-slate-400
                       hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">
                <span class="material-icons-round">notifications</span>

                {{-- Badge Merah (Hanya muncul jika ada unread) --}}
                @if ($unreadCount > 0)
                    <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                @endif
            </button>

            {{-- DROPDOWN NOTIFIKASI --}}
            <div x-show="notifOpen" x-transition x-cloak @click.outside="notifOpen = false"
                class="absolute right-0 top-14 w-80 sm:w-96
                       rounded-xl overflow-hidden shadow-2xl
                       border border-border-light dark:border-slate-700
                       bg-white/95 dark:bg-slate-800/95
                       backdrop-blur-md z-50">

                <div
                    class="flex items-center justify-between px-4 py-3 border-b border-border-light dark:border-slate-700">
                    <h3 class="font-semibold text-text-light dark:text-slate-100">Notifikasi</h3>
                    @if ($unreadCount > 0)
                        <form action="{{ route('notifications.markAll') }}" method="POST">
                            @csrf
                            <button type="submit" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                Tandai semua dibaca
                            </button>
                        </form>
                    @endif
                </div>

                <div class="max-h-[300px] overflow-y-auto">
                    @forelse($unreadNotifs as $notification)
                        {{-- Klik notifikasi akan memanggil route 'read' yang kemudian me-redirect ke URL tujuan --}}
                        <a href="{{ route('notifications.read', $notification->id) }}"
                            class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/50 border-b border-gray-100 dark:border-slate-700/50 transition-colors group">
                            <div class="flex gap-3">
                                {{-- Icon Berdasarkan Tipe --}}
                                <div class="mt-1 shrink-0">
                                    @if ($notification->data['type'] == 'success')
                                        <span
                                            class="material-icons-round text-green-500 bg-green-100 dark:bg-green-900/30 rounded-full p-1 text-sm">check_circle</span>
                                    @elseif($notification->data['type'] == 'error')
                                        <span
                                            class="material-icons-round text-red-500 bg-red-100 dark:bg-red-900/30 rounded-full p-1 text-sm">error</span>
                                    @else
                                        <span
                                            class="material-icons-round text-blue-500 bg-blue-100 dark:bg-blue-900/30 rounded-full p-1 text-sm">info</span>
                                    @endif
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-text-light dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400">
                                        {{ $notification->data['title'] }}
                                    </p>
                                    <p class="text-xs text-muted-light dark:text-slate-400 mt-0.5 line-clamp-2">
                                        {{ $notification->data['message'] }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 mt-1">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="py-8 text-center text-muted-light dark:text-slate-500">
                            <span
                                class="material-icons-round text-4xl mb-2 text-gray-300 dark:text-slate-600">notifications_off</span>
                            <p class="text-sm">Tidak ada notifikasi baru</p>
                        </div>
                    @endforelse
                </div>

                <a href="{{ route('notifications.index') }}"
                    class="block bg-gray-50 dark:bg-slate-700/30 py-2 text-center text-xs font-medium text-text-light dark:text-slate-300 hover:text-blue-600 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                    Lihat Semua Histori
                </a>
            </div>
        </div>

        {{-- PEMISAH VERTIKAL --}}
        <div class="h-6 w-px bg-border-light dark:bg-slate-700/70"></div>

        {{-- === MENU PROFIL === --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-3 focus:outline-none group">
                <div class="text-right hidden sm:block leading-tight min-w-0 max-w-[180px]">
                    <p class="text-sm font-medium text-text-light dark:text-slate-100 truncate"
                        title="{{ auth()->user()->name }}">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-muted-light dark:text-slate-400 capitalize truncate">
                        {{ auth()->user()->role->value }}
                    </p>
                </div>

                <img src="{{ auth()->user()->photo
                    ? asset('storage/' . auth()->user()->photo)
                    : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}"
                    class="w-9 h-9 rounded-full object-cover
                           border border-border-light dark:border-slate-600
                           shadow-sm group-hover:ring-2 group-hover:ring-blue-100 transition-all" />
            </button>

            {{-- DROPDOWN PROFIL --}}
            <div x-show="open" x-transition x-cloak @click.outside="open = false"
                class="absolute right-0 top-14 w-48
                       rounded-xl overflow-hidden shadow-xl
                       border border-border-light dark:border-slate-700
                       bg-white/90 dark:bg-slate-800/90
                       backdrop-blur-md backdrop-saturate-150 z-50">

                <div
                    class="block sm:hidden px-4 py-2 border-b border-border-light dark:border-slate-700 text-xs text-muted-light">
                    {{ auth()->user()->name }} <br>
                    <span class="capitalize">{{ auth()->user()->role->value }}</span>
                </div>

                <a href="#"
                    class="block px-4 py-2.5 text-sm
                           text-text-light dark:text-slate-100
                           hover:bg-gray-100/70 dark:hover:bg-slate-700/60">
                    Profil
                </a>

                <div class="h-px bg-border-light dark:bg-slate-700/70"></div>

                <form method="POST" action="#"> {{-- Sesuaikan route logout Anda --}}
                    @csrf
                    <button
                        class="w-full text-left px-4 py-2.5 text-sm
                               text-red-600 dark:text-red-400
                               hover:bg-red-50/70 dark:hover:bg-red-900/30">
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
