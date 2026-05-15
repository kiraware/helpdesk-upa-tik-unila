<x-layouts.dashboard title="Laporan & Statistik">

    <div class="space-y-6" x-data="{ activeTab: '{{ request('period', 'custom') }}' }">

        {{-- ================================================= --}}
        {{-- HEADER --}}
        {{-- ================================================= --}}
        <div
            class="bg-surface-light dark:bg-surface-dark p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
            <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-text-light dark:text-text-dark tracking-tight">
                        Ringkasan Eksekutif Helpdesk
                    </h1>
                    <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                        {{ $startDate->format('d F Y') }} &mdash; {{ $endDate->format('d F Y') }}
                        &nbsp;&bull;&nbsp;
                        <span class="font-medium text-blue-600 dark:text-blue-400">
                            {{ number_format($stats['total']) }} tiket
                        </span> dalam periode ini
                    </p>
                </div>

                {{-- EXPORT BUTTON --}}
                <a href="{{ route('reports.export', array_merge(request()->all(), ['period' => $period])) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold shadow-md transition-colors whitespace-nowrap">
                    <span class="material-icons-round text-lg">download</span>
                    Unduh Laporan Excel
                </a>
            </div>

            {{-- PERIOD TABS --}}
            <div class="flex flex-wrap gap-2 mt-5 pt-4 border-t border-gray-100 dark:border-gray-700">
                @foreach (['daily' => 'Hari Ini', 'weekly' => 'Minggu Ini', 'monthly' => 'Bulan Ini', 'yearly' => 'Tahun Ini', 'custom' => 'Kustom'] as $key => $label)
                    <a href="{{ route('reports.index', array_merge(request()->except(['period', 'start_date', 'end_date']), ['period' => $key])) }}"
                        class="px-4 py-1.5 rounded-lg text-sm font-semibold transition-colors
                           {{ $period === $key
                               ? 'bg-blue-600 text-white shadow'
                               : 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            {{-- CUSTOM DATE FILTER (shown only when period=custom) --}}
            @if ($period === 'custom')
                <form action="{{ route('reports.index') }}" method="GET"
                    class="flex flex-col sm:flex-row gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700"
                    x-data>
                    <input type="hidden" name="period" value="custom">
                    <div class="relative w-full sm:w-44">
                        <input x-ref="sd" type="text" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                            onfocus="(this.type='date')"
                            class="w-full pl-3 pr-10 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 text-sm">
                        <span @click="$refs.sd.showPicker()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 material-icons-round text-gray-400 cursor-pointer text-base">
                            calendar_today
                        </span>
                    </div>
                    <div class="relative w-full sm:w-44">
                        <input x-ref="ed" type="text" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                            onfocus="(this.type='date')"
                            class="w-full pl-3 pr-10 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 text-sm">
                        <span @click="$refs.ed.showPicker()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 material-icons-round text-gray-400 cursor-pointer text-base">
                            event
                        </span>
                    </div>
                    <button type="submit"
                        class="px-5 py-2.5 bg-secondary text-white rounded-lg text-sm font-bold shadow flex items-center gap-2">
                        <span class="material-icons-round text-base">filter_alt</span>
                        Terapkan Filter
                    </button>
                </form>
            @endif
        </div>

        {{-- ================================================= --}}
        {{-- STAT CARDS --}}
        {{-- ================================================= --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">

            @php
                $cards = [
                    [
                        'label' => 'Total Tiket',
                        'value' => number_format($stats['total']),
                        'icon' => 'confirmation_number',
                        'color' => 'blue',
                    ],
                    [
                        'label' => 'Selesai',
                        'value' => number_format($stats['done']),
                        'icon' => 'task_alt',
                        'color' => 'emerald',
                    ],
                    [
                        'label' => 'Menunggu',
                        'value' => number_format($stats['waiting']),
                        'icon' => 'schedule',
                        'color' => 'amber',
                    ],
                    [
                        'label' => 'Diproses',
                        'value' => number_format($stats['progress']),
                        'icon' => 'sync',
                        'color' => 'indigo',
                    ],
                    [
                        'label' => 'Ditolak',
                        'value' => number_format($stats['reject']),
                        'icon' => 'cancel',
                        'color' => 'red',
                    ],
                    [
                        'label' => 'Tingkat Selesai',
                        'value' => $stats['completion_rate'] . '%',
                        'icon' => 'percent',
                        'color' => 'violet',
                    ],
                ];
                $colorMap = [
                    'blue' => [
                        'bg' => 'bg-blue-600',
                        'light' => 'bg-blue-50 dark:bg-blue-900/20',
                        'text' => 'text-blue-600',
                    ],
                    'emerald' => [
                        'bg' => 'bg-emerald-600',
                        'light' => 'bg-emerald-50 dark:bg-emerald-900/20',
                        'text' => 'text-emerald-600',
                    ],
                    'amber' => [
                        'bg' => 'bg-amber-500',
                        'light' => 'bg-amber-50 dark:bg-amber-900/20',
                        'text' => 'text-amber-500',
                    ],
                    'indigo' => [
                        'bg' => 'bg-indigo-600',
                        'light' => 'bg-indigo-50 dark:bg-indigo-900/20',
                        'text' => 'text-indigo-600',
                    ],
                    'red' => [
                        'bg' => 'bg-red-500',
                        'light' => 'bg-red-50 dark:bg-red-900/20',
                        'text' => 'text-red-500',
                    ],
                    'violet' => [
                        'bg' => 'bg-violet-600',
                        'light' => 'bg-violet-50 dark:bg-violet-900/20',
                        'text' => 'text-violet-600',
                    ],
                ];
            @endphp

            @foreach ($cards as $card)
                @php $c = $colorMap[$card['color']]; @endphp
                <div
                    class="p-4 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col gap-3">
                    <div class="flex justify-between items-center">
                        <span
                            class="text-xs text-gray-500 dark:text-gray-400 font-medium leading-tight">{{ $card['label'] }}</span>
                        <div class="p-1.5 rounded-lg {{ $c['light'] }}">
                            <span
                                class="material-icons-round text-base {{ $c['text'] }}">{{ $card['icon'] }}</span>
                        </div>
                    </div>
                    <p class="text-2xl font-black text-text-light dark:text-text-dark">{{ $card['value'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- CSI & Avg Time row --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- CSI --}}
            <div
                class="p-5 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Indeks Kepuasan (CSI)</p>
                        <h3 class="text-3xl font-black text-text-light dark:text-text-dark mt-1">
                            {{ number_format($avgCSI, 2) }}<span class="text-lg text-gray-400 font-normal">%</span>
                        </h3>
                        @php
                            $badgeColor = match (true) {
                                $avgCSI >= 81 => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                $avgCSI >= 61 => 'bg-blue-100 text-blue-700 border-blue-200',
                                $avgCSI >= 41 => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                default => 'bg-red-100 text-red-700 border-red-200',
                            };
                        @endphp
                        <span
                            class="inline-block mt-2 px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $badgeColor }}">
                            {{ $csiPredicate }}
                        </span>
                    </div>
                    <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-yellow-500">
                        <span class="material-icons-round">sentiment_satisfied_alt</span>
                    </div>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mt-4 overflow-hidden">
                    <div class="h-full rounded-full {{ $avgCSI >= 61 ? 'bg-emerald-500' : ($avgCSI >= 41 ? 'bg-yellow-400' : 'bg-red-500') }}"
                        style="width: {{ min($avgCSI, 100) }}%"></div>
                </div>
            </div>

            {{-- Rata-rata Waktu --}}
            @php
                $validTimes = $staffPerformance->where('avg_resolution_time', '>', 0);
                $globalAvgTime = $validTimes->count() > 0 ? round($validTimes->avg('avg_resolution_time'), 1) : 0;
            @endphp
            <div
                class="p-5 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Rata-rata Waktu Selesai</p>
                        <h3 class="text-3xl font-black text-text-light dark:text-text-dark mt-1">
                            {{ $globalAvgTime }}<span class="text-sm font-bold text-gray-400 ml-1">Jam</span>
                        </h3>
                    </div>
                    <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-indigo-500">
                        <span class="material-icons-round">timer</span>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Dari tiket ditugaskan hingga diselesaikan.</p>
            </div>

            {{-- Reject Rate --}}
            <div
                class="p-5 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Tingkat Penolakan</p>
                        <h3 class="text-3xl font-black text-text-light dark:text-text-dark mt-1">
                            {{ $stats['total'] > 0 ? round(($stats['reject'] / $stats['total']) * 100, 1) : 0 }}%
                        </h3>
                    </div>
                    <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-lg text-red-500">
                        <span class="material-icons-round">block</span>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Persentase tiket ditolak oleh petugas.</p>
            </div>
        </div>

        {{-- ================================================= --}}
        {{-- CHART AREA --}}
        {{-- ================================================= --}}
        @php
            $entityColors = ['#3b82f6', '#8b5cf6', '#10b981', '#14b8a6', '#fb923c', '#facc15', '#9ca3af'];
            $chartData['entity_colors'] = $entityColors;
        @endphp

        <div class="space-y-6"
            x-data='chartHandler(
                 @json($dailyTrend),
                 @json($statusDist),
                 @json($chartData),
                 @json($durationStats),
                 @json($priorityStats)
             )'>

            {{-- ROW 1: Tren & Status --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div
                    class="lg:col-span-2 p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-lg">Tren Tiket Harian</h3>
                        <span class="text-xs text-gray-400">{{ $startDate->format('d M') }} —
                            {{ $endDate->format('d M Y') }}</span>
                    </div>
                    <div class="relative h-72">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>
                <div
                    class="p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-bold text-lg mb-2">Status Tiket</h3>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- ROW 2: Layanan & Entitas --}}
            <div class="grid grid-cols-1 gap-6">
                <div
                    class="p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-bold text-lg mb-4">Tiket per Layanan</h3>
                    <div class="overflow-y-auto" style="min-height: 260px;">
                        <canvas id="serviceBarChart"></canvas>
                    </div>
                </div>

                <div
                    class="p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-bold text-lg mb-1">Entitas Pengguna</h3>
                    <p class="text-xs text-gray-400 mb-4">Distribusi berdasarkan jenis pembuat tiket.</p>
                    @php
                        $entityMeta = [
                            [
                                'label' => 'Mahasiswa',
                                'sub' => 'Mahasiswa aktif',
                                'dot' => 'bg-blue-500',
                                'bar' => 'bg-blue-500',
                            ],
                            [
                                'label' => 'Dosen',
                                'sub' => 'Dosen / Pengajar',
                                'dot' => 'bg-violet-500',
                                'bar' => 'bg-violet-500',
                            ],
                            [
                                'label' => 'Tendik',
                                'sub' => 'Tenaga Kependidikan',
                                'dot' => 'bg-emerald-500',
                                'bar' => 'bg-emerald-500',
                            ],
                            [
                                'label' => 'Karyawan',
                                'sub' => 'Karyawan non-Tendik',
                                'dot' => 'bg-teal-500',
                                'bar' => 'bg-teal-500',
                            ],
                            [
                                'label' => 'Superuser',
                                'sub' => 'Admin sistem',
                                'dot' => 'bg-orange-400',
                                'bar' => 'bg-orange-400',
                            ],
                            [
                                'label' => 'Tamu',
                                'sub' => 'Pengguna tanpa akun',
                                'dot' => 'bg-yellow-400',
                                'bar' => 'bg-yellow-400',
                            ],
                            [
                                'label' => 'Lainnya',
                                'sub' => 'Entitas tidak terkategori',
                                'dot' => 'bg-gray-400',
                                'bar' => 'bg-gray-400',
                            ],
                        ];
                        $totalEnt = array_sum($chartData['entity_totals']) ?: 1;
                    @endphp
                    <div class="flex items-center gap-4">

                        {{-- Kolom kiri: entitas 0–2 (Mahasiswa, Dosen, Tendik) --}}
                        <div class="flex-1 space-y-3 min-w-0">
                            @foreach (array_slice($entityMeta, 0, 3) as $i => $leg)
                                @php
                                    $cnt = $chartData['entity_totals'][$i] ?? 0;
                                    $pct = round(($cnt / $totalEnt) * 100, 1);
                                @endphp
                                <div class="flex flex-col gap-0.5 min-w-0">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <div class="w-2 h-2 rounded-full shrink-0 {{ $leg['dot'] }}"></div>
                                        <span
                                            class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate">{{ $leg['label'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 pl-3.5">
                                        <div
                                            class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                            <div class="{{ $leg['bar'] }} h-full rounded-full"
                                                style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span
                                            class="text-[10px] font-bold text-gray-600 dark:text-gray-300 shrink-0">{{ $cnt }}</span>
                                        <span class="text-[10px] text-gray-400 shrink-0">{{ $pct }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Chart tengah --}}
                        <div class="shrink-0 w-40 h-40">
                            <canvas id="entityPieChart"></canvas>
                        </div>

                        <div class="flex-1 space-y-3 min-w-0">
                            @foreach (array_slice($entityMeta, 3) as $j => $leg)
                                @php
                                    $i = $j + 3;
                                    $cnt = $chartData['entity_totals'][$i] ?? 0;
                                    $pct = round(($cnt / $totalEnt) * 100, 1);
                                @endphp
                                <div class="flex flex-col gap-0.5 min-w-0">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <div class="w-2 h-2 rounded-full shrink-0 {{ $leg['dot'] }}"></div>
                                        <span
                                            class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate">{{ $leg['label'] }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 pl-3.5">
                                        <div
                                            class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                            <div class="{{ $leg['bar'] }} h-full rounded-full"
                                                style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span
                                            class="text-[10px] font-bold text-gray-600 dark:text-gray-300 shrink-0">{{ $cnt }}</span>
                                        <span class="text-[10px] text-gray-400 shrink-0">{{ $pct }}%</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>

            {{-- ROW 3: Tren Bulanan & Durasi Resolusi --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Tren Bulanan --}}
                <div
                    class="p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-bold text-lg mb-4">Tren Tiket Bulanan</h3>
                    <div class="h-64">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>

                {{-- Histogram Durasi --}}
                <div
                    class="p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-bold text-lg mb-4">Distribusi Durasi Penyelesaian</h3>
                    <div class="h-64">
                        <canvas id="durationChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- ROW 4: Prioritas --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div
                    class="p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-bold text-lg mb-4">Komposisi Prioritas</h3>
                    <div class="h-56 flex items-center justify-center">
                        <canvas id="priorityChart"></canvas>
                    </div>
                </div>

                {{-- Completion Rate per Layanan (mini table) --}}
                <div
                    class="lg:col-span-2 p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-bold text-lg mb-4">Tingkat Penyelesaian per Layanan</h3>
                    <div class="space-y-3">
                        @foreach ($serviceStats as $svc)
                            @php
                                $compRate = $svc['total'] > 0 ? round(($svc['done'] / $svc['total']) * 100, 1) : 0;
                                $barColor =
                                    $compRate >= 80
                                        ? 'bg-emerald-500'
                                        : ($compRate >= 50
                                            ? 'bg-blue-500'
                                            : 'bg-amber-500');
                            @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-600 dark:text-gray-300 w-36 truncate shrink-0"
                                    title="{{ $svc['name'] }}">
                                    {{ $svc['name'] }}
                                </span>
                                <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                                    <div class="{{ $barColor }} h-full rounded-full"
                                        style="width: {{ $compRate }}%"></div>
                                </div>
                                <div class="text-right shrink-0 w-32">
                                    <span
                                        class="text-xs font-bold text-gray-700 dark:text-gray-200">{{ $compRate }}%</span>
                                    <span
                                        class="text-[10px] text-gray-400 ml-1">({{ $svc['done'] }}/{{ $svc['total'] }})</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>{{-- end chart x-data --}}

        {{-- ================================================= --}}
        {{-- TABEL REKAP LAYANAN & ENTITAS --}}
        {{-- ================================================= --}}
        @php
            /**
             * Konfigurasi tampilan per status tiket — otomatis mengikuti TicketStatus enum.
             * Tambah/hapus case di enum → otomatis muncul/hilang di tabel.
             */
            $statusMeta = [
                'waiting' => ['label' => 'Waiting', 'color' => 'text-amber-500', 'bg' => 'bg-amber-50/40'],
                'progress' => ['label' => 'Progress', 'color' => 'text-blue-600', 'bg' => ''],
                'done' => ['label' => 'Done', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50/40'],
                'reject' => ['label' => 'Reject', 'color' => 'text-red-500', 'bg' => 'bg-red-50/40'],
            ];

            /**
             * Konfigurasi tampilan per entitas pengguna — otomatis mengikuti UserEntity enum.
             * Kolom abbreviasi & tooltip diatur di sini; nilai data dibaca dari $service['entities'][value].
             */
            $entityMeta = [
                'superuser' => ['abbr' => 'Superuser', 'title' => 'Superuser'],
                'mahasiswa' => ['abbr' => 'Mahasiswa', 'title' => 'Mahasiswa'],
                'dosen' => ['abbr' => 'Dosen', 'title' => 'Dosen'],
                'tendik' => ['abbr' => 'Tenaga Kependidikan', 'title' => 'Tenaga Kependidikan'],
                'karyawan' => ['abbr' => 'Karyawan', 'title' => 'Karyawan'],
                'tamu' => ['abbr' => 'Tamu', 'title' => 'Tamu'],
                'lainnya' => ['abbr' => 'Lainnya', 'title' => 'Lainnya'],
            ];

            // Hanya render status & entitas yang ada di enum (order enum = order kolom)
            $activeStatuses = collect($ticketStatuses)->filter(fn($s) => isset($statusMeta[$s->value]))->values();

            $activeEntities = collect($userEntities)->filter(fn($e) => isset($entityMeta[$e->value]))->values();

            $statusColspan = $activeStatuses->count() + 1; // +1 untuk kolom Total
            $entityColspan = $activeEntities->count();
        @endphp

        <div
            class="bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold text-lg text-text-light dark:text-text-dark">Rekapitulasi Layanan & Entitas
                    Pengguna</h3>
                <p class="text-sm text-gray-500 mt-1">Detail distribusi penyelesaian tiket dan rincian entitas pembuat
                    per layanan.</p>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead
                        class="bg-gray-50 dark:bg-slate-800/50 text-gray-600 dark:text-gray-300 font-bold tracking-wider text-xs">

                        {{-- ── Baris header atas: grup kolom ── --}}
                        <tr>
                            <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700" rowspan="2">No</th>
                            <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700" rowspan="2">Layanan
                            </th>

                            {{-- Status penyelesaian (dinamis dari TicketStatus) --}}
                            <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-center uppercase tracking-widest"
                                colspan="{{ $statusColspan }}">
                                Status Penyelesaian
                            </th>

                            {{-- Entitas (dinamis dari UserEntity) --}}
                            <th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-center uppercase tracking-widest bg-slate-50 dark:bg-slate-800"
                                colspan="{{ $entityColspan }}">
                                Entitas Pembuat Tiket
                            </th>

                            <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-center"
                                rowspan="2">% Total</th>
                            <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 text-center"
                                rowspan="2">Rate Selesai</th>
                        </tr>

                        {{-- ── Baris header bawah: label per kolom ── --}}
                        <tr>
                            {{-- Total selalu tampil pertama --}}
                            <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-center">
                                Total
                            </th>

                            {{-- Satu kolom per TicketStatus --}}
                            @foreach ($activeStatuses as $status)
                                @php $sm = $statusMeta[$status->value]; @endphp
                                <th
                                    class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-center {{ $sm['color'] }}">
                                    {{ $sm['label'] }}
                                </th>
                            @endforeach

                            {{-- Satu kolom per UserEntity --}}
                            @foreach ($activeEntities as $entity)
                                @php $em = $entityMeta[$entity->value]; @endphp
                                <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                    title="{{ $em['title'] }}">
                                    {{ $em['abbr'] }}
                                </th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                        @php
                            $no = 1;
                            $grandTotal = collect($serviceStats)->sum('total') ?: 1;
                        @endphp

                        @forelse ($serviceStats as $service)
                            @php
                                $cr =
                                    $service['total'] > 0 ? round(($service['done'] / $service['total']) * 100, 1) : 0;
                                $crColor =
                                    $cr >= 80 ? 'text-emerald-600' : ($cr >= 50 ? 'text-blue-600' : 'text-amber-600');
                                $sharePct = round(($service['total'] / $grandTotal) * 100, 1);
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-3 text-gray-400">{{ $no++ }}</td>
                                <td class="px-4 py-3 font-semibold text-text-light dark:text-text-dark">
                                    {{ $service['name'] }}</td>

                                {{-- Total --}}
                                <td class="px-3 py-3 text-center font-bold">{{ $service['total'] }}</td>

                                {{-- Nilai per status (dinamis) --}}
                                @foreach ($activeStatuses as $status)
                                    @php
                                        $sm = $statusMeta[$status->value];
                                        $val = $service['statuses'][$status->value] ?? ($service[$status->value] ?? 0);
                                    @endphp
                                    <td class="px-3 py-3 text-center font-bold {{ $sm['color'] }}">
                                        {{ $val }}</td>
                                @endforeach

                                {{-- Nilai per entitas (dinamis) --}}
                                @foreach ($activeEntities as $entity)
                                    <td class="px-3 py-3 text-center">
                                        {{ $service['entities'][$entity->value] ?? 0 }}
                                    </td>
                                @endforeach

                                <td class="px-3 py-3 text-center text-xs font-semibold text-gray-500">
                                    {{ $sharePct }}%</td>
                                <td class="px-3 py-3 text-center font-bold {{ $crColor }}">{{ $cr }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 4 + $activeStatuses->count() + $activeEntities->count() }}"
                                    class="px-6 py-10 text-center text-gray-400">
                                    <span class="material-icons-round text-4xl mb-2 opacity-40">inbox</span>
                                    <p>Tidak ada data layanan pada periode ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    {{-- ── FOOTER TOTAL ── --}}
                    @php
                        $totals = ['total' => collect($serviceStats)->sum('total')];

                        foreach ($activeStatuses as $status) {
                            $totals[$status->value] = collect($serviceStats)->sum(
                                fn($s) => $s['statuses'][$status->value] ?? ($s[$status->value] ?? 0),
                            );
                        }
                        foreach ($activeEntities as $entity) {
                            $totals[$entity->value] = collect($serviceStats)->sum(
                                fn($s) => $s['entities'][$entity->value] ?? 0,
                            );
                        }

                        $totalDone =
                            $totals['total'] > 0 ? round((($totals['done'] ?? 0) / $totals['total']) * 100, 1) : 0;
                    @endphp
                    <tfoot
                        class="bg-gray-100 dark:bg-slate-700/60 text-xs font-bold uppercase text-gray-600 dark:text-gray-300">
                        <tr>
                            <td class="px-4 py-3" colspan="2">Total Keseluruhan</td>

                            {{-- Total --}}
                            <td class="px-3 py-3 text-center">{{ $totals['total'] }}</td>

                            {{-- Total per status --}}
                            @foreach ($activeStatuses as $status)
                                @php $sm = $statusMeta[$status->value]; @endphp
                                <td class="px-3 py-3 text-center {{ $sm['color'] }}">
                                    {{ $totals[$status->value] ?? 0 }}
                                </td>
                            @endforeach

                            {{-- Total per entitas --}}
                            @foreach ($activeEntities as $entity)
                                <td class="px-3 py-3 text-center">{{ $totals[$entity->value] ?? 0 }}</td>
                            @endforeach

                            <td class="px-3 py-3 text-center">100%</td>
                            <td
                                class="px-3 py-3 text-center {{ $totalDone >= 80 ? 'text-emerald-600' : 'text-amber-600' }}">
                                {{ $totalDone }}%
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        {{-- ================================================= --}}
        {{-- TABEL TREN BULANAN --}}
        {{-- ================================================= --}}
        @if (count($monthlyTrendFlat) > 1)
            <div
                class="bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
                <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                    <h3 class="font-bold text-lg text-text-light dark:text-text-dark">Rekap Tiket per Bulan</h3>
                    <p class="text-sm text-gray-500 mt-1">Ringkasan jumlah tiket masuk dan penyelesaian per bulan dalam
                        periode terpilih.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead
                            class="bg-gray-50 dark:bg-slate-800/50 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-6 py-3">Bulan</th>
                                <th class="px-6 py-3 text-center">Total Tiket</th>
                                <th class="px-6 py-3 text-center">Selesai</th>
                                <th class="px-6 py-3 text-center">Ditolak</th>
                                <th class="px-6 py-3 text-center">Tingkat Selesai</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach ($monthlyTrendFlat as $label => $count)
                                <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50">
                                    <td class="px-6 py-3 font-semibold">{{ $label }}</td>
                                    <td class="px-6 py-3 text-center font-bold text-blue-600">{{ $count }}</td>
                                    <td class="px-6 py-3 text-center text-gray-400">—</td>
                                    <td class="px-6 py-3 text-center text-gray-400">—</td>
                                    <td class="px-6 py-3 text-center text-gray-400">—</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ================================================= --}}
        {{-- TABEL KINERJA PETUGAS --}}
        {{-- ================================================= --}}
        <div
            class="bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold text-lg text-text-light dark:text-text-dark">Papan Peringkat Kinerja Petugas</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Diurutkan berdasarkan skor kepuasan (CSI)
                    tertinggi.</p>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead
                        class="bg-gray-50 dark:bg-slate-800/50 text-gray-500 dark:text-gray-400 uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="px-4 py-4">Peringkat</th>
                            <th class="px-6 py-4">Nama Petugas</th>
                            <th class="px-6 py-4 text-center">Ditugaskan</th>
                            <th class="px-6 py-4 text-center">Selesai</th>
                            <th class="px-6 py-4 text-center">Rata-rata Waktu</th>
                            <th class="px-6 py-4 text-center w-1/5">Efektivitas</th>
                            <th class="px-6 py-4 text-center">Rating ⭐</th>
                            <th class="px-6 py-4 text-center">Skor CSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($staffPerformance as $idx => $staff)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-4 text-center">
                                    @if ($idx === 0)
                                        <span class="material-icons-round text-yellow-400 text-xl">emoji_events</span>
                                    @elseif ($idx === 1)
                                        <span class="text-gray-400 font-bold text-sm">#2</span>
                                    @elseif ($idx === 2)
                                        <span class="text-amber-700 font-bold text-sm">#3</span>
                                    @else
                                        <span class="text-gray-400 text-sm">#{{ $idx + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $staff->avatar ? asset('storage/' . $staff->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($staff->name) }}"
                                            class="w-10 h-10 rounded-full object-cover border-2 border-white dark:border-slate-700 shadow-sm">
                                        <div>
                                            <p class="font-bold text-text-light dark:text-text-dark">
                                                {{ $staff->name }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $staff->survey_count }} survei
                                                diterima</p>
                                            @if ($idx === 0 && $staff->csi_score > 80)
                                                <span
                                                    class="text-[10px] bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-bold border border-yellow-200 inline-flex items-center gap-1 mt-1">
                                                    Pelayanan Terbaik
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-gray-700 dark:text-gray-200">
                                    {{ $staff->assigned }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-bold text-emerald-600">{{ $staff->done }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-bold border border-indigo-100">
                                        {{ $staff->avg_resolution_time }}
                                        <span class="text-[10px] font-normal uppercase ml-0.5">Jam</span>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3 justify-center">
                                        <div class="w-24 bg-gray-200 rounded-full h-2">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                style="width: {{ $staff->rate }}%"></div>
                                        </div>
                                        <span class="text-xs font-bold w-10 text-right">{{ $staff->rate }}%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-1 text-yellow-500">
                                        <span class="material-icons-round text-sm">star</span>
                                        <span
                                            class="font-bold text-gray-900 dark:text-white">{{ number_format($staff->rating_star, 2) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $csiColor = match (true) {
                                            $staff->csi_score >= 80
                                                => 'bg-green-100 text-green-800 border-green-200 dark:bg-green-900/30 dark:text-green-400',
                                            $staff->csi_score >= 60
                                                => 'bg-blue-100 text-blue-800 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400',
                                            $staff->csi_score >= 40
                                                => 'bg-yellow-100 text-yellow-800 border-yellow-200 dark:bg-yellow-900/30 dark:text-yellow-400',
                                            default
                                                => 'bg-red-100 text-red-800 border-red-200 dark:bg-red-900/30 dark:text-red-400',
                                        };
                                    @endphp
                                    <span class="px-3 py-1 rounded-full text-sm font-bold border {{ $csiColor }}">
                                        {{ number_format($staff->csi_score, 2) }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                    <span class="material-icons-round text-4xl mb-2 opacity-50">search_off</span>
                                    <p>Tidak ada data kinerja petugas pada periode ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-layouts.dashboard>
