<x-layouts.dashboard title="Laporan & Statistik">

    <div class="space-y-6">

        {{-- HEADER & FILTER --}}
        <div
            class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 bg-surface-light dark:bg-surface-dark p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">

            {{-- Kiri --}}
            <div>
                <h1 class="text-2xl font-bold text-text-light dark:text-text-dark tracking-tight">
                    Ringkasan Eksekutif
                </h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
                    Pantau kinerja helpdesk, statistik tiket, layanan, dan indeks kepuasan pengguna (CSI).
                </p>
            </div>

            {{-- Kanan --}}
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto">

                {{-- FILTER --}}
                <form action="{{ route('reports.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full"
                    x-data>

                    <div class="relative w-full sm:w-40">
                        <input x-ref="startDate" type="text" name="start_date"
                            value="{{ $startDate->format('Y-m-d') }}" onfocus="(this.type='date')"
                            class="w-full pl-3 pr-10 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 text-sm shadow-sm">
                        <span @click="$refs.startDate.showPicker()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 material-icons-round text-gray-400 cursor-pointer">
                            calendar_today
                        </span>
                    </div>

                    <div class="relative w-full sm:w-40">
                        <input x-ref="endDate" type="text" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                            onfocus="(this.type='date')"
                            class="w-full pl-3 pr-10 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 text-sm shadow-sm">
                        <span @click="$refs.endDate.showPicker()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 material-icons-round text-gray-400 cursor-pointer">
                            event
                        </span>
                    </div>

                    <button type="submit"
                        class="px-4 py-2.5 bg-secondary text-white rounded-lg text-sm font-bold shadow-md flex items-center gap-2">
                        <span class="material-icons-round text-lg">filter_alt</span>
                        Filter
                    </button>
                </form>

                {{-- EXPORT --}}
                <a href="{{ route('reports.export', request()->all()) }}"
                    class="px-4 py-2.5 bg-emerald-600 text-white rounded-lg text-sm font-bold shadow-md flex items-center gap-2 justify-center">
                    <span class="material-icons-round text-lg">file_download</span>
                    Export
                </a>

            </div>
        </div>

        {{-- 4 KARTU STATISTIK UTAMA (Sama Seperti Sebelumnya) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Kartu 1: Total Tiket --}}
            <div
                class="p-5 rounded-2xl bg-linear-to-br from-blue-600 to-blue-800 text-white shadow-xl shadow-blue-900/20 relative overflow-hidden group">
                <div
                    class="absolute right-0 top-0 p-4 opacity-10 group-hover:scale-110 transition-transform duration-500">
                    <span class="material-icons-round text-6xl">analytics</span>
                </div>
                <p class="text-blue-100 text-sm font-medium">Total Tiket Masuk</p>
                <div class="flex items-end gap-2 mt-1">
                    <h3 class="text-3xl font-black">{{ number_format($stats['total']) }}</h3>
                    <span
                        class="text-xs bg-white/20 px-2 py-0.5 rounded text-white mb-1.5 backdrop-blur-sm">{{ $stats['completion_rate'] }}%
                        Selesai</span>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-y-1 gap-x-2 text-xs text-blue-100 border-t border-white/10 pt-3">
                    <div>Wait: <span class="font-bold text-white">{{ $stats['waiting'] }}</span></div>
                    <div>Prog: <span class="font-bold text-white">{{ $stats['progress'] }}</span></div>
                    <div>Done: <span class="font-bold text-white">{{ $stats['done'] }}</span></div>
                    <div>Reject: <span class="font-bold text-white">{{ $stats['reject'] }}</span></div>
                </div>
            </div>

            {{-- Kartu 2: CSI --}}
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
                            class="inline-block mt-2 px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $badgeColor }}">{{ $csiPredicate }}</span>
                    </div>
                    <div class="p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg text-yellow-500"><span
                            class="material-icons-round">sentiment_satisfied_alt</span></div>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2 mt-4 overflow-hidden">
                    <div class="h-full rounded-full {{ $avgCSI >= 61 ? 'bg-emerald-500' : ($avgCSI >= 41 ? 'bg-yellow-400' : 'bg-red-500') }}"
                        style="width: {{ $avgCSI }}%"></div>
                </div>
            </div>

            {{-- Kartu 3: Rata-rata Waktu --}}
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
                    <div class="p-2 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg text-indigo-500"><span
                            class="material-icons-round">timer</span></div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Durasi rata-rata dari tiket ditugaskan hingga selesai.</p>
            </div>

            {{-- Kartu 4: Reject Rate --}}
            <div
                class="p-5 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm flex flex-col justify-between">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-gray-500 dark:text-gray-400 text-sm font-medium">Tingkat Penolakan</p>
                        <h3 class="text-3xl font-black text-text-light dark:text-text-dark mt-1">
                            {{ $stats['total'] > 0 ? round(($stats['reject'] / $stats['total']) * 100, 1) : 0 }}%
                        </h3>
                    </div>
                    <div class="p-2 bg-red-50 dark:bg-red-900/20 rounded-lg text-red-500"><span
                            class="material-icons-round">block</span></div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Persentase tiket yang ditolak oleh petugas.</p>
            </div>
        </div>

        {{-- AREA GRAFIK --}}
        @php
            // Warna entitas — sumber tunggal untuk chart JS dan legend Blade
            $entityColors = ['#3b82f6', '#8b5cf6', '#10b981', '#14b8a6', '#fb923c', '#facc15', '#9ca3af'];
            $chartData['entity_colors'] = $entityColors;
        @endphp
        <div class="space-y-6"
            x-data='chartHandler(@json($dailyTrend), @json($statusDist), @json($chartData))'>

            {{-- ROW 1 --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Tren --}}
                <div
                    class="lg:col-span-2 p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-bold text-lg mb-6">Tren Tiket Harian</h3>
                    <div class="relative h-80">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                {{-- Status --}}
                <div
                    class="p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-bold text-lg mb-2">Komposisi Status</h3>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

            </div>

            {{-- ROW 2 --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                {{-- Entitas --}}
                <div
                    class="p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-bold text-lg mb-1">Entitas Pengguna</h3>
                    <p class="text-xs text-gray-400 mb-4">Distribusi tiket berdasarkan seluruh entitas pembuat.</p>
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
                                'sub' => 'Pengguna tanpa akun terdaftar',
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
                    <div class="flex flex-col sm:flex-row items-center gap-6">
                        {{-- Pie --}}
                        <div class="w-44 h-44 flex-shrink-0">
                            <canvas id="entityPieChart"></canvas>
                        </div>
                        {{-- Legend --}}
                        <div class="flex-1 w-full space-y-2">
                            @foreach ($entityMeta as $i => $leg)
                                @php
                                    $cnt = $chartData['entity_totals'][$i] ?? 0;
                                    $pct = round(($cnt / $totalEnt) * 100, 1);
                                @endphp
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full flex-shrink-0 {{ $leg['dot'] }}"></span>
                                    <div class="w-20 flex-shrink-0">
                                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-200 leading-tight">
                                            {{ $leg['label'] }}</p>
                                        <p class="text-[10px] text-gray-400 leading-tight">{{ $leg['sub'] }}</p>
                                    </div>
                                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-1.5 overflow-hidden">
                                        <div class="{{ $leg['bar'] }} h-full rounded-full"
                                            style="width: {{ $pct }}%"></div>
                                    </div>
                                    <span
                                        class="text-xs font-bold text-gray-700 dark:text-gray-200 w-6 text-right flex-shrink-0">{{ $cnt }}</span>
                                    <span
                                        class="text-[10px] text-gray-400 w-10 text-right flex-shrink-0">{{ $pct }}%</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Service --}}
                <div
                    class="p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                    <h3 class="font-bold text-lg mb-4">Tiket Berdasarkan Layanan</h3>
                    <div class="h-72">
                        <canvas id="serviceBarChart"></canvas>
                    </div>
                </div>

            </div>

        </div>

        {{-- TABEL REKAP LAYANAN DAN ENTITAS --}}
        <div
            class="bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold text-lg text-text-light dark:text-text-dark">Rekapitulasi Layanan & Pengguna</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Distribusi penyelesaian tiket dan rincian entitas pembuat tiket per layanan.
                </p>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead
                        class="bg-gray-50 dark:bg-slate-800/50 text-gray-600 dark:text-gray-300 font-bold tracking-wider text-xs">
                        <tr>
                            <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700" rowspan="2">No</th>
                            <th class="px-4 py-3 border-b border-gray-200 dark:border-gray-700" rowspan="2">Layanan
                            </th>
                            {{-- Status group --}}
                            <th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-center uppercase tracking-widest"
                                colspan="3">
                                Status Penyelesaian
                            </th>
                            {{-- Entitas group --}}
                            <th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-center uppercase tracking-widest bg-slate-50 dark:bg-slate-800"
                                colspan="7">
                                Entitas Pembuat Tiket
                            </th>
                        </tr>
                        <tr>
                            <th
                                class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-center text-blue-600 bg-blue-50/50">
                                Total</th>
                            <th
                                class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-center text-emerald-600 bg-emerald-50/50">
                                Done</th>
                            <th
                                class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-center text-red-500 bg-red-50/50">
                                Reject</th>
                            <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                title="Mahasiswa">Mhs</th>
                            <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                title="Dosen">Dosen</th>
                            <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                title="Tenaga Kependidikan">Tendik</th>
                            <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                title="Karyawan">Kary.</th>
                            <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                title="Superuser / Admin Sistem">S.User</th>
                            <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                title="Tamu (pengguna tanpa akun)">Tamu</th>
                            <th class="px-3 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                title="Entitas lain / tidak terkategori">Lain.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-sm">
                        @php $no = 1; @endphp
                        @forelse ($serviceStats as $service)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-4 py-3 text-gray-400">{{ $no++ }}</td>
                                <td class="px-4 py-3 font-semibold text-text-light dark:text-text-dark">
                                    {{ $service['name'] }}</td>
                                <td class="px-4 py-3 text-center font-bold text-blue-600">{{ $service['total'] }}</td>
                                <td class="px-4 py-3 text-center font-bold text-emerald-600">{{ $service['done'] }}
                                </td>
                                <td class="px-4 py-3 text-center font-bold text-red-500">{{ $service['reject'] }}</td>
                                <td class="px-3 py-3 text-center">{{ $service['entities']['mahasiswa'] }}</td>
                                <td class="px-3 py-3 text-center">{{ $service['entities']['dosen'] }}</td>
                                <td class="px-3 py-3 text-center">{{ $service['entities']['tendik'] }}</td>
                                <td class="px-3 py-3 text-center">{{ $service['entities']['karyawan'] }}</td>
                                <td class="px-3 py-3 text-center">{{ $service['entities']['superuser'] }}</td>
                                <td class="px-3 py-3 text-center">{{ $service['entities']['tamu'] }}</td>
                                <td class="px-3 py-3 text-center">{{ $service['entities']['lainnya'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-6 py-10 text-center text-gray-400">
                                    <span class="material-icons-round text-4xl mb-2 opacity-40">inbox</span>
                                    <p>Tidak ada data layanan pada periode ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    {{-- FOOTER TOTAL --}}
                    @php
                        $totals = [
                            'total' => collect($serviceStats)->sum('total'),
                            'done' => collect($serviceStats)->sum('done'),
                            'reject' => collect($serviceStats)->sum('reject'),
                            'mahasiswa' => collect($serviceStats)->sum(fn($s) => $s['entities']['mahasiswa']),
                            'dosen' => collect($serviceStats)->sum(fn($s) => $s['entities']['dosen']),
                            'tendik' => collect($serviceStats)->sum(fn($s) => $s['entities']['tendik']),
                            'karyawan' => collect($serviceStats)->sum(fn($s) => $s['entities']['karyawan']),
                            'superuser' => collect($serviceStats)->sum(fn($s) => $s['entities']['superuser']),
                            'tamu' => collect($serviceStats)->sum(fn($s) => $s['entities']['tamu']),
                            'lainnya' => collect($serviceStats)->sum(fn($s) => $s['entities']['lainnya']),
                        ];
                    @endphp
                    <tfoot
                        class="bg-gray-100 dark:bg-slate-700/60 text-xs font-bold uppercase text-gray-600 dark:text-gray-300">
                        <tr>
                            <td class="px-4 py-3" colspan="2">Total Keseluruhan</td>
                            <td class="px-4 py-3 text-center text-blue-600">{{ $totals['total'] }}</td>
                            <td class="px-4 py-3 text-center text-emerald-600">{{ $totals['done'] }}</td>
                            <td class="px-4 py-3 text-center text-red-500">{{ $totals['reject'] }}</td>
                            <td class="px-3 py-3 text-center">{{ $totals['mahasiswa'] }}</td>
                            <td class="px-3 py-3 text-center">{{ $totals['dosen'] }}</td>
                            <td class="px-3 py-3 text-center">{{ $totals['tendik'] }}</td>
                            <td class="px-3 py-3 text-center">{{ $totals['karyawan'] }}</td>
                            <td class="px-3 py-3 text-center">{{ $totals['superuser'] }}</td>
                            <td class="px-3 py-3 text-center">{{ $totals['tamu'] }}</td>
                            <td class="px-3 py-3 text-center">{{ $totals['lainnya'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- TABEL KINERJA PETUGAS --}}
        <div
            class="bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold text-lg text-text-light dark:text-text-dark">Papan Peringkat Kinerja Staf</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Diurutkan berdasarkan skor kepuasan (CSI)
                    tertinggi.</p>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead
                        class="bg-gray-50 dark:bg-slate-800/50 text-gray-500 dark:text-gray-400 uppercase text-xs font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Nama Petugas</th>
                            <th class="px-6 py-4 text-center">Tiket Selesai</th>
                            <th class="px-6 py-4 text-center">Rata-rata Waktu</th>
                            <th class="px-6 py-4 text-center w-1/5">Efektivitas</th>
                            <th class="px-6 py-4 text-center">Rating Bintang (1-5)</th>
                            <th class="px-6 py-4 text-center">Skor CSI (0-100)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($staffPerformance as $staff)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <img src="{{ $staff->avatar ? asset('storage/' . $staff->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($staff->name) }}"
                                            class="w-10 h-10 rounded-full object-cover border-2 border-white dark:border-slate-700 shadow-sm">
                                        <div>
                                            <p class="font-bold text-text-light dark:text-text-dark">
                                                {{ $staff->name }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $staff->survey_count }} survei
                                                diterima</p>
                                            @if ($loop->first && $staff->csi_score > 80)
                                                <span
                                                    class="text-[10px] bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-bold border border-yellow-200 inline-flex items-center gap-1 mt-1">
                                                    <span class="material-icons-round text-[10px]">emoji_events</span>
                                                    Pelayanan Terbaik
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-bold text-emerald-600">{{ $staff->done }}</span>
                                    <span class="text-xs text-gray-400">/ {{ $staff->assigned }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-indigo-50 text-indigo-700 font-bold border border-indigo-100">
                                        {{ $staff->avg_resolution_time }} <span
                                            class="text-[10px] font-normal uppercase ml-0.5">Jam</span>
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
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
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
