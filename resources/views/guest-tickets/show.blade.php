<x-layouts.guest title="Tiket #{{ $ticket->ticket_code }}">

    <div class="min-h-screen bg-gray-50 dark:bg-slate-900 py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            {{-- Tombol Kembali --}}
            <a href="{{ route('guest.tracking.index') }}"
                class="inline-flex items-center text-sm text-gray-500 hover:text-blue-600 mb-6 transition-colors">
                <span class="material-icons-round mr-1 text-base">arrow_back</span>
                Cari Tiket Lain
            </a>

            {{-- HEADER SECTION --}}
            {{-- Kita reuse header component, tapi pastikan form edit title TIDAK MUNCUL untuk guest --}}
            {{-- Karena di komponen header ada cek auth/permission untuk edit, seharusnya aman. --}}
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
                    {{-- KITA TIMPA KOMPONEN REPLY FORM AGAR ROUTE POST-NYA BENAR --}}
                    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
                        @if (!in_array($ticket->status, [\App\Enums\TicketStatus::DONE, \App\Enums\TicketStatus::REJECT]))
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Balas Tiket</h3>

                            <form action="{{ route('guest.tickets.comments.store', $ticket->uuid) }}" method="POST">
                                @csrf
                                <div
                                    class="border border-gray-300 dark:border-gray-600 rounded-xl overflow-hidden focus-within:ring-1 focus-within:ring-blue-500 bg-white dark:bg-slate-800">
                                    <div class="px-4 py-2 border-b bg-gray-50 dark:bg-slate-700/50">
                                        <input id="x_message_guest" type="hidden" name="message">
                                        <trix-editor input="x_message_guest"
                                            data-upload-url="{{ route('guest.comments.upload.editor.attachments') }}"
                                            class="prose dark:prose-invert max-w-none min-h-[100px] outline-none bg-transparent"
                                            placeholder="Tulis balasan Anda..."></trix-editor>
                                    </div>
                                    <div class="px-3 py-2 bg-gray-50 dark:bg-slate-800/50 flex justify-end">
                                        <button type="submit"
                                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg shadow-sm transition-colors">
                                            Kirim Balasan
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @else
                            <div class="p-4 bg-gray-100 dark:bg-slate-800 rounded-lg text-center text-gray-500">
                                Tiket ini telah ditutup.
                            </div>
                        @endif
                    </div>

                </div>

                {{-- RIGHT COLUMN: Sidebar --}}
                <div class="lg:col-span-1">
                    <x-tickets.show.sidebar :ticket="$ticket" />
                </div>

            </div>
        </div>
    </div>
</x-layouts.guest>
