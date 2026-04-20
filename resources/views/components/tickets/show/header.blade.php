@props(['ticket'])

@php
    $statusColors = match ($ticket->status) {
        \App\Enums\TicketStatus::WAITING => [
            'bg' => 'bg-yellow-100 dark:bg-yellow-900/30',
            'text' => 'text-yellow-700 dark:text-yellow-400',
            'icon' => 'radio_button_checked',
        ],
        \App\Enums\TicketStatus::PROGRESS => [
            'bg' => 'bg-blue-100 dark:bg-blue-900/30',
            'text' => 'text-blue-700 dark:text-blue-400',
            'icon' => 'adjust',
        ],
        \App\Enums\TicketStatus::DONE => [
            'bg' => 'bg-emerald-100 dark:bg-emerald-900/30',
            'text' => 'text-emerald-700 dark:text-emerald-400',
            'icon' => 'check_circle',
        ],
        \App\Enums\TicketStatus::REJECT => [
            'bg' => 'bg-red-100 dark:bg-red-900/30',
            'text' => 'text-red-700 dark:text-red-400',
            'icon' => 'cancel',
        ],
    };

    $creatorName = $ticket->user
        ? $ticket->user->name
        : ($ticket->guestDetail
            ? $ticket->guestDetail->full_name
            : 'Guest');

    $isClosed = in_array($ticket->status, [\App\Enums\TicketStatus::DONE, \App\Enums\TicketStatus::REJECT]);
    $isGuestTicket = is_null($ticket->user_id);
    $currentUser = auth()->user();

    // Logic Close Ticket (Hanya Staff yang di-assign atau jika tiket belum ada petugasnya)
    $canCloseTicket =
        $currentUser &&
        in_array($currentUser->role, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::SUPERUSER]) &&
        !$isClosed &&
        ($ticket->assigned_to === $currentUser->id || is_null($ticket->assigned_to));

    // Cek apakah sudah ada komentar dari staf
    $hasStaffComment = $ticket->comments->contains(function ($comment) {
        return $comment->user &&
            in_array($comment->user->role, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::SUPERUSER]);
    });
@endphp

<div class="border-b border-border-light dark:border-border-dark pb-6 mb-6">
    <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">

        {{-- Title & Meta --}}
        <div class="flex-1 min-w-0 w-full">
            <div class="mb-2 min-h-10 flex items-center w-full">
                <h1
                    class="text-xl sm:text-2xl font-light text-muted-light dark:text-muted-dark ml-2 inline-block whitespace-nowrap">
                    #{{ $ticket->ticket_code }}
                </h1>
            </div>

            {{-- Meta Pills --}}
            <div class="flex flex-wrap items-center gap-3 text-sm text-muted-light dark:text-muted-dark mt-1">
                <span
                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-sm font-medium {{ $statusColors['bg'] }} {{ $statusColors['text'] }} border border-transparent">
                    <span class="material-icons-round text-base">{{ $statusColors['icon'] }}</span>
                    {{ ucfirst($ticket->status->value) }}
                </span>
                <span class="hidden sm:inline">&bull;</span>
                <span class="flex flex-wrap items-center gap-1">
                    <span class="font-semibold text-text-light dark:text-text-dark break-all">
                        {{ $creatorName }}
                    </span>
                    <span class="hidden sm:inline whitespace-nowrap">membuka tiket ini</span>
                    <span class="whitespace-nowrap">{{ $ticket->created_at->diffForHumans() }}</span>
                </span>
                <span class="hidden sm:inline">&bull;</span>
                <span class="whitespace-nowrap">{{ $ticket->comments->count() }} komentar</span>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-wrap items-center gap-2 shrink-0 w-full md:w-auto mb-2 md:mb-0 order-first md:order-last">

            <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">

                {{-- Tombol Close Ticket --}}
                @if ($canCloseTicket)
                    {{-- Wrapper Alpine JS untuk Dropdown & Modal --}}
                    <div class="relative flex-1 md:flex-none" x-data="{
                        open: false,
                        showModal: false,
                        showWarningModal: false,
                        actionStatus: '',
                        actionLabel: '',
                        actionColor: '',
                    
                        // Fungsi untuk memicu modal
                        confirmAction(status, label, color) {
                            if (!{{ $hasStaffComment ? 'true' : 'false' }}) {
                                this.showWarningModal = true;
                                this.open = false; // Tutup dropdown
                                return;
                            }
                    
                            this.actionStatus = status;
                            this.actionLabel = label;
                            this.actionColor = color;
                            this.showModal = true;
                            this.open = false; // Tutup dropdown
                        },
                    
                        // Fungsi untuk submit form
                        submitForm() {
                            this.$refs.closeForm.status.value = this.actionStatus;
                            this.$refs.closeForm.submit();
                        }
                    }">

                        <button type="button" @click="open = !open" @click.outside="open = false"
                            class="w-full md:w-auto flex items-center justify-center gap-1 px-4 py-2 bg-white dark:bg-surface-dark hover:bg-gray-50 dark:hover:bg-slate-700 border border-border-light dark:border-border-dark text-text-light dark:text-text-dark text-sm font-medium rounded-lg shadow-sm transition-colors whitespace-nowrap">
                            <span>Tutup Tiket</span>
                            <span class="material-icons-round text-base">expand_more</span>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div x-show="open" x-transition x-cloak
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-xl z-40 overflow-hidden">

                            {{-- Option: Selesai --}}
                            <button type="button"
                                @click="confirmAction('done', 'Selesai', 'bg-emerald-600 hover:bg-emerald-700')"
                                class="w-full text-left px-4 py-3 text-sm text-text-light dark:text-text-dark hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors">
                                <span class="material-icons-round text-emerald-600">check_circle</span>
                                Selesai
                            </button>

                            <div class="border-t border-border-light dark:border-border-dark"></div>

                            {{-- Option: Tolak --}}
                            <button type="button"
                                @click="confirmAction('reject', 'Tolak', 'bg-red-600 hover:bg-red-700')"
                                class="w-full text-left px-4 py-3 text-sm text-text-light dark:text-text-dark hover:bg-gray-50 dark:hover:bg-slate-700 flex items-center gap-2 transition-colors">
                                <span class="material-icons-round text-red-600">cancel</span>
                                Tolak
                            </button>
                        </div>

                        {{-- Modal Konfirmasi (Tailwind + Alpine) --}}
                        <div x-show="showModal" style="display: none;" class="relative z-100"
                            aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            {{-- Backdrop Blur / Dim --}}
                            <div x-show="showModal" x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-gray-900/50 dark:bg-black/60 backdrop-blur-sm transition-opacity">
                            </div>

                            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">

                                    {{-- Modal Panel --}}
                                    <div x-show="showModal" @click.outside="showModal = false"
                                        x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="ease-in duration-200"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="relative transform overflow-hidden rounded-xl bg-white dark:bg-surface-dark text-left shadow-xl transition-all w-full sm:my-8 sm:max-w-md border border-border-light dark:border-border-dark">

                                        <div class="px-4 pb-4 pt-5 sm:p-6">
                                            <div class="sm:flex sm:items-start">
                                                <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                                    <h3 class="text-lg font-semibold leading-6 text-text-light dark:text-text-dark"
                                                        id="modal-title">
                                                        Konfirmasi Tutup Tiket
                                                    </h3>
                                                    <div class="mt-2">
                                                        <p class="text-sm text-muted-light dark:text-muted-dark">
                                                            Apakah Anda yakin ingin menutup tiket <span
                                                                class="font-bold text-text-light dark:text-text-dark">#{{ $ticket->ticket_code }}</span>
                                                            dengan status <span class="font-bold"
                                                                :class="actionStatus === 'done' ? 'text-emerald-600' :
                                                                    'text-red-600'"
                                                                x-text="`&quot;${actionLabel}&quot;`"></span>? Aksi ini
                                                            tidak dapat dibatalkan.
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-gray-50 dark:bg-slate-800/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                                            <button type="button" @click="submitForm()"
                                                class="inline-flex w-full justify-center rounded-lg px-4 py-2 text-sm font-medium text-white shadow-sm sm:w-auto transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2"
                                                :class="actionColor">
                                                Ya, <span x-text="actionLabel" class="ml-1"></span>
                                            </button>
                                            <button type="button" @click="showModal = false"
                                                class="mt-3 inline-flex w-full justify-center rounded-lg bg-white dark:bg-surface-dark px-4 py-2 text-sm font-medium text-text-light dark:text-text-dark shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-border-dark hover:bg-gray-50 dark:hover:bg-slate-700 sm:mt-0 sm:w-auto transition-colors">
                                                Batal
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Hidden Form Master --}}
                        <form x-ref="closeForm" action="{{ route('tickets.close', $ticket) }}" method="POST"
                            class="hidden">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="">
                        </form>

                        {{-- MODAL PERINGATAN (BELUM ADA KOMENTAR) --}}
                        <div x-show="showWarningModal" style="display: none;" class="relative z-50" role="dialog"
                            aria-modal="true">
                            {{-- Backdrop --}}
                            <div x-show="showWarningModal" x-transition:enter="ease-out duration-300"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
                                x-transition:leave-end="opacity-0"
                                class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-sm transition-opacity">
                            </div>

                            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                                    <div x-show="showWarningModal" @click.away="showWarningModal = false"
                                        x-transition:enter="ease-out duration-300"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="ease-in duration-200"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="relative transform overflow-hidden rounded-xl bg-white dark:bg-surface-dark text-left shadow-xl transition-all w-full sm:my-8 sm:max-w-md border border-gray-200 dark:border-border-dark">

                                        <div class="bg-white dark:bg-surface-dark px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                            <div class="text-center sm:text-left">
                                                <h3
                                                    class="text-base font-semibold leading-6 text-text-light dark:text-text-dark">
                                                    Tanggapan Diperlukan
                                                </h3>
                                                <div class="mt-2">
                                                    <p
                                                        class="text-sm text-muted-light dark:text-muted-dark leading-relaxed">
                                                        Tiket belum dapat ditutup. Silakan berikan setidaknya satu
                                                        tanggapan kepada pengguna sebelum mengakhiri sesi tiket ini.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            class="bg-gray-50 dark:bg-slate-800/50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                                            <button type="button" @click="showWarningModal = false"
                                                class="inline-flex w-full justify-center rounded-lg bg-white dark:bg-surface-dark px-4 py-2 text-sm font-medium text-text-light dark:text-text-dark shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-border-dark hover:bg-gray-50 dark:hover:bg-slate-700 sm:w-auto transition-colors">
                                                Kembali
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>
