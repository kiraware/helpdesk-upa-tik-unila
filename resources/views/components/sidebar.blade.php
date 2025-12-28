<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col
           bg-primary dark:bg-surface-dark text-text-dark
           border-r border-border-dark
           transform transition-transform duration-300
           lg:translate-x-0 
           lg:sticky lg:top-0 lg:h-screen">

    {{-- Logo / Brand --}}
    <div class="h-16 shrink-0 flex items-center px-6 border-b border-border-dark bg-opacity-50">
        <div class="flex items-center gap-3">
            <div class="bg-secondary p-1.5 rounded-lg">
                <span class="material-icons-round text-white text-xl">support_agent</span>
            </div>
            <span class="text-lg font-bold text-white tracking-wide">
                Helpdesk
            </span>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto custom-scrollbar">

        {{-- 1. GLOBAL MENUS --}}
        <p class="px-3 text-xs font-semibold text-muted-dark uppercase tracking-wider mb-2 mt-2">
            Menu Utama
        </p>

        <a href="{{ route('dashboard') }}"
            class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group
            {{ request()->routeIs('dashboard')
                ? 'bg-secondary text-white shadow-lg shadow-blue-900/20'
                : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
            <span
                class="material-icons-round mr-3 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                dashboard
            </span>
            Dasbor
        </a>

        {{-- 2. USER MENU --}}
        @if (auth()->user()->role === \App\Enums\UserRole::USER)
            <a href="{{ route('tickets.create') }}"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group mt-1
               {{ request()->routeIs('tickets.create')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round mr-3 {{ request()->routeIs('tickets.create') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    add_circle
                </span>
                Buat Tiket Baru
            </a>

            <a href="{{ route('tickets.index') }}"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group
               {{ request()->routeIs('tickets.index') || request()->routeIs('tickets.show')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round mr-3 {{ request()->routeIs('tickets.index') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    list_alt
                </span>
                Tiket Saya
            </a>
        @endif

        {{-- 3. ADMIN & SUPERUSER MENU --}}
        @if (auth()->user()->role === \App\Enums\UserRole::ADMIN || auth()->user()->role === \App\Enums\UserRole::SUPERUSER)
            {{-- Tiket Management --}}
            <div class="mt-6 mb-2 px-3">
                <p class="text-xs font-semibold text-muted-dark uppercase tracking-wider">
                    Manajemen Tiket
                </p>
            </div>

            <a href="{{ route('tickets.index') }}"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group
               {{ request()->routeIs('tickets.index') && !request()->has('assigned')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round mr-3 {{ request()->routeIs('tickets.index') && !request()->has('assigned') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    inbox
                </span>
                Semua Tiket
            </a>

            <a href="{{ route('tickets.index', ['assigned_to' => 'me']) }}"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group
               {{ request()->input('assigned_to') == 'me'
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round mr-3 {{ request()->input('assigned_to') == 'me' ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    assignment_ind
                </span>
                Tiket Ditugaskan
            </a>

            <a href="{{ route('reports.index') }}"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group
               {{ request()->routeIs('reports.*')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round mr-3 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    bar_chart
                </span>
                Laporan
            </a>

            {{-- Master Data --}}
            <div class="mt-6 mb-2 px-3">
                <p class="text-xs font-semibold text-muted-dark uppercase tracking-wider">
                    Master Data
                </p>
            </div>

            <a href="{{ route('services.index') }}"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group
               {{ request()->routeIs('services.*')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round mr-3 {{ request()->routeIs('services.*') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    dns
                </span>
                Layanan
            </a>

            <a href="{{ route('divisions.index') }}"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group
               {{ request()->routeIs('divisions.*')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round mr-3 {{ request()->routeIs('divisions.*') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    business
                </span>
                Divisi
            </a>
        @endif

        {{-- 4. SUPERUSER ONLY --}}
        @if (auth()->user()->role === \App\Enums\UserRole::SUPERUSER)
            <div class="mt-6 mb-2 px-3">
                <p class="text-xs font-semibold text-muted-dark uppercase tracking-wider">
                    Administrator
                </p>
            </div>

            <a href="{{ route('users.index') }}"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group
               {{ request()->routeIs('users.*')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round mr-3 {{ request()->routeIs('users.*') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    manage_accounts
                </span>
                Manajemen Pengguna
            </a>

            <a href="#"
                class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors group 
               text-muted-dark hover:bg-background-dark/30 hover:text-white">
                <span class="material-icons-round mr-3 text-muted-dark group-hover:text-white">
                    settings
                </span>
                Pengaturan Sistem
            </a>
        @endif

    </nav>

    {{-- Footer Sidebar --}}
    <div class="p-4 border-t border-border-dark shrink-0">
        <p class="text-xs text-center text-muted-dark">
            &copy; {{ date('Y') }} Helpdesk System
        </p>
    </div>
</aside>
