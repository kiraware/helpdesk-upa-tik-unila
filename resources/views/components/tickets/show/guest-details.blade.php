@props(['ticket'])

@php
    use App\Enums\UserRole;

    $currentUser = auth()->user();
    $isAdminOrSuper = $currentUser && in_array($currentUser->role, [UserRole::ADMIN, UserRole::SUPERUSER]);

    $isGuestTicket = is_null($ticket->user_id);
    $showSensitiveData = !$isGuestTicket || $isAdminOrSuper;

    $maskEmail = function ($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return '******';
        }
        [$first, $last] = explode('@', $email);
        $visible = floor(strlen($first) / 2);
        return substr($first, 0, min(3, $visible)) . str_repeat('*', 5) . '@' . $last;
    };

    $maskID = function ($id) {
        $len = strlen($id);
        return $len <= 4 ? str_repeat('*', $len) : str_repeat('*', $len - 4) . substr($id, -4);
    };

    $maskPhone = function ($phone) {
        $len = strlen($phone);
        return $len <= 4 ? str_repeat('*', $len) : str_repeat('*', $len - 4) . substr($phone, -4);
    };
@endphp

@if (!$ticket->user_id && $ticket->guestDetail)
    <div
        class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm">

        <div class="px-4 py-3 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50">
            <h3 class="text-sm font-bold uppercase tracking-wider text-muted-light">Detail Pelapor (Tamu)</h3>
        </div>

        <div class="p-5">
            <div class="flex items-center gap-4 mb-6">
                <div class="shrink-0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->guestDetail->full_name) }}&size=64"
                        class="w-14 h-14 rounded-full bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-300 flex items-center justify-center font-bold border border-border-light dark:border-border-dark shadow-sm object-cover">
                </div>
                <div class="flex flex-col min-w-0">
                    <span
                        class="text-lg font-bold text-text-light dark:text-text-dark break-all wrap-break-word whitespace-normal max-w-full leading-tight">
                        {{ $ticket->guestDetail->full_name }}
                    </span>
                    <span class="text-sm text-muted-light dark:text-muted-dark capitalize mt-0.5">
                        {{ $ticket->guestDetail->entity_type->value }}
                        @if ($ticket->guestDetail->department)
                            @if (strtolower($ticket->guestDetail->department->name) === 'lainnya' && !empty($ticket->guestDetail->other_department))
                                &bull; {{ $ticket->guestDetail->other_department }}
                            @else
                                &bull; {{ $ticket->guestDetail->department->name }}
                            @endif
                        @endif
                    </span>
                </div>
            </div>

            <div
                class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm mb-6 border-t border-border-light dark:border-border-dark pt-6">
                <div>
                    <p class="text-muted-light dark:text-muted-dark font-medium mb-1">Email</p>
                    <p class="text-text-light dark:text-text-dark font-semibold break-all">
                        {{ $showSensitiveData ? $ticket->guestDetail->email : $maskEmail($ticket->guestDetail->email) }}
                    </p>
                </div>

                <div>
                    <p class="text-muted-light dark:text-muted-dark font-medium mb-1">WhatsApp</p>
                    <p class="text-text-light dark:text-text-dark font-semibold font-mono break-all">
                        @if ($showSensitiveData && !empty($ticket->guestDetail->phone))
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $ticket->guestDetail->phone)) }}"
                                target="_blank"
                                class="text-green-600 dark:text-green-400 hover:underline flex items-center gap-1 w-fit">
                                {{ $ticket->guestDetail->phone }}
                                <span class="material-icons-round text-[14px]">open_in_new</span>
                            </a>
                        @else
                            {{ !empty($ticket->guestDetail->phone) ? $maskPhone($ticket->guestDetail->phone) : '-' }}
                        @endif
                    </p>
                </div>

                <div>
                    <p class="text-muted-light dark:text-muted-dark font-medium mb-1">Nomor ID</p>
                    <p class="text-text-light dark:text-text-dark font-semibold font-mono break-all">
                        {{ $showSensitiveData ? $ticket->guestDetail->identity_number : $maskID($ticket->guestDetail->identity_number) }}
                    </p>
                </div>
            </div>

            @if ($showSensitiveData)
                @if ($ticket->guestDetail->photo_identity_path || $ticket->guestDetail->photo_selfie_path)
                    <div class="border-t border-border-light dark:border-border-dark pt-6">
                        <p class="text-muted-light dark:text-muted-dark font-medium mb-4 text-sm">Lampiran Identitas</p>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if ($ticket->guestDetail->photo_identity_path)
                                <div
                                    class="bg-gray-50 dark:bg-slate-900 p-2 rounded-lg border border-border-light dark:border-border-dark">
                                    <p class="text-xs text-center text-muted-light font-medium mb-2">Kartu Identitas</p>
                                    <img src="{{ asset('storage/' . $ticket->guestDetail->photo_identity_path) }}"
                                        class="w-full max-h-64 object-contain rounded">
                                </div>
                            @endif

                            @if ($ticket->guestDetail->photo_selfie_path)
                                <div
                                    class="bg-gray-50 dark:bg-slate-900 p-2 rounded-lg border border-border-light dark:border-border-dark">
                                    <p class="text-xs text-center text-muted-light font-medium mb-2">Foto Selfie</p>
                                    <img src="{{ asset('storage/' . $ticket->guestDetail->photo_selfie_path) }}"
                                        class="w-full max-h-64 object-contain rounded">
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @else
                <div
                    class="p-3 bg-gray-50 dark:bg-slate-800 rounded-lg text-center border border-dashed border-gray-300 dark:border-gray-600">
                    <span class="material-icons-round text-gray-400 text-lg mb-1">lock</span>
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-tight">
                        Lampiran gambar identitas dilindungi demi privasi pelapor.
                    </p>
                </div>
            @endif
        </div>
    </div>
@endif
