<x-layouts.dashboard title="Edit FAQ">

    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text-light dark:text-text-dark">
                Edit FAQ
            </h1>
            <p class="text-sm text-muted-light dark:text-muted-dark">
                Ubah informasi dan panduan layanan pada halaman publik FAQ.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('faq') }}"
                class="inline-flex justify-center items-center gap-2 px-4 py-2 bg-white dark:bg-slate-800 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 dark:focus:ring-gray-700">
                <span class="material-icons-round text-sm">visibility</span>
                Lihat Halaman Publik
            </a>
        </div>
    </div>

    <div
        class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden">
        <div
            class="px-4 sm:px-6 py-4 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/50">
            <h3 class="font-bold text-text-light dark:text-text-dark flex items-center gap-2">
                <span class="material-icons-round text-blue-500">article</span>
                Editor Konten FAQ
            </h3>
        </div>

        <div class="p-4 sm:p-6">
            <form action="{{ route('faqs.update') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <div>
                        @php
                            $maxSizeKp = 2048; // 2MB dalam KB
                            $acceptedMimes = 'image/jpeg,image/png,application/pdf';
                            $readableFormat = 'JPG, PNG, PDF';
                        @endphp

                        <div
                            class="border border-border-light dark:border-border-dark rounded-xl bg-surface-light dark:bg-surface-dark overflow-hidden shadow-sm focus-within:ring-1 focus-within:ring-secondary focus-within:border-secondary transition-all">

                            <div
                                class="px-3 sm:px-4 py-3 sm:py-4 border-b border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800/30">

                                <input id="x_description" type="hidden" name="description"
                                    value="{{ old('description', $faq->description) }}">

                                <trix-editor input="x_description"
                                    data-upload-url="{{ route('faqs.upload.attachment') }}"
                                    data-max-size="{{ $maxSizeKp }}" data-accept="{{ $acceptedMimes }}"
                                    class="prose prose-sm sm:prose-base dark:prose-invert max-w-none text-text-light dark:text-text-dark bg-transparent border-none focus:outline-none px-0 min-h-[350px] sm:min-h-[450px]"
                                    placeholder="Ketikkan panduan layanan atau informasi Helpdesk di sini...">
                                </trix-editor>
                            </div>

                            <div class="px-3 sm:px-4 py-3 bg-gray-50 dark:bg-slate-800/50 flex justify-end">
                                <button type="submit"
                                    class="w-full sm:w-auto px-6 py-2.5 flex items-center justify-center bg-secondary hover:bg-blue-600 text-white text-sm font-bold rounded-lg shadow-sm transition-colors whitespace-nowrap active:scale-95">
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>

                        <div class="flex items-start gap-2 mt-3 ml-1">
                            <span class="material-icons-round text-base text-blue-500 mt-0.5">info</span>
                            <div class="text-xs sm:text-sm text-slate-500 dark:text-slate-400">
                                <p class="font-medium text-slate-700 dark:text-slate-300 mb-0.5">
                                    Sisipkan gambar dengan cara <span
                                        class="text-blue-600 dark:text-blue-400 font-bold">Drag & Drop</span> ke area
                                    teks di atas.
                                </p>
                                <p>Max <strong>{{ $maxSizeKp / 1024 }}MB</strong>. Format: {{ $readableFormat }}.</p>
                            </div>
                        </div>

                        @error('description')
                            <p class="mt-2 text-xs text-red-500 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </form>
        </div>
    </div>

</x-layouts.dashboard>
