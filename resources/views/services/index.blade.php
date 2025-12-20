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

    {{-- Component: Filter --}}
    <x-services.filter />

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
                            Nama Layanan</th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider w-32">
                            Status</th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase tracking-wider text-right w-32">
                            Aksi</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse ($services as $service)
                        {{-- Component: Item Tabel --}}
                        {{-- Kita kirim perhitungan nomor urut ke component agar rapi --}}
                        <x-services.item :service="$service" :number="$loop->iteration + ($services->currentPage() - 1) * $services->perPage()" />
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

    {{-- Components: Modals --}}
    <x-services.modal-add />
    <x-services.modal-edit />
    <x-services.modal-delete />

    {{-- Script Modal Logic (Tetap Disini) --}}
    <script>
        // Modal Tambah
        function openAddServiceModal() {
            document.getElementById('addServiceModal').classList.remove('hidden');
        }

        function closeAddServiceModal() {
            document.getElementById('addServiceModal').classList.add('hidden');
        }

        // Modal Edit
        function openEditServiceModal(button) {
            const {
                id,
                name,
                active
            } = button.dataset;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_is_active').checked = active == 1;
            document.getElementById('editServiceForm').action = `/services/${id}`;
            document.getElementById('editServiceModal').classList.remove('hidden');
        }

        function closeEditServiceModal() {
            document.getElementById('editServiceModal').classList.add('hidden');
        }

        // Modal Hapus
        function openDeleteServiceModal(button) {
            const {
                id,
                name
            } = button.dataset;
            document.getElementById('deleteServiceName').textContent = `"${name}"`;
            document.getElementById('deleteServiceForm').action = `/services/${id}`;
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

</x-layouts.dashboard>
