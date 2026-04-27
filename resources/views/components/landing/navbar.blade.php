<header x-data="{ scrolled: false, mobileMenuOpen: false }" @scroll.window="scrolled = (window.pageYOffset > 20)"
    :class="{ 'bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md shadow-sm': scrolled, 'bg-transparent': !scrolled }"
    class="sticky top-0 z-50 border-b border-transparent transition-all duration-300"
    :class="{ 'border-border-light! dark:border-border-dark!': scrolled }">

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- LOGO --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                <div class="text-brand">
                    <span class="material-icons-round icon-lg">support_agent</span>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-lg font-bold leading-tight tracking-tight text-text-light dark:text-surface-light">
                        Helpdesk UPA TIK
                    </h1>
                    <span class="text-xs font-medium text-muted-light dark:text-muted-dark">Universitas Lampung</span>
                </div>
            </a>

            {{-- DESKTOP NAVIGATION --}}
            <nav class="hidden md:flex items-center gap-4">

                {{-- 1. FAQ --}}
                <a href="{{ route('faq') }}"
                    class="text-sm font-medium text-text-light dark:text-text-dark hover:text-brand transition-colors px-3 py-2">
                    FAQ
                </a>

                {{-- LOGIKA: HANYA UNTUK GUEST --}}
                @guest
                    {{-- Cek Status (Ghost Button style) --}}
                    <a href="{{ route('guest.tracking.index') }}"
                        class="text-sm font-medium text-text-light dark:text-text-dark 
                               hover:bg-gray-100 dark:hover:bg-slate-800 
                               px-4 py-2 rounded-lg transition-all">
                        Cek Status Tiket
                    </a>

                    <div class="h-4 w-px bg-border-light dark:bg-border-dark mx-1"></div>

                    {{-- Login --}}
                    <a href="{{ route('login') }}"
                        class="text-sm font-semibold text-text-light dark:text-text-dark 
                               border border-border-light dark:border-border-dark 
                               hover:border-brand hover:text-brand 
                               px-4 py-2 rounded-lg transition-all">
                        Login
                    </a>

                    {{-- Buat Tiket --}}
                    <a href="{{ route('guest.tickets.create') }}"
                        class="text-sm font-semibold text-white bg-brand hover:bg-brand-hover 
                               px-4 py-2 rounded-lg transition-colors shadow-sm shadow-blue-500/30">
                        Buat Tiket
                    </a>
                @endguest

                {{-- LOGIKA: HANYA UNTUK USER LOGIN --}}
                @auth
                    @php
                        $user = auth()->user();

                        $avatarUrl = $user->avatar_path
                            ? asset('storage/' . $user->avatar_path)
                            : 'https://ui-avatars.com/api/?name=' . urlencode($user->name);
                    @endphp

                    {{-- 2. TOMBOL DASHBOARD (LANGSUNG MUNCUL) --}}
                    <a href="{{ route('dashboard') }}"
                        class="text-sm font-semibold text-white bg-brand hover:bg-brand-hover 
                               px-4 py-2 rounded-lg transition-colors shadow-sm shadow-blue-500/30">
                        Dashboard
                    </a>

                    <div class="h-4 w-px bg-border-light dark:bg-border-dark mx-2"></div>

                    {{-- 3. PROFIL DROPDOWN --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-3 focus:outline-none pl-2 group">
                            <div class="text-right hidden lg:block leading-tight min-w-0 max-w-[150px]">
                                <p class="text-sm font-medium text-text-light dark:text-slate-100 truncate transition-colors"
                                    title="{{ auth()->user()->name }}">
                                    {{ auth()->user()->name }}
                                </p>
                                <p class="text-xs text-muted-light dark:text-slate-400 capitalize truncate">
                                    {{ auth()->user()->role->value }}
                                </p>
                            </div>

                            <img src="{{ $avatarUrl }}"
                                class="w-9 h-9 rounded-full object-cover
           border border-border-light dark:border-slate-600
           shadow-sm transition-all" />
                        </button>

                        {{-- DROPDOWN --}}
                        <div x-show="open" x-transition x-cloak @click.outside="open = false"
                            class="absolute right-0 top-12 w-48
                                   rounded-xl overflow-hidden shadow-xl
                                   border border-border-light dark:border-slate-700
                                   bg-white/90 dark:bg-slate-800/90
                                   backdrop-blur-md backdrop-saturate-150 mt-2 origin-top-right">

                            {{-- Menu Profil --}}
                            <a href="{{ route('profile.edit') }}"
                                class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium
                                       text-text-light dark:text-slate-100
                                       hover:bg-gray-100/70 dark:hover:bg-slate-700/60 transition-colors">
                                <span class="material-icons-round text-base text-muted-light">person</span>
                                Profil
                            </a>

                            <div class="h-px bg-border-light dark:bg-slate-700/70"></div>

                            {{-- Menu Keluar --}}
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full flex items-center gap-2 px-4 py-2.5 text-sm
                                           text-red-600 dark:text-red-400
                                           hover:bg-red-50/70 dark:hover:bg-red-900/30 transition-colors text-left">
                                    <span class="material-icons-round text-base">logout</span>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                @endauth

            </nav>

            {{-- MOBILE MENU BUTTON --}}
            <button @click="mobileMenuOpen = !mobileMenuOpen"
                class="md:hidden p-2 text-muted-light dark:text-muted-dark hover:bg-gray-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                <span class="material-icons-round icon-md">menu</span>
            </button>
        </div>
    </div>

    {{-- MOBILE MENU --}}
    <div x-show="mobileMenuOpen" x-transition
        class="md:hidden bg-surface-light dark:bg-surface-dark border-b border-border-light dark:border-border-dark shadow-lg"
        style="display: none;">
        <div class="px-4 py-4 space-y-3">

            <a href="{{ route('faq') }}"
                class="block text-sm font-medium text-text-light dark:text-text-dark py-2 border-b border-border-light dark:border-border-dark">FAQ</a>

            @guest
                {{-- MOBILE GUEST MENU --}}
                <a href="{{ route('guest.tracking.index') }}"
                    class="block text-sm font-medium text-text-light dark:text-text-dark py-2 hover:text-brand">
                    Cek Status Tiket
                </a>

                <div class="pt-2 flex flex-col gap-3">
                    <a href="{{ route('login') }}"
                        class="block text-center text-sm font-semibold border border-border-light dark:border-border-dark py-2.5 rounded-lg dark:text-text-dark hover:bg-gray-50 dark:hover:bg-slate-800">
                        Login
                    </a>
                    <a href="{{ route('guest.tickets.create') }}"
                        class="block text-center text-sm font-semibold bg-brand hover:bg-brand-hover text-white py-2.5 rounded-lg shadow-sm">
                        Buat Tiket
                    </a>
                </div>
            @endguest

            @auth
                @php
                    $user = auth()->user();

                    $avatarUrl = $user->avatar_path
                        ? asset('storage/' . $user->avatar_path)
                        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name);
                @endphp

                {{-- MOBILE AUTH MENU --}}
                <div class="pt-2">
                    {{-- User Info Header --}}
                    <div class="flex items-center gap-3 mb-4">
                        <img src="{{ $avatarUrl }}"
                            class="w-10 h-10 rounded-full object-cover border border-border-light">
                        <div class="flex flex-col">
                            <span
                                class="text-sm font-bold text-text-light dark:text-text-dark">{{ auth()->user()->name }}</span>
                            <span class="text-xs text-muted-light capitalize">{{ auth()->user()->role->value }}</span>
                        </div>
                    </div>

                    {{-- Dashboard Button --}}
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-2 w-full text-sm font-medium text-white bg-brand px-3 py-2.5 rounded-lg hover:bg-brand-hover shadow-sm mb-2">
                        <span class="material-icons-round text-white">dashboard</span>
                        Dashboard
                    </a>

                    {{-- Profil Link --}}
                    <a href="{{ route('profile.edit') }}"
                        class="flex items-center gap-2 w-full text-sm font-medium text-text-light dark:text-text-dark px-3 py-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-800">
                        <span class="material-icons-round text-muted-light">person</span>
                        Profil
                    </a>

                    {{-- Logout --}}
                    <form method="POST" action="{{ route('logout') }}" class="mt-1">
                        @csrf
                        <button
                            class="flex items-center gap-2 w-full text-left text-sm font-medium text-red-600 px-3 py-2.5 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20">
                            <span class="material-icons-round">logout</span>
                            Keluar
                        </button>
                    </form>
                </div>
            @endauth

        </div>
    </div>
</header>
