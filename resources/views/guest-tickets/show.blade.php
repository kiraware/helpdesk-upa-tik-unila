<x-layouts.guest title="Tiket #{{ $ticket->ticket_code }}">

    <div
        class="min-h-screen bg-background-light dark:bg-background-dark py-8 px-4 sm:px-6 lg:px-8 font-sans overflow-x-hidden">
        <div class="max-w-7xl mx-auto w-full">

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
                        // Logika Status Tiket
                        $isClosed = in_array($ticket->status, [
                            \App\Enums\TicketStatus::DONE,
                            \App\Enums\TicketStatus::REJECT,
                        ]);

                        // Logika Nama & Avatar
                        if (auth()->check()) {
                            $responderName = auth()->user()->name;
                        } else {
                            $responderName = $ticket->guestDetail->full_name ?? 'Guest';
                        }
                        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($responderName);

                        // KONFIGURASI FILE
                        $maxSizeKp = 5120; // 5MB dalam KB
                        $acceptedMimes =
                            'image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip';
                        $readableFormat = 'JPG, PNG, PDF, DOC, DOCX, ZIP';
                    @endphp

                    @if (!$isClosed)
                        <div class="flex gap-4 pt-6 border-t border-border-light dark:border-border-dark mt-6">

                            {{-- Avatar --}}
                            <div class="shrink-0 hidden sm:block">
                                <img src="{{ $avatarUrl }}" alt="{{ $responderName }}"
                                    class="w-10 h-10 rounded-full border border-border-light dark:border-border-dark shadow-sm"
                                    title="{{ $responderName }}">
                            </div>

                            <div class="grow min-w-0">
                                <form action="{{ route('guest.tickets.comments.store', $ticket) }}" method="POST">
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

                                            {{-- Update: Tambahkan data-max-size & data-accept --}}
                                            <trix-editor input="x_message_guest"
                                                data-upload-url="{{ route('guest.comments.upload.editor.attachments') }}"
                                                data-max-size="{{ $maxSizeKp }}" data-accept="{{ $acceptedMimes }}"
                                                class="prose dark:prose-invert max-w-none
                                                       text-text-light dark:text-text-dark
                                                       bg-transparent min-h-25 outline-none"
                                                placeholder="Tulis balasan anda..."></trix-editor>
                                        </div>

                                        {{-- FOOTER --}}
                                        <div
                                            class="px-4 py-3 bg-gray-50 dark:bg-slate-800/50 flex flex-col sm:flex-row items-center justify-between gap-4">

                                            {{-- AREA TURNSTILE (KIRI) --}}
                                            <div class="w-full sm:w-auto">
                                                {{-- Widget Container --}}
                                                <div class="cf-turnstile scale-90 sm:scale-100 origin-left"
                                                    data-sitekey="{{ config('services.turnstile.key') }}"
                                                    data-theme="auto" data-size="flexible"
                                                    data-callback="enableSubmitButton"
                                                    data-expired-callback="disableSubmitButton"
                                                    data-error-callback="disableSubmitButton">
                                                </div>

                                                @error('cf-turnstile-response')
                                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- TOMBOL KIRIM (KANAN) --}}
                                            <div class="flex items-center gap-2 self-end sm:self-auto">
                                                <button type="submit" id="submitButton" disabled
                                                    class="px-4 py-2 bg-secondary text-white text-sm font-medium rounded-lg shadow-sm transition-all whitespace-nowrap
                                                           hover:opacity-90 
                                                           disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:opacity-50">
                                                    Kirim Balasan
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Update: Informasi File dengan Layout Baru --}}
                                    <div class="flex items-start gap-2 mt-2 ml-1">
                                        <span class="material-icons-round text-base text-blue-500 mt-0.5">info</span>

                                        <div class="text-xs text-slate-500 dark:text-slate-400">
                                            <p class="font-medium text-slate-700 dark:text-slate-300 mb-0.5">
                                                Sisipkan file atau gambar dengan cara <span
                                                    class="text-blue-600 dark:text-blue-400 font-bold">Drag &
                                                    Drop</span> ke kolom editor.
                                            </p>
                                            <p>
                                                Max <strong>{{ $maxSizeKp / 1024 }}MB</strong>.
                                                Format: {{ $readableFormat }}.
                                            </p>
                                        </div>
                                    </div>

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

                    {{-- 4. Survey Kepuasan --}}
                    <x-tickets.show.survey :ticket="$ticket" />
                </div>

                {{-- RIGHT COLUMN: Sidebar --}}
                <div class="lg:col-span-1">
                    <x-tickets.show.sidebar :ticket="$ticket" />
                </div>

            </div>
        </div>
    </div>

    {{-- SCRIPT PENGENDALI TOMBOL --}}
    <script>
        // Fungsi Callback Turnstile (Global Scope)
        function enableSubmitButton() {
            const btn = document.getElementById('submitButton');
            if (btn) {
                btn.removeAttribute('disabled');
            }
        }

        function disableSubmitButton() {
            const btn = document.getElementById('submitButton');
            if (btn) {
                btn.setAttribute('disabled', 'disabled');
            }
        }
    </script>

</x-layouts.guest>
