<x-layouts.guest title="Tiket #{{ $ticket->ticket_code }}">

    <div
        class="min-h-screen bg-background-light dark:bg-background-dark py-8 px-4 sm:px-6 lg:px-8 font-sans overflow-x-hidden">
        <div class="max-w-7xl mx-auto w-full">

            {{-- Tombol Kembali --}}
            <a href="{{ route('guest.tracking.index') }}"
                class="inline-flex items-center text-sm text-muted-light hover:text-secondary mb-6 transition-colors">
                <span class="material-icons-round mr-1 text-base">arrow_back</span>
                Cari Tiket Lain
            </a>

            {{-- HEADER SECTION --}}
            <x-tickets.show.header :ticket="$ticket" />

            {{-- MAIN LAYOUT --}}
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

                {{-- LEFT COLUMN: Discussion --}}
                <div class="lg:col-span-3 space-y-8">

                    {{-- 1. Initial Description --}}
                    <x-tickets.show.description :ticket="$ticket" />

                    {{-- 2. Comments --}}
                    <x-tickets.show.comments :ticket="$ticket" />

                    {{-- 3. Reply Form --}}
                    @php
                        $isClosed = in_array($ticket->status, [
                            \App\Enums\TicketStatus::DONE,
                            \App\Enums\TicketStatus::REJECT,
                        ]);

                        // LOGIKA PENENTUAN NAMA AVATAR
                        if (auth()->check()) {
                            // Jika yang melihat adalah User Login (Admin/Staff)
                            $responderName = auth()->user()->name;
                        } else {
                            // Jika yang melihat adalah Guest (Masyarakat/Pelapor)
                            // Ambil nama dari tabel guest_ticket_details via relasi
                            $responderName = $ticket->guestDetail->full_name ?? 'Guest';
                        }

                        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($responderName);
                    @endphp

                    @if (!$isClosed)
                        <div class="flex gap-4 pt-6 border-t border-border-light dark:border-border-dark mt-6">

                            {{-- Avatar (Dinamis: User Login atau Guest Name) --}}
                            <div class="shrink-0 hidden sm:block">
                                <img src="{{ $avatarUrl }}" alt="{{ $responderName }}"
                                    class="w-10 h-10 rounded-full border border-border-light dark:border-border-dark shadow-sm"
                                    title="{{ $responderName }}">
                            </div>

                            <div class="grow min-w-0">
                                <form action="{{ route('guest.tickets.comments.store', $ticket->uuid) }}"
                                    method="POST">
                                    @csrf

                                    {{-- Styling Container Form --}}
                                    <div
                                        class="border border-border-light dark:border-border-dark rounded-xl
                                               bg-surface-light dark:bg-surface-dark
                                               overflow-hidden shadow-sm
                                               focus-within:ring-1 focus-within:ring-secondary
                                               focus-within:border-secondary transition-all">

                                        {{-- AREA EDITOR --}}
                                        <div
                                            class="px-4 py-2 border-b border-border-light dark:border-border-dark
                                                   bg-gray-50 dark:bg-slate-800/30">

                                            <input id="x_message_guest" type="hidden" name="message">

                                            <trix-editor input="x_message_guest"
                                                data-upload-url="{{ route('guest.comments.upload.editor.attachments') }}"
                                                class="prose dark:prose-invert max-w-none
                                                       text-text-light dark:text-text-dark
                                                       bg-transparent min-h-[100px] outline-none"
                                                placeholder="Tulis balasan anda... (Drag & drop gambar atau file di sini)"></trix-editor>
                                        </div>

                                        {{-- FOOTER --}}
                                        <div class="px-3 py-2 bg-gray-50 dark:bg-slate-800/50 flex justify-end">
                                            <button type="submit"
                                                class="px-3 py-1.5 sm:px-4
                                                       bg-secondary hover:opacity-90
                                                       text-white text-xs sm:text-sm
                                                       font-medium rounded-lg shadow-sm
                                                       transition-all whitespace-nowrap">
                                                Kirim Balasan
                                            </button>
                                        </div>
                                    </div>

                                    <p class="text-xs text-muted-light mt-2 ml-1">
                                        * Anda dapat menyisipkan gambar atau file langsung ke dalam editor.
                                    </p>
                                </form>
                            </div>
                        </div>
                    @else
                        {{-- CLOSED STATE --}}
                        <div
                            class="p-6 mt-6 bg-gray-50 dark:bg-slate-800/50 rounded-xl
                                   border border-border-light dark:border-border-dark
                                   text-center">
                            <span class="material-icons-round text-4xl text-muted-light mb-2">lock</span>
                            <h3 class="text-text-light dark:text-text-dark font-semibold">
                                Percakapan Dikunci
                            </h3>
                            <p class="text-sm text-muted-light">
                                Tiket ini telah ditutup atau diselesaikan.
                            </p>
                        </div>
                    @endif

                </div>

                {{-- RIGHT COLUMN: Sidebar --}}
                <div class="lg:col-span-1">
                    <x-tickets.show.sidebar :ticket="$ticket" />
                </div>

            </div>
        </div>
    </div>
</x-layouts.guest>
