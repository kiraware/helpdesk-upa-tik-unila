<x-layouts.dashboard title="Manajemen Unit Kerja">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Kelola Unit Kerja
            </h1>
            <p class="text-sm text-muted-light dark:text-muted-dark">
                Daftar unit kerja yang tersedia di dalam sistem
            </p>
        </div>

        <button type="button" onclick="openAddDepartmentModal()"
            class="flex items-center justify-center px-4 py-2 bg-secondary hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <span class="material-icons-round text-sm mr-2">add</span>
            Tambah Unit Kerja
        </button>
    </div>

    <x-departments.filter />

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
                            Nama Unit Kerja</th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider text-right w-32">
                            Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse ($departments as $department)
                        <x-departments.item :department="$department" :number="$loop->iteration + ($departments->currentPage() - 1) * $departments->perPage()" />
                    @empty
                        <tr>
                            <td colspan="3"
                                class="px-6 py-10 text-center text-sm text-muted-light dark:text-muted-dark">
                                Belum ada data unit kerja.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-slate-800/50 border-t border-border-light dark:border-border-dark">
            {{ $departments->links() }}
        </div>
    </div>

    <x-departments.modal-add />
    <x-departments.modal-edit />
    <x-departments.modal-delete />

    <script>
        function openAddDepartmentModal() {
            document.getElementById('addDepartmentModal').classList.remove('hidden');
        }

        function closeAddDepartmentModal() {
            document.getElementById('addDepartmentModal').classList.add('hidden');
        }

        function openEditDepartmentModal(button) {
            const {
                id,
                name
            } = button.dataset;
            document.getElementById('edit_name').value = name;
            document.getElementById('editDepartmentForm').action = `/departments/${id}`;
            document.getElementById('editDepartmentModal').classList.remove('hidden');
        }

        function closeEditDepartmentModal() {
            document.getElementById('editDepartmentModal').classList.add('hidden');
        }

        function openDeleteDepartmentModal(button) {
            const {
                id,
                name
            } = button.dataset;
            document.getElementById('deleteDepartmentName').textContent = `"${name}"`;
            document.getElementById('deleteDepartmentForm').action = `/departments/${id}`;
            document.getElementById('deleteDepartmentModal').classList.remove('hidden');
        }

        function closeDeleteDepartmentModal() {
            document.getElementById('deleteDepartmentModal').classList.add('hidden');
        }
    </script>

    @if ($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                openAddDepartmentModal();
            });
        </script>
    @endif

</x-layouts.dashboard>
