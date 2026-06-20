<div id="addServiceModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-white/30 dark:bg-slate-900/30 backdrop-blur-sm">
    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden max-h-[90vh] flex flex-col">
        <div
            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800 shrink-0">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Tambah Layanan Baru</h3>
            <button type="button" onclick="closeAddServiceModal()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        <form action="{{ route('services.store') }}" method="POST">
            @csrf
            <div class="p-6 overflow-y-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    {{-- Kolom Kiri: Konfigurasi Layanan --}}
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama
                                Layanan</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><span
                                        class="material-icons-round text-lg">layers</span></span>
                                <input type="text" name="name" value="{{ old('name') }}"
                                    placeholder="Contoh: Pemeliharaan Server" required maxlength="50"
                                    class="pl-10 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-secondary focus:ring-secondary sm:text-sm py-2.5">
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status Aktif</label>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Layanan beroperasi secara
                                    keseluruhan.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                                    {{ old('is_active', true) ? 'checked' : '' }}>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                                </div>
                            </label>
                        </div>

                        <hr class="border-gray-200 dark:border-gray-700">

                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Akses Pengguna Tamu
                                    (Guest)</label>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Muncul di form pembuatan tiket
                                    publik.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="show_to_guest" value="0">
                                <input type="checkbox" name="show_to_guest" value="1" class="sr-only peer" checked>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-secondary">
                                </div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex flex-col">
                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Akses Pengguna Login
                                    (User)</label>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Muncul di form pembuatan tiket
                                    internal.</span>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="show_to_user" value="0">
                                <input type="checkbox" name="show_to_user" value="1" class="sr-only peer" checked>
                                <div
                                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-secondary">
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Catatan & Template --}}
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Catatan
                                Layanan
                                (Opsional)</label>
                            <textarea name="notes" rows="4" placeholder="Contoh: Lampirkan surat permohonan yang ditandatangani pimpinan..."
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-secondary focus:ring-secondary sm:text-sm py-2.5 px-3">{{ old('notes') }}</textarea>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">Catatan ini akan
                                ditampilkan
                                pada form pembuatan tiket.</span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Template
                                Jawaban
                                (Opsional)</label>
                            <textarea name="reply_template" rows="4"
                                placeholder="Contoh: Terima kasih atas laporannya. Kami akan segera menindaklanjuti..."
                                class="block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-secondary focus:ring-secondary sm:text-sm py-2.5 px-3">{{ old('reply_template') }}</textarea>
                            <span class="text-xs text-gray-500 dark:text-gray-400 mt-1 block">Template ini akan otomatis
                                mengisi form balasan komentar saat Anda menangani tiket dengan layanan ini.</span>
                        </div>
                    </div>

                </div>
            </div>
            <div
                class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3 shrink-0">
                <button type="button" onclick="closeAddServiceModal()"
                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-secondary hover:bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm">Simpan
                    Layanan</button>
            </div>
        </form>
    </div>
</div>
