<x-layouts.dashboard title="Kelola Pertanyaan Kuesioner">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Kelola Pertanyaan Kuesioner
            </h1>
            <p class="text-sm text-muted-light dark:text-muted-dark">
                Kelola pertanyaan survei kepuasan layanan helpdesk
            </p>
        </div>

        <button type="button" onclick="openAddQuestionModal()"
            class="flex items-center justify-center px-4 py-2 bg-secondary hover:bg-blue-600 text-white text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <span class="material-icons-round text-sm mr-2">add</span>
            Tambah Pertanyaan
        </button>
    </div>

    <x-survey-questions.filter />

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
                            class="px-6 py-4 text-xs font-bold text-muted-light dark:text-muted-dark uppercase tracking-wider w-40">
                            Aspek</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                            Pertanyaan Kepuasan</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-muted-light dark:text-muted-dark uppercase tracking-wider">
                            Pertanyaan Kepentingan</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-muted-light dark:text-muted-dark uppercase tracking-wider w-20 text-center">
                            Urutan</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-muted-light dark:text-muted-dark uppercase tracking-wider w-28 text-center">
                            Status</th>
                        <th
                            class="px-6 py-4 text-xs font-bold text-muted-light dark:text-muted-dark uppercase tracking-wider text-right w-28">
                            Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border-light dark:divide-border-dark">
                    @forelse ($questions as $index => $question)
                        <x-survey-questions.item :question="$question" :number="$questions->firstItem() + $index" />
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <div
                                    class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-100 dark:bg-slate-800 mb-4">
                                    <span
                                        class="material-icons-round text-2xl text-muted-light dark:text-muted-dark">quiz</span>
                                </div>
                                <h3 class="text-sm font-medium text-text-light dark:text-text-dark mb-1">Tidak ada
                                    pertanyaan</h3>
                                <p class="text-sm text-muted-light dark:text-muted-dark">Belum ada pertanyaan kuesioner
                                    yang ditambahkan atau tidak sesuai filter.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($questions->hasPages())
            <div class="px-6 py-4 border-t border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50">
                {{ $questions->links() }}
            </div>
        @endif
    </div>

    <x-survey-questions.modal-add />
    <x-survey-questions.modal-edit />
    <x-survey-questions.modal-delete />

    <script>
        function openAddQuestionModal() {
            const modal = document.getElementById('addQuestionModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeAddQuestionModal() {
            const modal = document.getElementById('addQuestionModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openEditQuestionModal(button) {
            const {
                id,
                aspect,
                satisfaction,
                importance,
                sortorder,
                active
            } = button.dataset;

            document.getElementById('edit_aspect_name').value = aspect;
            document.getElementById('edit_satisfaction_question').value = satisfaction;
            document.getElementById('edit_importance_question').value = importance;
            document.getElementById('edit_sort_order').value = sortorder;
            document.getElementById('edit_is_active').checked = active == 1;

            document.getElementById('editQuestionForm').action = `/survey-questions/${id}`;

            const modal = document.getElementById('editQuestionModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeEditQuestionModal() {
            const modal = document.getElementById('editQuestionModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        function openDeleteQuestionModal(button) {
            const {
                id,
                aspect
            } = button.dataset;
            document.getElementById('deleteQuestionName').textContent = `"${aspect}"`;
            document.getElementById('deleteQuestionForm').action = `/survey-questions/${id}`;

            const modal = document.getElementById('deleteQuestionModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteQuestionModal() {
            const modal = document.getElementById('deleteQuestionModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                openAddQuestionModal();
            });
        @endif
    </script>
</x-layouts.dashboard>
