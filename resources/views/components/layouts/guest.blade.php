@props([
    'title' => 'Helpdesk UPA TIK - Universitas Lampung',
])

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }}</title>

    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark 
           antialiased transition-colors duration-200 font-sans 
           overflow-x-hidden">

    <div class="min-h-screen flex flex-col w-full min-w-0">
        <x-landing.navbar />

        <main class="flex-1 w-full">
            {{ $slot }}
        </main>

        <x-landing.footer />
    </div>

    {{-- Toast Notifications --}}
    @if (session('success'))
        <x-toast type="success" :message="session('success')" />
    @elseif (session('warning'))
        <x-toast type="warning" :message="session('warning')" />
    @elseif (session('error'))
        <x-toast type="error" :message="session('error')" />
    @else
        {{-- Toast Kosong (Hidden) standby menunggu event JS --}}
        <x-toast />
    @endif

</body>

</html>
