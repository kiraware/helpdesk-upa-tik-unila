@props(['department', 'number'])

<tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors group">
    <td class="px-6 py-4 text-sm text-muted-light dark:text-muted-dark">
        {{ $number }}
    </td>

    <td class="px-6 py-4 text-sm font-medium text-text-light dark:text-text-dark">
        {{ $department->name }}
    </td>

    <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
        <div
            class="flex items-center justify-end space-x-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button" onclick="openEditDepartmentModal(this)" data-id="{{ $department->id }}"
                data-name="{{ $department->name }}"
                class="p-1.5 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-md transition-colors"
                title="Ubah">
                <span class="material-icons-round text-lg">edit</span>
            </button>

            <button type="button" onclick="openDeleteDepartmentModal(this)" data-id="{{ $department->id }}"
                data-name="{{ $department->name }}"
                class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                title="Hapus">
                <span class="material-icons-round text-lg">delete</span>
            </button>
        </div>
    </td>
</tr>
