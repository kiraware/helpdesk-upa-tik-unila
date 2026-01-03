@props(['ticket'])

@php
    // Logika Avatar & Nama (Sama seperti Comment)
    $creator = $ticket->user;
    $isGuest = !$creator;

    // Nama Pengirim
    $senderName = $creator ? $creator->name : ($ticket->guestDetail ? $ticket->guestDetail->full_name : 'Guest');

    // Role Label
    $roleLabel = $creator ? 'User' : 'Guest';

    // Cek apakah Admin/Superuser (Untuk styling border biru)
    $isStaff = $creator && in_array($creator->role->value, ['admin', 'superuser']);

    // Avatar URL
    if ($creator && $creator->photo) {
        $avatarUrl = asset('storage/' . $creator->photo);
    } else {
        $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($senderName);
    }
@endphp

<div class="flex gap-4 relative mb-6 group">
    {{-- Avatar --}}
    <div class="shrink-0 hidden sm:block z-10">
        <img src="{{ $avatarUrl }}"
            class="w-10 h-10 rounded-full border border-border-light dark:border-border-dark shadow-sm bg-surface-light">
    </div>

    {{-- Bubble Content --}}
    <div class="grow min-w-0">
        <div
            class="border {{ $isStaff ? 'border-blue-200 dark:border-blue-900/50' : 'border-border-light dark:border-border-dark' }} rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm">

            {{-- HEADER --}}
            <div
                class="px-4 py-2.5
           {{ $isStaff ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-gray-50 dark:bg-slate-800/50' }}
           border-b {{ $isStaff ? 'border-blue-100 dark:border-blue-900/30' : 'border-border-light dark:border-border-dark' }}
           flex items-start justify-between text-sm gap-4">

                {{-- LEFT : NAME --}}
                <div class="flex flex-wrap items-center min-w-0">
                    <span
                        class="font-semibold text-text-light dark:text-text-dark break-all wrap-break-word whitespace-normal max-w-full">
                        {{ $senderName }}
                    </span>
                </div>

                {{-- ROLE BADGE --}}
                <span
                    class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full
           bg-gray-100 text-gray-600 dark:bg-slate-700 dark:text-slate-300
           border border-gray-200 dark:border-slate-600 shrink-0">
                    {{ $roleLabel }}
                </span>

            </div>

            {{-- CONTENT BODY --}}
            <div
                class="p-4 text-text-light dark:text-text-dark leading-relaxed max-w-none break-all wrap-break-word whitespace-normal prose dark:prose-invert prose-sm">
                {!! $ticket->description !!}
            </div>

        </div>
    </div>
</div>

<div class="relative py-2">
    <div class="absolute inset-0 flex items-center ml-5 sm:ml-5" aria-hidden="true">
        <div class="w-0.5 h-full bg-border-light dark:bg-border-dark"></div>
    </div>
</div>
