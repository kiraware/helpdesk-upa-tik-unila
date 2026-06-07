<x-layouts.guest title="Informasi & FAQ - UPA TIK Unila">
    <section class="relative pt-16 pb-20 lg:pt-28 lg:pb-32 overflow-hidden bg-background-light dark:bg-background-dark">
        <!-- Abstract Background Shapes matching Homepage -->
        <div class="absolute inset-0 z-0 pointer-events-none">
            <div
                class="absolute top-0 right-0 w-1/2 h-full bg-linear-to-l from-blue-50 to-transparent dark:from-blue-900/10 opacity-60">
            </div>
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-brand/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 left-24 w-80 h-80 bg-blue-400/10 rounded-full blur-3xl animate-pulse"
                style="animation-delay: 2s;"></div>
            <div
                class="absolute bottom-0 left-1/2 -translate-x-1/2 w-full h-1/2 bg-gradient-to-t from-surface-light dark:from-surface-dark to-transparent">
            </div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="max-w-3xl mx-auto space-y-8">
                <!-- Badge matching Homepage style -->
                <div
                    class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 dark:bg-blue-900/30 text-brand text-xs font-bold uppercase tracking-wide border border-blue-100 dark:border-blue-800 mb-2">
                    <span class="material-icons-round text-sm">lightbulb</span>
                    Pusat Bantuan & Informasi
                </div>

                <!-- Heading matching Homepage style -->
                <h1
                    class="text-4xl lg:text-6xl font-bold tracking-tight text-text-light dark:text-text-dark leading-[1.15]">
                    Panduan Layanan <br class="hidden sm:block" />
                    <span class="text-brand">UPA TIK Universitas Lampung</span>
                </h1>

                <!-- Description matching Homepage style -->
                <p class="text-lg lg:text-xl text-muted-light dark:text-muted-dark max-w-2xl mx-auto leading-relaxed">
                    Temukan informasi lengkap dan panduan teknis mengenai Layanan Email Institusi, Akun SSO, dan
                    berbagai prosedur layanan Helpdesk kami.
                </p>
            </div>
        </div>
    </section>

    <div class="relative z-20 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 -mt-16 pb-24">
        <div
            class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl p-8 sm:p-12 md:p-16 rounded-3xl shadow-2xl shadow-blue-900/5 border border-white/50 dark:border-slate-700/50 transform transition-all hover:shadow-blue-900/10">
            <div
                class="prose prose-lg sm:prose-xl dark:prose-invert max-w-none prose-blue prose-headings:font-bold prose-a:text-blue-600 hover:prose-a:text-blue-500 prose-img:rounded-2xl prose-img:shadow-lg trix-content">
                {!! $faq->description ??
                    '<div class="flex flex-col items-center justify-center text-slate-400 dark:text-slate-500 py-20"><span class="material-icons-round text-6xl mb-4 opacity-50">article</span><p class="text-lg">Belum ada informasi FAQ yang dipublikasikan.</p></div>' !!}
            </div>
        </div>
    </div>
</x-layouts.guest>
