<x-layouts.dashboard title="Profil Saya">
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8 space-y-8">

        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Profil Pengguna</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Kelola informasi profil akun Anda.</p>
        </div>

        <div
            class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
                class="p-6 md:p-8 space-y-8">
                @csrf
                @method('PATCH')

                <div x-data="{ photoName: null, photoPreview: null }"
                    class="flex flex-col sm:flex-row items-center gap-6 pb-6 border-b border-slate-100 dark:border-slate-800">

                    <input type="file" id="avatar" name="avatar" class="hidden" x-ref="photo" accept="image/*"
                        x-on:change="
                            photoName = $refs.photo.files[0].name;
                            const reader = new FileReader();
                            reader.onload = (e) => { photoPreview = e.target.result; };
                            reader.readAsDataURL($refs.photo.files[0]);
                        ">

                    <div @click="$refs.photo.click()"
                        class="relative h-28 w-28 rounded-full overflow-hidden border-4 border-white dark:border-slate-800 shadow-lg cursor-pointer group bg-slate-100 dark:bg-slate-800 shrink-0 transition-transform hover:scale-105">

                        <img x-show="!photoPreview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                            class="h-full w-full object-cover">

                        <img x-show="photoPreview" :src="photoPreview" class="h-full w-full object-cover"
                            style="display: none;">

                        <div
                            class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <span class="material-icons-round text-white text-3xl">photo_camera</span>
                        </div>
                    </div>

                    <div class="space-y-3 text-center sm:text-left w-full">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">Foto Profil</h3>
                            <p class="text-sm text-slate-500 dark:text-slate-400">Klik foto di samping untuk mengganti.
                                Maksimal 2MB (JPG, PNG).</p>
                        </div>

                        <div class="flex flex-wrap justify-center sm:justify-start gap-2">
                            @if ($user->avatar_path)
                                <button type="button" onclick="document.getElementById('delete-avatar-form').submit();"
                                    class="px-4 py-2 text-sm font-medium border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 hover:bg-red-50 hover:text-red-600 hover:border-red-200 dark:hover:bg-red-900/30 dark:hover:border-red-800 dark:hover:text-red-400 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 shadow-sm transition-colors">
                                    Hapus Foto
                                </button>
                            @endif
                        </div>

                        @error('avatar')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nama
                            Lengkap</label>
                        <input type="text" value="{{ $user->name }}" disabled
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 cursor-not-allowed sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Username
                            SSO</label>
                        <input type="text" value="{{ $user->username_sso }}" disabled
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 cursor-not-allowed sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Email</label>
                        <input type="email" value="{{ $user->email }}" disabled
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 cursor-not-allowed sm:text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nomor
                            Identitas (NIP/NPM)</label>
                        <input type="text" value="{{ $user->identity_number }}" disabled
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 cursor-not-allowed sm:text-sm">
                    </div>

                    <div>
                        <label
                            class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Entitas</label>
                        <input type="text" value="{{ $user->entity?->value ?? '-' }}" disabled
                            class="w-full h-11 px-4 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 cursor-not-allowed sm:text-sm">
                    </div>

                    <div>
                        <label for="phone"
                            class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Nomor WhatsApp /
                            Telepon</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone', $user->phone) }}"
                            placeholder="Contoh: 081234567890"
                            class="block w-full px-4 h-11 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:border-blue-500 focus:ring-blue-500 transition-colors shadow-sm sm:text-sm"
                            inputmode="numeric" pattern="[0-9]*">
                        @error('phone')
                            <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                        @enderror
                    </div>

                    @if ($isAdminOrSuperuser)
                        <div x-data='{
                            open: false,
                            selected: "{{ old('division_id', $user->division_id) }}",
                            listData: @json($divisions->keyBy('id')->map->name)
                        }'
                            class="relative">

                            <label
                                class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Penanggung
                                Jawab</label>

                            <input type="hidden" name="division_id" :value="selected">

                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-4 h-11 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 shadow-sm hover:bg-white dark:hover:bg-slate-700/50 focus:ring-2 focus:ring-blue-500 transition-colors">

                                <span class="flex items-center gap-2 truncate">
                                    <span class="material-icons-round text-base text-slate-400">business</span>
                                    <span
                                        x-text="selected && listData[selected] ? listData[selected] : 'Pilih Penanggung Jawab...'"></span>
                                </span>

                                <span class="material-icons-round text-slate-400 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>

                            <div x-show="open" x-transition.opacity.duration.200ms x-cloak
                                @click.outside="open = false"
                                class="absolute z-30 bottom-full mb-2 w-full rounded-xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                                <div class="max-h-60 overflow-y-auto py-1">
                                    <button type="button" @click="selected=''; open=false"
                                        class="w-full text-left px-4 py-2 text-sm text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">Kosongkan
                                        / Pilih Penanggung Jawab</button>
                                    @foreach ($divisions as $division)
                                        <button type="button" @click="selected='{{ $division->id }}'; open=false"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors {{ old('division_id', $user->division_id) == $division->id ? 'font-semibold text-blue-600 bg-blue-50/50' : '' }}">
                                            {{ $division->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            @error('division_id')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                    @if ($user->role === \App\Enums\UserRole::USER)
                        <div x-data='{
                            open: false,
                            selected: "{{ old('department_id', $user->department_id) }}",
                            listData: @json($departments->keyBy('id')->map->name)
                        }'
                            class="relative">

                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Fakultas
                                / Unit Kerja</label>

                            <input type="hidden" name="department_id" :value="selected">

                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-4 h-11 border border-slate-200 dark:border-slate-700 rounded-xl bg-slate-50 dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 shadow-sm hover:bg-white dark:hover:bg-slate-700/50 focus:ring-2 focus:ring-blue-500 transition-colors">

                                <span class="flex items-center gap-2 truncate">
                                    <span class="material-icons-round text-base text-slate-400">apartment</span>
                                    <span
                                        x-text="selected && listData[selected] ? listData[selected] : 'Pilih Fakultas / Unit Kerja...'"></span>
                                </span>

                                <span class="material-icons-round text-slate-400 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>

                            <div x-show="open" x-transition.opacity.duration.200ms x-cloak
                                @click.outside="open = false"
                                class="absolute z-30 bottom-full mb-2 w-full rounded-xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800">
                                <div class="max-h-60 overflow-y-auto py-1">
                                    <button type="button" @click="selected=''; open=false"
                                        class="w-full text-left px-4 py-2 text-sm text-slate-500 hover:bg-slate-100 dark:hover:bg-slate-700">--
                                        Kosongkan / Pilih Fakultas --</button>
                                    @foreach ($departments as $department)
                                        <button type="button" @click="selected='{{ $department->id }}'; open=false"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-50 dark:hover:bg-slate-700/60 transition-colors {{ old('department_id', $user->department_id) == $department->id ? 'font-semibold text-blue-600 bg-blue-50/50' : '' }}">
                                            {{ $department->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            @error('department_id')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                    @endif

                </div>

                <div class="flex justify-end pt-6 border-t border-slate-100 dark:border-slate-800">
                    <button type="submit"
                        class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-lg h-11 px-8 bg-blue-600 text-white font-bold text-sm shadow-md shadow-blue-500/30 transition-all hover:bg-blue-700 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900">
                        Simpan
                    </button>
                </div>
            </form>
        </div>

        <form id="delete-avatar-form" action="{{ route('profile.avatar.destroy') }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>

    </div>
</x-layouts.dashboard>
