<x-layouts.guest title="Informasi & FAQ - UPA TIK Unila">

    {{-- HERO SECTION --}}
    {{-- Mobile: Padding lebih kecil (pt-12), Desktop: Padding besar (pt-20) --}}
    <section class="relative pt-12 pb-16 md:pt-20 md:pb-24 overflow-hidden">
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div
                class="absolute top-0 right-0 w-3/4 h-full bg-linear-to-l from-blue-50 to-transparent dark:from-blue-900/10 opacity-60">
            </div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div
                class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-secondary text-[10px] sm:text-xs font-bold uppercase tracking-wide border border-blue-100 dark:border-blue-800 mb-4 sm:mb-6">
                <span class="material-icons-round text-sm">info</span>
                Pusat Informasi
            </div>
            {{-- Typography Scaling: text-3xl di HP, text-5xl di Desktop --}}
            <h1
                class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-text-light dark:text-text-dark tracking-tight mb-4">
                Panduan Layanan <span
                    class="text-transparent bg-clip-text bg-gradient-to-r from-secondary to-blue-600">UPA TIK</span>
            </h1>
            <p class="text-base sm:text-lg text-muted-light dark:text-muted-dark max-w-2xl mx-auto px-2">
                Informasi lengkap mengenai Email Institusi, Akun SSO, dan prosedur layanan Helpdesk Universitas Lampung.
            </p>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20 space-y-12 sm:space-y-16">

        {{-- BAGIAN 1: EMAIL RESMI UNILA --}}
        <section>
            <div class="flex items-center gap-3 mb-6">
                <div
                    class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center text-secondary shrink-0">
                    <span class="material-icons-round">alternate_email</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-text-light dark:text-text-dark">1. Email Resmi
                    @unila.ac.id</h2>
            </div>

            {{-- Grid Domain Email --}}
            {{-- Mobile: 1 kolom, Tablet: 2 kolom, Desktop: 4 kolom --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-8">
                <div
                    class="bg-surface-light dark:bg-surface-dark p-5 rounded-xl border border-border-light dark:border-border-dark shadow-sm hover:border-secondary transition-colors group">
                    <div class="text-secondary font-bold mb-1 group-hover:translate-x-1 transition-transform">Dosen
                    </div>
                    <div class="text-sm text-muted-light dark:text-muted-dark">Contoh (FMIPA):</div>
                    <code
                        class="text-xs bg-gray-100 dark:bg-slate-800 px-2 py-1 rounded mt-2 block w-fit text-blue-600 dark:text-blue-400 font-mono break-all">user@fmipa.unila.ac.id</code>
                </div>
                <div
                    class="bg-surface-light dark:bg-surface-dark p-5 rounded-xl border border-border-light dark:border-border-dark shadow-sm hover:border-secondary transition-colors group">
                    <div class="text-secondary font-bold mb-1 group-hover:translate-x-1 transition-transform">Tenaga
                        Kependidikan</div>
                    <code
                        class="text-xs bg-gray-100 dark:bg-slate-800 px-2 py-1 rounded mt-2 block w-fit text-blue-600 dark:text-blue-400 font-mono break-all">user@staff.unila.ac.id</code>
                </div>
                <div
                    class="bg-surface-light dark:bg-surface-dark p-5 rounded-xl border border-border-light dark:border-border-dark shadow-sm hover:border-secondary transition-colors group">
                    <div class="text-secondary font-bold mb-1 group-hover:translate-x-1 transition-transform">Mahasiswa
                    </div>
                    <code
                        class="text-xs bg-gray-100 dark:bg-slate-800 px-2 py-1 rounded mt-2 block w-fit text-blue-600 dark:text-blue-400 font-mono break-all">user@students.unila.ac.id</code>
                </div>
                <div
                    class="bg-surface-light dark:bg-surface-dark p-5 rounded-xl border border-border-light dark:border-border-dark shadow-sm hover:border-secondary transition-colors group">
                    <div class="text-secondary font-bold mb-1 group-hover:translate-x-1 transition-transform">Unit Kerja
                        / Kegiatan</div>
                    <code
                        class="text-xs bg-gray-100 dark:bg-slate-800 px-2 py-1 rounded mt-2 block w-fit text-blue-600 dark:text-blue-400 font-mono break-all">user@kpa.unila.ac.id</code>
                </div>
            </div>

            {{-- Persyaratan Unit Kerja --}}
            {{-- Mobile: Stack vertikal, Desktop: Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-6">
                <div
                    class="col-span-1 md:col-span-2 bg-yellow-50 dark:bg-yellow-900/10 border border-yellow-200 dark:border-yellow-700/30 rounded-xl p-5 sm:p-6">
                    <h3
                        class="font-bold text-yellow-800 dark:text-yellow-500 mb-3 flex items-center gap-2 text-sm sm:text-base">
                        <span class="material-icons-round">description</span>
                        Syarat Khusus Unit Kerja / Kegiatan
                    </h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-400 mb-3 leading-relaxed">
                        Wajib membuat <strong>Surat Permohonan Email Resmi</strong> ditujukan kepada Kepala UPA TIK
                        Unila, berisi:
                    </p>
                    <ul
                        class="list-disc list-inside text-sm text-yellow-700 dark:text-yellow-400 space-y-1 ml-1 sm:ml-2">
                        <li>Deskripsi unit kerja / jurnal / seminar</li>
                        <li>Username yang diusulkan</li>
                        <li>NIP dan Nama Penanggung Jawab</li>
                    </ul>
                </div>

                {{-- Note Simple --}}
                <div
                    class="bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800 rounded-xl p-5 sm:p-6 flex flex-col justify-center">
                    <h3 class="font-bold text-blue-800 dark:text-blue-400 mb-2 text-sm sm:text-base">Penting!</h3>
                    <p class="text-sm text-blue-700 dark:text-blue-300 leading-relaxed">
                        Dosen, Tendik, dan Mahasiswa <strong>tidak perlu surat</strong>. Cukup ajukan via TIKET Helpdesk
                        kategori "Email Resmi Unila".
                    </p>
                </div>
            </div>
        </section>

        <hr class="border-border-light dark:border-border-dark">

        {{-- BAGIAN 2 & 3: SSO --}}
        <section x-data="{ tab: 'reg' }">
            <div class="flex items-center gap-3 mb-6">
                <div
                    class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-emerald-600 dark:text-emerald-400 shrink-0">
                    <span class="material-icons-round">lock_person</span>
                </div>
                <h2 class="text-xl sm:text-2xl font-bold text-text-light dark:text-text-dark">2. Layanan Akun SSO</h2>
            </div>

            {{-- Mobile-First Tab Switcher --}}
            {{-- Mobile: Grid 2 kolom (tombol besar), Desktop: Flex inline (tombol rapi) --}}
            <div
                class="grid grid-cols-2 sm:inline-flex sm:space-x-1 rounded-xl bg-gray-100 dark:bg-slate-800 p-1 mb-8 w-full sm:w-fit">
                <button @click="tab = 'reg'"
                    :class="tab === 'reg' ? 'bg-white dark:bg-surface-dark text-secondary shadow-sm' :
                        'text-muted-light hover:text-text-light'"
                    class="rounded-lg px-2 sm:px-4 py-2.5 text-xs sm:text-sm font-bold transition-all text-center">
                    Registrasi Baru
                </button>
                <button @click="tab = 'reset'"
                    :class="tab === 'reset' ? 'bg-white dark:bg-surface-dark text-secondary shadow-sm' :
                        'text-muted-light hover:text-text-light'"
                    class="rounded-lg px-2 sm:px-4 py-2.5 text-xs sm:text-sm font-bold transition-all text-center">
                    Lupa Password
                </button>
            </div>

            {{-- Content: Registrasi --}}
            <div x-show="tab === 'reg'" x-transition.opacity class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                <div
                    class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl p-5 sm:p-6 relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-blue-50 dark:bg-blue-900/20 rounded-bl-full -mr-4 -mt-4 pointer-events-none">
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark mb-4 relative z-10">
                        👨‍🏫 Dosen & Tendik</h3>
                    <ul class="space-y-3 relative z-10">
                        <li class="flex items-start gap-3 text-sm text-muted-light dark:text-muted-dark">
                            <span class="material-icons-round text-emerald-500 text-lg shrink-0">check_circle</span>
                            <span>Scan/Foto SK Pengangkatan</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-muted-light dark:text-muted-dark">
                            <span class="material-icons-round text-emerald-500 text-lg shrink-0">check_circle</span>
                            <span>Swafoto (Selfie) memegang SK Pengangkatan</span>
                        </li>
                    </ul>
                </div>
                <div
                    class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl p-5 sm:p-6 relative overflow-hidden">
                    <div
                        class="absolute top-0 right-0 w-24 h-24 bg-orange-50 dark:bg-orange-900/20 rounded-bl-full -mr-4 -mt-4 pointer-events-none">
                    </div>
                    <h3 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark mb-4 relative z-10">🎓
                        Mahasiswa (Pindahan/Profesi)</h3>
                    <ul class="space-y-3 relative z-10">
                        <li class="flex items-start gap-3 text-sm text-muted-light dark:text-muted-dark">
                            <span class="material-icons-round text-emerald-500 text-lg shrink-0">check_circle</span>
                            <span>Scan/Foto KTM Unila & KTP</span>
                        </li>
                        <li class="flex items-start gap-3 text-sm text-muted-light dark:text-muted-dark">
                            <span class="material-icons-round text-emerald-500 text-lg shrink-0">check_circle</span>
                            <span>Swafoto (Selfie) memegang KTM & KTP</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Content: Lupa Password --}}
            <div x-show="tab === 'reset'" x-cloak x-transition.opacity class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <div
                        class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl p-5 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark mb-4">👨‍🏫 Dosen
                            & Tendik</h3>
                        <p class="text-xs text-muted-light mb-3 font-semibold uppercase tracking-wider">Persyaratan:</p>
                        <ul class="space-y-2 text-sm text-muted-light dark:text-muted-dark">
                            <li class="flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 bg-secondary rounded-full shrink-0"></span> ID Unila / KTP / SK
                            </li>
                            <li class="flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 bg-secondary rounded-full shrink-0"></span> Selfie dengan dokumen
                            </li>
                        </ul>
                    </div>
                    <div
                        class="bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl p-5 sm:p-6">
                        <h3 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark mb-4">🎓 Mahasiswa
                            Aktif</h3>
                        <p class="text-xs text-muted-light mb-3 font-semibold uppercase tracking-wider">Persyaratan:</p>
                        <ul class="space-y-2 text-sm text-muted-light dark:text-muted-dark">
                            <li class="flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 bg-secondary rounded-full shrink-0"></span> KTM dan KTP</li>
                            <li class="flex items-center gap-2"><span
                                    class="w-1.5 h-1.5 bg-secondary rounded-full shrink-0"></span> Selfie dengan KTM &
                                KTP</li>
                        </ul>
                    </div>
                </div>

                {{-- Critical Warning (Onsite) --}}
                <div
                    class="bg-red-50 dark:bg-red-900/10 border-l-4 border-red-500 p-5 rounded-r-xl flex flex-col sm:flex-row items-start gap-3 sm:gap-4">
                    <span
                        class="material-icons-round text-red-600 dark:text-red-400 text-2xl mt-0.5 shrink-0">warning</span>
                    <div>
                        <h4 class="font-bold text-red-800 dark:text-red-400 text-base">Lupa Username SSO?</h4>
                        <p class="text-sm text-red-700 dark:text-red-300 mt-1 leading-relaxed">
                            Layanan ini <strong>TIDAK BISA ONLINE</strong>. Anda wajib datang (Onsite) ke <strong>Gedung
                                UPA TIK Unila Lantai 1</strong> dengan membawa seluruh persyaratan fisik di atas.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <hr class="border-border-light dark:border-border-dark">

        {{-- BAGIAN 4: JENIS LAYANAN HELPDESK --}}
        <section>
            <div class="text-center mb-6 sm:mb-8">
                <h2 class="text-xl sm:text-2xl font-bold text-text-light dark:text-text-dark">Kategori Tiket Bantuan
                </h2>
                <p class="text-sm sm:text-base text-muted-light dark:text-muted-dark mt-2">Pilih kategori ini saat
                    membuat tiket baru</p>
            </div>

            {{-- Grid Icon: 2 kolom di HP, 3 di Tablet, 6 di Desktop --}}
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                {{-- Item 1 --}}
                <div
                    class="flex flex-col items-center p-4 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl text-center hover:shadow-md transition-shadow active:scale-95 duration-200">
                    <span class="material-icons-round text-3xl text-blue-500 mb-2">lock_reset</span>
                    <span class="text-xs sm:text-sm font-semibold text-text-light dark:text-text-dark">Lupa
                        Password</span>
                </div>
                {{-- Item 2 --}}
                <div
                    class="flex flex-col items-center p-4 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl text-center hover:shadow-md transition-shadow active:scale-95 duration-200">
                    <span class="material-icons-round text-3xl text-blue-500 mb-2">person_add</span>
                    <span class="text-xs sm:text-sm font-semibold text-text-light dark:text-text-dark">Registrasi
                        SSO</span>
                </div>
                {{-- Item 3 --}}
                <div
                    class="flex flex-col items-center p-4 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl text-center hover:shadow-md transition-shadow active:scale-95 duration-200">
                    <span class="material-icons-round text-3xl text-blue-500 mb-2">mail</span>
                    <span class="text-xs sm:text-sm font-semibold text-text-light dark:text-text-dark">Email
                        Resmi</span>
                </div>
                {{-- Item 4 --}}
                <div
                    class="flex flex-col items-center p-4 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl text-center hover:shadow-md transition-shadow active:scale-95 duration-200">
                    <span class="material-icons-round text-3xl text-blue-500 mb-2">wifi</span>
                    <span
                        class="text-xs sm:text-sm font-semibold text-text-light dark:text-text-dark">Jaringan/WiFi</span>
                </div>
                {{-- Item 5 --}}
                <div
                    class="flex flex-col items-center p-4 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl text-center hover:shadow-md transition-shadow active:scale-95 duration-200">
                    <span class="material-icons-round text-3xl text-blue-500 mb-2">language</span>
                    <span class="text-xs sm:text-sm font-semibold text-text-light dark:text-text-dark">Website
                        Down</span>
                </div>
                {{-- Item 6 --}}
                <div
                    class="flex flex-col items-center p-4 bg-surface-light dark:bg-surface-dark border border-border-light dark:border-border-dark rounded-xl text-center hover:shadow-md transition-shadow active:scale-95 duration-200">
                    <span class="material-icons-round text-3xl text-blue-500 mb-2">dns</span>
                    <span class="text-xs sm:text-sm font-semibold text-text-light dark:text-text-dark">Sistem
                        Informasi</span>
                </div>
            </div>
        </section>

        {{-- CTA Bottom --}}
        <div class="mt-12 sm:mt-16 text-center">
            <p class="text-sm sm:text-base text-muted-light dark:text-muted-dark mb-4">Sudah menyiapkan persyaratan?
            </p>
            <a href="{{ auth()->check() ? route('tickets.create') : route('guest.tickets.create') }}"
                class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-8 py-3 bg-secondary hover:bg-blue-600 text-white font-bold rounded-lg shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-1 active:scale-95">
                <span class="material-icons-round">confirmation_number</span>
                Buat Tiket Sekarang
            </a>
        </div>

    </div>
</x-layouts.guest>
