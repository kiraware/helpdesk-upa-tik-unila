<x-layouts.dashboard title="Buat Tiket Baru">

    <x-tickets.create.header />

    <form action="{{ route('tickets.store') }}" method="POST" enctype="multipart/form-data" class="max-w-4xl">
        @csrf

        <div class="space-y-6">

            <x-tickets.create.metadata :services="$services" />

            <x-tickets.create.editor />

        </div>
    </form>

</x-layouts.dashboard>
