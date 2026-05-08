<div id="editUserModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-white/30 dark:bg-slate-900/40 backdrop-blur-sm transition-opacity">
    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]">

        {{-- Header Modal --}}
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

        {{-- Form --}}
        <form id="editUserForm" method="POST" class="flex-1 overflow-y-auto custom-scrollbar">
            @csrf
            @method('PUT')
            <div class="p-6 space-y-5">

                {{-- Baris 1: Nama & SSO --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Nama
                            Lengkap</label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-icons-round text-[18px]">badge</span>
                            </div>
                            <input type="text" name="name" id="edit_name" required
                                class="block w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Username
                            SSO</label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-icons-round text-[18px]">account_circle</span>
                            </div>
                            <input type="text" name="username_sso" id="edit_username_sso" required
                                class="block w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Baris 2: Email & Phone --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Email</label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-icons-round text-[18px]">email</span>
                            </div>
                            <input type="email" name="email" id="edit_email" required
                                class="block w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">No.
                            Telepon</label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-icons-round text-[18px]">phone</span>
                            </div>
                            <input type="text" name="phone" id="edit_phone"
                                class="block w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm">
                        </div>
                    </div>
                </div>

                {{-- Baris 3: Identity & Penanggung Jawab --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">NIP /
                            NIK</label>
                        <div class="relative">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-icons-round text-[18px]">pin</span>
                            </div>
                            <input type="text" name="identity_number" id="edit_identity_number"
                                class="block w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white sm:text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors shadow-sm">
                        </div>
                    </div>

                    {{-- Dropdown Penanggung Jawab Alpine JS --}}
                    <div x-data="{ open: false, selectedId: '', selectedName: '' }"
                        @set-edit-division.window="selectedId = $event.detail.id; selectedName = $event.detail.name"
                        class="relative">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Penanggung
                            Jawab</label>
                        <input type="hidden" name="division_id" :value="selectedId">
                        <button type="button" @click="open = !open"
                            class="relative flex items-center justify-between w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-left text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors shadow-sm">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-icons-round text-[18px]">domain</span>
                            </div>
                            <span x-text="selectedName || 'Pilih Penanggung Jawab...'"
                                :class="{
                                    'text-gray-900 dark:text-white': selectedName,
                                    'text-gray-500 dark:text-gray-400': !
                                        selectedName
                                }"></span>
                            <span
                                class="material-icons-round text-gray-400 text-[18px] transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition x-cloak
                            class="absolute z-50 top-full mt-1.5 w-full rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-800 max-h-48 overflow-y-auto py-1">
                            <button type="button" @click="selectedId = ''; selectedName = ''; open = false"
                                class="w-full px-4 py-2 text-left text-sm text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-slate-700 italic transition-colors">
                                -- Tidak ada penanggung jawab --
                            </button>
                            @foreach ($divisions as $div)
                                <button type="button"
                                    @click="selectedId = '{{ $div->id }}'; selectedName = '{{ $div->name }}'; open = false"
                                    class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    {{ $div->name }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Baris 4: Role --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    {{-- Dropdown Role Alpine JS --}}
                    <div x-data="{ open: false, selectedId: '', selectedName: '' }"
                        @set-edit-role.window="selectedId = $event.detail.id; selectedName = $event.detail.name"
                        class="relative">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Role
                            Akses</label>
                        <input type="hidden" name="role" :value="selectedId">
                        <button type="button" @click="open = !open"
                            class="relative flex items-center justify-between w-full pl-10 pr-3 py-2.5 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-left text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors shadow-sm">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <span class="material-icons-round text-[18px]">admin_panel_settings</span>
                            </div>
                            <span x-text="selectedName || 'Pilih Role...'"
                                :class="{
                                    'text-gray-900 dark:text-white': selectedName,
                                    'text-gray-500 dark:text-gray-400': !
                                        selectedName
                                }"></span>
                            <span
                                class="material-icons-round text-gray-400 text-[18px] transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''">expand_more</span>
                        </button>
                        <div x-show="open" @click.outside="open = false" x-transition x-cloak
                            class="absolute z-50 top-full mt-1.5 w-full rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-800 py-1">
                            <button type="button" @click="selectedId = 'admin'; selectedName = 'ADMIN'; open = false"
                                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                ADMIN
                            </button>
                            <button type="button"
                                @click="selectedId = 'superuser'; selectedName = 'SUPERUSER'; open = false"
                                class="w-full px-4 py-2 text-left text-sm text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-700 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                SUPERUSER
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer Modal --}}
            <div
                class="px-6 py-4 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-3 shrink-0">
                <button type="button" onclick="closeEditUserModal()"
                    class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-200 transition-all shadow-sm">
                    Batal
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition-all shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
