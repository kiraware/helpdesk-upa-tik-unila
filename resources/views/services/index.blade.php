<x-layouts.dashboard title="Service Management">
    <thead class="bg-gray-100 text-left">
        <tr>
            <th class="px-4 py-3 text-sm font-semibold">#</th>
            <th class="px-4 py-3 text-sm font-semibold">Nama Service</th>
            <th class="px-4 py-3 text-sm font-semibold">Status</th>
            <th class="px-4 py-3 text-sm font-semibold text-right">Aksi</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($services as $service)
            <tr class="border-t hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">
                    {{ $loop->iteration + ($services->currentPage() - 1) * $services->perPage() }}
                </td>

                <td class="px-4 py-3">
                    {{ $service->name }}
                </td>

                <td class="px-4 py-3">
                    @if ($service->is_active)
                        <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-700">
                            Aktif
                        </span>
                    @else
                        <span class="px-2 py-1 text-xs rounded bg-red-100 text-red-700">
                            Nonaktif
                        </span>
                    @endif
                </td>

                <td class="px-4 py-3 text-right">
                    <div class="flex justify-end gap-2">
                        <a href="{{ route('services.edit', $service) }}"
                            class="px-3 py-1 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600">
                            Edit
                        </a>

                        <form action="{{ route('services.destroy', $service) }}" method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus service ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700">
                                Hapus
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                    Belum ada data service.
                </td>
            </tr>
        @endforelse
    </tbody>
    </table>
    </div>

    {{-- Pagination --}}
    <div class="mt-6">
        {{ $services->links() }}
    </div>

</x-layouts.dashboard>
