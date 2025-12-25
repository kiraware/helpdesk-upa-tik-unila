@props(['services'])

<div
    class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Judul Tiket --}}
        <div class="md:col-span-2">
            <label for="title" class="block text-sm font-semibold text-text-light dark:text-text-dark mb-2">
                Judul Laporan <span class="text-red-500">*</span>
            </label>
            <input type="text" id="title" name="title" value="{{ old('title') }}" required
                placeholder="Contoh: Internet di Gedung A Lantai 2 Mati Total"
                class="w-full px-4 py-2.5 rounded-lg border border-border-light dark:border-border-dark bg-gray-50 dark:bg-slate-800 text-text-light dark:text-text-dark focus:ring-1 focus:ring-secondary focus:border-secondary transition-colors placeholder:text-sm shadow-sm">
            @error('title')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Layanan (Custom Dropdown) --}}
        <div>
            <label class="block text-sm font-semibold text-text-light dark:text-text-dark mb-2">
                Jenis Layanan <span class="text-red-500">*</span>
            </label>
            @php
                $oldServiceId = old('service_id');
                $oldServiceName = $oldServiceId
                    ? $services->find($oldServiceId)?->name ?? 'Pilih Layanan...'
                    : 'Pilih Layanan...';
            @endphp
            <div class="relative w-full" x-data="{ open: false, selected: '{{ $oldServiceId }}', label: '{{ $oldServiceName }}' }">
                <input type="hidden" name="service_id" x-model="selected">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-2.5 border border-border-light dark:border-border-dark rounded-lg bg-gray-50 dark:bg-slate-800 text-text-light dark:text-text-dark shadow-sm hover:bg-gray-100 dark:hover:bg-slate-700/50 transition-colors">
                    <span class="flex items-center gap-2 truncate text-sm"
                        :class="selected ? 'text-text-light dark:text-text-dark' : 'text-muted-light'">
                        <span class="material-icons-round text-base text-muted-light">dns</span>
                        <span x-text="label"></span>
                    </span>
                    <span class="material-icons-round text-base text-muted-light transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>
                <div x-show="open" x-transition x-cloak @click.outside="open = false"
                    class="absolute z-20 mt-1 w-full max-h-60 overflow-y-auto rounded-xl shadow-xl border border-border-light dark:border-slate-700 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md">
                    @foreach ($services as $service)
                        <button type="button"
                            @click="selected = '{{ $service->id }}'; label = '{{ $service->name }}'; open = false"
                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 transition-colors flex items-center justify-between group"
                            :class="selected == '{{ $service->id }}' ?
                                'font-semibold text-secondary bg-blue-50 dark:bg-blue-900/10' :
                                'text-text-light dark:text-text-dark'">
                            <span>{{ $service->name }}</span>
                            <span x-show="selected == '{{ $service->id }}'"
                                class="material-icons-round text-sm text-secondary">check</span>
                        </button>
                    @endforeach
                </div>
            </div>
            @error('service_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Prioritas (Custom Dropdown) --}}
        <div>
            <label class="block text-sm font-semibold text-text-light dark:text-text-dark mb-2">
                Tingkat Urgensi <span class="text-red-500">*</span>
            </label>
            @php
                $oldPriority = old('priority');
                $oldPriorityLabel = $oldPriority ? ucfirst($oldPriority) : 'Pilih Prioritas...';
            @endphp
            <div class="relative w-full" x-data="{ open: false, selected: '{{ $oldPriority }}', label: '{{ $oldPriorityLabel }}' }">
                <input type="hidden" name="priority" x-model="selected">
                <button type="button" @click="open = !open"
                    class="w-full flex items-center justify-between px-4 py-2.5 border border-border-light dark:border-border-dark rounded-lg bg-gray-50 dark:bg-slate-800 text-text-light dark:text-text-dark shadow-sm hover:bg-gray-100 dark:hover:bg-slate-700/50 transition-colors">
                    <span class="flex items-center gap-2 truncate text-sm"
                        :class="selected ? 'text-text-light dark:text-text-dark' : 'text-muted-light'">
                        <span class="material-icons-round text-base text-muted-light">priority_high</span>
                        <span x-text="label"></span>
                    </span>
                    <span class="material-icons-round text-base text-muted-light transition-transform duration-200"
                        :class="open ? 'rotate-180' : ''">expand_more</span>
                </button>
                <div x-show="open" x-transition x-cloak @click.outside="open = false"
                    class="absolute z-20 mt-1 w-full rounded-xl overflow-hidden shadow-xl border border-border-light dark:border-slate-700 bg-white/95 dark:bg-slate-800/95 backdrop-blur-md">
                    @foreach (\App\Enums\TicketPriority::cases() as $priority)
                        @php
                            $prioColor = match ($priority->value) {
                                'high' => 'text-red-600',
                                'medium' => 'text-yellow-600',
                                'low' => 'text-gray-600',
                                default => 'text-gray-600',
                            };
                        @endphp
                        <button type="button"
                            @click="selected = '{{ $priority->value }}'; label = '{{ ucfirst($priority->value) }}'; open = false"
                            class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-100/70 dark:hover:bg-slate-700/60 transition-colors flex items-center justify-between"
                            :class="selected == '{{ $priority->value }}' ?
                                'font-semibold {{ $prioColor }} bg-gray-50 dark:bg-slate-700/50' :
                                '{{ $prioColor }}'">
                            <span>{{ ucfirst($priority->value) }}</span>
                            <span x-show="selected == '{{ $priority->value }}'"
                                class="material-icons-round text-sm">check</span>
                        </button>
                    @endforeach
                </div>
            </div>
            @error('priority')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>
