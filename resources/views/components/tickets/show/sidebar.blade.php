@props(['ticket'])

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

    // Logic Menampilkan Data Sensitif (Email, ID, Foto, Phone)
    // Jika tiket dibuat oleh Guest (user_id null), maka data sensitif HANYA boleh dilihat oleh Admin/Superuser.
    // User biasa atau Guest (public tracking) akan melihat data tersensor.
    $isGuestTicket = is_null($ticket->user_id);
    $showSensitiveData = !$isGuestTicket || $isAdminOrSuper;

    $canEditPriority = false;

    // Cek apakah status memenuhi syarat
    if (in_array($ticket->status, [TicketStatus::WAITING, TicketStatus::PROGRESS])) {
        if ($currentUser->role === UserRole::SUPERUSER) {
            $canEditPriority = true;
        } elseif ($currentUser->role === UserRole::ADMIN) {
            if (is_null($ticket->assigned_to) || $ticket->assigned_to === $currentUser->id) {
                $canEditPriority = true;
            }
        }
    }

    $prioColor = match ($ticket->priority) {
        TicketPriority::HIGH => 'text-red-600',
        TicketPriority::MEDIUM => 'text-yellow-600',
        TicketPriority::LOW => 'text-gray-600',
        TicketPriority::DEFAULT => 'text-gray-600',
    };

    // Helper closure untuk sensor Email
    $maskEmail = function ($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '******';
        }
        [$first, $last] = explode('@', $email);
        $len = strlen($first);
        $visible = floor($len / 2);
        $show = min(3, $visible);
        return substr($first, 0, $show) . str_repeat('*', 5) . '@' . $last;
    };

    // Helper closure untuk sensor ID
    $maskID = function ($id) {
        $len = strlen($id);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        return str_repeat('*', $len - 4) . substr($id, -4);
    };

    // [BARU] Helper closure untuk sensor Phone
    $maskPhone = function ($phone) {
        $len = strlen($phone);
        if ($len <= 4) {
            return str_repeat('*', $len);
        }
        // Tampilkan 4 digit terakhir
        return str_repeat('*', $len - 4) . substr($phone, -4);
    };
@endphp

<div class="space-y-6" x-data="{ showModal: false, modalImage: '' }">
    {{-- ASSIGNEE --}}
    <div
        class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm">
        <div
            class="px-4 py-3 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50 flex justify-between items-center">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-light">Petugas</h3>

            @if (is_null($ticket->assigned_to) && !$isClosed && $canTakeTicket)
                <form method="POST" action="{{ route('tickets.assign.me', $ticket) }}">
                    @csrf
                    <button type="submit" class="text-xs text-secondary hover:underline">Ambil Tiket</button>
                </form>
            @endif

        </div>
        <div class="p-4">
            @if ($ticket->assignee)
                <div class="flex items-center gap-3">
                    <img src="{{ $ticket->assignee->photo ? asset('storage/' . $ticket->assignee->photo) : 'https://ui-avatars.com/api/?name=' . urlencode($ticket->assignee->name) }}"
                        class="w-8 h-8 rounded-full">
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
        </div>
    </div>

    {{-- INFO --}}
    <div
        class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark shadow-sm">
        {{-- Catatan: 'overflow-hidden' dihapus dari wrapper atas dan diganti dengan 'rounded-t-xl' di bawah agar dropdown tidak terpotong --}}
        <div
            class="px-4 py-3 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50 rounded-t-xl">
            <h3 class="text-xs font-bold uppercase tracking-wider text-muted-light">Informasi</h3>
        </div>
        <div class="p-4 space-y-4">
            {{-- Layanan --}}
            <div>
                <p class="text-xs text-muted-light mb-1">Layanan</p>
                <span
                    class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 border border-indigo-100 dark:border-indigo-800">
                    {{ $ticket->service->name }}
                </span>
            </div>

            {{-- Prioritas --}}
            <div x-data="{ openPriority: false }" class="relative">
                <div class="flex justify-between items-center gap-2 mb-1">
                    <p class="text-xs text-muted-light">Prioritas</p>

                    {{-- Ikon Gear Muncul Jika Kondisi Logic Terpenuhi --}}
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

                {{-- Dropdown Ubah Prioritas --}}
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

    {{-- GUEST DETAILS --}}
    @if (!$ticket->user_id && $ticket->guestDetail)
        <div
            class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm">

            {{-- Header --}}
            <div class="px-4 py-3 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50">
                <h3 class="text-xs font-bold uppercase tracking-wider text-muted-light">
                    Detail Pelapor
                </h3>
            </div>

            <div class="p-4">
                {{-- Profile Section --}}
                <div class="flex items-center gap-3 mb-4">
                    {{-- Avatar --}}
                    <div class="shrink-0">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->guestDetail->full_name) }}"
                            class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold text-sm border border-border-light dark:border-border-dark shadow-sm object-cover">
                    </div>

                    {{-- Name --}}
                    <div class="flex flex-col min-w-0">
                        <span
                            class="text-sm font-bold text-text-light dark:text-text-dark break-all wrap-break-word whitespace-normal max-w-full leading-tight">
                            {{ $ticket->guestDetail->full_name }}
                        </span>
                    </div>
                </div>

                {{-- Fields Detail --}}
                <div class="space-y-4 text-xs">
                    {{-- Email --}}
                    <div>
                        <p class="text-muted-light dark:text-muted-dark font-medium mb-0.5">Email</p>
                        <p
                            class="text-text-light dark:text-text-dark font-medium break-all wrap-break-word whitespace-normal max-w-full">
                            @if ($showSensitiveData)
                                {{ $ticket->guestDetail->email }}
                            @else
                                {{ $maskEmail($ticket->guestDetail->email) }}
                            @endif
                        </p>
                    </div>

                    {{-- WhatsApp --}}
                    <div>
                        <p class="text-muted-light dark:text-muted-dark font-medium mb-0.5">WhatsApp</p>
                        <p
                            class="text-text-light dark:text-text-dark font-medium font-mono break-all wrap-break-word whitespace-normal max-w-full">
                            @if ($showSensitiveData && !empty($ticket->guestDetail->phone))
                                {{-- Jika Admin, Tampilkan Link WA --}}
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $ticket->guestDetail->phone)) }}"
                                    target="_blank"
                                    class="text-green-600 dark:text-green-400 hover:underline flex items-center gap-1 w-fit">
                                    {{ $ticket->guestDetail->phone }}
                                    <span class="material-icons-round text-[12px]">open_in_new</span>
                                </a>
                            @elseif(!empty($ticket->guestDetail->phone))
                                {{-- Jika Guest, Sensor --}}
                                {{ $maskPhone($ticket->guestDetail->phone) }}
                            @else
                                -
                            @endif
                        </p>
                    </div>

                    {{-- ID Number --}}
                    <div>
                        <p class="text-muted-light dark:text-muted-dark font-medium mb-0.5">Nomor ID</p>
                        <p
                            class="text-text-light dark:text-text-dark font-medium font-mono break-all wrap-break-word whitespace-normal max-w-full">
                            @if ($showSensitiveData)
                                {{ $ticket->guestDetail->identity_number }}
                            @else
                                {{ $maskID($ticket->guestDetail->identity_number) }}
                            @endif
                        </p>
                    </div>

                    {{-- Entity / Identity --}}
                    <div>
                        <p class="text-muted-light dark:text-muted-dark font-medium mb-0.5">Status</p>
                        <p
                            class="text-text-light dark:text-text-dark font-medium break-all wrap-break-word whitespace-normal max-w-full capitalize">
                            {{ $ticket->guestDetail->entity_type->value }}
                        </p>
                    </div>

                    {{-- Fakultas / Unit Kerja --}}
                    <div>
                        <p class="text-muted-light dark:text-muted-dark font-medium mb-0.5">Fakultas / Unit Kerja</p>
                        <p
                            class="text-text-light dark:text-text-dark font-medium break-all wrap-break-word whitespace-normal max-w-full">
                            {{ $ticket->guestDetail->department->name ?? '-' }}
                        </p>
                    </div>
                </div>

                {{-- Action Buttons (Identity/Selfie) - HANYA UNTUK ADMIN/SUPERUSER --}}
                @if ($showSensitiveData)
                    <div class="mt-6 flex flex-row md:flex-col gap-2">
                        @if ($ticket->guestDetail->photo_identity_path)
                            {{-- Trigger Modal --}}
                            <button type="button"
                                @click="modalImage = '{{ asset('storage/' . $ticket->guestDetail->photo_identity_path) }}'; showModal = true"
                                class="w-full flex items-center justify-center md:justify-start py-2 px-3 bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-slate-700 border border-border-light dark:border-border-dark rounded-lg text-secondary hover:text-blue-700 transition-colors text-xs font-medium group cursor-pointer">
                                <span
                                    class="material-icons-round text-sm mr-2 text-muted-light dark:text-muted-dark group-hover:text-secondary transition-colors">badge</span>
                                Kartu Identitas
                            </button>
                        @endif

                        @if ($ticket->guestDetail->photo_selfie_path)
                            {{-- Trigger Modal --}}
                            <button type="button"
                                @click="modalImage = '{{ asset('storage/' . $ticket->guestDetail->photo_selfie_path) }}'; showModal = true"
                                class="w-full flex items-center justify-center md:justify-start py-2 px-3 bg-background-light dark:bg-background-dark hover:bg-gray-200 dark:hover:bg-slate-700 border border-border-light dark:border-border-dark rounded-lg text-secondary hover:text-blue-700 transition-colors text-xs font-medium group cursor-pointer">
                                <span
                                    class="material-icons-round text-sm mr-2 text-muted-light dark:text-muted-dark group-hover:text-secondary transition-colors">face</span>
                                Selfie
                            </button>
                        @endif
                    </div>
                @else
                    {{-- Pesan Privasi untuk User/Guest --}}
                    <div
                        class="mt-6 p-3 bg-gray-100 dark:bg-slate-800 rounded-lg text-center border border-dashed border-gray-300 dark:border-gray-600">
                        <span class="material-icons-round text-gray-400 text-lg mb-1">lock</span>
                        <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-tight">
                            Lampiran identitas dilindungi demi privasi pelapor.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- MODAL PREVIEW GAMBAR --}}
    <div x-show="showModal" style="display: none;"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">

        {{-- Close on click outside --}}
        <div class="absolute inset-0" @click="showModal = false"></div>

        <div
            class="relative bg-white dark:bg-surface-dark rounded-xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col">
            {{-- Modal Header --}}
            <div class="flex justify-between items-center p-4 border-b border-border-light dark:border-border-dark">
                <h3 class="font-bold text-text-light dark:text-text-dark">Preview Gambar</h3>
                <button @click="showModal = false" class="text-muted-light hover:text-red-500 transition-colors">
                    <span class="material-icons-round">close</span>
                </button>
            </div>

            {{-- Modal Image Content --}}
            <div class="p-2 flex items-center justify-center bg-gray-100 dark:bg-slate-900 overflow-auto h-full">
                <img :src="modalImage" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-sm">
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
