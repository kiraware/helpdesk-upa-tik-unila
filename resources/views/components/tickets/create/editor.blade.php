<div>
    @php
        $maxSizeKp = 2048; // 2MB dalam KB
        $acceptedMimes = 'image/jpeg,image/png,application/pdf';
        $readableFormat = 'JPG, PNG, PDF';
    @endphp

    <label class="block text-sm font-semibold text-text-light dark:text-text-dark mb-2 pl-1">
        Deskripsi Detail <span class="text-red-500">*</span>
    </label>

    <div
        class="border border-border-light dark:border-border-dark
        rounded-xl bg-surface-light dark:bg-surface-dark
        overflow-hidden shadow-sm
        focus-within:ring-1 focus-within:ring-secondary
        focus-within:border-secondary transition-all">

        <div class="px-4 py-2 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/30">

            <input id="x_description" type="hidden" name="description" value="{{ old('description') }}">

            <trix-editor input="x_description" data-upload-url="{{ route('tickets.upload.attachment') }}"
                data-max-size="{{ $maxSizeKp }}" data-accept="{{ $acceptedMimes }}"
                class="prose dark:prose-invert max-w-none text-text-light dark:text-text-dark bg-transparent w-full max-w-full overflow-x-hidden break-words [word-break:break-word]"
                placeholder="Jelaskan kronologi dan detail masalah Anda...">
            </trix-editor>
        </div>

        <div class="px-3 py-2 bg-gray-50 dark:bg-slate-800/50 flex justify-end">
            <button type="submit"
                class="px-3 py-1.5 sm:px-4
                bg-secondary hover:bg-blue-600
                text-white text-xs sm:text-sm
                font-medium rounded-lg shadow-sm
                transition-colors whitespace-nowrap">
                Kirim Tiket
            </button>
        </div>
    </div>

    <div class="flex items-start gap-2 mt-2 ml-1">
        <span class="material-icons-round text-base text-blue-500 mt-0.5">info</span>

        <div class="text-xs text-slate-500 dark:text-slate-400">
            <p class="font-medium text-slate-700 dark:text-slate-300 mb-0.5">
                Sisipkan file atau gambar dengan cara <span class="text-blue-600 dark:text-blue-400 font-bold">Drag &
                    Drop</span> ke kolom editor.
            </p>
            <p>
                Max <strong>{{ $maxSizeKp / 1024 }}MB</strong>.
                Format: {{ $readableFormat }}. Min <strong>20 Karakter</strong>.
            </p>
        </div>
    </div>

    @error('description')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
