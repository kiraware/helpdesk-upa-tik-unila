<div id="editUserModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-white/30 dark:bg-slate-900/40 backdrop-blur-sm transition-opacity">
    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden flex flex-col max-h-[90vh]">

        <div
            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-800/50 shrink-0">
            <div class="flex items-center gap-3">
                <div class="bg-blue-100 dark:bg-blue-900/30 p-2 rounded-lg text-blue-600 dark:text-blue-400">
                    <span class="material-icons-round text-xl">manage_accounts</span>
                </div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Edit Data Staff</h3>
            </div>
            <button type="button" onclick="closeEditUserModal()"
                class="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        <form id="editUserForm" method="POST" class="flex-1 overflow-y-auto custom-scrollbar">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-5">

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Username SSO
                        <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <span class="material-icons-round text-[18px]">account_circle</span>
                        </div>
                        <input type="text" name="username_sso" id="edit_username_sso" required
                            placeholder="Contoh: budi.santoso"
                            class="block w-full pl-10 pr-4 h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:border-blue-500 focus:ring-blue-500 transition-colors shadow-sm sm:text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nomor
                        Telepon</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <span class="material-icons-round text-[18px]">phone</span>
                        </div>
                        <input type="text" name="phone" id="edit_phone" placeholder="Contoh: 081234567890"
                            class="block w-full pl-10 pr-4 h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:border-blue-500 focus:ring-blue-500 transition-colors shadow-sm sm:text-sm">
                    </div>
                </div>

                <div x-data="{ open: false, selectedId: '', selectedName: '' }"
                    @set-edit-role.window="selectedId = $event.detail.id; selectedName = $event.detail.name"
                    class="relative">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Role Akses <span
                            class="text-red-500">*</span></label>
                    <input type="hidden" name="role" :value="selectedId">
                    <button type="button" @click="open = !open"
                        class="relative flex items-center justify-between w-full pl-10 pr-4 h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-left text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <span class="material-icons-round text-[18px]">admin_panel_settings</span>
                        </div>
                        <span x-text="selectedName || 'Pilih Role...'"
                            :class="{ 'text-slate-900 dark:text-white': selectedName, 'text-slate-400': !selectedName }"></span>
                        <span class="material-icons-round text-slate-400 text-[18px] transition-transform duration-200"
                            :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition x-cloak
                        class="absolute z-50 top-full mt-1.5 w-full rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 py-1">
                        <button type="button" @click="selectedId = 'admin'; selectedName = 'ADMIN'; open = false"
                            class="w-full px-4 py-2 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-600 transition-colors">ADMIN</button>
                        <button type="button"
                            @click="selectedId = 'superuser'; selectedName = 'SUPERUSER'; open = false"
                            class="w-full px-4 py-2 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-600 transition-colors">SUPERUSER</button>
                    </div>
                </div>

                <div x-data="{ open: false, selectedId: '', selectedName: '' }"
                    @set-edit-division.window="selectedId = $event.detail.id; selectedName = $event.detail.name"
                    class="relative">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Penanggung
                        Jawab</label>
                    <input type="hidden" name="division_id" :value="selectedId">
                    <button type="button" @click="open = !open"
                        class="relative flex items-center justify-between w-full pl-10 pr-4 h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-left text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <span class="material-icons-round text-[18px]">business</span>
                        </div>
                        <span x-text="selectedName || 'Pilih Penanggung Jawab...'"
                            :class="{ 'text-slate-900 dark:text-white': selectedName, 'text-slate-400': !selectedName }"></span>
                        <span class="material-icons-round text-slate-400 text-[18px] transition-transform duration-200"
                            :class="open ? 'rotate-180' : ''">expand_more</span>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition x-cloak
                        class="absolute z-50 top-full mt-1.5 w-full rounded-xl shadow-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 max-h-48 overflow-y-auto py-1">
                        <button type="button" @click="selectedId = ''; selectedName = ''; open = false"
                            class="w-full px-4 py-2 text-left text-sm text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 italic transition-colors">--
                            Tidak ada penanggung jawab --</button>
                        @foreach ($divisions as $div)
                            <button type="button"
                                @click="selectedId = '{{ $div->id }}'; selectedName = '{{ addslashes($div->name) }}'; open = false"
                                class="w-full px-4 py-2 text-left text-sm text-slate-700 dark:text-slate-300 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-600 transition-colors">{{ $div->name }}</button>
                        @endforeach
                    </div>
                </div>

            </div>

            <div
                class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 shrink-0">
                <button type="button" onclick="closeEditUserModal()"
                    class="px-4 py-2 bg-white dark:bg-slate-700 border border-slate-300 dark:border-slate-600 rounded-lg text-sm font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-50 transition-all shadow-sm">Batal</button>
                <button type="submit"
                    class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-bold shadow-md transition-all hover:-translate-y-0.5">Simpan
                    Perubahan</button>
            </div>
        </form>
    </div>
</div>
