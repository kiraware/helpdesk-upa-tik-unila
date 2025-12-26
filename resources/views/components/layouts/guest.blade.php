@props([
    'title' => 'Helpdesk System',
])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- PENTING: CSRF Token wajib ada untuk upload Trix & Form --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>

    {{-- Load Assets (Sama seperti dashboard) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark antialiased transition-colors duration-200 font-sans">

    {{-- Main Content Wrapper --}}
    {{-- Layout ini memusatkan konten (form) di tengah layar --}}
    <main class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        {{ $slot }}
    </main>

    {{-- Toast Notifications --}}
    {{-- Kita pasang juga disini agar Guest bisa melihat pesan sukses/error --}}
    @if (session('success'))
        <x-toast type="success" :message="session('success')" />
    @endif

    @if (session('error'))
        <x-toast type="error" :message="session('error')" />
    @endif

</body>

</html>
