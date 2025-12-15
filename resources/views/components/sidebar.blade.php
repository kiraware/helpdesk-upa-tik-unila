<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="
        fixed inset-y-0 left-0 z-30 w-64
        flex flex-col
        bg-primary dark:bg-background-dark text-slate-300
        border-r border-slate-800
        transform transition-transform duration-300
        lg:static lg:translate-x-0
    ">
    {{-- Logo / Brand --}}
    <div class="h-16 flex items-center px-6 border-b border-slate-700/50">
        <span class="text-xl font-bold text-white tracking-wide">
            Helpdesk
        </span>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">

        {{-- Dashboard --}}
        <a href="#"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                  hover:bg-slate-700/50 hover:text-white transition-colors group">
            <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                dashboard
            </span>
            Dashboard
        </a>

        {{-- SUPERUSER --}}
        @if (auth()->user()->role === \App\Enums\UserRole::SUPERUSER)
            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                      hover:bg-slate-700/50 hover:text-white transition-colors group">
                <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                    people
                </span>
                User Management
            </a>

            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                      hover:bg-slate-700/50 hover:text-white transition-colors group">
                <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                    settings
                </span>
                System Settings
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
                Tickets
            </a>

            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                      hover:bg-slate-700/50 hover:text-white transition-colors group">
                <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                    category
                </span>
                Categories
            </a>
        @endif

        {{-- USER --}}
        @if (auth()->user()->role === \App\Enums\UserRole::USER)
            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                      hover:bg-slate-700/50 hover:text-white transition-colors group">
                <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                    list_alt
                </span>
                My Tickets
            </a>

            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg
                      hover:bg-slate-700/50 hover:text-white transition-colors group">
                <span class="material-icons-round text-slate-400 group-hover:text-white mr-3">
                    add_circle_outline
                </span>
                Create Ticket
            </a>
        @endif

    </nav>
</aside>
