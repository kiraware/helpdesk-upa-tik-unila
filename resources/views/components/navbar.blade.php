<header
    class="sticky top-0 h-16 flex items-center justify-between px-4 sm:px-6 lg:px-8
           bg-surface-light dark:bg-surface-dark 
           border-b border-border-light dark:border-border-dark 
           shadow-sm z-40 gap-4">

    {{-- PEMISAH BAWAH HALUS --}}
    <div
        class="pointer-events-none absolute inset-x-0 bottom-0 h-px
               bg-linear-to-r from-transparent via-gray-300/70 dark:via-slate-600/60 to-transparent
               blur-[0.5px]">
    </div>

    {{-- KIRI: Toggle Sidebar & Title --}}
    <div class="flex items-center gap-3 flex-1 min-w-0">
        <button @click="sidebarOpen = !sidebarOpen"
            class="lg:hidden text-muted-light dark:text-slate-400
                   hover:text-primary transition-colors focus:outline-none shrink-0">
            <span class="material-icons-round">menu</span>
        </button>

        {{-- Judul: Truncate jika terlalu panjang --}}
        <h1 class="text-lg sm:text-xl font-semibold text-text-light dark:text-text-dark truncate"
            title="{{ $title ?? 'Dasbor' }}">
            {{ $title ?? 'Dasbor' }}
        </h1>
    </div>

    {{-- KANAN: Notifikasi & Profil --}}
    <div class="flex items-center space-x-2 sm:space-x-4 shrink-0">

        {{-- === TOMBOL NOTIFIKASI === --}}
        @php
            // Initial data agar load pertama tetap cepat (tidak perlu menunggu JS fetch)
            $initialUnreadCount = auth()->user()->unreadNotifications()->count();
            $initialNotifs = auth()
                ->user()
                ->unreadNotifications()
                ->take(5)
                ->get()
                ->map(function ($notif) {
                    return [
                        'id' => $notif->id,
                        'data' => $notif->data,
                        'created_at' => $notif->created_at->diffForHumans(),
                        'read_url' => route('notifications.read', $notif->id),
                    ];
                });
        @endphp

        <div class="relative" x-data="notificationManager({{ $initialUnreadCount }}, {{ json_encode($initialNotifs) }}, '{{ route('api.notifications') }}')">
            <button @click="notifOpen = !notifOpen"
                class="relative p-2 rounded-full text-muted-light dark:text-slate-400
                       hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors focus:outline-none">
                <span class="material-icons-round">notifications</span>

                <template x-if="unreadCount > 0">
                    <span class="absolute top-1.5 right-1.5 flex h-2.5 w-2.5">
                        <span
                            class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
                    </span>
                </template>
            </button>

            {{-- DROPDOWN NOTIFIKASI --}}
            <div x-show="notifOpen" x-transition x-cloak @click.outside="notifOpen = false"
                class="fixed inset-x-4 top-20 w-auto sm:absolute sm:inset-auto sm:right-0 sm:top-14 sm:w-96
                       rounded-xl overflow-hidden shadow-2xl border border-border-light dark:border-slate-700
                       bg-white/95 dark:bg-slate-800/95 backdrop-blur-md z-50">

                <div>
                    {{-- Header Dropdown --}}
                    <div
                        class="flex items-center justify-between px-4 py-3 border-b border-border-light dark:border-slate-700">
                        <h3 class="font-semibold text-text-light dark:text-slate-100">Notifikasi</h3>

                        <template x-if="unreadCount > 0">
                            <form action="{{ route('notifications.markAll') }}" method="POST">
                                @csrf
                                <button type="submit" class="text-xs text-blue-600 dark:text-blue-400 hover:underline">
                                    Tandai semua dibaca
                                </button>
                            </form>
                        </template>
                    </div>

                    {{-- List Notifikasi --}}
                    <div class="max-h-75 overflow-y-auto">
                        {{-- Looping Data JSON --}}
                        <template x-for="notification in notifications" :key="notification.id">
                            <a :href="notification.read_url"
                                class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-slate-700/50 border-b border-gray-100 dark:border-slate-700/50 transition-colors group">
                                <div class="flex gap-3">
                                    <div class="mt-1 shrink-0">
                                        <template x-if="notification.data.type === 'success'">
                                            <span
                                                class="material-icons-round text-green-500 bg-green-100 dark:bg-green-900/30 rounded-full p-1 text-sm">check_circle</span>
                                        </template>
                                        <template x-if="notification.data.type === 'error'">
                                            <span
                                                class="material-icons-round text-red-500 bg-red-100 dark:bg-red-900/30 rounded-full p-1 text-sm">error</span>
                                        </template>
                                        <template
                                            x-if="notification.data.type !== 'success' && notification.data.type !== 'error'">
                                            <span
                                                class="material-icons-round text-blue-500 bg-blue-100 dark:bg-blue-900/30 rounded-full p-1 text-sm">info</span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-text-light dark:text-slate-200 group-hover:text-blue-600 dark:group-hover:text-blue-400 truncate"
                                            x-text="notification.data.title"></p>
                                        <p class="text-xs text-muted-light dark:text-slate-400 mt-0.5 line-clamp-2"
                                            x-text="notification.data.message"></p>
                                        <p class="text-[10px] text-gray-400 mt-1" x-text="notification.created_at"></p>
                                    </div>
                                </div>
                            </a>
                        </template>

                        {{-- State Kosong --}}
                        <template x-if="notifications.length === 0">
                            <div class="py-8 text-center text-muted-light dark:text-slate-500">
                                <span
                                    class="material-icons-round text-4xl mb-2 text-gray-300 dark:text-slate-600">notifications_off</span>
                                <p class="text-sm">Tidak ada notifikasi baru</p>
                            </div>
                        </template>
                    </div>

                    {{-- Footer Dropdown --}}
                    <a href="{{ route('notifications.index') }}"
                        class="block bg-gray-50 dark:bg-slate-700/30 py-2 text-center text-xs font-medium text-text-light dark:text-slate-300 hover:text-blue-600 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors">
                        Lihat Semua Histori
                    </a>
                </div>
            </div>
        </div>

        {{-- PEMISAH VERTIKAL --}}
        <div class="h-6 w-px bg-border-light dark:bg-slate-700/70 hidden sm:block"></div>

        {{-- MENU PROFIL --}}
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-3 focus:outline-none group max-w-full">

                {{-- NAMA USER --}}
                <div class="text-right hidden sm:block leading-tight min-w-0 sm:max-w-30 md:max-w-37.5 lg:max-w-50">
                    <p class="text-sm font-medium text-text-light dark:text-slate-100 truncate"
                        title="{{ auth()->user()->name }}">
                        {{ auth()->user()->name }}
                    </p>
                    <p class="text-xs text-muted-light dark:text-slate-400 capitalize truncate">
                        {{ auth()->user()->role->value }}
                    </p>
                </div>

                {{-- FOTO PROFIL --}}
                <img src="{{ auth()->user()->avatar_path
                    ? asset('storage/' . auth()->user()->avatar_path)
                    : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}"
                    alt="Profile Photo"
                    class="w-9 h-9 rounded-full object-cover shrink-0
                           border border-border-light dark:border-slate-600
                           shadow-sm group-hover:ring-2 group-hover:ring-blue-100 transition-all" />
            </button>

            {{-- DROPDOWN PROFIL --}}
            <div x-show="open" x-transition x-cloak @click.outside="open = false"
                class="absolute right-0 top-14 w-56
                       rounded-xl overflow-hidden shadow-xl
                       border border-border-light dark:border-slate-700
                       bg-white/90 dark:bg-slate-800/90
                       backdrop-blur-md backdrop-saturate-150 z-50">

                <div
                    class="block sm:hidden px-4 py-3 border-b border-border-light dark:border-slate-700 bg-gray-50/50 dark:bg-slate-700/30">
                    <p class="text-sm font-medium text-text-light dark:text-white wrap-break-word">
                        {{ auth()->user()->name }}
                    </p>
                    <span class="text-xs text-muted-light dark:text-slate-400 capitalize">
                        {{ auth()->user()->role->value }}
                    </span>
                </div>

                {{-- Route Profil --}}
                <a href="{{ route('profile.edit') }}"
                    class="block px-4 py-2.5 text-sm
                           text-text-light dark:text-slate-100
                           hover:bg-gray-100/70 dark:hover:bg-slate-700/60">
                    Profil
                </a>

                <div class="h-px bg-border-light dark:bg-slate-700/70"></div>

                {{-- Route Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
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
