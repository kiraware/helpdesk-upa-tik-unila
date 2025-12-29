<footer
    class="bg-surface-light dark:bg-surface-dark border-t border-border-light dark:border-border-dark pt-16 pb-8 transition-colors duration-300 mt-auto w-full overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Ubah gap-12 menjadi gap-8 di mobile agar lebih aman --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 md:gap-12 mb-12">

            {{-- Brand --}}
            <div class="col-span-1 md:col-span-1">
                <div class="flex items-center gap-2 mb-4">
                    <span class="material-icons-round text-brand icon-lg">school</span>
                    <span class="text-lg font-bold text-text-light dark:text-text-dark">UPA TIK Unila</span>
                </div>
                <p class="text-sm text-muted-light dark:text-muted-dark leading-relaxed">
                    Unit Pelaksana Akademik Teknologi Informasi dan Komunikasi Universitas Lampung.
                </p>
            </div>

            {{-- Links --}}
            <div>
                <h5 class="text-sm font-bold text-text-light dark:text-text-dark uppercase tracking-wider mb-4">
                    Tautan Cepat
                </h5>
                <ul class="space-y-3">
                    <li>
                        <a href="https://unila.ac.id" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            Website Unila
                        </a>
                    </li>
                    <li>
                        <a href="https://siakad.unila.ac.id" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            SIAKAD
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Help --}}
            <div>
                <h5 class="text-sm font-bold text-text-light dark:text-text-dark uppercase tracking-wider mb-4">
                    Bantuan
                </h5>
                <ul class="space-y-3">
                    <li>
                        <a href="{{ route('guest.tickets.create') }}"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            Cara Buat Tiket
                        </a>
                    </li>
                    <li>
                        <a href="#faq"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            FAQ
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h5 class="text-sm font-bold text-text-light dark:text-text-dark uppercase tracking-wider mb-4">
                    Hubungi Kami
                </h5>
                <ul class="space-y-3">
                    {{-- Alamat --}}
                    <li class="flex items-start gap-3">
                        <span class="material-icons-round text-muted-dark icon-sm mt-0.5 shrink-0">location_on</span>
                        {{-- Tambahkan break-words dan min-w-0 --}}
                        <span class="text-sm text-muted-light dark:text-muted-dark break-words min-w-0">
                            Gedung UPA TIK, Jl. Prof. Dr. Sumantri Brojonegoro No. 1
                        </span>
                    </li>

                    {{-- Telepon --}}
                    <li class="flex items-center gap-3">
                        <span class="material-icons-round text-muted-dark icon-sm shrink-0">call</span>
                        <span class="text-sm text-muted-light dark:text-muted-dark">
                            (0721) 701609
                        </span>
                    </li>

                    {{-- Email --}}
                    <li class="flex items-center gap-3">
                        <span class="material-icons-round text-muted-dark icon-sm shrink-0">mail</span>
                        {{-- Tambahkan break-all atau break-words untuk email panjang --}}
                        <span class="text-sm text-muted-light dark:text-muted-dark break-all min-w-0">
                            helpdesk@kpa.unila.ac.id
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <div
            class="border-t border-border-light dark:border-border-dark pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs text-muted-dark text-center md:text-left w-full">
                © {{ date('Y') }} UPA TIK Universitas Lampung. All rights reserved.
            </p>
        </div>
    </div>
</footer>
