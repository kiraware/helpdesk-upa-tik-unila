<header x-data="{ scrolled: false, mobileMenuOpen: false }" @scroll.window="scrolled = (window.pageYOffset > 20)"
    :class="{ 'bg-surface-light/90 dark:bg-surface-dark/90 backdrop-blur-md shadow-sm': scrolled, 'bg-transparent': !scrolled }"
    class="sticky top-0 z-50 border-b border-transparent transition-all duration-300"
    :class="{ 'border-border-light! dark:border-border-dark!': scrolled }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            {{-- Logo (Tetap berfungsi sebagai link ke Beranda) --}}
            <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
                <div class="text-brand">
                    <span class="material-icons-round icon-lg">support_agent</span>
                </div>
                <div class="flex flex-col">
                    <h1 class="text-lg font-bold leading-tight tracking-tight text-text-light dark:text-surface-light">
                        Helpdesk UPA TIK</h1>
                    <span class="text-xs font-medium text-muted-light dark:text-muted-dark">Universitas Lampung</span>
                </div>
            </a>

            {{-- Desktop Navigation --}}
            <nav class="hidden md:flex items-center gap-6">
                {{-- Tombol Beranda Dihapus --}}
                {{-- Tombol Layanan Dihapus --}}

                <a href="#faq"
                    class="text-sm font-medium text-text-light dark:text-text-dark hover:text-brand transition-colors">FAQ</a>

                <div class="h-4 w-px bg-border-light dark:bg-border-dark mx-2"></div>

                <div class="flex items-center gap-3">
                    <a href="{{-- route('login') --}}"
                        class="text-sm font-semibold text-text-light dark:text-text-dark border border-border-light dark:border-border-dark hover:border-brand hover:text-brand px-4 py-2 rounded-lg transition-all">
                        Login
                    </a>
                    <a href="{{ route('guest.tickets.create') }}"
                        class="text-sm font-semibold text-white bg-brand hover:bg-brand-hover px-4 py-2 rounded-lg transition-colors shadow-sm shadow-blue-500/30">
                        Buat Tiket
                    </a>
                </div>
            </nav>

            {{-- Mobile Menu Button --}}
            <button @click="mobileMenuOpen = !mobileMenuOpen"
                class="md:hidden p-2 text-muted-light dark:text-muted-dark">
                <span class="material-icons-round icon-md">menu</span>
            </button>
        </div>
    </div>

    {{-- Mobile Menu --}}
    <div x-show="mobileMenuOpen" x-transition
        class="md:hidden bg-surface-light dark:bg-surface-dark border-b border-border-light dark:border-border-dark"
        style="display: none;">
        <div class="px-4 py-4 space-y-3">
            {{-- Tombol Beranda Mobile Dihapus --}}
            {{-- Tombol Layanan Mobile Dihapus --}}

            <a href="#faq" class="block text-sm font-medium text-text-light dark:text-text-dark">FAQ</a>
            <div class="border-t border-border-light dark:border-border-dark pt-3 flex flex-col gap-3">
                <a href="{{-- route('login') --}}"
                    class="block text-center text-sm font-semibold border border-border-light dark:border-border-dark py-2 rounded-lg dark:text-text-dark">Login</a>
                <a href="{{ route('guest.tickets.create') }}"
                    class="block text-center text-sm font-semibold bg-brand text-white py-2 rounded-lg">Buat Tiket</a>
            </div>
        </div>
    </div>
</header>
