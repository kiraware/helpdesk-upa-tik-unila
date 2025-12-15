<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="
        fixed inset-y-0 left-0 z-30 w-64
        flex flex-col
        bg-primary dark:bg-background-dark text-slate-300
        border-r border-slate-800
        transform transition-transform duration-300
        lg:static lg:translate-x-0
    ">

    {{-- Logo / Merek --}}
    <div class="h-16 flex items-center px-6 border-b border-slate-700/50">
        <span class="text-xl font-bold text-white tracking-wide">
            Helpdesk
        </span>
    </div>

    {{-- Navigasi --}}
    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">

        {{-- Dasbor --}}
        <a href="#"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                  hover:bg-slate-700/50 hover:text-white transition-colors group">
            <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                dashboard
            </span>
            Dasbor
        </a>

        {{-- SUPERUSER --}}
        @if (auth()->user()->role === \App\Enums\UserRole::SUPERUSER)
            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                      hover:bg-slate-700/50 hover:text-white transition-colors group">
                <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                    people
                </span>
                Manajemen Pengguna
            </a>

            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                      hover:bg-slate-700/50 hover:text-white transition-colors group">
                <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                    settings
                </span>
                Pengaturan Sistem
            </a>
        @endif

        {{-- ADMIN --}}
        @if (auth()->user()->role === \App\Enums\UserRole::ADMIN)
            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                      hover:bg-slate-700/50 hover:text-white transition-colors group">
                <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                    confirmation_number
                </span>
                Tiket
            </a>

            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                      hover:bg-slate-700/50 hover:text-white transition-colors group">
                <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                    category
                </span>
                Kategori
            </a>
        @endif

        {{-- PENGGUNA --}}
        @if (auth()->user()->role === \App\Enums\UserRole::USER)
            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                      hover:bg-slate-700/50 hover:text-white transition-colors group">
                <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                    list_alt
                </span>
                Tiket Saya
            </a>

            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                      hover:bg-slate-700/50 hover:text-white transition-colors group">
                <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                    add_circle_outline
                </span>
                Buat Tiket
            </a>
        @endif

    </nav>
</aside>
