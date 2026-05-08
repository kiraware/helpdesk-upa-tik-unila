<div id="editDivisionModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-white/30 dark:bg-slate-900/30 backdrop-blur-sm">
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div
            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Edit Penanggung Jawab</h3>
            <button type="button" onclick="closeEditDivisionModal()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        <form id="editDivisionForm" method="POST">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nama Penanggung
                        Jawab</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><span
                                class="material-icons-round text-lg">layers</span></span>
                        <input type="text" name="name" id="edit_name" required maxlength="50"
                            class="pl-10 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-secondary focus:ring-secondary sm:text-sm py-2.5">
                    </div>
                </div>
            </div>
            <div
                class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
                <button type="button" onclick="closeEditDivisionModal()"
                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-secondary hover:bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm">Simpan
                    Perubahan</button>
            </div>
        </form>
    </div>
</div>
