@props([
    'title' => 'Dashboard',
])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
            <x-navbar :title="$title" />

            <main class="flex-1 p-6 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>

    </div>

    {{-- Toast Notifications --}}
    @if (session('success'))
        <x-toast type="success" :message="session('success')" />
    @endif

    @if (session('error'))
        <x-toast type="error" :message="session('error')" />
    @endif

</body>

</html>
