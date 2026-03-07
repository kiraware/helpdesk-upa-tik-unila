<x-layouts.guest title="Formulir Buat Tiket">

    {{-- Container Utama --}}
    <div class="px-4 sm:px-0 sm:mx-auto sm:w-full sm:max-w-4xl my-4 sm:my-8" x-data="{ isMobile: window.innerWidth < 640 }"
        @resize.window="isMobile = window.innerWidth < 640">

        {{-- Header Halaman --}}
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center p-3 bg-white dark:bg-slate-800 rounded-xl shadow-sm mb-4 border border-slate-100 dark:border-slate-700">
                <span class="material-icons-round text-3xl text-blue-600 dark:text-blue-400">support_agent</span>
            </div>
            <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                Buat Tiket Baru
            </h2>
            <p class="mt-2 text-lg text-slate-600 dark:text-slate-400">
                Lengkapi data diri dan detail masalah Anda untuk diproses.
            </p>
        </div>

        {{-- Form Container --}}
        <div
            class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-800 overflow-hidden">

            <form id="ticketForm" action="{{ route('guest.tickets.store') }}" method="POST"
                enctype="multipart/form-data">
                @csrf

                {{-- SECTION 1: VERIFIKASI IDENTITAS --}}
                <div
                    class="p-6 md:p-8 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/30">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1 flex items-center gap-2">
                        <span class="material-icons-round text-blue-600 dark:text-blue-400 text-xl">verified_user</span>
                        <span>Verifikasi Identitas</span>
                    </h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">Data ini wajib diisi untuk validasi
                        pengajuan tiket Anda.</p>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- Nama Lengkap --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="full_name" value="{{ old('full_name') }}" required
                                class="w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400 text-sm md:text-base"
                                placeholder="Sesuai kartu identitas">
                            @error('full_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Email --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Email Aktif <span class="text-red-500">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                class="w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400 text-sm md:text-base"
                                placeholder="nama@email.com">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nomor WhatsApp --}}
                        <div>
                            <label for="phone"
                                class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1">
                                Nomor WhatsApp
                            </label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                                placeholder="Contoh: 081234567890 (Boleh dikosongkan)"
                                class="block w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white placeholder-slate-400 focus:border-blue-500 focus:ring-blue-500 transition-colors shadow-sm sm:text-sm"
                                inputmode="numeric" pattern="[0-9]*">
                            @error('phone')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Identity Number --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Nomor Identitas <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="identity_number" value="{{ old('identity_number') }}" required
                                class="w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400 text-sm md:text-base"
                                placeholder="Nomor KTM / NIP / NIK / SK">
                            @error('identity_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Fakultas / Unit Kerja --}}
                        <div x-data='{
                            open: false,
                            selected: "{{ old('department_id') }}",
                            listDepartment: @json($departments->keyBy('id')->map->name)
                        }'
                            class="relative">

                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Asal Fakultas / Unit Kerja <span class="text-red-500">*</span>
                            </label>

                            <input type="hidden" name="department_id" :value="selected" required>

                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-4 h-11 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">

                                <span class="flex items-center gap-2 truncate">
                                    <span class="material-icons-round text-base text-slate-400">apartment</span>
                                    <span
                                        x-text="selected && listDepartment[selected] ? listDepartment[selected] : 'Pilih Fakultas / Unit Kerja...'"></span>
                                </span>

                                <span class="material-icons-round text-slate-400 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>

                            {{-- Dropdown Menu --}}
                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" x-cloak @click.outside="open = false"
                                class="absolute z-30 mt-1 w-full rounded-xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-700 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md">

                                <div class="max-h-60 overflow-y-auto">
                                    @foreach ($departments as $dept)
                                        <button type="button" @click="selected='{{ $dept->id }}'; open=false"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-100/70 dark:hover:bg-slate-700/60 transition-colors {{ old('department_id') == $dept->id ? 'font-semibold text-blue-600 bg-blue-50/50' : '' }}">
                                            {{ $dept->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            @error('department_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Entity Type --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                @foreach (\App\Enums\IdentityType::cases() as $type)
                                    <label class="relative cursor-pointer group">
                                        <input type="radio" name="entity_type" value="{{ $type->value }}"
                                            class="peer sr-only"
                                            {{ old('entity_type') == $type->value ? 'checked' : '' }} required>

                                        <div
                                            class="flex items-center justify-center p-4 rounded-xl border-2 border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 transition-all duration-200 ease-in-out hover:border-blue-300 dark:hover:border-blue-700 peer-checked:border-blue-600 dark:peer-checked:border-blue-500 peer-checked:bg-blue-50 dark:peer-checked:bg-blue-900/20 peer-checked:text-blue-700 dark:peer-checked:text-blue-400 shadow-sm">
                                            <span
                                                class="font-bold text-sm tracking-wide">{{ ucfirst($type->value) }}</span>
                                            <span
                                                class="material-icons-round absolute top-3 right-3 text-blue-600 text-lg opacity-0 peer-checked:opacity-100 transition-opacity">check_circle</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('entity_type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Upload Foto Identitas --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Foto Kartu Identitas <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <input type="file" name="photo_identity" id="photo_identity" accept="image/*"
                                    class="hidden" onchange="previewFile(this, 'preview-identity', 'label-identity')"
                                    required>
                                <label for="photo_identity"
                                    class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-800 hover:border-blue-400 transition-all">

                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-slate-400 group-hover:text-blue-500 transition-colors px-4 text-center"
                                        id="label-identity">
                                        <span class="material-icons-round text-5xl mb-3">badge</span>
                                        <p class="text-sm font-medium">Klik untuk upload foto</p>
                                        <p class="text-xs text-slate-400 mt-1">
                                            KTM / ID Card / KTP / SK Pengangkatan harus terlihat jelas
                                        </p>
                                    </div>

                                    <img id="preview-identity"
                                        class="hidden h-full w-full object-contain rounded-xl p-2">
                                </label>
                            </div>
                            @error('photo_identity')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Upload Foto Selfie --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Selfie dengan Kartu Identitas <span class="text-red-500">*</span>
                            </label>
                            <div class="relative group">
                                <input type="file" name="photo_selfie" id="photo_selfie" accept="image/*"
                                    class="hidden" onchange="previewFile(this, 'preview-selfie', 'label-selfie')"
                                    required>
                                <label for="photo_selfie"
                                    class="flex flex-col items-center justify-center w-full h-64 border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-xl cursor-pointer bg-slate-50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-800 hover:border-blue-400 transition-all">

                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-slate-400 group-hover:text-blue-500 transition-colors px-4 text-center"
                                        id="label-selfie">
                                        <span class="material-icons-round text-5xl mb-3">camera_front</span>
                                        <p class="text-sm font-medium">Klik untuk upload selfie</p>
                                        <p class="text-xs text-slate-400 mt-1">Wajah & kartu harus jelas</p>
                                    </div>

                                    <img id="preview-selfie"
                                        class="hidden h-full w-full object-contain rounded-xl p-2">
                                </label>
                            </div>
                            @error('photo_selfie')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                {{-- SECTION 2: DETAIL PERMASALAHAN --}}
                <div class="p-6 md:p-8 space-y-6">
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1 flex items-center gap-2">
                        <span class="material-icons-round text-blue-600 dark:text-blue-400 text-xl">edit_note</span>
                        <span>Detail Permasalahan</span>
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Judul --}}
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Judul Laporan <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="title" value="{{ old('title') }}" required
                                class="w-full h-11 px-4 rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all placeholder:text-slate-400 text-sm md:text-base"
                                placeholder="Contoh: Lupa Password SSO">
                            @error('title')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Service --}}
                        <div x-data='{
                            open: false,
                            selected: "{{ old('service_id') }}",
                            listLayanan: @json($services->keyBy('id')->map->name)
                        }'
                            class="relative">

                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Jenis Layanan <span class="text-red-500">*</span>
                            </label>

                            <input type="hidden" name="service_id" :value="selected" required>

                            {{-- Button --}}
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-4 h-11 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">

                                <span class="flex items-center gap-2 truncate">
                                    <span class="material-icons-round text-base text-slate-400">dns</span>
                                    <span
                                        x-text="selected && listLayanan[selected] ? listLayanan[selected] : 'Pilih Layanan...'"></span>
                                </span>

                                <span class="material-icons-round text-slate-400 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>

                            {{-- Dropdown --}}
                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" x-cloak @click.outside="open = false"
                                class="absolute z-30 mt-1 w-full rounded-xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-700 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md">

                                <div class="max-h-60 overflow-y-auto">
                                    @foreach ($services as $service)
                                        <button type="button" @click="selected='{{ $service->id }}'; open=false"
                                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-100/70 dark:hover:bg-slate-700/60 transition-colors {{ old('service_id') == $service->id ? 'font-semibold text-blue-600 bg-blue-50/50' : '' }}">
                                            {{ $service->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            @error('service_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Priority --}}
                        <div x-data="{ open: false, selected: '{{ old('priority') }}' }" class="relative">
                            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                                Tingkat Urgensi <span class="text-red-500">*</span>
                            </label>

                            <input type="hidden" name="priority" :value="selected" required>

                            {{-- Button --}}
                            <button type="button" @click="open = !open"
                                class="w-full flex items-center justify-between px-4 h-11 border border-slate-300 dark:border-slate-700 rounded-lg bg-white dark:bg-slate-800 text-sm text-slate-700 dark:text-slate-200 shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700/50 transition-colors">

                                <span class="flex items-center gap-2 truncate">
                                    <span class="material-icons-round text-base text-slate-400">priority_high</span>
                                    <span
                                        x-text="selected ? selected.charAt(0).toUpperCase() + selected.slice(1) : 'Pilih Prioritas...'"></span>
                                </span>

                                <span class="material-icons-round text-slate-400 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''">expand_more</span>
                            </button>

                            {{-- Dropdown --}}
                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 scale-95"
                                x-transition:enter-end="opacity-100 scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 scale-100"
                                x-transition:leave-end="opacity-0 scale-95" x-cloak @click.outside="open = false"
                                class="absolute z-30 mt-1 w-full rounded-xl overflow-hidden shadow-xl border border-slate-200 dark:border-slate-700 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md">

                                @foreach (\App\Enums\TicketPriority::cases() as $priority)
                                    @php
                                        $color = match ($priority->value) {
                                            'high' => 'text-red-600',
                                            'medium' => 'text-yellow-600',
                                            'low' => 'text-slate-600',
                                        };
                                    @endphp

                                    <button type="button" @click="selected='{{ $priority->value }}'; open=false"
                                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-slate-100/70 dark:hover:bg-slate-700/60 transition-colors {{ $color }} {{ old('priority') === $priority->value ? 'font-semibold bg-slate-50' : '' }}">
                                        {{ ucfirst($priority->value) }}
                                    </button>
                                @endforeach
                            </div>

                            @error('priority')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    {{-- Editor --}}
                    <div>
                        @php
                            // KONFIGURASI FILE
                            $maxSizeKp = 5120; // 5MB dalam KB
                            $acceptedMimes =
                                'image/jpeg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/zip';
                            $readableFormat = 'JPG, PNG, PDF, DOC, DOCX, ZIP';
                        @endphp

                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2 pl-1">
                            Deskripsi Detail & Lampiran <span class="text-red-500">*</span>
                        </label>

                        <div id="editor-container"
                            class="border border-slate-300 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 overflow-hidden shadow-sm focus-within:ring-1 focus-within:ring-blue-500 focus-within:border-blue-500 transition-all">

                            <div class="px-4 py-2 bg-slate-50 dark:bg-slate-800/30">
                                <input id="x_description" type="hidden" name="description"
                                    value="{{ old('description') }}">

                                <trix-editor input="x_description"
                                    data-upload-url="{{ route('guest.upload.editor.trix') }}"
                                    data-max-size="{{ $maxSizeKp }}" data-accept="{{ $acceptedMimes }}"
                                    class="min-h-50 prose dark:prose-invert max-w-none bg-transparent border-none focus:outline-none px-0 pb-2"
                                    placeholder="Jelaskan kronologi dan detail masalah Anda..."></trix-editor>
                            </div>
                        </div>

                        <div class="flex items-start gap-2 mt-2 ml-1">
                            <span class="material-icons-round text-base text-blue-500 mt-0.5">info</span>

                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                <p class="font-medium text-slate-700 dark:text-slate-300 mb-0.5">
                                    Sisipkan file atau gambar dengan cara <span
                                        class="text-blue-600 dark:text-blue-400 font-bold">Drag & Drop</span> ke kolom
                                    editor.
                                </p>
                                <p>
                                    Max <strong>{{ $maxSizeKp / 1024 }}MB</strong>.
                                    Format: {{ $readableFormat }}.
                                </p>
                            </div>
                        </div>

                        @error('description')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Footer Buttons & Security --}}
                <div
                    class="p-6 md:px-8 md:py-6 bg-slate-50 dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800">
                    <div class="flex flex-col-reverse sm:flex-row items-center justify-between gap-6">

                        {{-- KIRI: Widget Captcha --}}
                        <div class="flex flex-col items-center sm:items-start w-full sm:w-auto">
                            <div class="cf-turnstile scale-90 sm:scale-100 origin-center sm:origin-top-left"
                                data-sitekey="{{ config('services.turnstile.key') }}" data-theme="auto"
                                data-size="flexible" data-callback="enableSubmitButton"
                                data-expired-callback="disableSubmitButton" data-error-callback="disableSubmitButton">
                            </div>
                            @error('cf-turnstile-response')
                                <p class="text-red-500 text-xs mt-1 text-center sm:text-left">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- KANAN: Tombol Submit --}}
                        <div class="w-full sm:w-auto flex justify-end">
                            <button type="submit" id="submitButton" disabled
                                class="w-full sm:w-auto flex items-center justify-center gap-2 rounded-lg h-11 px-10 bg-secondary text-white font-bold text-sm shadow-md shadow-blue-500/30 transition-all  hover:bg-blue-700 hover:-translate-y-0.5  disabled:opacity-50  disabled:cursor-not-allowed  disabled:transform-none  disabled:shadow-none  disabled:bg-blue-600 disabled:hover:bg-blue-600">
                                Kirim Tiket
                            </button>
                        </div>

                    </div>
                </div>

            </form>
        </div>
    </div>

    {{-- Script Preview & Validasi --}}
    <script>
        // Fungsi Callback Turnstile
        function enableSubmitButton() {
            const btn = document.getElementById('submitButton');
            if (btn) {
                btn.removeAttribute('disabled');
            }
        }

        function disableSubmitButton() {
            const btn = document.getElementById('submitButton');
            if (btn) {
                btn.setAttribute('disabled', 'disabled');
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            // Validasi Trix Editor
            const form = document.getElementById('ticketForm');
            const descriptionInput = document.getElementById('x_description');
            const editorContainer = document.getElementById('editor-container');
            const trixEditor = document.querySelector('trix-editor');

            if (form) {
                form.addEventListener('submit', (e) => {
                    const content = descriptionInput.value;
                    const cleanContent = content.replace(/<[^>]*>?/gm, '').trim();

                    if (cleanContent.length === 0) {
                        e.preventDefault();
                        trixEditor.focus();
                        editorContainer.classList.remove('border-slate-300', 'dark:border-slate-700');
                        editorContainer.classList.add('border-red-500', 'ring-1', 'ring-red-500');
                        alert('Mohon isi deskripsi detail permasalahan Anda.');
                    }
                });

                trixEditor.addEventListener('trix-change', () => {
                    editorContainer.classList.remove('border-red-500', 'ring-1', 'ring-red-500');
                    editorContainer.classList.add('border-slate-300', 'dark:border-slate-700');
                });
            }
        });

        function previewFile(input, imgId, labelId) {
            const preview = document.getElementById(imgId);
            const label = document.getElementById(labelId);
            const file = input.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    label.classList.add('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
    </script>

</x-layouts.guest>
