<x-layouts.dashboard title="Detail Tiket #{{ $ticket->ticket_code }}">

    <x-tickets.show.header :ticket="$ticket" />

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">

        <div class="lg:col-span-3 space-y-8">

            <x-tickets.show.guest-details :ticket="$ticket" />

            <x-tickets.show.description :ticket="$ticket" />

            <x-tickets.show.comments :ticket="$ticket" />

            <x-tickets.show.reply-form :ticket="$ticket" />

            <x-tickets.show.survey :ticket="$ticket" />

        </div>

        <div class="lg:col-span-1">
            <x-tickets.show.sidebar :ticket="$ticket" :admins="$admins" :services="$services" />
        </div>

    </div>

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
