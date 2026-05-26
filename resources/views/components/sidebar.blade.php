@php
    // Cek apakah user adalah Admin atau Superuser
    $isStaff =
        auth()->user()->role === \App\Enums\UserRole::ADMIN || auth()->user()->role === \App\Enums\UserRole::SUPERUSER;
@endphp

<aside x-data="sidebarCounter(
    {{ $waitingCount ?? 0 }},
    {{ $assignedProgressCount ?? 0 }},
    '{{ $isStaff ? route('api.ticket.counts') : '' }}'
)" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-30 w-64 flex flex-col
           bg-primary dark:bg-surface-dark text-text-dark
           border-r border-border-dark
           transform transition-transform duration-300
           lg:translate-x-0 
           lg:sticky lg:top-0 lg:h-screen">

    {{-- Logo / Brand --}}
    <div class="h-12 shrink-0 flex items-center px-5 border-b border-border-dark bg-opacity-50">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5 hover:opacity-80 transition">

            <div class="p-1 rounded-lg">
                <img src="{{ asset('img/logo-unila.png') }}" alt="Logo Unila" class="w-7 h-7 object-contain">
            </div>

            <span class="text-base font-bold text-white tracking-wide">
                Helpdesk
            </span>
        </a>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto custom-scrollbar">

        {{-- 1. GLOBAL MENUS --}}
        <p class="px-2 text-[10px] font-semibold text-muted-dark uppercase tracking-wider mb-1 mt-1">
            Menu Utama
        </p>

        <a href="{{ route('dashboard') }}"
            class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
            {{ request()->routeIs('dashboard')
                ? 'bg-secondary text-white shadow-lg shadow-blue-900/20'
                : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
            <span
                class="material-icons-round text-[18px] mr-2.5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                dashboard
            </span>
            Dasbor
        </a>

        {{-- 2. USER MENU --}}
        @if (auth()->user()->role === \App\Enums\UserRole::USER)
            <a href="{{ route('tickets.create') }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
               {{ request()->routeIs('tickets.create')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round text-[18px] mr-2.5 {{ request()->routeIs('tickets.create') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    add_circle
                </span>
                Buat Tiket Baru
            </a>

            <a href="{{ route('tickets.index') }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
               {{ request()->routeIs('tickets.index') || request()->routeIs('tickets.show')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round text-[18px] mr-2.5 {{ request()->routeIs('tickets.index') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    list_alt
                </span>
                Tiket Saya
            </a>
        @endif

        {{-- 3. ADMIN & SUPERUSER MENU --}}
        @if (auth()->user()->role === \App\Enums\UserRole::ADMIN || auth()->user()->role === \App\Enums\UserRole::SUPERUSER)
            {{-- Tiket Management --}}
            <div class="mt-3 mb-1 px-2">
                <p class="text-[10px] font-semibold text-muted-dark uppercase tracking-wider">
                    Manajemen Tiket
                </p>
            </div>

            <a href="{{ route('tickets.index') }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
               {{ request()->routeIs('tickets.index') && !request()->has('assigned')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round text-[18px] mr-2.5 {{ request()->routeIs('tickets.index') && !request()->has('assigned') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    inbox
                </span>
                Semua Tiket
            </a>

            <a href="{{ route('tickets.index', ['status' => \App\Enums\TicketStatus::WAITING->value]) }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
    {{ request()->input('status') == \App\Enums\TicketStatus::WAITING->value
        ? 'bg-secondary text-white'
        : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">

                <span
                    class="material-icons-round text-[18px] mr-2.5
        {{ request()->input('status') == \App\Enums\TicketStatus::WAITING->value ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    schedule
                </span>

                <div class="flex items-center justify-between w-full">
                    <span>Tiket Menunggu</span>

                    <template x-if="waitingCount > 0">
                        <span class="relative ml-auto mr-1">
                            <span
                                class="absolute -top-2.5 -right-1 flex items-center justify-center min-w-4.5 h-4.5 px-1
                         text-[10px] font-bold leading-none text-yellow-800 bg-yellow-400 rounded-full z-10"
                                x-text="waitingCount > 99 ? '99+' : waitingCount">
                            </span>
                            <span
                                class="absolute -top-2.5 -right-1 min-w-4.5 h-4.5 bg-yellow-400 rounded-full animate-ping opacity-75"></span>
                        </span>
                    </template>
                </div>
            </a>

            <a href="{{ route('tickets.index', ['assigned_to' => 'me']) }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
               {{ request()->input('assigned_to') == 'me'
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round text-[18px] mr-2.5 {{ request()->input('assigned_to') == 'me' ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    assignment_ind
                </span>
                <div class="flex items-center justify-between w-full">
                    <span>Tiket Ditugaskan</span>

                    <template x-if="assignedProgressCount > 0">
                        <span class="relative ml-auto mr-1">
                            <span
                                class="absolute -top-2.5 -right-1 flex items-center justify-center min-w-4.5 h-4.5 px-1
                         text-[10px] font-bold leading-none text-blue-900 bg-blue-400 rounded-full z-10"
                                x-text="assignedProgressCount > 99 ? '99+' : assignedProgressCount">
                            </span>
                            <span
                                class="absolute -top-2.5 -right-1 min-w-4.5 h-4.5 bg-blue-400 rounded-full animate-ping opacity-75"></span>
                        </span>
                    </template>
                </div>
            </a>

            <a href="{{ route('reports.index') }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
               {{ request()->routeIs('reports.*')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round text-[18px] mr-2.5 {{ request()->routeIs('reports.*') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    bar_chart
                </span>
                Laporan
            </a>

            {{-- Master Data --}}
            <div class="mt-3 mb-1 px-2">
                <p class="text-[10px] font-semibold text-muted-dark uppercase tracking-wider">
                    Master Data
                </p>
            </div>

            <a href="{{ route('sso-users.index') }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
               {{ request()->routeIs('sso-users.*')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round text-[18px] mr-2.5 {{ request()->routeIs('sso-users.*') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    lock_reset
                </span>
                User SSO
            </a>

            <a href="{{ route('services.index') }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
               {{ request()->routeIs('services.*')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round text-[18px] mr-2.5 {{ request()->routeIs('services.*') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    dns
                </span>
                Layanan
            </a>

            <a href="{{ route('divisions.index') }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
               {{ request()->routeIs('divisions.*')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round text-[18px] mr-2.5 {{ request()->routeIs('divisions.*') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    business
                </span>
                Penanggung Jawab
            </a>

            <a href="{{ route('departments.index') }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
               {{ request()->routeIs('departments.*')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round text-[18px] mr-2.5 {{ request()->routeIs('departments.*') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    apartment
                </span>
                Departemen
            </a>
        @endif

        {{-- 4. SUPERUSER ONLY --}}
        @if (auth()->user()->role === \App\Enums\UserRole::SUPERUSER)
            <div class="mt-3 mb-1 px-2">
                <p class="text-[10px] font-semibold text-muted-dark uppercase tracking-wider">
                    Administrator
                </p>
            </div>

            <a href="{{ route('users.index') }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group
               {{ request()->routeIs('users.*')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round text-[18px] mr-2.5 {{ request()->routeIs('users.*') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    manage_accounts
                </span>
                Manajemen Staff
            </a>

            <a href="{{ route('configurations.index') }}"
                class="flex items-center px-2 py-1.5 text-xs font-medium rounded-lg transition-colors group 
               {{ request()->routeIs('configurations.*')
                   ? 'bg-secondary text-white'
                   : 'text-muted-dark hover:bg-background-dark/30 hover:text-white' }}">
                <span
                    class="material-icons-round text-[18px] mr-2.5 {{ request()->routeIs('configurations.*') ? 'text-white' : 'text-muted-dark group-hover:text-white' }}">
                    admin_panel_settings
                </span>
                Pengaturan Surat Tugas
            </a>
        @endif

    </nav>

    {{-- Footer Sidebar --}}
    <div class="py-2 px-4 border-t border-border-dark shrink-0">
        <p class="text-[10px] text-center text-muted-dark">
            &copy; {{ date('Y') }} Helpdesk UPA TIK Unila
        </p>
    </div>
</aside>
