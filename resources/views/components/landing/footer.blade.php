<footer
    class="bg-surface-light dark:bg-surface-dark border-t border-border-light dark:border-border-dark pt-16 pb-8 transition-colors duration-300 mt-auto w-full overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Grid diubah menjadi 3 kolom --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12 mb-12">

            {{-- Brand --}}
            <div class="col-span-1">
                <a href="{{ url('/') }}"
                    class="flex items-center gap-2 mb-4 hover:opacity-80 transition-opacity w-fit">

                    <img src="{{ asset('img/logo-unila.png') }}" alt="Logo Unila" class="w-8 h-8 object-contain">

                    <span class="text-lg font-bold text-text-light dark:text-text-dark">
                        UPA TIK Unila
                    </span>
                </a>
                <p class="text-sm text-muted-light dark:text-muted-dark leading-relaxed">
                    Unit Pelaksana Akademik Teknologi Informasi dan Komunikasi Universitas Lampung.
                </p>
            </div>

            {{-- Links (Tautan Cepat) --}}
            <div>
                <h5 class="text-sm font-bold text-text-light dark:text-text-dark uppercase tracking-wider mb-4">
                    Tautan Cepat
                </h5>
                <ul class="space-y-3">
                    <li>
                        <a href="https://library.unila.ac.id/" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            UPA Perpustakaan
                        </a>
                    </li>
                    <li>
                        <a href="https://uptbahasa.unila.ac.id/" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            UPA Bahasa
                        </a>
                    </li>
                    <li>
                        <a href="https://uptltsit.unila.ac.id/" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            UPA Laboratorium Terpadu dan Sentra Inovasi Teknologi
                        </a>
                    </li>
                    <li>
                        <a href="https://cced.unila.ac.id/" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            UPA Pengembangan Karir dan Kewirausahaan
                        </a>
                    </li>
                    <li>
                        <a href="https://upa-bk.unila.ac.id/" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            UPA Bimbingan dan Konseling
                        </a>
                    </li>
                    <li>
                        <a href="https://upa-luk.unila.ac.id/" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            UPA Layanan Uji Kompetensi
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Contact (Hubungi Kami) --}}
            <div>
                <h5 class="text-sm font-bold text-text-light dark:text-text-dark uppercase tracking-wider mb-4">
                    Hubungi Kami
                </h5>
                <ul class="space-y-3">
                    {{-- Alamat --}}
                    <li class="flex items-start gap-3">
                        <span class="material-icons-round text-muted-dark icon-sm mt-0.5 shrink-0">location_on</span>
                        <span class="text-sm text-muted-light dark:text-muted-dark wrap-break-word min-w-0">
                            Gedung UPA TIK Jl. Prof. Sumantri Brojonegoro No. 1 Gedong Meneng, Kec. Rajabasa Bandar
                            Lampung, Lampung 35145
                        </span>
                    </li>

                    {{-- Email --}}
                    <li class="flex items-center gap-3">
                        <span class="material-icons-round text-muted-dark icon-sm shrink-0">mail</span>
                        <span class="text-sm text-muted-light dark:text-muted-dark break-all min-w-0">
                            tik@kpa.unila.ac.id
                        </span>
                    </li>
                </ul>
            </div>
        </div>

        <div
            class="border-t border-border-light dark:border-border-dark pt-8 flex flex-col md:flex-row justify-between items-center gap-4">

            <p class="text-xs text-muted-dark text-center md:text-left w-full md:w-auto">
                &copy; {{ date('Y') }} UPA TIK Universitas Lampung. All rights reserved.
            </p>

            <p class="text-xs text-muted-dark text-center md:text-right w-full md:w-auto">
                Made with <span class="text-red-500">❤️</span> by
                <a href="https://github.com/kiraware" target="_blank" class="hover:text-brand transition-colors">
                    kiraware
                </a>
            </p>

        </div>
    </div>
</footer>
