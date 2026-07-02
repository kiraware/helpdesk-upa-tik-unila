<x-layouts.guest>
    <section class="relative pt-16 pb-20 lg:pt-28 lg:pb-32 overflow-hidden">
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div
                class="absolute top-0 right-0 w-1/2 h-full bg-linear-to-l from-blue-50 to-transparent dark:from-blue-900/10 opacity-60">
            </div>
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-24 w-64 h-64 bg-blue-400/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="text-center lg:text-left space-y-8">
                    <h2
                        class="text-4xl lg:text-6xl font-bold tracking-tight text-text-light dark:text-text-dark leading-[1.15]">
                        Selamat Datang di <br />
                        <span class="text-brand">Helpdesk UPA TIK Unila</span>
                    </h2>
                    <p
                        class="text-lg lg:text-xl text-muted-light dark:text-muted-dark max-w-2xl mx-auto lg:mx-0 leading-relaxed">
                        Layanan Bantuan Terpadu Teknologi Informasi & Komunikasi Universitas Lampung. Laporkan kendala
                        dan pantau status tiket bantuan Anda.
                    </p>
                </div>

                <div
                    class="relative rounded-2xl bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark p-8 shadow-xl shadow-blue-900/5">
                    <h3 class="text-2xl font-bold text-text-light dark:text-text-dark mb-3">
                        Cek Status Tiket
                    </h3>
                    <p class="text-muted-light dark:text-muted-dark mb-6 text-sm lg:text-base">
                        Sudah mengirimkan laporan? Masukkan kode tiket Anda untuk melihat progress.
                    </p>

                    <form action="{{ route('guest.tracking.search') }}" method="POST">
                        @csrf
                        <div class="relative">
                            <input type="text" name="ticket_code" required
                                class="w-full pl-5 pr-14 py-4 bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-brand focus:border-transparent outline-none transition-all text-text-light dark:text-text-dark shadow-sm text-sm md:text-base placeholder-gray-400 uppercase placeholder:normal-case"
                                placeholder="Kode Tiket (Contoh: A7B9K2)" value="{{ old('ticket_code') }}" />

                            <button type="submit"
                                class="absolute right-2 top-2 bottom-2 bg-brand hover:bg-brand-hover text-white rounded-lg transition-colors flex items-center justify-center aspect-square shadow-sm group">
                                <span
                                    class="material-icons-round group-hover:translate-x-1 transition-transform">arrow_forward</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section
        class="py-16 lg:py-24 bg-surface-light dark:bg-surface-dark border-y border-border-light dark:border-border-dark">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl lg:text-4xl font-bold text-text-light dark:text-text-dark mb-4">
                    Layanan UPA TIK
                </h2>
                <p class="text-lg text-muted-light dark:text-muted-dark max-w-2xl mx-auto">
                    Pilih layanan bantuan di bawah ini yang sesuai dengan kebutuhan Anda.
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
                @foreach ($services as $service)
                    @php
                        $targetRoute = $service->show_to_guest
                            ? route('guest.tickets.create', ['service_id' => $service->id])
                            : route('tickets.create', ['service_id' => $service->id]);
                    @endphp
                    <a href="{{ $targetRoute }}"
                        class="group flex flex-col items-center justify-start text-center p-5 h-full rounded-2xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark hover:border-brand dark:hover:border-brand hover:shadow-md hover:shadow-blue-900/5 transition-all duration-300">
                        <div
                            class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-900/20 flex items-center justify-center text-brand mb-4 shrink-0 group-hover:scale-110 transition-transform duration-300">
                            <span class="material-icons-round text-2xl">miscellaneous_services</span>
                        </div>
                        <h3
                            class="text-base lg:text-lg font-bold text-text-light dark:text-text-dark group-hover:text-brand transition-colors break-words [word-break:break-word] w-full line-clamp-3">
                            {{ $service->name }}
                        </h3>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section id="services"
        class="py-24 bg-surface-light dark:bg-surface-dark border-b border-border-light dark:border-border-dark">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div
                    class="bg-background-light dark:bg-background-dark rounded-xl p-8 shadow-sm border border-border-light dark:border-border-dark">
                    <div
                        class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-brand mb-6">
                        <span class="material-icons-round icon-md">support_agent</span>
                    </div>
                    <h4 class="text-xl font-bold text-text-light dark:text-text-dark mb-3">Layanan Helpdesk</h4>
                    <p class="text-sm text-muted-light dark:text-muted-dark">
                        Kami melayani permasalahan Email Unila, Jaringan Internet, Siakadu, SSO, dan Lainnya.
                    </p>
                </div>

                <div
                    class="bg-background-light dark:bg-background-dark rounded-xl p-8 shadow-sm border border-border-light dark:border-border-dark">
                    <div
                        class="w-12 h-12 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 mb-6">
                        <span class="material-icons-round icon-md">schedule</span>
                    </div>
                    <h4 class="text-xl font-bold text-text-light dark:text-text-dark mb-4">Jam Operasional</h4>

                    <ul class="text-sm text-muted-light dark:text-muted-dark space-y-3">
                        <li>
                            <span class="font-bold text-text-light dark:text-text-dark block mb-1">Senin - Kamis</span>
                            08.00 - 12.00 | 13.30 - 16.00 WIB
                        </li>
                        <li>
                            <span class="font-bold text-text-light dark:text-text-dark block mb-1">Jum'at</span>
                            08.00 - 11.30 | 14.00 - 16.30 WIB
                        </li>
                    </ul>
                </div>

                <div id="faq"
                    class="bg-background-light dark:bg-background-dark rounded-xl p-8 shadow-sm border border-border-light dark:border-border-dark">
                    <div
                        class="w-12 h-12 rounded-lg bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400 mb-6">
                        <span class="material-icons-round icon-md">help_outline</span>
                    </div>
                    <h4 class="text-xl font-bold text-text-light dark:text-text-dark mb-3">Butuh Bantuan?</h4>
                    <p class="text-sm text-muted-light dark:text-muted-dark mb-4">
                        Cek FAQ kami untuk solusi cepat masalah umum.
                    </p>
                    <a href="{{ route('faq') }}" class="text-brand text-sm font-semibold hover:underline">
                        Lihat FAQ &rarr;
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.guest>
