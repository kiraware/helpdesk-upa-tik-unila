<x-layouts.dashboard title="Manajemen Layanan">

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

    <x-services.filter />

    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr
                        class="bg-gray-50 dark:bg-slate-800/50 border-b border-border-light dark:border-border-dark text-left">
                        <th
                            class="px-6 py-4 text-xs font-bold text-muted-light dark:text-muted-dark uppercase tracking-wider w-16">
                            No</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                            Nama Layanan</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-muted-light dark:text-muted-dark uppercase tracking-wider w-32">
                            Aksesibilitas</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-muted-light dark:text-muted-dark uppercase tracking-wider w-32">
                            Status</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-muted-light dark:text-muted-dark uppercase tracking-wider text-right w-24">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse ($services as $index => $service)
                        <x-services.item :service="$service" :number="$services->firstItem() + $index" />
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div
                                    class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-slate-800 mb-4">
                                    <span
                                        class="material-icons-round text-2xl text-muted-light dark:text-muted-dark">inbox</span>
                                </div>
                                <h3 class="text-sm font-medium text-text-light dark:text-text-dark mb-1">Tidak ada
                                    layanan</h3>
                                <p class="text-sm text-muted-light dark:text-muted-dark">Belum ada layanan yang
                                    ditambahkan atau tidak sesuai filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($services->hasPages())
            <div class="px-6 py-4 border-t border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50">
                {{ $services->links() }}
            </div>
        @endif
    </div>

    <x-services.modal-add />
    <x-services.modal-edit />
    <x-services.modal-delete />

    <script>
        function openAddServiceModal() {
            const modal = document.getElementById('addServiceModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeAddServiceModal() {
            const modal = document.getElementById('addServiceModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openEditServiceModal(button) {
            const {
                id,
                name,
                notes,
                active,
                guest,
                user,
                replyTemplate
            } = button.dataset;

            document.getElementById('edit_name').value = name;
            document.getElementById('edit_notes').value = notes || '';
            document.getElementById('edit_reply_template').value = replyTemplate || '';
            document.getElementById('edit_is_active').checked = active == 1;
            document.getElementById('edit_show_to_guest').checked = guest == 1;
            document.getElementById('edit_show_to_user').checked = user == 1;

            document.getElementById('editServiceForm').action = `/services/${id}`;

            const modal = document.getElementById('editServiceModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditServiceModal() {
            const modal = document.getElementById('editServiceModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openDeleteServiceModal(button) {
            const {
                id,
                name
            } = button.dataset;
            document.getElementById('deleteServiceName').textContent = `"${name}"`;
            document.getElementById('deleteServiceForm').action = `/services/${id}`;

            const modal = document.getElementById('deleteServiceModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteServiceModal() {
            const modal = document.getElementById('deleteServiceModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                openAddServiceModal();
            });
        @endif
    </script>
</x-layouts.dashboard>
