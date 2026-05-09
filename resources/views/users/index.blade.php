<x-layouts.dashboard title="Manajemen Staff">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">Kelola Staff</h1>
            <p class="text-sm text-muted-light dark:text-muted-dark">Daftar Admin dan Superuser</p>
        </div>
        <button type="button" onclick="openAddUserModal()"
            class="flex items-center justify-center px-4 py-2 bg-secondary hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <span class="material-icons-round text-sm mr-2">add</span> Tambah Staff
        </button>
    </div>

    @include('users.filter')

    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr
                        class="bg-gray-50 dark:bg-slate-800/50 border-b border-border-light dark:border-border-dark text-left">
                        <th class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase">No
                        </th>
                        <th class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase">
                            Profil Staff</th>
                        <th class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase">
                            Kontak</th>
                        <th class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase">
                            Identitas (NIP/NIK)</th>
                        <th class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase">
                            Penanggung Jawab</th>
                        <th class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase">Role
                        </th>
                        <th
                            class="px-6 py-4 text-xs font-semibold text-muted-light dark:text-muted-dark uppercase text-right">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse ($users as $index => $user)
                        @include('users.item', ['user' => $user, 'number' => $users->firstItem() + $index])
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-muted-light dark:text-muted-dark">Tidak
                                ada data staff.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="px-6 py-4 border-t border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/30">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    @include('users.modal-add')
    @include('users.modal-edit')
    @include('users.modal-delete')

    <script>
        function openAddUserModal() {
            document.getElementById('addUserModal').classList.remove('hidden');
        }

        function closeAddUserModal() {
            document.getElementById('addUserModal').classList.add('hidden');
        }

        function openEditUserModal(button) {
            const data = button.dataset;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_username_sso').value = data.sso;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_phone').value = data.phone;
            document.getElementById('edit_identity_number').value = data.identity;

            // Dispatch untuk update alpine.js dropdown
            window.dispatchEvent(new CustomEvent('set-edit-division', {
                detail: {
                    id: data.division,
                    name: data.divisionName
                }
            }));
            window.dispatchEvent(new CustomEvent('set-edit-role', {
                detail: {
                    id: data.role,
                    name: data.role.toUpperCase()
                }
            }));

            document.getElementById('editUserForm').action = `/users/${data.id}`;
            document.getElementById('editUserModal').classList.remove('hidden');
        }

        function closeEditUserModal() {
            document.getElementById('editUserModal').classList.add('hidden');
        }

        function openDeleteUserModal(button) {
            const {
                id,
                name
            } = button.dataset;
            document.getElementById('deleteUserName').textContent = `"${name}"`;
            document.getElementById('deleteUserForm').action = `/users/${id}`;
            document.getElementById('deleteUserModal').classList.remove('hidden');
        }

        function closeDeleteUserModal() {
            document.getElementById('deleteUserModal').classList.add('hidden');
        }
    </script>
</x-layouts.dashboard>
