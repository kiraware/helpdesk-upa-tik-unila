<x-layouts.dashboard title="Pengaturan Sistem">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Pengaturan Surat Tugas
            </h1>
            <p class="text-sm text-muted-light dark:text-muted-dark">
                Kelola data pejabat penandatangan (Kepala UPA TIK) untuk keperluan cetak surat.
            </p>
        </div>
    </div>

    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden max-w-3xl">
        <div class="px-6 py-4 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50">
            <h3 class="font-bold text-text-light dark:text-text-dark flex items-center gap-2">
                <span class="material-icons-round text-blue-500">edit_note</span>
                Data Kepala UPA TIK
            </h3>
        </div>

        <div class="p-6">
            <form action="{{ route('configurations.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        <label for="upa_head_name"
                            class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                            Nama Lengkap (beserta gelar) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted-light">
                                <span class="material-icons-round text-lg">person</span>
                            </span>
                            <input type="text" name="upa_head_name" id="upa_head_name"
                                value="{{ old('upa_head_name', $config->upa_head_name) }}"
                                class="pl-10 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 text-text-light dark:text-text-dark focus:border-secondary focus:ring-secondary sm:text-sm py-2.5"
                                placeholder="Contoh: Muhammad Komaruddin, S.T., M.T." required maxlength="50">
                        </div>
                        @error('upa_head_name')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="upa_head_nip"
                            class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                            NIP <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted-light">
                                <span class="material-icons-round text-lg">badge</span>
                            </span>
                            <input type="text" name="upa_head_nip" id="upa_head_nip"
                                value="{{ old('upa_head_nip', $config->upa_head_nip) }}"
                                class="pl-10 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 text-text-light dark:text-text-dark focus:border-secondary focus:ring-secondary sm:text-sm py-2.5"
                                placeholder="Contoh: 19681207 199703 1 006" required maxlength="32">
                        </div>
                        @error('upa_head_nip')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="upa_head_position"
                            class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">
                            Jabatan (Tertulis di Surat) <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-muted-light">
                                <span class="material-icons-round text-lg">work</span>
                            </span>
                            <input type="text" name="upa_head_position" id="upa_head_position"
                                value="{{ old('upa_head_position', $config->upa_head_position) }}"
                                class="pl-10 block w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-slate-800 text-text-light dark:text-text-dark focus:border-secondary focus:ring-secondary sm:text-sm py-2.5"
                                placeholder="Contoh: Kepala UPA TIK" required maxlength="50">
                        </div>
                        @error('upa_head_position')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 pt-6 border-t border-border-light dark:border-border-dark flex justify-end">
                    <button type="submit"
                        class="flex items-center justify-center px-4 py-2 bg-secondary hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

</x-layouts.dashboard>
