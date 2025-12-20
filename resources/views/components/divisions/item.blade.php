@props(['division', 'number'])

<tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors group">
    {{-- No --}}
    <td class="px-6 py-4 text-sm text-muted-light dark:text-muted-dark">
        {{ $number }}
    </td>

    {{-- Nama --}}
    <td class="px-6 py-4 text-sm font-medium text-text-light dark:text-text-dark">
        {{ $division->name }}
    </td>

    {{-- Aksi --}}
    <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
        <div
            class="flex items-center justify-end space-x-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
            {{-- Edit Button --}}
            <button type="button" onclick="openEditDivisionModal(this)" data-id="{{ $division->id }}"
                data-name="{{ $division->name }}"
                class="p-1.5 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-md transition-colors"
                title="Ubah">
                <span class="material-icons-round text-lg">edit</span>
            </button>

            {{-- Hapus Button --}}
            <button type="button" onclick="openDeleteDivisionModal(this)" data-id="{{ $division->id }}"
                data-name="{{ $division->name }}"
                class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                title="Hapus">
                <span class="material-icons-round text-lg">delete</span>
            </button>
        </div>
    </td>
</tr>
