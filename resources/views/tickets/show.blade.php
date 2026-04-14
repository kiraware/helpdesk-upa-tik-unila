<x-layouts.dashboard title="Detail Tiket #{{ $ticket->ticket_code }}">

    {{-- HEADER SECTION --}}
    <x-tickets.show.header :ticket="$ticket" />

    {{-- MAIN LAYOUT --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        {{-- LEFT COLUMN: Discussion --}}
        <div class="lg:col-span-3 space-y-8">

            {{-- 1. Initial Description --}}
            <x-tickets.show.description :ticket="$ticket" />

            {{-- 2. Comments --}}
            <x-tickets.show.comments :ticket="$ticket" />

            {{-- 3. Reply Form --}}
            <x-tickets.show.reply-form :ticket="$ticket" />

            {{-- 4. Survey Kepuasan --}}
            <x-tickets.show.survey :ticket="$ticket" />

        </div>

        {{-- RIGHT COLUMN: Sidebar --}}
        <div class="lg:col-span-1">
            <x-tickets.show.sidebar :ticket="$ticket" :services="$services" />
        </div>

    </div>

    {{-- Script untuk update jumlah file di input --}}
    <script>
        function updateFileCount(input) {
            const label = document.getElementById('file-count-label');
            if (input.files && input.files.length > 0) {
                label.textContent = input.files.length + " file dipilih";
                label.classList.add('text-secondary', 'font-bold');
            } else {
                label.textContent = "Lampirkan file";
                label.classList.remove('text-secondary', 'font-bold');
            }
        }
    </script>

</x-layouts.dashboard>
