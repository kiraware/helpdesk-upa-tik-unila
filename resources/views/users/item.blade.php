@props(['user', 'number'])

@php
    // Meniru logika avatar yang diberikan dari navbar
    $avatarUrl = $user->avatar_path
        ? asset('storage/' . $user->avatar_path)
        : 'https://ui-avatars.com/api/?name=' . urlencode($user->name);

    // Menentukan warna tag berdasarkan nilai Entitas
    $entityColor = match ($user->entity?->value) {
        'Mahasiswa' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300',
        'Dosen' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300',
        'Karyawan' => 'bg-cyan-100 text-cyan-800 dark:bg-cyan-900/30 dark:text-cyan-300',
        'Super User' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300',
        'Tamu' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-300',
        default
            => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300', // Untuk 'Lainnya' atau tidak dikenali
    };
@endphp

<tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors group">

    {{-- No --}}
    <td class="px-6 py-4 text-sm text-muted-light dark:text-muted-dark">
        {{ $number }}
    </td>

    {{-- Profil (Avatar + Nama + SSO) --}}
    <td class="px-6 py-4">
        <div class="flex items-center gap-3">
            <img src="{{ $avatarUrl }}" alt="{{ $user->name }}"
                class="w-10 h-10 rounded-full object-cover border border-border-light dark:border-slate-600 shadow-sm transition-all">
            <div class="flex flex-col">
                <span class="text-sm font-bold text-text-light dark:text-text-dark">{{ $user->name }}</span>
                <span class="text-xs text-muted-light dark:text-muted-dark">{{ $user->username_sso }}</span>
            </div>
        </div>
    </td>

    {{-- Kontak (Email + Phone) --}}
    <td class="px-6 py-4">
        <div class="flex flex-col gap-0.5">
            <div class="flex items-center gap-1.5 text-sm text-text-light dark:text-text-dark">
                <span class="material-icons-round text-[16px] text-muted-light dark:text-muted-dark">email</span>
                {{ $user->email }}
            </div>
            <div class="flex items-center gap-1.5 text-xs text-muted-light dark:text-muted-dark">
                <span class="material-icons-round text-[14px]">phone</span>
                {{ $user->phone ?? 'Belum diisi' }}
            </div>
        </div>
    </td>

    {{-- Identitas (NIP/NIK) --}}
    <td class="px-6 py-4 text-sm text-text-light dark:text-text-dark">
        {{ $user->identity_number ?? '-' }}
    </td>

    {{-- Entitas (Diubah menjadi Tag/Badge) --}}
    <td class="px-6 py-4">
        @if ($user->entity)
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $entityColor }}">
                {{ $user->entity->value }}
            </span>
        @else
            <span class="text-sm text-muted-light dark:text-muted-dark">-</span>
        @endif
    </td>

    {{-- Penanggung Jawab --}}
    <td class="px-6 py-4 text-sm text-text-light dark:text-text-dark">
        {{ $user->division?->name ?? '-' }}
    </td>

    {{-- Role --}}
    <td class="px-6 py-4">
        <span
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize
            {{ $user->role->value === 'superuser' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' }}">
            {{ $user->role->value }}
        </span>
    </td>

    {{-- Aksi --}}
    <td class="px-6 py-4 text-sm text-right whitespace-nowrap">
        <div
            class="flex items-center justify-end space-x-2 opacity-100 sm:opacity-0 group-hover:opacity-100 transition-opacity">
            <button type="button" onclick="openEditUserModal(this)" data-id="{{ $user->id }}"
                data-name="{{ $user->name }}" data-sso="{{ $user->username_sso }}" data-email="{{ $user->email }}"
                data-phone="{{ $user->phone }}" data-identity="{{ $user->identity_number }}"
                data-entity="{{ $user->entity?->value }}" data-role="{{ $user->role->value }}"
                data-division="{{ $user->division_id }}" data-division-name="{{ $user->division?->name }}"
                class="p-1.5 text-amber-500 hover:bg-amber-50 dark:hover:bg-amber-900/20 rounded-md transition-colors"
                title="Ubah">
                <span class="material-icons-round text-lg">edit</span>
            </button>
            <button type="button" onclick="openDeleteUserModal(this)" data-id="{{ $user->id }}"
                data-name="{{ $user->name }}"
                class="p-1.5 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition-colors"
                title="Hapus">
                <span class="material-icons-round text-lg">delete</span>
            </button>
        </div>
    </td>
</tr>
