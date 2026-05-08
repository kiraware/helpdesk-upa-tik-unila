@props(['ticket', 'admins', 'services'])

@php
    use Illuminate\Support\Str;
    use App\Enums\TicketStatus;
    use App\Enums\UserRole;
    use App\Enums\TicketPriority;

    $isClosed = in_array($ticket->status, [TicketStatus::DONE, TicketStatus::REJECT]);
    $currentUser = auth()->user();

    // Cek Role Admin/Superuser
    $isAdminOrSuper = $currentUser && in_array($currentUser->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

    // Logic Tombol "Ambil Tiket"
    $canTakeTicket = $isAdminOrSuper;

    $canEditPriority = false;
    $canEditAssignee = false;

    // Cek apakah status memenuhi syarat: Berikan hak edit ke Admin & Superuser
    if (in_array($ticket->status, [TicketStatus::WAITING, TicketStatus::PROGRESS])) {
        if ($isAdminOrSuper) {
            $canEditPriority = true;
            $canEditAssignee = true;
        }
    }

    $canEditService = $canEditPriority;

    $prioColor = match ($ticket->priority) {
        TicketPriority::HIGH => 'text-red-600',
        TicketPriority::MEDIUM => 'text-yellow-600',
        TicketPriority::LOW => 'text-gray-600',
        TicketPriority::DEFAULT => 'text-gray-600',
    };
@endphp

<div class="space-y-6">
    {{-- ASSIGNEE --}}
    <div x-data="{ openAssignee: false }"
        class="relative border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark shadow-sm">

        <div
            class="px-4 py-3 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50 flex justify-between items-center rounded-t-xl">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-light">Petugas</h3>

            <div class="flex items-center gap-2">
                {{-- Tombol Ambil Tiket --}}
                @if (is_null($ticket->assigned_to) && !$isClosed && $canTakeTicket)
                    <form method="POST" action="{{ route('tickets.assign.me', $ticket) }}">
                        @csrf
                        <button type="submit" class="text-xs text-secondary hover:underline">Ambil Tiket</button>
                    </form>
                @endif

                @if ($canEditAssignee)
                    <button @click="openAssignee = !openAssignee" type="button"
                        class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors focus:outline-none flex items-center justify-center p-0.5 rounded hover:bg-gray-200 dark:hover:bg-slate-700"
                        title="Ubah Petugas">
                        <span class="material-icons-round text-[14px]">settings</span>
                    </button>
                @endif
            </div>
        </div>

        <div class="p-4">
            @if ($ticket->assignee)
                <div class="flex items-center gap-3">
                    {{-- Foto Profil Petugas Utama --}}
                    <img src="{{ $ticket->assignee->avatar_path ? asset('storage/' . $ticket->assignee->avatar_path) : 'https://ui-avatars.com/api/?name=' . urlencode($ticket->assignee->name) }}"
                        class="w-8 h-8 rounded-full object-cover border border-border-light dark:border-slate-600 shadow-sm">

                    <div class="min-w-0">
                        <p
                            class="text-sm font-medium text-text-light dark:text-text-dark break-all wrap-break-word whitespace-normal max-w-full">
                            {{ $ticket->assignee->name }}
                        </p>
                        <p class="text-xs text-muted-light">Ditugaskan
                            {{ $ticket->assigned_at ? $ticket->assigned_at->diffForHumans() : '-' }}</p>
                    </div>
                </div>

                {{-- Tombol Download Surat Tugas --}}
                @if ($isAdminOrSuper || auth()->id() === $ticket->assigned_to)
                    <div class="mt-4 pt-3 border-t border-border-light dark:border-border-dark/50">
                        <a href="{{ route('tickets.print_assignment', $ticket) }}" target="_blank"
                            class="flex items-center justify-center gap-2 w-full py-2 px-3 text-xs font-medium text-text-light dark:text-text-dark bg-white dark:bg-slate-700 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 hover:text-blue-600 transition-colors">
                            <span class="material-icons-round text-sm">print</span>
                            Unduh Surat Tugas
                        </a>
                    </div>
                @endif
            @else
                <div class="text-sm text-muted-light italic">Belum ada petugas</div>
            @endif

            {{-- Dropdown Ubah Petugas --}}
            @if ($canEditAssignee)
                <div x-show="openAssignee" @click.outside="openAssignee = false"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="transform opacity-100 scale-100"
                    x-transition:leave-end="transform opacity-0 scale-95"
                    class="absolute right-0 top-12 mt-1 w-56 rounded-lg shadow-lg bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 dark:ring-border-dark z-50 py-1 max-h-60 overflow-y-auto"
                    style="display: none;">

                    <form method="POST" action="{{ route('tickets.update_assignee', $ticket) }}">
                        @csrf
                        @method('PATCH')

                        <button type="submit" name="assigned_to" value=""
                            class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors {{ is_null($ticket->assigned_to) ? 'bg-red-50 dark:bg-slate-700/50 font-bold' : '' }}">
                            <span class="material-icons-round text-[16px]">person_off</span>
                            Kosongkan Petugas

                            @if (is_null($ticket->assigned_to))
                                <span class="material-icons-round text-[14px] ml-auto text-red-600">check</span>
                            @endif
                        </button>

                        <div class="border-t border-border-light dark:border-border-dark my-1"></div>

                        @foreach ($admins as $admin)
                            <button type="submit" name="assigned_to" value="{{ $admin->id }}"
                                class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors {{ $ticket->assigned_to === $admin->id ? 'bg-gray-50 dark:bg-slate-700/50 font-bold' : '' }}">

                                {{-- Foto Profil Petugas di Dropdown --}}
                                <img src="{{ $admin->avatar_path ? asset('storage/' . $admin->avatar_path) : 'https://ui-avatars.com/api/?name=' . urlencode($admin->name) }}"
                                    class="w-5 h-5 rounded-full object-cover border border-border-light dark:border-slate-600">

                                <span class="truncate">{{ $admin->name }}</span>

                                @if ($ticket->assigned_to === $admin->id)
                                    <span
                                        class="material-icons-round text-[14px] ml-auto text-blue-600 dark:text-blue-400">check</span>
                                @endif
                            </button>
                        @endforeach
                    </form>
                </div>
            @endif
        </div>
    </div>

    {{-- INFO --}}
    <div
        class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark shadow-sm">
        <div
            class="px-4 py-3 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50 rounded-t-xl">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-light">Informasi</h3>
        </div>
        <div class="p-4 space-y-4">
            {{-- Layanan --}}
            <div x-data="{ openService: false }" class="relative">
                <div class="flex justify-between items-center gap-2 mb-1">
                    <p class="text-xs text-muted-light">Layanan</p>

                    @if ($canEditService)
                        <button @click="openService = !openService" type="button"
                            class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors flex items-center justify-center p-0.5 rounded hover:bg-gray-200 dark:hover:bg-slate-700"
                            title="Ubah Layanan">
                            <span class="material-icons-round text-[14px]">settings</span>
                        </button>
                    @endif
                </div>

                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                    {{ $ticket->service->name }}
                </span>

                @if ($canEditService)
                    <div x-show="openService" @click.outside="openService = false" x-transition
                        class="absolute right-0 top-full mt-2 w-56 bg-white dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-xl z-50 overflow-hidden max-h-60 overflow-y-auto"
                        style="display: none;">

                        <form method="POST" action="{{ route('tickets.update_service', $ticket) }}">
                            @csrf
                            @method('PATCH')

                            @foreach ($services as $index => $service)
                                <button type="submit" name="service_id" value="{{ $service->id }}"
                                    class="w-full text-left px-4 py-2 text-sm flex items-center gap-2 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors {{ $ticket->service_id === $service->id ? 'bg-gray-50 dark:bg-slate-700/50 font-semibold' : '' }}">

                                    <span class="truncate">{{ $service->name }}</span>

                                    @if ($ticket->service_id === $service->id)
                                        <span
                                            class="material-icons-round text-[14px] ml-auto text-blue-600 dark:text-blue-400">check</span>
                                    @endif
                                </button>

                                @if ($index < count($services) - 1)
                                    <div class="border-t border-border-light dark:border-border-dark"></div>
                                @endif
                            @endforeach
                        </form>
                    </div>
                @endif
            </div>

            {{-- Prioritas --}}
            <div x-data="{ openPriority: false }" class="relative">
                <div class="flex justify-between items-center gap-2 mb-1">
                    <p class="text-xs text-muted-light">Prioritas</p>

                    @if ($canEditPriority)
                        <button @click="openPriority = !openPriority" type="button"
                            class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors focus:outline-none flex items-center justify-center p-0.5 rounded hover:bg-gray-200 dark:hover:bg-slate-700"
                            title="Ubah Prioritas">
                            <span class="material-icons-round text-[14px]">settings</span>
                        </button>
                    @endif
                </div>

                <div class="flex items-center gap-1 text-sm font-medium {{ $prioColor }}">
                    <span class="material-icons-round text-base">flag</span>
                    {{ ucfirst($ticket->priority->value) }}
                </div>

                @if ($canEditPriority)
                    <div x-show="openPriority" @click.outside="openPriority = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 top-full mt-2 w-48 bg-white dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-xl z-50 overflow-hidden"
                        style="display: none;">

                        <form method="POST" action="{{ route('tickets.update_priority', $ticket) }}">
                            @csrf
                            @method('PATCH')
                            @foreach (\App\Enums\TicketPriority::cases() as $priority)
                                <button type="submit" name="priority" value="{{ $priority->value }}"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors {{ $ticket->priority === $priority ? 'bg-gray-50 dark:bg-slate-700/50 font-bold' : '' }}">

                                    <span
                                        class="material-icons-round text-[16px] 
                                        @if ($priority === \App\Enums\TicketPriority::HIGH) text-red-600 
                                        @elseif($priority === \App\Enums\TicketPriority::MEDIUM) text-yellow-600 
                                        @else text-gray-600 dark:text-gray-400 @endif">
                                        flag
                                    </span>
                                    {{ ucfirst($priority->value) }}

                                    @if ($ticket->priority === $priority)
                                        <span
                                            class="material-icons-round text-[14px] ml-auto text-blue-600 dark:text-blue-400">check</span>
                                    @endif
                                </button>
                            @endforeach
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- RIWAYAT TIKET (TIMELINE) --}}
    <div
        class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm">
        <div class="px-4 py-3 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-light">Riwayat Tiket</h3>
        </div>
        <div class="p-4 space-y-4">

            {{-- Created At --}}
            <div class="relative pl-4 border-l-2 border-border-light dark:border-border-dark">
                <div
                    class="absolute -left-1.25 top-1 w-2.5 h-2.5 rounded-full bg-blue-500 ring-2 ring-white dark:ring-surface-dark">
                </div>
                <p class="text-xs text-muted-light mb-0.5">Dibuat</p>
                <p class="text-sm font-medium text-text-light dark:text-text-dark">
                    {{ $ticket->created_at->format('d M Y, H:i') }}
                </p>
            </div>

            {{-- Assigned At --}}
            @if ($ticket->assigned_at)
                <div class="relative pl-4 border-l-2 border-border-light dark:border-border-dark">
                    <div
                        class="absolute -left-1.25 top-1 w-2.5 h-2.5 rounded-full bg-yellow-500 ring-2 ring-white dark:ring-surface-dark">
                    </div>
                    <p class="text-xs text-muted-light mb-0.5">Ditugaskan</p>
                    <p class="text-sm font-medium text-text-light dark:text-text-dark">
                        {{ $ticket->assigned_at->format('d M Y, H:i') }}
                    </p>
                </div>
            @endif

            {{-- Closed At --}}
            @if ($ticket->closed_at)
                <div class="relative pl-4 border-l-2 border-transparent">
                    <div
                        class="absolute -left-1.25 top-1 w-2.5 h-2.5 rounded-full {{ $ticket->status === TicketStatus::DONE ? 'bg-emerald-500' : 'bg-red-500' }} ring-2 ring-white dark:ring-surface-dark">
                    </div>
                    <p class="text-xs text-muted-light mb-0.5">
                        {{ $ticket->status === TicketStatus::DONE ? 'Selesai' : 'Ditolak' }}
                    </p>
                    <p class="text-sm font-medium text-text-light dark:text-text-dark">
                        {{ $ticket->closed_at->format('d M Y, H:i') }}
                    </p>
                </div>
            @endif

        </div>
    </div>
</div>
