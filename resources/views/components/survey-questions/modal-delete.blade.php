<div id="deleteQuestionModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-white/30 dark:bg-slate-900/30 backdrop-blur-sm">
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div class="p-6 text-center">
            <div
                class="w-16 h-16 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center mx-auto mb-4">
                <span class="material-icons-round text-3xl text-red-600 dark:text-red-400">warning</span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Hapus Pertanyaan Kuesioner</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                Apakah Anda yakin ingin menghapus pertanyaan <span id="deleteQuestionName"
                    class="font-bold text-gray-800 dark:text-gray-200"></span>?
                <br>Tindakan ini tidak dapat dibatalkan.
            </p>

            <form id="deleteQuestionForm" method="POST" class="flex justify-center space-x-3">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteQuestionModal()"
                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
