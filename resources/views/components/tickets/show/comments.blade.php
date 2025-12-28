@props(['ticket'])

@foreach ($ticket->comments as $comment)
    @php
        $isStaff = $comment->user && in_array($comment->user->role->value, ['admin', 'superuser']);
        $senderName = $comment->sender_name;

        if ($comment->user && $comment->user->photo) {
            $avatarUrl = asset('storage/' . $comment->user->photo);
        } elseif ($comment->user) {
            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($comment->user->name);
        } else {
            $guestName = $ticket->guestDetail ? $ticket->guestDetail->full_name : 'Guest';
            $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($guestName);
        }
    @endphp

    <div class="flex gap-4 relative">
        <div class="shrink-0 hidden sm:block z-10">
            <img src="{{ $avatarUrl }}"
                class="w-10 h-10 rounded-full border border-border-light dark:border-border-dark shadow-sm bg-surface-light">
        </div>
        <div class="grow min-w-0">
            <div
                class="border {{ $isStaff ? 'border-blue-200 dark:border-blue-900/50' : 'border-border-light dark:border-border-dark' }} rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm">

                {{-- HEADER --}}
                <div
                    class="px-4 py-2.5 {{ $isStaff ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-gray-50 dark:bg-slate-800/50' }} border-b {{ $isStaff ? 'border-blue-100 dark:border-blue-900/30' : 'border-border-light dark:border-border-dark' }} flex items-start justify-between text-sm gap-4">
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 min-w-0">
                        <span
                            class="font-semibold text-text-light dark:text-text-dark break-all wrap-break-word whitespace-normal max-w-full">
                            {{ $senderName }}
                        </span>

                        <span class="text-muted-light dark:text-muted-dark text-xs whitespace-nowrap">
                            berkomentar {{ $comment->created_at->diffForHumans() }}
                        </span>
                    </div>

                    @if ($isStaff)
                        <span
                            class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300 border border-blue-200 dark:border-blue-800 shrink-0">
                            Staff
                        </span>
                    @endif
                </div>

                {{-- CONTENT BODY --}}
                <div
                    class="p-4 text-text-light dark:text-text-dark leading-relaxed max-w-none break-all wrap-break-word prose dark:prose-invert prose-sm">
                    {!! $comment->message !!}
                </div>
            </div>
        </div>
    </div>
@endforeach
