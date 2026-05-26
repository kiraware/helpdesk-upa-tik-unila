<x-layouts.dashboard title="Manajemen Unit Fungsi">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Kelola Unit Fungsi
            </h1>
            <p class="text-sm text-muted-light dark:text-muted-dark">
                Daftar unit fungsi yang tersedia di dalam sistem
            </p>
        </div>

        <button type="button" onclick="openAddDivisionModal()"
            class="flex items-center justify-center px-4 py-2 bg-secondary hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <span class="material-icons-round text-sm mr-2">add</span>
            Tambah Unit Fungsi
        </button>
    </div>

    {{-- Component: Filter --}}
    <x-divisions.filter />

    {{-- Table Card --}}
    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-50 dark:bg-slate-800/50 border-b border-border-light dark:border-border-dark">
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider w-16">
                            No</th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                            Nama Unit Fungsi</th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider text-right w-32">
                            Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse ($divisions as $division)
                        {{-- Component: Item Tabel --}}
                        <x-divisions.item :division="$division" :number="$loop->iteration + ($divisions->currentPage() - 1) * $divisions->perPage()" />
                    @empty
                        <tr>
                            <td colspan="3"
                                class="px-6 py-10 text-center text-sm text-muted-light dark:text-muted-dark">
                                Belum ada data unit fungsi.
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

    {{-- Components: Modals --}}
    <x-divisions.modal-add />
    <x-divisions.modal-edit />
    <x-divisions.modal-delete />

    {{-- Script Modal Logic --}}
    <script>
        // Modal Tambah
        function openAddDivisionModal() {
            document.getElementById('addDivisionModal').classList.remove('hidden');
        }

        function closeAddDivisionModal() {
            document.getElementById('addDivisionModal').classList.add('hidden');
        }

        // Modal Edit
        function openEditDivisionModal(button) {
            const {
                id,
                name
            } = button.dataset;
            document.getElementById('edit_name').value = name;
            document.getElementById('editDivisionForm').action = `/divisions/${id}`;
            document.getElementById('editDivisionModal').classList.remove('hidden');
        }

        function closeEditDivisionModal() {
            document.getElementById('editDivisionModal').classList.add('hidden');
        }

        // Modal Hapus
        function openDeleteDivisionModal(button) {
            const {
                id,
                name
            } = button.dataset;
            document.getElementById('deleteDivisionName').textContent = `"${name}"`;
            document.getElementById('deleteDivisionForm').action = `/divisions/${id}`;
            document.getElementById('deleteDivisionModal').classList.remove('hidden');
        }

        function closeDeleteDivisionModal() {
            document.getElementById('deleteDivisionModal').classList.add('hidden');
        }
    </script>

    {{-- Handle Validation Error (Re-open Add Modal) --}}
    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                openAddDivisionModal();
            });
        </script>
    @endif

</x-layouts.dashboard>
