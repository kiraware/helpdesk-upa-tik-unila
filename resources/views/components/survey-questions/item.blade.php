@props(['question', 'number'])

<tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors group">
    <td class="px-6 py-4 text-sm text-muted-light dark:text-muted-dark">
        {{ $number }}
    </td>

    <td class="px-6 py-4 text-sm font-medium text-text-light dark:text-text-dark">
        {{ $question->aspect_name }}
    </td>

    <td class="px-6 py-4 text-sm text-text-light dark:text-text-dark">
        <span class="line-clamp-2" title="{{ $question->satisfaction_question }}">
            {{ $question->satisfaction_question }}
        </span>
    </td>

    <td class="px-6 py-4 text-sm text-text-light dark:text-text-dark">
        <span class="line-clamp-2" title="{{ $question->importance_question }}">
            {{ $question->importance_question }}
        </span>
    </td>

    <td class="px-6 py-4 text-sm text-center text-muted-light dark:text-muted-dark">
        {{ $question->sort_order }}
    </td>

    <td class="px-6 py-4 text-sm text-center">
        <form action="{{ route('survey-questions.toggle', $question) }}" method="POST" class="inline">
            @csrf
            @method('PATCH')
            <button type="submit"
                title="{{ $question->is_active ? 'Klik untuk nonaktifkan' : 'Klik untuk aktifkan' }}">
                @if ($question->is_active)
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 hover:bg-emerald-200 dark:hover:bg-emerald-900/50 transition-colors cursor-pointer">
                        Aktif
                    </span>
                @else
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800 hover:bg-red-200 dark:hover:bg-red-900/50 transition-colors cursor-pointer">
                        Non Aktif
                    </span>
                @endif
            </button>
        </form>
    </td>

    <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
        <div
            class="flex items-center justify-end space-x-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button" onclick="openEditQuestionModal(this)" data-id="{{ $question->id }}"
                data-aspect="{{ $question->aspect_name }}" data-satisfaction="{{ $question->satisfaction_question }}"
                data-importance="{{ $question->importance_question }}" data-sortorder="{{ $question->sort_order }}"
                data-active="{{ $question->is_active ? 1 : 0 }}"
                class="p-1.5 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-md transition-colors"
                title="Ubah">
                <span class="material-icons-round text-lg">edit</span>
            </button>

            @if ($question->answers_count === 0)
                <button type="button" onclick="openDeleteQuestionModal(this)" data-id="{{ $question->id }}"
                    data-aspect="{{ $question->aspect_name }}"
                    class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                    title="Hapus">
                    <span class="material-icons-round text-lg">delete</span>
                </button>
            @else
                <span class="p-1.5 text-gray-300 dark:text-gray-600 cursor-not-allowed"
                    title="Tidak dapat dihapus — sudah memiliki {{ $question->answers_count }} jawaban survei">
                    <span class="material-icons-round text-lg">delete</span>
                </span>
            @endif
        </div>
    </td>
</tr>
