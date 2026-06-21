<div id="editQuestionModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-white/30 dark:bg-slate-900/30 backdrop-blur-sm">
    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]">
        <div
            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50 dark:bg-gray-800 shrink-0">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Ubah Pertanyaan Kuesioner</h3>
            <button type="button" onclick="closeEditQuestionModal()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        <form id="editQuestionForm" method="POST" class="flex flex-col overflow-hidden">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-6 overflow-y-auto custom-scrollbar">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Aspek
                        Penilaian <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><span
                                class="material-icons-round text-lg">category</span></span>
                        <input type="text" name="aspect_name" id="edit_aspect_name" required maxlength="100"
                            class="pl-10 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-secondary focus:ring-secondary sm:text-sm py-2.5">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pertanyaan
                        Kepuasan <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute top-2.5 left-0 pl-3 flex items-start text-gray-400"><span
                                class="material-icons-round text-lg">sentiment_satisfied</span></span>
                        <textarea name="satisfaction_question" id="edit_satisfaction_question" required maxlength="500" rows="3"
                            class="pl-10 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-secondary focus:ring-secondary sm:text-sm py-2.5 resize-none"></textarea>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Pertanyaan
                        Kepentingan <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute top-2.5 left-0 pl-3 flex items-start text-gray-400"><span
                                class="material-icons-round text-lg">priority_high</span></span>
                        <textarea name="importance_question" id="edit_importance_question" required maxlength="500" rows="3"
                            class="pl-10 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-secondary focus:ring-secondary sm:text-sm py-2.5 resize-none"></textarea>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nomor
                            Urut <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400"><span
                                    class="material-icons-round text-lg">format_list_numbered</span></span>
                            <input type="number" name="sort_order" id="edit_sort_order" min="0" max="999"
                                required
                                class="pl-10 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-secondary focus:ring-secondary sm:text-sm py-2.5">
                        </div>
                    </div>

                    <div class="flex items-center justify-between h-full pt-6">
                        <div class="flex flex-col">
                            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Status Aktif</label>
                            <span class="text-xs text-gray-500 dark:text-gray-400">Pertanyaan akan muncul di
                                form.</span>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500">
                            </div>
                        </label>
                    </div>
                </div>

            </div>
            <div
                class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3 shrink-0">
                <button type="button" onclick="closeEditQuestionModal()"
                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">Batal</button>
                <button type="submit"
                    class="px-4 py-2 bg-secondary hover:bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">Simpan
                    Perubahan</button>
            </div>
        </form>
    </div>
</div>
