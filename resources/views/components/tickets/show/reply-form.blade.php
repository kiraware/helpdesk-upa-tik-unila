@props(['ticket'])

@php
    $isClosed = in_array($ticket->status, [\App\Enums\TicketStatus::DONE, \App\Enums\TicketStatus::REJECT]);
    $userAvatar = auth()->user()->photo
        ? asset('storage/' . auth()->user()->photo)
        : 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name);
@endphp

@if (!$isClosed)
    <div class="flex gap-4 pt-6 border-t border-border-light dark:border-border-dark mt-6">
        <div class="shrink-0 hidden sm:block">
            <img src="{{ $userAvatar }}"
                class="w-10 h-10 rounded-full border border-border-light dark:border-border-dark shadow-sm">
        </div>
        <div class="grow min-w-0">
            <form action="{{ route('tickets.comments.store', $ticket->uuid) }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                <div
                    class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm focus-within:ring-1 focus-within:ring-secondary focus-within:border-secondary transition-all">

                    {{-- AREA EDITOR --}}
                    <div
                        class="px-4 py-2 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/30">
                        <input id="x_message" type="hidden" name="message">

                        <trix-editor input="x_message" data-upload-url="{{ route('comments.upload.editor.image') }}"
                            class="prose dark:prose-invert max-w-none text-text-light dark:text-text-dark bg-transparent"
                            placeholder="Tulis balasan anda, drag & drop gambar disini...">
                        </trix-editor>
                    </div>

                    {{-- FOOTER / ATTACHMENT LIST --}}
                    <div class="px-2 py-2 bg-gray-50 dark:bg-slate-800/50 flex items-center justify-between">
                        <div class="relative">
                            <input type="file" name="attachments[]" id="attachments" multiple class="hidden"
                                onchange="updateFileCount(this)">
                            <label for="attachments"
                                class="cursor-pointer p-2 rounded hover:bg-gray-200 dark:hover:bg-slate-700 text-muted-light hover:text-text-light transition-colors flex items-center gap-2 text-xs font-medium">
                                <span class="material-icons-round text-lg">attach_file</span>
                                <span id="file-count-label">Lampiran Tambahan (ZIP/Doc)</span>
                            </label>
                        </div>
                        <button type="submit"
                            class="px-3 py-1.5 sm:px-4 bg-secondary hover:bg-blue-600 text-white text-xs sm:text-sm font-medium rounded-lg shadow-sm transition-colors whitespace-nowrap">
                            Kirim Balasan
                        </button>
                    </div>
                </div>
                <p class="text-xs text-muted-light mt-2 ml-1">* Paste gambar langsung ke editor, atau gunakan tombol
                    lampiran untuk file lain.</p>
            </form>
        </div>
    </div>
@else
    <div
        class="p-6 bg-gray-50 dark:bg-slate-800/50 rounded-xl border border-border-light dark:border-border-dark text-center">
        <span class="material-icons-round text-4xl text-muted-light mb-2">lock</span>
        <h3 class="text-text-light dark:text-text-dark font-semibold">Percakapan Dikunci</h3>
        <p class="text-sm text-muted-light">Tiket ini telah ditutup atau diselesaikan.</p>
    </div>
@endif
