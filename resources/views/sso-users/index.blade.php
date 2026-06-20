<x-layouts.dashboard title="Manajemen User SSO">

    <div x-data="{
        isResetModalOpen: false,
        isCreateModalOpen: false,
        isInactiveModalOpen: false,
        selectedUsername: '',
        selectedName: '',
        openResetModal(username, name) {
            this.selectedUsername = username;
            this.selectedName = name;
            this.isResetModalOpen = true;
        },
        openInactiveModal(username, name) {
            this.selectedUsername = username;
            this.selectedName = name;
            this.isInactiveModalOpen = true;
        }
    }">

        @if (session('success'))
            <x-toast type="success" message="{{ session('success') }}" />
        @endif
        @if (session('error'))
            <x-toast type="error" message="{{ session('error') }}" />
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                    Manajemen User SSO
                </h1>
                <p class="text-sm text-muted-light dark:text-muted-dark">
                    Daftar pengguna dari server SSO Unila.
                </p>
            </div>

            <button type="button" @click="isCreateModalOpen = true"
                class="flex items-center justify-center px-4 py-2 bg-secondary hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <span class="material-icons-round text-sm mr-2">person_add</span>
                Tambah User SSO
            </button>
        </div>

        <form method="GET" action="{{ route('sso-users.index') }}" class="mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">
                <div class="sm:col-span-8 lg:col-span-9 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="material-icons-round text-gray-400">search</span>
                    </div>
                    <input type="text" name="search" value="{{ $search }}"
                        placeholder="Cari pengguna berdasarkan nama, username, atau email..."
                        class="h-10 block w-full pl-10 pr-10 py-2 border border-border-light dark:border-border-dark rounded-lg leading-5 bg-surface-light dark:bg-slate-800 text-text-light dark:text-text-dark placeholder-muted-light dark:placeholder-muted-dark focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary sm:text-sm shadow-sm">

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
                                Nama & NIP/NPM</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                                Email</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                                Fakultas / Unit Kerja</th>
                            <th
                                class="px-6 py-4 text-left text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                                Status & Akun</th>
                            <th
                                class="px-6 py-4 text-right text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                                Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border-light dark:divide-border-dark">
                        @forelse ($users as $user)
                            <tr
                                class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors group {{ isset($user['active']) && !$user['active'] ? 'opacity-60' : '' }}">
                                <td class="px-6 py-4">
                                    <span
                                        class="text-sm font-medium text-text-light dark:text-text-dark">{{ $user['username'] }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-text-light dark:text-text-dark">
                                        {{ $user['name'] }}
                                    </div>
                                    <div class="text-xs text-muted-light dark:text-muted-dark">
                                        {{ $user['numberID'] ?: '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-light dark:text-muted-dark">
                                    {{ $user['email'] ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-muted-light dark:text-muted-dark">
                                    {{ !empty($user['fakultas']) ? $user['fakultas'] : (!empty($user['unit_kerja']) ? $user['unit_kerja'] : '-') }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1 items-start">
                                        <span
                                            class="px-2.5 py-1 text-xs font-medium bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded-md border border-blue-200 dark:border-blue-800">
                                            {{ ucwords(str_replace('_', ' ', $user['status'] ?? 'Unknown')) }}
                                        </span>
                                        @if (isset($user['active']) && !$user['active'])
                                            <span
                                                class="px-2.5 py-1 text-xs font-medium bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-md border border-red-200 dark:border-red-800">
                                                Inactive
                                            </span>
                                        @endif
                                    </div>
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

                                        @if (!isset($user['active']) || $user['active'])
                                            <button type="button"
                                                @click="openInactiveModal('{{ $user['username'] }}', '{{ addslashes($user['name']) }}')"
                                                class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                                                title="Nonaktifkan Akun">
                                                <span class="material-icons-round text-lg">person_off</span>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"
                                    class="px-6 py-10 text-center text-sm text-muted-light dark:text-muted-dark">
                                    Tidak ada data user SSO yang ditemukan.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-slate-800/50 border-t border-border-light dark:border-border-dark">
                {{ $users->links() }}
            </div>
        </div>

        <div x-show="isCreateModalOpen" x-cloak style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center">
            <div x-show="isCreateModalOpen" x-transition.opacity
                class="fixed inset-0 bg-black/40 dark:bg-slate-900/60 backdrop-blur-sm"
                @click="isCreateModalOpen = false"></div>

            <div x-show="isCreateModalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-4"
                class="relative bg-surface-light dark:bg-surface-dark rounded-xl shadow-2xl border border-border-light dark:border-border-dark w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]">

                <div
                    class="px-6 py-4 border-b border-border-light dark:border-border-dark flex items-center justify-between bg-gray-50 dark:bg-slate-800/50">
                    <div class="flex items-center gap-3">
                        <div
                            class="flex items-center justify-center h-10 w-10 rounded-full bg-blue-100 dark:bg-blue-900/40">
                            <span class="material-icons-round text-blue-600 dark:text-blue-400">person_add</span>
                        </div>
                        <h3 class="text-lg font-bold text-text-light dark:text-text-dark">Tambah User SSO</h3>
                    </div>
                    <button type="button" @click="isCreateModalOpen = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <span class="material-icons-round">close</span>
                    </button>
                </div>

                <div class="p-6 overflow-y-auto">
                    <form action="{{ route('sso-users.store') }}" method="POST" id="formCreateUser">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <div>
                                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">NIP /
                                    NPM <span class="text-red-500">*</span></label>
                                <input type="text" name="numberID" required
                                    placeholder="Contoh: 199001012020121001"
                                    class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary bg-white dark:bg-slate-700 text-text-light dark:text-text-dark sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Nama
                                    Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="name" required placeholder="Contoh: Budi Santoso"
                                    class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary bg-white dark:bg-slate-700 text-text-light dark:text-text-dark sm:text-sm">
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Username
                                    <span class="text-red-500">*</span></label>
                                <input type="text" name="username" required placeholder="Contoh: budi.santoso"
                                    class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary bg-white dark:bg-slate-700 text-text-light dark:text-text-dark sm:text-sm">
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Domain
                                    Email <span class="text-red-500">*</span></label>
                                <div x-data="{ open: false, selectedValue: '', selectedLabel: '-- Pilih Domain --' }" class="relative w-full">
                                    <select name="domain_email" x-model="selectedValue" required
                                        class="absolute inset-0 w-full h-full opacity-0 pointer-events-none"
                                        tabindex="-1">
                                        <option value="">-- Pilih Domain --</option>
                                        @foreach (['fk.unila.ac.id', 'eng.unila.ac.id', 'fmipa.unila.ac.id', 'fp.unila.ac.id', 'fisip.unila.ac.id', 'fkip.unila.ac.id', 'fh.unila.ac.id', 'feb.unila.ac.id', 'staff.unila.ac.id', 'students.unila.ac.id', 'kpa.unila.ac.id', 'adm.unila.ac.id', 'alumni.unila.ac.id', 'lk.unila.ac.id', 'or.unila.ac.id', 'akademik.unila.ac.id', 'unila.ac.id'] as $domain)
                                            <option value="{{ $domain }}">{{ $domain }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between px-3 py-2 border border-border-light dark:border-border-dark rounded-lg bg-white dark:bg-slate-700 text-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary">
                                        <span class="truncate" x-text="selectedLabel"
                                            :class="!selectedValue ? 'text-muted-light dark:text-muted-dark' :
                                                'text-text-light dark:text-text-dark'"></span>
                                        <span
                                            class="material-icons-round text-base text-muted-light">expand_more</span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak style="display: none;"
                                        class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-border-light dark:border-border-dark py-1 max-h-48 overflow-y-auto">
                                        <button type="button"
                                            @click="selectedValue = ''; selectedLabel = '-- Pilih Domain --'; open = false"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 text-muted-light dark:text-muted-dark">--
                                            Pilih Domain --</button>
                                        @foreach (['fk.unila.ac.id', 'eng.unila.ac.id', 'fmipa.unila.ac.id', 'fp.unila.ac.id', 'fisip.unila.ac.id', 'fkip.unila.ac.id', 'fh.unila.ac.id', 'feb.unila.ac.id', 'staff.unila.ac.id', 'students.unila.ac.id', 'kpa.unila.ac.id', 'adm.unila.ac.id', 'alumni.unila.ac.id', 'lk.unila.ac.id', 'or.unila.ac.id', 'akademik.unila.ac.id', 'unila.ac.id'] as $domain)
                                            <button type="button"
                                                @click="selectedValue = '{{ $domain }}'; selectedLabel = '{{ $domain }}'; open = false"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 text-text-light dark:text-text-dark"
                                                :class="selectedValue === '{{ $domain }}' ?
                                                    'font-bold text-secondary bg-gray-50 dark:bg-slate-700/50' : ''">{{ $domain }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Status
                                    <span class="text-red-500">*</span></label>
                                <div x-data="{ open: false, selectedValue: '', selectedLabel: '-- Pilih Status --' }" class="relative w-full">
                                    <select name="status" x-model="selectedValue" required
                                        class="absolute inset-0 w-full h-full opacity-0 pointer-events-none"
                                        tabindex="-1">
                                        <option value="">-- Pilih Status --</option>
                                        @foreach (['Super User', 'Mahasiswa', 'Dosen', 'Karyawan', 'Tamu', 'Lainnya'] as $status)
                                            <option value="{{ strtolower(str_replace(' ', '_', $status)) }}">
                                                {{ $status }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between px-3 py-2 border border-border-light dark:border-border-dark rounded-lg bg-white dark:bg-slate-700 text-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary">
                                        <span class="truncate" x-text="selectedLabel"
                                            :class="!selectedValue ? 'text-muted-light dark:text-muted-dark' :
                                                'text-text-light dark:text-text-dark'"></span>
                                        <span
                                            class="material-icons-round text-base text-muted-light">expand_more</span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak style="display: none;"
                                        class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-border-light dark:border-border-dark py-1 max-h-48 overflow-y-auto">
                                        <button type="button"
                                            @click="selectedValue = ''; selectedLabel = '-- Pilih Status --'; open = false"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 text-muted-light dark:text-muted-dark">--
                                            Pilih Status --</button>
                                        @foreach (['Super User', 'Mahasiswa', 'Dosen', 'Karyawan', 'Tamu', 'Lainnya'] as $status)
                                            <button type="button"
                                                @click="selectedValue = '{{ strtolower(str_replace(' ', '_', $status)) }}'; selectedLabel = '{{ $status }}'; open = false"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 text-text-light dark:text-text-dark"
                                                :class="selectedValue === '{{ strtolower(str_replace(' ', '_', $status)) }}' ?
                                                    'font-bold text-secondary bg-gray-50 dark:bg-slate-700/50' : ''">{{ $status }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Fakultas</label>
                                <div x-data="{ open: false, selectedValue: '', selectedLabel: '-- Pilih Fakultas --' }" class="relative w-full">
                                    <select name="fakultas" x-model="selectedValue"
                                        class="absolute inset-0 w-full h-full opacity-0 pointer-events-none"
                                        tabindex="-1">
                                        <option value="">-- Pilih Fakultas --</option>
                                        @foreach (['Ekonomi dan Bisnis', 'Hukum', 'Ilmu Sosial dan Ilmu Politik', 'Kedokteran', 'Keguruan dan Ilmu Pendidikan', 'Matematika dan Ilmu Pengetahuan Alam', 'Pertanian', 'Teknik'] as $fakultas)
                                            <option value="{{ $fakultas }}">{{ $fakultas }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" @click="open = !open"
                                        class="w-full flex items-center justify-between px-3 py-2 border border-border-light dark:border-border-dark rounded-lg bg-white dark:bg-slate-700 text-sm focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary">
                                        <span class="truncate" x-text="selectedLabel"
                                            :class="!selectedValue ? 'text-muted-light dark:text-muted-dark' :
                                                'text-text-light dark:text-text-dark'"></span>
                                        <span
                                            class="material-icons-round text-base text-muted-light">expand_more</span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" x-cloak style="display: none;"
                                        class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 rounded-lg shadow-lg border border-border-light dark:border-border-dark py-1 max-h-48 overflow-y-auto">
                                        <button type="button"
                                            @click="selectedValue = ''; selectedLabel = '-- Pilih Fakultas --'; open = false"
                                            class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 text-muted-light dark:text-muted-dark">--
                                            Pilih Fakultas --</button>
                                        @foreach (['Ekonomi dan Bisnis', 'Hukum', 'Ilmu Sosial dan Ilmu Politik', 'Kedokteran', 'Keguruan dan Ilmu Pendidikan', 'Matematika dan Ilmu Pengetahuan Alam', 'Pertanian', 'Teknik'] as $fakultas)
                                            <button type="button"
                                                @click="selectedValue = '{{ $fakultas }}'; selectedLabel = '{{ $fakultas }}'; open = false"
                                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 dark:hover:bg-slate-700 text-text-light dark:text-text-dark"
                                                :class="selectedValue === '{{ $fakultas }}' ?
                                                    'font-bold text-secondary bg-gray-50 dark:bg-slate-700/50' : ''">{{ $fakultas }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Jurusan</label>
                                <input type="text" name="jurusan" placeholder="Contoh: Ilmu Komputer"
                                    class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary bg-white dark:bg-slate-700 text-text-light dark:text-text-dark sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Unit
                                    Kerja</label>
                                <input type="text" name="unit_kerja" placeholder="Contoh: UPA TIK"
                                    class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary bg-white dark:bg-slate-700 text-text-light dark:text-text-dark sm:text-sm">
                            </div>

                            <div>
                                <label
                                    class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Tanggal
                                    Lahir</label>
                                <input type="date" name="tgl_lahir"
                                    class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary bg-white dark:bg-slate-700 text-text-light dark:text-text-dark sm:text-sm">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">No.
                                    Telpon</label>
                                <input type="text" name="no_telp" placeholder="Contoh: 081234567890"
                                    class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary bg-white dark:bg-slate-700 text-text-light dark:text-text-dark sm:text-sm"
                                    inputmode="numeric" pattern="[0-9]*"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>

                            <div class="sm:col-span-2">
                                <label
                                    class="block text-sm font-medium text-text-light dark:text-text-dark mb-1">Alamat</label>
                                <textarea name="alamat" rows="2" placeholder="Masukkan alamat lengkap..."
                                    class="w-full px-3 py-2 border border-border-light dark:border-border-dark rounded-lg focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary bg-white dark:bg-slate-700 text-text-light dark:text-text-dark sm:text-sm"></textarea>
                            </div>
                        </div>
                    </form>
                </div>

                <div
                    class="px-6 py-4 border-t border-border-light dark:border-border-dark flex justify-end gap-3 bg-gray-50 dark:bg-slate-800/50">
                    <button type="button" @click="isCreateModalOpen = false"
                        class="px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600">
                        Batal
                    </button>
                    <button type="submit" form="formCreateUser"
                        class="px-4 py-2 bg-secondary hover:bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">
                        Simpan User
                    </button>
                </div>
            </div>
        </div>

        <div x-show="isResetModalOpen" x-cloak style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center">
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
                        </div>

                        <div
                            class="flex justify-end gap-3 pt-2 border-t border-border-light dark:border-border-dark mt-4">
                            <button type="button" @click="isResetModalOpen = false"
                                class="px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 bg-secondary hover:bg-blue-600 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">Simpan
                                Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div x-show="isInactiveModalOpen" x-cloak style="display: none;"
            class="fixed inset-0 z-50 flex items-center justify-center">
            <div x-show="isInactiveModalOpen" x-transition.opacity
                class="fixed inset-0 bg-black/30 dark:bg-slate-900/50 backdrop-blur-sm"
                @click="isInactiveModalOpen = false"></div>

            <div x-show="isInactiveModalOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative bg-surface-light dark:bg-surface-dark rounded-xl shadow-2xl border border-border-light dark:border-border-dark w-full max-w-md mx-4 overflow-hidden">

                <div
                    class="px-6 py-4 border-b border-border-light dark:border-border-dark flex items-center gap-3 bg-red-50 dark:bg-red-900/20">
                    <div class="flex items-center justify-center h-10 w-10 rounded-full bg-red-100 dark:bg-red-900/40">
                        <span class="material-icons-round text-red-600 dark:text-red-400">person_off</span>
                    </div>
                    <h3 class="text-lg font-bold text-text-light dark:text-text-dark">Nonaktifkan Akun SSO</h3>
                </div>

                <div class="p-6">
                    <p class="text-sm text-muted-light dark:text-muted-dark leading-relaxed mb-6">
                        Apakah Anda yakin ingin menonaktifkan pengguna <span
                            class="font-bold text-text-light dark:text-text-dark" x-text="selectedName"></span> (<span
                            x-text="selectedUsername"></span>)?
                        <br><br>
                        Pengguna tidak akan bisa melakukan login menggunakan kredensial ini setelah dinonaktifkan.
                    </p>

                    <form action="{{ route('sso-users.inactive') }}" method="POST">
                        @csrf
                        <input type="hidden" name="username" :value="selectedUsername">

                        <div
                            class="flex justify-end gap-3 pt-2 border-t border-border-light dark:border-border-dark mt-4">
                            <button type="button" @click="isInactiveModalOpen = false"
                                class="px-4 py-2 bg-white dark:bg-slate-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-slate-600">Batal</button>
                            <button type="submit"
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium shadow-sm transition-colors">Ya,
                                Nonaktifkan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
