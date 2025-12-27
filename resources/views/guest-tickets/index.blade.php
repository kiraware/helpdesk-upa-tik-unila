<x-layouts.guest title="Lacak Tiket">
    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-100 dark:bg-slate-900 p-4">
        <div class="w-full max-w-md bg-white dark:bg-slate-800 rounded-2xl shadow-xl p-8 space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Lacak Tiket Anda</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2 text-sm">Masukkan Kode Tiket yang Anda dapatkan saat
                    pelaporan.</p>
            </div>

            <form action="{{ route('guest.tracking.index') }}" method="GET" class="space-y-4">
                {{-- Logic redirect via Javascript atau Controller search handler. 
                     Disini saya gunakan Javascript simple untuk redirect ke URL tracking --}}
                <div x-data="{ code: '' }">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kode Tiket</label>
                    <input type="text" x-model="code" placeholder="Contoh: TIK-20231227-1234"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-slate-700 focus:ring-2 focus:ring-blue-500 uppercase"
                        @keydown.enter.prevent="if(code) window.location.href = '/tracking/' + code">

                    <button type="button" @click="if(code) window.location.href = '/tracking/' + code"
                        class="mt-4 w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition shadow-lg">
                        Cek Status
                    </button>
                </div>
            </form>

            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="text-sm text-blue-600 hover:underline">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</x-layouts.guest>
