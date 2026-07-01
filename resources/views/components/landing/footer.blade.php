<footer
    class="bg-surface-light dark:bg-surface-dark border-t border-border-light dark:border-border-dark pt-16 pb-8 transition-colors duration-300 mt-auto w-full overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12 mb-12">
            <div class="col-span-1">
                <a href="{{ url('/') }}"
                    class="flex items-center gap-2 mb-4 hover:opacity-80 transition-opacity w-fit">
                    <img src="{{ asset('img/logo-unila.png') }}" alt="Logo Unila" class="w-8 h-8 object-contain">
                    <span class="text-lg font-bold text-text-light dark:text-text-dark">
                        UPA TIK Unila
                    </span>
                </a>
                <p class="text-sm text-muted-light dark:text-muted-dark leading-relaxed">
                    Unit Penunjang Akademik Teknologi Informasi dan Komunikasi Universitas Lampung.
                </p>
                <div class="flex items-center gap-4 mt-6">
                    <a href="https://www.youtube.com/@UPATIKUniversitasLampung" target="_blank"
                        class="text-muted-light dark:text-muted-dark hover:text-brand transition-colors"
                        title="YouTube">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="w-5 h-5">
                            <path
                                d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17" />
                            <path d="m10 15 5-3-5-3z" />
                        </svg>
                    </a>
                    <a href="https://www.instagram.com/tikunila/" target="_blank"
                        class="text-muted-light dark:text-muted-dark hover:text-brand transition-colors"
                        title="Instagram">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                            stroke-linejoin="round" class="w-5 h-5">
                            <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                            <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                        </svg>
                    </a>
                </div>
            </div>
            <div>
                <h5 class="text-sm font-bold text-text-light dark:text-text-dark uppercase tracking-wider mb-4">
                    Tautan Cepat
                </h5>
                <ul class="space-y-3">
                    <li>
                        <a href="https://www.unila.ac.id/" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            Unila
                        </a>
                    </li>
                    <li>
                        <a href="https://tik.unila.ac.id/" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            UPA TIK
                        </a>
                    </li>
                    <li>
                        <a href="https://siakadu.unila.ac.id/" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            Siakadu
                        </a>
                    </li>
                    <li>
                        <a href="https://my.unila.ac.id/" target="_blank"
                            class="text-sm text-muted-light dark:text-muted-dark hover:text-brand transition-colors">
                            My Unila
                        </a>
                    </li>
                </ul>
            </div>
            <div>
                <h5 class="text-sm font-bold text-text-light dark:text-text-dark uppercase tracking-wider mb-4">
                    Hubungi Kami
                </h5>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3">
                        <span class="material-icons-round text-muted-dark icon-sm mt-0.5 shrink-0">location_on</span>
                        <span class="text-sm text-muted-light dark:text-muted-dark wrap-break-word min-w-0">
                            Gedung UPA TIK Jl. Prof. Sumantri Brojonegoro No. 1 Gedong Meneng, Kec. Rajabasa Bandar
                            Lampung, Lampung 35145
                        </span>
                    </li>
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
                &copy; {{ date('Y') }}
                <a href="https://tik.unila.ac.id/" target="_blank" class="hover:text-brand transition-colors">
                    UPA TIK Universitas Lampung
                </a>. All rights reserved.
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
