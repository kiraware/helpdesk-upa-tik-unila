<x-layouts.dashboard title="Manajemen User SSO">

    <div x-data="{
        isResetModalOpen: false,
        selectedUsername: '',
        selectedName: '',
        openResetModal(username, name) {
            this.selectedUsername = username;
            this.selectedName = name;
            this.isResetModalOpen = true;
        }
    }">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                    Manajemen User SSO
                </h1>
                <p class="text-sm text-muted-light dark:text-muted-dark">
                    Daftar pengguna dari server SSO Unila. Anda dapat mereset sandi di sini.
                </p>
            </div>
        </div>

        {{-- Filter / Search --}}
        <form method="GET" action="{{ route('sso-users.index') }}" class="mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-8 lg:col-span-9 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-icons-round text-gray-400">search</span>
                    </div>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Cari pengguna..."
                        class="h-10 block w-full pl-10 pr-10 py-2 border border-border-light dark:border-border-dark rounded-lg leading-5 bg-surface-light dark:bg-slate-800 text-text-light dark:text-text-dark placeholder-muted-light dark:placeholder-muted-dark focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary sm:text-sm shadow-sm">

                    {{-- Tombol Reset Search --}}
                    @if ($search)
                        <a href="{{ route('sso-users.index') }}"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-red-500 transition-colors">
                            <span class="material-icons-round text-lg">close</span>
                        </a>
                    @endif
                </div>
                <div class="sm:col-span-4 lg:col-span-3 flex items-center">
                    <button type="submit"
                        class="w-full h-10 px-4 bg-secondary text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors shadow-sm">
                        Cari Pengguna
                    </button>
                </div>
            </div>
        </form>

        {{-- Table Card --}}
        <div
            class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr
                            class="bg-gray-50 dark:bg-slate-800/50 border-b border-border-light dark:border-border-dark">
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                                Username SSO</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                                Nama & Nomor Identitas</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                                Email</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                                Status</th>
                            <th
                                class="px-6 py-4 text-right text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-light dark:divide-border-dark">
                        @forelse ($users as $user)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors group">
                                <td class="px-6 py-4">
                                    <span
                                        class="text-sm font-medium text-text-light dark:text-text-dark">{{ $user['username'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-text-light dark:text-text-dark">
                                        {{ $user['name'] }}</div>
                                    <div class="text-xs text-muted-light dark:text-muted-dark">{{ $user['numberID'] }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-light dark:text-muted-dark">
                                    {{ $user['email'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="px-2.5 py-1 text-xs font-medium bg-gray-100 dark:bg-slate-800 text-text-light dark:text-text-dark rounded-md border border-border-light dark:border-border-dark">
                                        {{ ucfirst($user['status'] ?? 'Unknown') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div
                                        class="flex items-center justify-end space-x-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button type="button"
                                            @click="openResetModal('{{ $user['username'] }}', '{{ addslashes($user['name']) }}')"
                                            class="p-1.5 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-md transition-colors"
                                            title="Reset Sandi SSO">
                                            <span class="material-icons-round text-lg">key</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5"
                                    class="px-6 py-10 text-center text-sm text-muted-light dark:text-muted-dark">
                                    Tidak ada data user SSO yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination Vendor Laravel --}}
            <div class="px-6 py-4 bg-gray-50 dark:bg-slate-800/50 border-t border-border-light dark:border-border-dark">
                {{ $users->links() }}
            </div>
        </div>

        {{-- MODAL RESET PASSWORD --}}
        <div x-show="isResetModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
            <div x-show="isResetModalOpen" x-transition.opacity
                class="fixed inset-0 bg-black/30 dark:bg-slate-900/50 backdrop-blur-sm"
                @click="isResetModalOpen = false"></div>

            <div x-show="isResetModalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative bg-surface-light dark:bg-surface-dark rounded-xl shadow-2xl border border-border-light dark:border-border-dark w-full max-w-md mx-4 overflow-hidden">

                <div
                    class="px-6 py-4 border-b border-border-light dark:border-border-dark flex items-center gap-3 bg-amber-50 dark:bg-amber-900/20">
                    <div
                        class="flex items-center justify-center h-10 w-10 rounded-full bg-amber-100 dark:bg-amber-900/40">
                        <span class="material-icons-round text-amber-600 dark:text-amber-400">lock_reset</span>
                    </div>
                    <h3 class="text-lg font-bold text-text-light dark:text-text-dark">Reset Password SSO</h3>
                </div>

                <div class="p-6">
                    <p class="text-sm text-muted-light dark:text-muted-dark leading-relaxed mb-6">
                        Masukkan password baru untuk pengguna <span
                            class="font-bold text-text-light dark:text-text-dark" x-text="selectedName"></span> (<span
                            x-text="selectedUsername"></span>).
                    </p>

                    <form action="{{ route('sso-users.reset-password') }}" method="POST">
                        @csrf
                        <input type="hidden" name="username" :value="selectedUsername">

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-2">Password
                                Baru</label>
                            <input type="text" name="new_password" required minlength="6"
                                placeholder="Minimal 6 karakter"
                                class="w-full px-4 py-2 border border-border-light dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary bg-white dark:bg-slate-700 text-text-light dark:text-text-dark sm:text-sm">
                            @error('new_password')
                                <span class="text-xs text-red-500 mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        <div
                            class="flex justify-end gap-3 pt-2 border-t border-border-light dark:border-border-dark mt-4">
                            <button type="button" @click="isResetModalOpen = false"
                                class="px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-secondary hover:bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                                Simpan Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
