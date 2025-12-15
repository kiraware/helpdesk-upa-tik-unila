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

<body class="bg-gray-100">

    <div x-data="{ sidebarOpen: false }" class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        <x-sidebar />

        {{-- Overlay (Mobile) --}}
        <div x-show="sidebarOpen" x-transition @click="sidebarOpen = false"
            class="fixed inset-0 bg-black/50 z-20 lg:hidden">
        </div>

        {{-- Main Content --}}
        <div class="flex flex-col flex-1">
            {{-- ⬇️ TERUSKAN TITLE KE NAVBAR --}}
            <x-navbar :title="$title" />

            <main class="flex-1 p-6 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>

    </div>

</body>

</html>
