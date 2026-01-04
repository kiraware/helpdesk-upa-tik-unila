<x-layouts.guest>
    {{-- Hero Section --}}
    <section class="relative pt-16 pb-20 lg:pt-28 lg:pb-32 overflow-hidden">
        {{-- Background Elements --}}
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div
                class="absolute top-0 right-0 w-1/2 h-full bg-linear-to-l from-blue-50 to-transparent dark:from-blue-900/10 opacity-60">
            </div>
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-brand/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-24 w-64 h-64 bg-blue-400/10 rounded-full blur-3xl"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="max-w-3xl mx-auto space-y-8">
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-brand text-xs font-bold uppercase tracking-wide border border-blue-100 dark:border-blue-800">
                    <span class="w-2 h-2 rounded-full bg-brand animate-pulse"></span>
                    Sistem Layanan Terpadu
                </div>
                <h2
                    class="text-4xl lg:text-6xl font-bold tracking-tight text-text-light dark:text-text-dark leading-[1.15]">
                    Selamat Datang di <br />
                    <span class="text-brand">Helpdesk UPA TIK Unila</span>
                </h2>
                <p class="text-lg lg:text-xl text-muted-light dark:text-muted-dark max-w-2xl mx-auto leading-relaxed">
                    Layanan Bantuan Terpadu Teknologi Informasi & Komunikasi Universitas Lampung. Laporkan kendala
                    atau pantau status tiket bantuan Anda.
                </p>
            </div>
        </div>
    </section>

    {{-- Action Section --}}
    <section class="bg-surface-light dark:bg-surface-dark border-y border-border-light dark:border-border-dark">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-12">

                {{-- Card 1: Create Ticket --}}
                <div
                    class="group relative overflow-hidden rounded-2xl bg-brand text-white shadow-xl shadow-blue-900/10 hover:shadow-2xl hover:shadow-blue-900/20 transition-all duration-300 transform hover:-translate-y-1">
                    <div
                        class="absolute -right-12 -top-12 bg-white/10 w-48 h-48 rounded-full blur-3xl group-hover:bg-white/20 transition-all duration-500">
                    </div>

                    <div class="relative z-10 p-8 lg:p-10 flex flex-col h-full">
                        <div class="flex items-start justify-between mb-6">
                            <div
                                class="w-14 h-14 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center border border-white/30">
                                <span class="material-icons-round icon-lg">edit_note</span>
                            </div>
                            <span
                                class="bg-white/20 backdrop-blur-md text-xs font-bold px-3 py-1 rounded-full border border-white/20">Langkah
                                1</span>
                        </div>
                        <h3 class="text-2xl lg:text-3xl font-bold mb-3">Buat Tiket Baru</h3>
                        <p class="text-blue-50 leading-relaxed mb-8 text-lg">
                            Mengalami kendala teknis? Segera buat tiket pelaporan baru untuk mendapatkan bantuan
                            langsung.
                        </p>
                        <div class="mt-auto">
                            <a href="{{ route('guest.tickets.create') }}"
                                class="group/btn inline-flex items-center justify-center gap-2 bg-white text-brand font-bold py-4 px-6 rounded-xl hover:bg-blue-50 transition-all w-full sm:w-auto shadow-sm">
                                <span
                                    class="material-icons-round group-hover/btn:scale-110 transition-transform">add_circle</span>
                                <span>Buat Tiket Sekarang</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Card 2: Check Ticket --}}
                <div
                    class="relative rounded-2xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark p-8 lg:p-10 flex flex-col justify-center shadow-sm hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-6">
                        <div
                            class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 rounded-2xl flex items-center justify-center text-brand border border-blue-200 dark:border-blue-800">
                            <span class="material-icons-round icon-lg">search</span>
                        </div>
                        <span
                            class="bg-gray-200 dark:bg-gray-700 text-text-light dark:text-text-dark text-xs font-bold px-3 py-1 rounded-full">Langkah
                            2</span>
                    </div>
                    <h3 class="text-2xl lg:text-3xl font-bold text-text-light dark:text-text-dark mb-3">Cek Status Tiket
                    </h3>
                    <p class="text-muted-light dark:text-muted-dark mb-8 leading-relaxed text-lg">
                        Sudah mengirimkan laporan? Masukkan kode tiket Anda untuk melihat progress.
                    </p>

                    {{-- Form Pencarian Tiket --}}
                    <div class="mt-auto relative" x-data="{
                        code: '',
                        isMobile: window.innerWidth < 640,
                        submit() {
                            if (this.code) {
                                window.location.href = '{{ url('/tracking') }}/' + this.code;
                            }
                        }
                    }"
                        @resize.window="isMobile = window.innerWidth < 640">

                        <input type="text" x-model="code" @keydown.enter.prevent="submit()"
                            class="w-full pl-5 pr-14 py-4 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl focus:ring-2 focus:ring-brand focus:border-transparent outline-none transition-all text-text-light dark:text-text-dark shadow-sm text-sm md:text-base placeholder-gray-400 uppercase placeholder:normal-case"
                            :placeholder="isMobile ? '(Contoh: TIK-20231227-1234)' : 'Kode Tiket (Contoh: TIK-20231227-1234)'" />

                        <button type="button" @click="submit()"
                            class="absolute right-2 top-2 bottom-2 bg-brand hover:bg-brand-hover text-white rounded-lg transition-colors flex items-center justify-center aspect-square shadow-sm group">
                            <span
                                class="material-icons-round group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- Services Info --}}
    <section id="services" class="py-24 bg-background-light dark:bg-background-dark">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                {{-- Item Layanan --}}
                <div
                    class="bg-surface-light dark:bg-surface-dark rounded-xl p-8 shadow-sm border border-border-light dark:border-border-dark">
                    <div
                        class="w-12 h-12 rounded-lg bg-blue-50 dark:bg-blue-900/30 flex items-center justify-center text-brand mb-6">
                        <span class="material-icons-round icon-md">support_agent</span>
                    </div>
                    <h4 class="text-xl font-bold text-text-light dark:text-text-dark mb-3">Layanan Helpdesk</h4>
                    <p class="text-sm text-muted-light dark:text-muted-dark">
                        Kami melayani permasalahan Email Unila, Jaringan Internet, Siakadu, SSO, dan Lainnya.
                    </p>
                </div>

                {{-- Item Waktu --}}
                <div
                    class="bg-surface-light dark:bg-surface-dark rounded-xl p-8 shadow-sm border border-border-light dark:border-border-dark">
                    <div
                        class="w-12 h-12 rounded-lg bg-green-50 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400 mb-6">
                        <span class="material-icons-round icon-md">schedule</span>
                    </div>
                    <h4 class="text-xl font-bold text-text-light dark:text-text-dark mb-4">Jam Operasional</h4>

                    <ul class="text-sm text-muted-light dark:text-muted-dark space-y-3">
                        <li>
                            <span class="font-bold text-text-light dark:text-text-dark block mb-1">Senin -
                                Kamis</span>
                            08.00 - 12.00 | 13.30 - 16.00 WIB
                        </li>
                        <li>
                            <span class="font-bold text-text-light dark:text-text-dark block mb-1">Jum'at</span>
                            08.00 - 11.30 | 14.00 - 16.30 WIB
                        </li>
                    </ul>
                </div>

                {{-- Item FAQ --}}
                <div id="faq"
                    class="bg-surface-light dark:bg-surface-dark rounded-xl p-8 shadow-sm border border-border-light dark:border-border-dark">
                    <div
                        class="w-12 h-12 rounded-lg bg-orange-50 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400 mb-6">
                        <span class="material-icons-round icon-md">help_outline</span>
                    </div>
                    <h4 class="text-xl font-bold text-text-light dark:text-text-dark mb-3">Butuh Bantuan?</h4>
                    <p class="text-sm text-muted-light dark:text-muted-dark mb-4">
                        Cek FAQ kami untuk solusi cepat masalah umum.
                    </p>
                    <a href="#" class="text-brand text-sm font-semibold hover:underline">Lihat FAQ &rarr;</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.guest>
