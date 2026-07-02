@props(['services'])

@php
    $oldServiceId = old('service_id', request('service_id'));
    $oldServiceName = $oldServiceId ? $services->find($oldServiceId)?->name ?? 'Pilih Layanan...' : 'Pilih Layanan...';
@endphp

<div class="bg-surface-light dark:bg-surface-dark p-6 rounded-xl shadow-sm border border-border-light dark:border-border-dark"
    x-data='{ 
        open: false, 
        selected: "{{ $oldServiceId }}", 
        label: "{{ $oldServiceName }}",
        listLayanan: @json($services->keyBy('id')->map(fn($s) => ['name' => $s->name, 'req' => $s->notes]))
    }'>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label class="block text-sm font-semibold text-text-light dark:text-text-dark mb-2">
                Jenis Layanan <span class="text-red-500">*</span>
            </label>
            <div class="relative w-full">
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

    <div x-show="selected && listLayanan[selected] && listLayanan[selected].req" x-cloak x-transition
        class="mt-6 p-3.5 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 text-sm text-blue-800 dark:text-blue-300">
        <div class="flex items-center gap-2 mb-1.5">
            <span class="material-icons-round text-blue-500 shrink-0">info</span>
            <p class="font-semibold">Catatan Layanan</p>
        </div>
        <p x-text="listLayanan[selected].req"
            class="whitespace-pre-line text-blue-700 dark:text-blue-400/90 break-words"></p>
    </div>
</div>
