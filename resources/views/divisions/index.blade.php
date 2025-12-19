<x-layouts.dashboard title="Manajemen Divisi">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Kelola Divisi
            </h1>
            <p class="text-sm text-muted-light dark:text-muted-dark">
                Daftar divisi yang tersedia di dalam sistem
            </p>
        </div>

        <button type="button" onclick="openAddDivisionModal()"
            class="flex items-center justify-center px-4 py-2 bg-secondary hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <span class="material-icons-round text-sm mr-2">add</span>
            Tambah Divisi
        </button>
    </div>

    {{-- Search & Filter --}}
    <form method="GET" action="{{ route('divisions.index') }}" class="mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-4">

            {{-- Search --}}
            <div class="sm:col-span-8 lg:col-span-9 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-icons-round text-gray-400">search</span>
                </div>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama divisi..."
                    class="h-10 block w-full pl-10 pr-3 py-2
                        border border-border-light dark:border-border-dark
                        rounded-lg leading-5
                        bg-surface-light dark:bg-slate-800
                        text-text-light dark:text-text-dark
                        placeholder-muted-light dark:placeholder-muted-dark
                        focus:outline-none focus:ring-1 focus:ring-secondary focus:border-secondary
                        sm:text-sm shadow-sm">
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
                            Nama Divisi
                        </th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider text-right w-32">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse ($divisions as $division)
                        <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors group">

                            {{-- No --}}
                            <td class="px-6 py-4 text-sm text-muted-light dark:text-muted-dark">
                                {{ $loop->iteration + ($divisions->currentPage() - 1) * $divisions->perPage() }}
                            </td>

                            {{-- Nama --}}
                            <td class="px-6 py-4 text-sm font-medium text-text-light dark:text-text-dark">
                                {{ $division->name }}
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
                                <div
                                    class="flex items-center justify-end space-x-2
                                    opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">

                                    {{-- Edit --}}
                                    <button type="button" onclick="openEditDivisionModal(this)"
                                        data-id="{{ $division->id }}" data-name="{{ $division->name }}"
                                        class="p-1.5 text-amber-500 hover:bg-amber-50
                                            dark:hover:bg-amber-900/20 rounded-md transition-colors"
                                        title="Ubah">
                                        <span class="material-icons-round text-lg">edit</span>
                                    </button>

                                    {{-- Hapus --}}
                                    <button type="button" onclick="openDeleteDivisionModal(this)"
                                        data-id="{{ $division->id }}" data-name="{{ $division->name }}"
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
                            <td colspan="3"
                                class="px-6 py-10 text-center text-sm text-muted-light dark:text-muted-dark">
                                Belum ada data divisi.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 bg-gray-50 dark:bg-slate-800/50 border-t border-border-light dark:border-border-dark">
            {{ $divisions->links() }}
        </div>
    </div>

</x-layouts.dashboard>

{{-- Modal Tambah Divisi --}}
<div id="addDivisionModal"
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
                Tambah Divisi Baru
            </h3>
            <button type="button" onclick="closeAddDivisionModal()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        {{-- Form --}}
        <form action="{{ route('divisions.store') }}" method="POST">
            @csrf

            <div class="p-6 space-y-6">

                {{-- Nama Divisi --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Divisi
                    </label>

                    <div class="relative">
                        <span
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <span class="material-icons-round text-lg">layers</span>
                        </span>

                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: Pusdatin"
                            required
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

            </div>

            {{-- Footer --}}
            <div
                class="px-6 py-4 bg-gray-50 dark:bg-gray-800
                       border-t border-gray-200 dark:border-gray-700
                       flex justify-end space-x-3">

                <button type="button" onclick="closeAddDivisionModal()"
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
                    Simpan Divisi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Divisi --}}
<div id="editDivisionModal"
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
                Edit Divisi
            </h3>
            <button type="button" onclick="closeEditDivisionModal()"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                <span class="material-icons-round">close</span>
            </button>
        </div>

        {{-- Form --}}
        <form id="editDivisionForm" method="POST">
            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nama Divisi
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

            </div>

            {{-- Footer --}}
            <div
                class="px-6 py-4 bg-gray-50 dark:bg-gray-800
                        border-t border-gray-200 dark:border-gray-700
                        flex justify-end gap-3">

                <button type="button" onclick="closeEditDivisionModal()"
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

{{-- Modal Konfirmasi Hapus Divisi --}}
<div id="deleteDivisionModal"
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
                Hapus Divisi
            </h3>
        </div>

        {{-- Body --}}
        <div class="p-6">
            <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                Apakah Anda yakin ingin menghapus divisi
                <span class="font-semibold text-gray-900 dark:text-white" id="deleteDivisionName"></span>?
                <br><br>
                <span class="text-red-600 dark:text-red-400 font-medium">
                    Tindakan ini tidak dapat dibatalkan dan semua data terkait akan hilang permanen.
                </span>
            </p>
        </div>

        {{-- Footer --}}
        <div
            class="px-6 py-4 bg-gray-50 dark:bg-gray-800
                   border-t border-gray-200 dark:border-gray-700
                   flex justify-end gap-3">

            <button type="button" onclick="closeDeleteDivisionModal()"
                class="px-4 py-2 bg-white dark:bg-gray-700
                       border border-gray-300 dark:border-gray-600
                       rounded-lg text-sm font-medium
                       text-gray-700 dark:text-gray-200
                       hover:bg-gray-50 dark:hover:bg-gray-600">
                Batal
            </button>

            <form id="deleteDivisionForm" method="POST">
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
    function openAddDivisionModal() {
        document.getElementById('addDivisionModal').classList.remove('hidden');
    }

    function closeAddDivisionModal() {
        document.getElementById('addDivisionModal').classList.add('hidden');
    }

    function openEditDivisionModal(button) {
        const id = button.dataset.id;
        const name = button.dataset.name;

        document.getElementById('edit_name').value = name;

        document.getElementById('editDivisionForm').action =
            `/divisions/${id}`;

        document.getElementById('editDivisionModal').classList.remove('hidden');
    }

    function closeEditDivisionModal() {
        document.getElementById('editDivisionModal').classList.add('hidden');
    }

    function openDeleteDivisionModal(button) {
        const id = button.dataset.id;
        const name = button.dataset.name;

        document.getElementById('deleteDivisionName').textContent = `"${name}"`;

        document.getElementById('deleteDivisionForm').action =
            `/divisions/${id}`;

        document.getElementById('deleteDivisionModal').classList.remove('hidden');
    }

    function closeDeleteDivisionModal() {
        document.getElementById('deleteDivisionModal').classList.add('hidden');
    }
</script>

@if ($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            openAddDivisionModal();
        });
    </script>
@endif
