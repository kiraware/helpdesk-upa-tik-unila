@props(['type' => 'success', 'message'])

@php
    // Tentukan warna dan icon berdasarkan tipe
    $isSuccess = $type === 'success';

    $colors = $isSuccess
        ? [
            'bg_icon' => 'bg-emerald-100 dark:bg-emerald-900/30',
            'text_icon' => 'text-emerald-600 dark:text-emerald-400',
            'progress_bg' => 'bg-emerald-200 dark:bg-emerald-900',
            'progress_fill' => 'bg-emerald-500',
        ]
        : [
            'bg_icon' => 'bg-red-100 dark:bg-red-900/30',
            'text_icon' => 'text-red-600 dark:text-red-400',
            'progress_bg' => 'bg-red-200 dark:bg-red-900',
            'progress_fill' => 'bg-red-500',
        ];

    $icon = $isSuccess ? 'check_circle' : 'error_outline';
    $title = $isSuccess ? 'Berhasil' : 'Gagal';
@endphp

<div x-data="toast" x-show="visible" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2 sm:translate-x-full sm:translate-y-0"
    x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed z-50 top-16 left-4 right-4 sm:top-20 sm:left-auto sm:right-6 sm:max-w-sm">

    <div
        class="relative flex overflow-hidden bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-xl">

        {{-- Icon --}}
        <div class="flex items-center justify-center w-12 shrink-0 {{ $colors['bg_icon'] }}">
            <span class="material-icons-round {{ $colors['text_icon'] }}">
                {{ $icon }}
            </span>
        </div>

        {{-- Content --}}
        <div class="flex-1 px-3 py-3 min-w-0">
            <p class="text-sm font-semibold text-text-light dark:text-text-dark">
                {{ $title }}
            </p>
            <p class="text-sm text-muted-light dark:text-muted-dark wrap-break-word">
                {{ $message }}
            </p>
        </div>

        {{-- Close Button --}}
        <button @click="close"
            class="px-3 shrink-0 text-muted-light dark:text-muted-dark hover:text-text-light dark:hover:text-text-dark">
            <span class="material-icons-round text-base">close</span>
        </button>

        {{-- Progress bar --}}
        <div class="absolute bottom-0 left-0 h-1 w-full {{ $colors['progress_bg'] }}">
            <div class="h-full {{ $colors['progress_fill'] }}" :style="`width: ${progress}%`"></div>
        </div>

    </div>
</div>
