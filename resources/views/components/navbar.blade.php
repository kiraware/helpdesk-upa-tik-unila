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

    {{-- KIRI --}}
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

    {{-- KANAN --}}
    <div class="flex items-center space-x-4 relative" x-data="{ open: false }">
        <button
            class="p-1 rounded-full text-muted-light dark:text-slate-400
                   hover:bg-gray-100 dark:hover:bg-slate-800 transition-colors">
            <span class="material-icons-round">notifications</span>
        </button>

        <div class="h-6 w-px bg-border-light dark:bg-slate-700/70"></div>

        <button @click="open = !open" class="flex items-center gap-3 focus:outline-none">
            <div class="text-right hidden sm:block leading-tight min-w-0 max-w-[180px]">
                <p class="text-sm font-medium text-text-light dark:text-slate-100 truncate"
                    title="{{ auth()->user()->name }}">
                    {{ auth()->user()->name }}
                </p>

                <p class="text-xs text-muted-light dark:text-slate-400 capitalize truncate">
                    {{ auth()->user()->role }}
                </p>
            </div>

            <img src="{{ auth()->user()->photo
                ? asset('storage/' . auth()->user()->photo)
                : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}"
                class="w-9 h-9 rounded-full object-cover
                       border border-border-light dark:border-slate-600
                       shadow-sm" />
        </button>

        {{-- MENU DROPDOWN --}}
        <div x-show="open" x-transition x-cloak @click.outside="open = false"
            class="absolute right-0 top-14 w-48
                   rounded-xl overflow-hidden shadow-xl
                   border border-border-light dark:border-slate-700
                   bg-white/90 dark:bg-slate-800/90
                   backdrop-blur-md backdrop-saturate-150">

            <a href="#"
                class="block px-4 py-2.5 text-sm
                       text-text-light dark:text-slate-100
                       hover:bg-gray-100/70 dark:hover:bg-slate-700/60">
                Profil
            </a>

            <div class="h-px bg-border-light dark:bg-slate-700/70"></div>

            <form method="POST" action="#">
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
</header>
