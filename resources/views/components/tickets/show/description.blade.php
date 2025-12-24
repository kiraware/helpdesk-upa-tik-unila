@props(['ticket'])

@php
    $creatorName = $ticket->user
        ? $ticket->user->name
        : ($ticket->guestDetail
            ? $ticket->guestDetail->full_name
            : 'Guest');
    $creatorAvatar = $ticket->user
        ? ($ticket->user->photo
            ? asset('storage/' . $ticket->user->photo)
            : 'https://ui-avatars.com/api/?name=' . urlencode($ticket->user->name))
        : 'https://ui-avatars.com/api/?name=' . urlencode($creatorName);
@endphp

<div class="flex gap-4 group relative">
    <div class="shrink-0 hidden sm:block">
        <img src="{{ $creatorAvatar }}" alt="{{ $creatorName }}"
            class="w-10 h-10 rounded-full border border-border-light dark:border-border-dark shadow-sm">
    </div>
    <div class="grow min-w-0">
        <div
            class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm">
            <div
                class="px-4 py-3 bg-gray-50 dark:bg-slate-800/50 border-b border-border-light dark:border-border-dark flex items-center justify-between gap-2 text-sm">
                <div class="flex items-center gap-2 text-muted-light dark:text-muted-dark">
                    <span class="font-semibold text-text-light dark:text-text-dark">{{ $creatorName }}</span>
                </div>
                <span
                    class="text-xs px-2 py-0.5 rounded-full border border-border-light dark:border-border-dark text-muted-light">
                    {{ $ticket->user ? ucfirst($ticket->user->role->value) : 'Guest' }}
                </span>
            </div>
            <div
                class="p-4 sm:p-6 text-text-light dark:text-text-dark leading-relaxed prose dark:prose-invert max-w-none wrap-break-word">
                {!! nl2br(e($ticket->description)) !!}
            </div>

            @if ($ticket->attachments->count() > 0)
                <div class="px-4 sm:px-6 pb-4 pt-2 border-t border-border-light dark:border-border-dark border-dashed">
                    <p class="text-xs font-semibold text-muted-light mb-3 uppercase tracking-wider">Lampiran Tiket</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($ticket->attachments as $att)
                            <a href="{{ $att->url }}" target="_blank"
                                class="group flex items-center gap-3 p-2 rounded-lg border border-border-light dark:border-border-dark hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                                @if (Str::startsWith($att->mime_type, 'image/'))
                                    <div
                                        class="w-10 h-10 shrink-0 bg-gray-200 dark:bg-gray-700 rounded overflow-hidden">
                                        <img src="{{ $att->url }}" class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div
                                        class="w-10 h-10 shrink-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800 rounded text-muted-light">
                                        <span class="material-icons-round text-xl">description</span>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p
                                        class="text-sm font-medium text-text-light dark:text-text-dark truncate group-hover:text-secondary">
                                        {{ $att->name }}</p>
                                    <p class="text-xs text-muted-light">{{ number_format($att->size / 1024, 1) }} KB</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="relative py-2">
    <div class="absolute inset-0 flex items-center ml-5 sm:ml-5" aria-hidden="true">
        <div class="w-0.5 h-full bg-border-light dark:bg-border-dark"></div>
    </div>
</div>
