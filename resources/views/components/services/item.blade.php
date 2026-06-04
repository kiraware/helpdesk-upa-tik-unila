@props(['service', 'number'])

<tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors group">
    <td class="px-6 py-4 text-sm text-muted-light dark:text-muted-dark">
        {{ $number }}
    </td>

    <td class="px-6 py-4 text-sm font-medium text-text-light dark:text-text-dark">
        {{ $service->name }}
    </td>

    <td class="px-6 py-4 text-sm space-y-1 sm:space-y-0 sm:space-x-1">
        @if ($service->show_to_guest)
            <span
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                Tamu
            </span>
        @endif
        @if ($service->show_to_user)
            <span
                class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400">
                User
            </span>
        @endif
    </td>

    <td class="px-6 py-4 text-sm">
        @if ($service->is_active)
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800">
                Aktif
            </span>
        @else
            <span
                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">
                Non Aktif
            </span>
        @endif
    </td>

    <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
        <div
            class="flex items-center justify-end space-x-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button" onclick="openEditServiceModal(this)" data-id="{{ $service->id }}"
                data-name="{{ $service->name }}" data-active="{{ $service->is_active ? 1 : 0 }}"
                data-guest="{{ $service->show_to_guest ? 1 : 0 }}" data-user="{{ $service->show_to_user ? 1 : 0 }}"
                class="p-1.5 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-md transition-colors"
                title="Ubah">
                <span class="material-icons-round text-lg">edit</span>
            </button>

            <button type="button" onclick="openDeleteServiceModal(this)" data-id="{{ $service->id }}"
                data-name="{{ $service->name }}"
                class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                title="Hapus">
                <span class="material-icons-round text-lg">delete</span>
            </button>
        </div>
    </td>
</tr>
