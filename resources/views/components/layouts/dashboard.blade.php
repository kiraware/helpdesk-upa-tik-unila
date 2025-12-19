@props([
    'title' => 'Dashboard',
])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Page Title --}}
    <title>{{ $title }}</title>

    @vite('resources/css/app.css')
    @vite('resources/js/app.js')

    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
</head>

<body
    class="bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark antialiased transition-colors duration-200">

    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <x-sidebar />

        {{-- Overlay (Mobile) --}}
        <div x-show="sidebarOpen" x-transition @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-20 lg:hidden">
        </div>

        {{-- Main Content --}}
        <div class="flex flex-col flex-1 w-full overflow-hidden">
            {{-- ⬇️ TERUSKAN TITLE KE NAVBAR --}}
            <x-navbar :title="$title" />

            <main class="flex-1 p-6 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>

    </div>

    {{-- Toast Success --}}
    @if (session('success'))
        <div x-data="toast()" x-init="show()" x-show="visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 sm:translate-x-full sm:translate-y-0"
            x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="
            fixed z-50
            top-16 left-4 right-4
            sm:top-20 sm:left-auto sm:right-6
            sm:max-w-sm
        ">

            <div
                class="relative flex overflow-hidden
                bg-surface-light dark:bg-surface-dark
                border border-border-light dark:border-border-dark
                rounded-xl shadow-xl">

                {{-- Icon --}}
                <div
                    class="flex items-center justify-center w-12 shrink-0
                        bg-emerald-100 dark:bg-emerald-900/30">
                    <span
                        class="material-icons-round
                            text-emerald-600 dark:text-emerald-400">
                        check_circle
                    </span>
                </div>

                {{-- Content --}}
                <div class="flex-1 px-3 py-3 min-w-0">
                    <p class="text-sm font-semibold text-text-light dark:text-text-dark">
                        Berhasil
                    </p>
                    <p class="text-sm text-muted-light dark:text-muted-dark wrap-break-word">
                        {{ session('success') }}
                    </p>
                </div>

                {{-- Close --}}
                <button @click="close"
                    class="px-3 shrink-0 text-muted-light dark:text-muted-dark
                    hover:text-text-light dark:hover:text-text-dark">
                    <span class="material-icons-round text-base">close</span>
                </button>

                {{-- Progress bar --}}
                <div
                    class="absolute bottom-0 left-0 h-1 w-full
                        bg-emerald-200 dark:bg-emerald-900">
                    <div class="h-full bg-emerald-500" :style="`width: ${progress}%`">
                    </div>
                </div>

            </div>
        </div>
    @endif

    {{-- Toast Error --}}
    @if (session('error'))
        <div x-data="toast()" x-init="show()" x-show="visible"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-2 sm:translate-x-full sm:translate-y-0"
            x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="
            fixed z-50
            top-16 left-4 right-4
            sm:top-20 sm:left-auto sm:right-6
            sm:max-w-sm
        ">

            <div
                class="relative flex overflow-hidden
                bg-surface-light dark:bg-surface-dark
                border border-border-light dark:border-border-dark
                rounded-xl shadow-xl">

                {{-- Icon Error (Red) --}}
                <div
                    class="flex items-center justify-center w-12 shrink-0
                        bg-red-100 dark:bg-red-900/30">
                    <span class="material-icons-round
                            text-red-600 dark:text-red-400">
                        error_outline
                    </span>
                </div>

                {{-- Content --}}
                <div class="flex-1 px-3 py-3 min-w-0">
                    <p class="text-sm font-semibold text-text-light dark:text-text-dark">
                        Gagal
                    </p>
                    <p class="text-sm text-muted-light dark:text-muted-dark wrap-break-word">
                        {{ session('error') }}
                    </p>
                </div>

                {{-- Close Button --}}
                <button @click="close"
                    class="px-3 shrink-0 text-muted-light dark:text-muted-dark
                    hover:text-text-light dark:hover:text-text-dark">
                    <span class="material-icons-round text-base">close</span>
                </button>

                {{-- Progress bar (Red) --}}
                <div class="absolute bottom-0 left-0 h-1 w-full
                        bg-red-200 dark:bg-red-900">
                    <div class="h-full bg-red-500" :style="`width: ${progress}%`">
                    </div>
                </div>

            </div>
        </div>
    @endif

    <script>
        function toast() {
            return {
                visible: false,
                progress: 100,
                duration: 4000,
                interval: null,

                show() {
                    this.visible = true;
                    this.progress = 100;

                    const step = 100 / (this.duration / 50);

                    this.interval = setInterval(() => {
                        this.progress -= step;
                        if (this.progress <= 0) {
                            this.close();
                        }
                    }, 50);
                },

                close() {
                    this.visible = false;
                    clearInterval(this.interval);
                }
            }
        }
    </script>

</body>

</html>
