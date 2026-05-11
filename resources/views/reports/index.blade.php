<x-layouts.dashboard title="Laporan & Statistik">

    <div class="space-y-6">

        {{-- HEADER & FILTER --}}
        <div
            class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 bg-surface-light dark:bg-surface-dark p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800">
            <div>
                <h1 class="text-2xl font-bold text-text-light dark:text-text-dark tracking-tight">Ringkasan Eksekutif
                </h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Pantau kinerja helpdesk, statistik tiket,
                    layanan, dan indeks kepuasan pengguna (CSI).</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 items-end w-full xl:w-auto">
                <form action="{{ route('reports.index') }}" method="GET"
                    class="flex flex-col sm:flex-row gap-3 items-end w-full" x-data>
                    {{-- Input Tanggal Awal --}}
                    <div class="relative w-full sm:w-40">
                        <label class="text-xs font-semibold text-gray-500 mb-1 block">Dari Tanggal</label>
                        <input x-ref="startDate" type="text" name="start_date"
                            value="{{ $startDate->format('Y-m-d') }}" onfocus="(this.type='date')"
                            onblur="(this.value ? this.type='date' : this.type='text')"
                            class="w-full pl-3 pr-10 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all shadow-sm cursor-pointer placeholder-gray-400 text-gray-900 dark:text-gray-100">
                        <span @click="$refs.startDate.type='date'; $refs.startDate.showPicker()"
                            class="absolute right-3 top-[34px] material-icons-round text-base text-gray-400 cursor-pointer hover:text-secondary transition-colors">calendar_today</span>
                    </div>

                    {{-- Input Tanggal Akhir --}}
                    <div class="relative w-full sm:w-40">
                        <label class="text-xs font-semibold text-gray-500 mb-1 block">Sampai Tanggal</label>
                        <input x-ref="endDate" type="text" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                            onfocus="(this.type='date')" onblur="(this.value ? this.type='date' : this.type='text')"
                            class="w-full pl-3 pr-10 py-2.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-slate-900 text-sm focus:ring-2 focus:ring-secondary focus:border-secondary outline-none transition-all shadow-sm cursor-pointer placeholder-gray-400 text-gray-900 dark:text-gray-100">
                        <span @click="$refs.endDate.type='date'; $refs.endDate.showPicker()"
                            class="absolute right-3 top-[34px] material-icons-round text-base text-gray-400 cursor-pointer hover:text-secondary transition-colors">event</span>
                    </div>

                    <button type="submit"
                        class="w-full sm:w-auto px-5 py-2.5 bg-secondary hover:brightness-110 text-white rounded-lg text-sm font-bold shadow-lg shadow-blue-500/30 transition-all active:scale-95 flex items-center justify-center gap-2 h-[42px]">
                        <span class="material-icons-round text-lg">filter_alt</span> Filter
                    </button>
                </form>

                <div class="h-10 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></div>

                <a href="{{ route('reports.export', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                    class="w-full sm:w-auto px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-bold shadow-lg shadow-emerald-500/30 transition-all active:scale-95 flex items-center justify-center gap-2 h-[42px]">
                    <span class="material-icons-round text-lg">file_download</span> Unduh Excel
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
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6"
            x-data='chartHandler(@json($dailyTrend), @json($statusDist), @json($chartData))'>

            {{-- Grafik Garis (Tren Harian) --}}
            <div
                class="xl:col-span-2 p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                <h3 class="font-bold text-lg text-text-light dark:text-text-dark mb-4">Tren Tiket Harian</h3>
                <div class="relative h-64 w-full"><canvas id="trendChart"></canvas></div>
            </div>

            {{-- Grafik Komposisi Status --}}
            <div
                class="p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                <h3 class="font-bold text-lg text-text-light dark:text-text-dark mb-4">Komposisi Status</h3>
                <div class="relative h-64 w-full flex items-center justify-center"><canvas id="statusChart"></canvas>
                </div>
            </div>

            {{-- Grafik Pie (Berdasarkan Entitas) --}}
            <div
                class="p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                <h3 class="font-bold text-lg text-text-light dark:text-text-dark mb-4">Entitas Pengguna</h3>
                <div class="relative h-64 w-full flex items-center justify-center"><canvas id="entityPieChart"></canvas>
                </div>
            </div>

            {{-- Grafik Bar (Berdasarkan Layanan) - Dibuat full width di bawahnya --}}
            <div
                class="xl:col-span-4 p-6 rounded-2xl bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 shadow-sm">
                <h3 class="font-bold text-lg text-text-light dark:text-text-dark mb-4">Tiket Berdasarkan Layanan</h3>
                <div class="relative h-75 w-full"><canvas id="serviceBarChart"></canvas></div>
            </div>
        </div>

        {{-- TABEL REKAP LAYANAN DAN ENTITAS --}}
        <div
            class="bg-surface-light dark:bg-surface-dark border border-gray-100 dark:border-gray-800 rounded-2xl overflow-hidden shadow-sm">
            <div class="p-6 border-b border-gray-100 dark:border-gray-800">
                <h3 class="font-bold text-lg text-text-light dark:text-text-dark">Rekapitulasi Layanan & Pengguna</h3>
                <p class="text-sm text-gray-500 mt-1">Distribusi penyelesaian tiket dan komposisi pembuat tiket (Tendik,
                    Dosen, Mahasiswa).</p>
            </div>
            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left whitespace-nowrap">
                    <thead
                        class="bg-gray-50 dark:bg-slate-800/50 text-gray-600 dark:text-gray-300 font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4 border-b border-gray-200 dark:border-gray-700" rowspan="2">No</th>
                            <th class="px-6 py-4 border-b border-gray-200 dark:border-gray-700" rowspan="2">Layanan
                            </th>
                            <th class="px-6 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                colspan="3">Status Penyelesaian</th>
                            <th class="px-6 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                colspan="4">Entitas Pembuat Tiket</th>
                        </tr>
                        <tr>
                            <th
                                class="px-6 py-2 border-b border-gray-200 dark:border-gray-700 text-center text-blue-600 bg-blue-50/50">
                                Total</th>
                            <th
                                class="px-6 py-2 border-b border-gray-200 dark:border-gray-700 text-center text-emerald-600 bg-emerald-50/50">
                                Done</th>
                            <th
                                class="px-6 py-2 border-b border-gray-200 dark:border-gray-700 text-center text-red-600 bg-red-50/50">
                                Reject</th>
                            <th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                title="Tenaga Kependidikan">Tendik (T)</th>
                            <th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                title="Dosen">Dosen (D)</th>
                            <th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-center"
                                title="Mahasiswa">Mhs (M)</th>
                            <th class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 text-center">Lainnya
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @php $no = 1; @endphp
                        @foreach ($serviceStats as $service)
                            <tr class="hover:bg-gray-50 dark:hover:bg-slate-800/50 transition-colors">
                                <td class="px-6 py-4">{{ $no++ }}</td>
                                <td class="px-6 py-4 font-semibold">{{ $service['name'] }}</td>
                                <td class="px-6 py-4 text-center font-bold text-blue-600">{{ $service['total'] }}</td>
                                <td class="px-6 py-4 text-center font-bold text-emerald-600">{{ $service['done'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-red-600">{{ $service['reject'] }}</td>
                                <td class="px-4 py-4 text-center">{{ $service['entities']['T'] }}</td>
                                <td class="px-4 py-4 text-center">{{ $service['entities']['D'] }}</td>
                                <td class="px-4 py-4 text-center">{{ $service['entities']['M'] }}</td>
                                <td class="px-4 py-4 text-center text-gray-400">{{ $service['entities']['L'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
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
                                            class="font-bold text-gray-900">{{ number_format($staff->rating_star, 2) }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $csiColor = match (true) {
                                            $staff->csi_score >= 80 => 'bg-green-100 text-green-800 border-green-200',
                                            $staff->csi_score >= 60 => 'bg-blue-100 text-blue-800 border-blue-200',
                                            $staff->csi_score >= 40
                                                => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            default => 'bg-red-100 text-red-800 border-red-200',
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
