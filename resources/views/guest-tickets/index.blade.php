<x-layouts.guest title="Lacak Tiket">
    {{-- Container Utama --}}
    <div
        class="min-h-screen flex items-center justify-center p-3 sm:p-4 bg-[#f3f4f6] dark:bg-background-dark transition-colors duration-300 font-sans">

        {{-- Card --}}
        <div
            class="w-full max-w-md bg-white dark:bg-surface-dark rounded-2xl shadow-soft dark:shadow-glow p-6 md:p-10 transform transition-all hover:scale-[1.01] duration-300 border border-gray-100 dark:border-gray-700">

            {{-- Header Text --}}
            <div class="text-center mb-6 md:mb-8">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-2 md:mb-3 tracking-tight">
                    Lacak Tiket Anda
                </h1>
                <p class="text-gray-500 dark:text-gray-400 text-xs md:text-sm leading-relaxed max-w-[95%] mx-auto">
                    Masukkan Kode Tiket yang Anda dapatkan saat pelaporan.
                </p>
            </div>

            {{-- Form Area dengan Alpine.js --}}
            <div x-data="{ code: '' }" class="space-y-4 md:space-y-6">

                {{-- Input Field --}}
                <div>
                    <label for="ticket-code"
                        class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-1.5 md:mb-2">
                        Kode Tiket
                    </label>
                    <div class="relative group">
                        <input type="text" id="ticket-code" x-model="code"
                            @keydown.enter.prevent="if(code) window.location.href = '/tracking/' + code"
                            placeholder="Contoh: TIK-20231227-1234"
                            class="w-full px-4 py-3 md:py-3.5 rounded-xl border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-border-dark text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-gray-500 focus:ring-2 focus:ring-[#1d4ed8] focus:border-transparent outline-none transition-all duration-200 uppercase placeholder:normal-case text-sm md:text-base" />

                        {{-- Icon Search --}}
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-focus-within:text-[#1d4ed8] transition-colors"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                {{-- Action Button --}}
                <button type="button" @click="if(code) window.location.href = '/tracking/' + code"
                    class="w-full bg-[#1d4ed8] hover:bg-[#1e40af] text-white font-semibold py-3 md:py-3.5 px-4 rounded-xl shadow-lg shadow-blue-500/30 transition-all duration-200 transform active:scale-[0.98] flex justify-center items-center gap-2 group text-sm md:text-base">
                    <span>Cek Status</span>
                    <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </button>
            </div>

        </div>
    </div>
</x-layouts.guest>
