@props(['ticket', 'replyTemplate' => null])

@php
    $isClosed = in_array($ticket->status, [\App\Enums\TicketStatus::DONE, \App\Enums\TicketStatus::REJECT]);

    $user = auth()->user();

    $userAvatar = $user->avatar_path
        ? asset('storage/' . $user->avatar_path)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name);

    $maxSizeKp = 2048; // 2MB dalam KB
    $acceptedMimes =
        'image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip';
    $readableFormat = 'JPG, PNG, PDF, DOC, DOCX, ZIP';

    $isStaff = in_array($user->role, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::SUPERUSER]);
@endphp

@if (!$isClosed)
    <div class="flex gap-4 pt-6 border-t border-border-light dark:border-border-dark mt-6">
        <div class="shrink-0 hidden sm:block">
            <img src="{{ $userAvatar }}"
                class="w-10 h-10 rounded-full border border-border-light dark:border-border-dark shadow-sm bg-surface-light object-cover">
        </div>

        <div class="grow min-w-0">
            <form action="{{ route('tickets.comments.store', $ticket) }}" method="POST">
                @csrf

                <div
                    class="border border-border-light dark:border-border-dark rounded-xl
                           bg-surface-light dark:bg-surface-dark
                           overflow-hidden shadow-sm
                           focus-within:ring-1 focus-within:ring-secondary
                           focus-within:border-secondary transition-all">

                    <div
                        class="px-4 py-2 border-b border-border-light dark:border-border-dark
                               bg-gray-50 dark:bg-slate-800/30">

                        <input id="x_message" type="hidden" name="message" value="{{ $replyTemplate }}">

                        <trix-editor input="x_message" data-upload-url="{{ route('comments.upload.attachments') }}"
                            data-max-size="{{ $maxSizeKp }}" data-accept="{{ $acceptedMimes }}"
                            class="prose dark:prose-invert max-w-none
                                   text-text-light dark:text-text-dark
                                   bg-transparent"
                            placeholder="Tulis balasan anda...">
                        </trix-editor>
                    </div>

                    <div class="px-3 py-2 bg-gray-50 dark:bg-slate-800/50 flex justify-end">
                        <button type="submit"
                            class="px-3 py-1.5 sm:px-4
                                   bg-secondary hover:bg-blue-600
                                   text-white text-xs sm:text-sm
                                   font-medium rounded-lg shadow-sm
                                   transition-colors whitespace-nowrap">
                            Kirim Balasan
                        </button>
                    </div>
                </div>

                <div class="flex items-start gap-2 mt-2 ml-1">
                    <span class="material-icons-round text-base text-blue-500 mt-0.5">info</span>

                    <div class="text-xs text-slate-500 dark:text-slate-400">
                        <p class="font-medium text-slate-700 dark:text-slate-300 mb-0.5">
                            Sisipkan file atau gambar dengan cara <span
                                class="text-blue-600 dark:text-blue-400 font-bold">Drag & Drop</span> ke kolom editor.
                        </p>
                        <p>
                            Max <strong>{{ $maxSizeKp / 1024 }}MB</strong>.
                            Format: {{ $readableFormat }}. Min <strong>20 Karakter</strong>.
                        </p>
                    </div>
                </div>

                @error('message')
                    <p class="text-red-500 text-xs mt-1 ml-1">{{ $message }}</p>
                @enderror

            </form>
        </div>
    </div>
@else
    <div
        class="p-6 bg-gray-50 dark:bg-slate-800/50 rounded-xl
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
