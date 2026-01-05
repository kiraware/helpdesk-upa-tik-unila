@props(['type' => 'success', 'message' => ''])

<div x-data="toast('{{ $message }}', '{{ $type }}')" x-show="visible" x-cloak x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2 sm:translate-x-full sm:translate-y-0"
    x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed z-50 top-16 left-4 right-4 sm:top-20 sm:left-auto sm:right-6 sm:max-w-sm">

    <div
        class="relative flex overflow-hidden bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl shadow-xl">

        {{-- Icon Section --}}
        <div class="flex items-center justify-center w-12 shrink-0 transition-colors duration-300"
            :class="theme.bg_icon">
            <span class="material-icons-round transition-colors duration-300" :class="theme.text_icon"
                x-text="theme.icon">
            </span>
        </div>

        {{-- Content Section --}}
        <div class="flex-1 px-3 py-3 min-w-0">
            <p class="text-sm font-semibold text-text-light dark:text-text-dark" x-text="theme.title"></p>
            <p class="text-sm text-muted-light dark:text-muted-dark wrap-break-word" x-text="message"></p>
        </div>

        {{-- Close Button --}}
        <button @click="close"
            class="px-3 shrink-0 text-muted-light dark:text-muted-dark hover:text-text-light dark:hover:text-text-dark">
            <span class="material-icons-round text-base">close</span>
        </button>

        {{-- Progress Bar --}}
        <div class="absolute bottom-0 left-0 h-1 w-full transition-colors duration-300" :class="theme.progress_bg">
            <div class="h-full transition-colors duration-300" :class="theme.progress_fill"
                :style="`width: ${progress}%`">
            </div>
        </div>

    </div>
</div>
