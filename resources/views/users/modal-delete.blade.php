<div id="deleteUserModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-white/30 dark:bg-slate-900/30 backdrop-blur-sm">
    <div class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-2xl w-full max-w-md mx-4 overflow-hidden">
        <div
            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center gap-3 bg-red-50 dark:bg-red-900/20">
            <div class="flex items-center justify-center h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/40">
                <span class="material-icons-round text-red-600 dark:text-red-400">warning</span>
            </div>
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Cabut Akses Staff</h3>
        </div>

        <div class="p-6">
            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                Apakah Anda yakin ingin mencabut akses staff untuk <span
                    class="font-semibold text-gray-900 dark:text-white" id="deleteUserName"></span>? <br><br>
                <span class="text-amber-600 dark:text-amber-400 font-medium">Akun ini tidak akan dihapus, melainkan
                    perannya dikembalikan menjadi user biasa.</span>
            </p>
        </div>

        <div
            class="px-6 py-4 bg-gray-50 dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3">
            <button type="button" onclick="closeDeleteUserModal()"
                class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600">Batal</button>
            <form id="deleteUserForm" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium shadow-sm focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Ya, Cabut Akses
                </button>
            </form>
        </div>
    </div>
</div>
