<div>
    <label class="block text-sm font-semibold text-text-light dark:text-text-dark mb-2 pl-1">
        Deskripsi Detail <span class="text-red-500">*</span>
    </label>

    {{-- CONTAINER EDITOR --}}
    <div
        class="border border-border-light dark:border-border-dark
       rounded-xl bg-surface-light dark:bg-surface-dark
       overflow-hidden shadow-sm
       focus-within:ring-1 focus-within:ring-secondary
       focus-within:border-secondary transition-all">

        {{-- AREA EDITOR --}}
        <div class="px-4 py-2 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/30">

            <input id="x_description" type="hidden" name="description" value="{{ old('description') }}">

            <trix-editor input="x_description" data-upload-url="{{ route('comments.upload.editor.image') }}"
                class="min-h-[250px]
               prose dark:prose-invert max-w-none
               text-text-light dark:text-text-dark
               bg-transparent border-none focus:outline-none px-0"
                placeholder="Jelaskan kronologi masalah, lokasi tepat, dan detail lainnya... (Paste atau drag gambar di sini)">
            </trix-editor>
        </div>

        {{-- FOOTER / ACTIONS --}}
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

    <p class="text-xs text-muted-light mt-2 ml-1">
        * Gambar dapat langsung ditempel (paste) atau drag ke editor.
    </p>

    @error('description')
        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
