<x-layouts.dashboard title="Manajemen Layanan">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Kelola Layanan
            </h1>
            <p class="text-sm text-muted-light dark:text-muted-dark">
                Daftar layanan yang tersedia di dalam sistem
            </p>
        </div>

        <button type="button" onclick="openAddServiceModal()"
            class="flex items-center justify-center px-4 py-2 bg-secondary hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <span class="material-icons-round text-sm mr-2">add</span>
            Tambah Layanan
        </button>
    </div>

    {{-- Search & Filter --}}
    <form method="GET" action="{{ route('services.index') }}" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">

            {{-- Search --}}
            <div class="sm:col-span-8 lg:col-span-9 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-icons-round text-gray-400">search</span>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama layanan..."
                    class="block w-full pl-10 pr-3 py-2
                        border border-border-light dark:border-border-dark
                        rounded-lg leading-5
                        bg-surface-light dark:bg-slate-800
                        text-text-light dark:text-text-dark
                        placeholder-muted-light dark:placeholder-muted-dark
                        focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary
                        sm:text-sm shadow-sm">
            </div>

            {{-- Filter Status --}}
            <div class="sm:col-span-4 lg:col-span-3">
                <select name="status" onchange="this.form.submit()"
                    class="block w-full pl-3 pr-10 py-2
                        border border-border-light dark:border-border-dark
                        rounded-lg
                        bg-surface-light dark:bg-slate-800
                        text-text-light dark:text-text-dark
                        focus:outline-none focus:ring-secondary focus:border-secondary
                        sm:text-sm shadow-sm">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                        Aktif
                    </option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                        Non Aktif
                    </option>
                </select>
            </div>
        </div>
    </form>

    {{-- Table Card --}}
    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">

        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-slate-800/50 border-b border-border-light dark:border-border-dark">
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider w-16">
                            No
                        </th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                            Nama Layanan
                        </th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider w-32">
                            Status
                        </th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider text-right w-32">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse ($services as $service)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors group">

                            {{-- No --}}
                            <td class="px-6 py-4 text-sm text-muted-light dark:text-muted-dark">
                                {{ $loop->iteration + ($services->currentPage() - 1) * $services->perPage() }}
                            </td>

                            {{-- Nama --}}
                            <td class="px-6 py-4 text-sm font-medium text-text-light dark:text-text-dark">
                                {{ $service->name }}
                            </td>

                            {{-- Status --}}
                            <td class="px-6 py-4 text-sm">
                                @if ($service->is_active)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        bg-emerald-100 text-emerald-800
                                        dark:bg-emerald-900/30 dark:text-emerald-400
                                        border border-emerald-200 dark:border-emerald-800">
                                        Aktif
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        bg-red-100 text-red-800
                                        dark:bg-red-900/30 dark:text-red-400
                                        border border-red-200 dark:border-red-800">
                                        Non Aktif
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                <div
                                    class="flex items-center justify-end space-x-2
                                    opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">

                                    {{-- Edit --}}
                                    <button type="button" onclick="openEditServiceModal(this)"
                                        data-id="{{ $service->id }}" data-name="{{ $service->name }}"
                                        data-active="{{ $service->is_active ? 1 : 0 }}"
                                        class="p-1.5 text-amber-500 hover:bg-amber-50
                                            dark:hover:bg-amber-900/20 rounded-md transition-colors"
                                        title="Ubah">
                                        <span class="material-icons-round text-lg">edit</span>
                                    </button>

                                    {{-- Hapus --}}
                                    <button type="button" onclick="openDeleteServiceModal(this)"
                                        data-id="{{ $service->id }}" data-name="{{ $service->name }}"
                                        class="p-1.5 text-red-500 hover:bg-red-50
                                            dark:hover:bg-red-900/20 rounded-md transition-colors"
                                        title="Hapus">
                                        <span class="material-icons-round text-lg">delete</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4"
                                class="px-6 py-10 text-center text-sm text-muted-light dark:text-muted-dark">
                                Belum ada data layanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 bg-gray-50 dark:bg-slate-800/50 border-t border-border-light dark:border-border-dark">
            {{ $services->links() }}
        </div>
    </div>

</x-layouts.dashboard>

{{-- Modal Tambah Layanan --}}
<div id="addServiceModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center
           bg-white/30 dark:bg-slate-900/30 backdrop-blur-sm">

    <div
        class="bg-surface-light dark:bg-surface-dark
               rounded-xl shadow-2xl
               w-full max-w-md mx-4 overflow-hidden">

        {{-- Header --}}
        <div
            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700
                   flex justify-between items-center
                   bg-gray-50 dark:bg-gray-800">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                Tambah Layanan Baru
            </h3>
            <button type="button" onclick="closeAddServiceModal()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        {{-- Form --}}
        <form action="{{ route('services.store') }}" method="POST">
            @csrf

            <div class="p-6 space-y-6">

                {{-- Nama Layanan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Layanan
                    </label>

                    <div class="relative">
                        <span
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <span class="material-icons-round text-lg">layers</span>
                        </span>

                        <input type="text" name="name" value="{{ old('name') }}"
                            placeholder="Contoh: Pemeliharaan Server" required
                            class="pl-10 block w-full rounded-lg
                                   border border-gray-300 dark:border-gray-600
                                   bg-white dark:bg-gray-700
                                   text-gray-900 dark:text-white
                                   shadow-sm
                                   focus:border-secondary focus:ring-secondary
                                   sm:text-sm py-2.5">

                    </div>

                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div class="flex items-center justify-between">
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Status Aktif
                        </label>
                        <span class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Layanan akan langsung tersedia jika aktif.
                        </span>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">

                        <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                            {{ old('is_active', true) ? 'checked' : '' }}>

                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none
                                   peer-focus:ring-4 peer-focus:ring-blue-300
                                   dark:peer-focus:ring-blue-800
                                   rounded-full peer dark:bg-gray-600
                                   peer-checked:after:translate-x-full
                                   peer-checked:after:border-white
                                   after:content-['']
                                   after:absolute after:top-[2px] after:left-[2px]
                                   after:bg-white after:border-gray-300 after:border
                                   after:rounded-full after:h-5 after:w-5
                                   after:transition-all
                                   peer-checked:bg-secondary">
                        </div>
                    </label>
                </div>

            </div>

            {{-- Footer --}}
            <div
                class="px-6 py-4 bg-gray-50 dark:bg-gray-800
                       border-t border-gray-200 dark:border-gray-700
                       flex justify-end space-x-3">

                <button type="button" onclick="closeAddServiceModal()"
                    class="px-4 py-2 bg-white dark:bg-gray-700
                           border border-gray-300 dark:border-gray-600
                           rounded-lg text-sm font-medium
                           text-gray-700 dark:text-gray-200
                           hover:bg-gray-50 dark:hover:bg-gray-600">
                    Batal
                </button>

                <button type="submit"
                    class="px-4 py-2 bg-secondary hover:bg-blue-600
                           text-white rounded-lg text-sm font-medium shadow-sm">
                    Simpan Layanan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Layanan --}}
<div id="editServiceModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center
           bg-white/30 dark:bg-slate-900/30 backdrop-blur-sm">

    <div
        class="bg-surface-light dark:bg-surface-dark
                rounded-xl shadow-2xl
                w-full max-w-md mx-4 overflow-hidden">

        {{-- Header --}}
        <div
            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700
                    flex justify-between items-center
                    bg-gray-50 dark:bg-gray-800">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                Edit Layanan
            </h3>
            <button type="button" onclick="closeEditServiceModal()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        {{-- Form --}}
        <form id="editServiceForm" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Layanan
                    </label>

                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                            <span class="material-icons-round text-lg">layers</span>
                        </span>

                        <input type="text" name="name" id="edit_name" required
                            class="pl-10 block w-full rounded-lg
                                   border border-gray-300 dark:border-gray-600
                                   bg-white dark:bg-gray-700
                                   text-gray-900 dark:text-white
                                   focus:border-secondary focus:ring-secondary
                                   sm:text-sm py-2.5">
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex items-center justify-between">
                    <div class="flex flex-col">
                        <label class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            Status Aktif
                        </label>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Aktifkan atau nonaktifkan layanan.
                        </span>
                    </div>

                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" id="edit_is_active"
                            class="sr-only peer">

                        <div
                            class="w-11 h-6 bg-gray-200 peer-focus:outline-none
                                   peer-focus:ring-4 peer-focus:ring-blue-300
                                   dark:peer-focus:ring-blue-800
                                   rounded-full peer dark:bg-gray-600
                                   peer-checked:after:translate-x-full
                                   after:absolute after:top-[2px] after:left-[2px]
                                   after:bg-white after:border after:rounded-full
                                   after:h-5 after:w-5 after:transition-all
                                   peer-checked:bg-secondary">
                        </div>
                    </label>
                </div>

            </div>

            {{-- Footer --}}
            <div
                class="px-6 py-4 bg-gray-50 dark:bg-gray-800
                        border-t border-gray-200 dark:border-gray-700
                        flex justify-end gap-3">

                <button type="button" onclick="closeEditServiceModal()"
                    class="px-4 py-2 bg-white dark:bg-gray-700
                           border border-gray-300 dark:border-gray-600
                           rounded-lg text-sm font-medium
                           text-gray-700 dark:text-gray-200">
                    Batal
                </button>

                <button type="submit"
                    class="px-4 py-2 bg-secondary hover:bg-blue-600
                           text-white rounded-lg text-sm font-medium shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus Layanan --}}
<div id="deleteServiceModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center
           bg-white/30 dark:bg-slate-900/30 backdrop-blur-sm">

    <div
        class="bg-surface-light dark:bg-surface-dark
               rounded-xl shadow-2xl
               w-full max-w-md mx-4 overflow-hidden">

        {{-- Header --}}
        <div
            class="px-6 py-4 border-b border-gray-200 dark:border-gray-700
                    flex items-center gap-3
                    bg-red-50 dark:bg-red-900/20">
            <div
                class="flex items-center justify-center
                       h-10 w-10 rounded-full
                       bg-red-100 dark:bg-red-900/40">
                <span class="material-icons-round text-red-600 dark:text-red-400">
                    warning
                </span>
            </div>

            <h3 class="text-lg font-bold text-gray-800 dark:text-white">
                Hapus Layanan
            </h3>
        </div>

        {{-- Body --}}
        <div class="p-6">
            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                Apakah Anda yakin ingin menghapus layanan
                <span class="font-semibold text-gray-900 dark:text-white" id="deleteServiceName"></span>?
                <br><br>
                <span class="text-red-600 dark:text-red-400 font-medium">
                    Tindakan ini tidak dapat dibatalkan.
                </span>
            </p>
        </div>

        {{-- Footer --}}
        <div
            class="px-6 py-4 bg-gray-50 dark:bg-gray-800
                   border-t border-gray-200 dark:border-gray-700
                   flex justify-end gap-3">

            <button type="button" onclick="closeDeleteServiceModal()"
                class="px-4 py-2 bg-white dark:bg-gray-700
                       border border-gray-300 dark:border-gray-600
                       rounded-lg text-sm font-medium
                       text-gray-700 dark:text-gray-200
                       hover:bg-gray-50 dark:hover:bg-gray-600">
                Batal
            </button>

            <form id="deleteServiceForm" method="POST">
                @csrf
                @method('DELETE')

                <button type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700
                           text-white rounded-lg text-sm font-medium shadow-sm">
                    Hapus
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function openAddServiceModal() {
        document.getElementById('addServiceModal').classList.remove('hidden');
    }

    function closeAddServiceModal() {
        document.getElementById('addServiceModal').classList.add('hidden');
    }

    function openEditServiceModal(button) {
        const id = button.dataset.id;
        const name = button.dataset.name;
        const active = button.dataset.active;

        document.getElementById('edit_name').value = name;
        document.getElementById('edit_is_active').checked = active == 1;

        document.getElementById('editServiceForm').action =
            `/services/${id}`;

        document.getElementById('editServiceModal').classList.remove('hidden');
    }

    function closeEditServiceModal() {
        document.getElementById('editServiceModal').classList.add('hidden');
    }

    function openDeleteServiceModal(button) {
        const id = button.dataset.id;
        const name = button.dataset.name;

        document.getElementById('deleteServiceName').textContent = `"${name}"`;

        document.getElementById('deleteServiceForm').action =
            `/services/${id}`;

        document.getElementById('deleteServiceModal').classList.remove('hidden');
    }

    function closeDeleteServiceModal() {
        document.getElementById('deleteServiceModal').classList.add('hidden');
    }
</script>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            openAddServiceModal();
        });
    </script>
@endif
