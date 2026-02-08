@props(['ticket'])

@php
    $survey = $ticket->survey;
    $questions = \App\Models\SurveyQuestion::active()->get();

    // 1. Ambil User yang sedang login (bisa null jika Guest)
    $currentUser = auth()->user();

    // 2. Cek apakah yang login adalah USER BIASA (Internal User)
    // Jika ya, dia boleh mengisi jika tiket ini miliknya.
    $isUserRole = $currentUser && $currentUser->role === \App\Enums\UserRole::USER;

    // 3. Cek apakah yang login adalah STAFF (Admin/Superuser)
    // Staff TIDAK BOLEH mengisi survei apapun.
    $isStaffRole =
        $currentUser && in_array($currentUser->role, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::SUPERUSER]);

    // 4. Cek apakah ini Tiket Guest (User ID Null)
    $isGuestTicket = is_null($ticket->user_id);

    // 5. Validasi Pengisi Survei Guest:
    // Form Guest hanya boleh muncul jika TIDAK ADA user yang login.
    // (Asumsi: Guest mengakses via link publik tanpa login).
    // Jika Admin login dan buka link guest, dia tidak boleh isi.
    $guestCanFill = $isGuestTicket && !$currentUser;

    // DEFINISI STATUS SELESAI (DONE & REJECT)
    $finishedStatuses = [\App\Enums\TicketStatus::DONE, \App\Enums\TicketStatus::REJECT];

    // 6. GABUNGKAN KONDISI UTAMA ($canFill):
    // - Tiket harus status SELESAI
    // - Survey belum pernah diisi
    // - DAN:
    //    a. User Internal (Role User) Pemilik Tiket
    //    b. ATAU Guest Asli (Tidak Login) pada Tiket Guest
    // - DAN BUKAN Staff (Admin/Superuser)
    $canFill =
        in_array($ticket->status, $finishedStatuses) && !$survey && !$isStaffRole && ($isUserRole || $guestCanFill);

    // Warna untuk rating
    $getRatingColor = fn($score) => match (true) {
        $score >= 4.5 => 'text-emerald-500',
        $score >= 3.0 => 'text-yellow-500',
        default => 'text-red-500',
    };
@endphp

{{-- Tampil jika Tiket Selesai (DONE atau REJECT) --}}
@if (in_array($ticket->status, $finishedStatuses))
    <div class="mt-8">

        {{-- BAGIAN 1: HASIL SURVEI (Bisa dilihat oleh Admin/Superuser/User) --}}
        @if ($survey)
            <div
                class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden p-6 md:p-8">
                <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">

                    {{-- Kiri: Overall Score --}}
                    <div
                        class="flex flex-col items-center justify-center text-center md:w-1/3 border-b md:border-b-0 md:border-r border-border-light dark:border-border-dark pb-6 md:pb-0 md:pr-6 w-full">
                        <div
                            class="text-xs font-bold uppercase tracking-wider text-muted-light dark:text-muted-dark mb-2">
                            Rating Kepuasan</div>
                        <div class="text-6xl font-black text-text-light dark:text-text-dark mb-2">
                            {{ $survey->overall_rating }}<span class="text-2xl text-muted-light">/5</span></div>

                        {{-- Bintang --}}
                        <div class="flex items-center gap-1 mb-4">
                            @for ($i = 1; $i <= 5; $i++)
                                <span
                                    class="material-icons-round text-3xl {{ $i <= $survey->overall_rating ? 'text-yellow-400' : 'text-gray-200 dark:text-slate-700' }}">star</span>
                            @endfor
                        </div>

                        <div
                            class="px-4 py-2 bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 rounded-full text-xs font-bold flex items-center gap-2">
                            <span class="material-icons-round text-sm">check_circle</span>
                            Survei Terkirim
                        </div>
                    </div>

                    {{-- Kanan: Detail & Feedback --}}
                    <div class="flex-1 w-full space-y-6">
                        <div>
                            <h4 class="font-bold text-text-light dark:text-text-dark mb-4">Detail Penilaian</h4>
                            <div class="space-y-4">
                                @foreach ($survey->answers as $ans)
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span
                                                class="text-muted-light dark:text-muted-dark">{{ $ans->question->question }}</span>
                                            <span
                                                class="font-bold {{ $getRatingColor($ans->score) }}">{{ $ans->score }}/5</span>
                                        </div>
                                        <div
                                            class="w-full bg-gray-100 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                            <div class="h-full rounded-full {{ str_replace('text-', 'bg-', $getRatingColor($ans->score)) }}"
                                                style="width: {{ ($ans->score / 5) * 100 }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div
                            class="bg-gray-50 dark:bg-slate-800/50 p-4 rounded-xl border border-border-light dark:border-border-dark">
                            <p class="text-xs font-bold text-muted-light dark:text-muted-dark uppercase mb-1">Masukan &
                                Saran</p>
                            <p class="text-text-light dark:text-text-dark italic">"{{ $survey->feedback }}"</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BAGIAN 2: FORMULIR INPUT (Hanya tampil jika $canFill bernilai true / Role User) --}}
        @elseif($canFill)
            <div x-data="{
                rating: 0,
                hoverRating: 0,
                submitLoading: false,
                validate() {
                    if (this.rating === 0) { alert('Mohon beri rating bintang.'); return false; }
                    this.submitLoading = true;
                    return true;
                }
            }"
                class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-lg border border-secondary/20 dark:border-secondary/20 overflow-hidden relative">

                {{-- Header Dekorasi --}}
                <div class="bg-gradient-to-r from-secondary to-blue-600 p-6 text-white text-center">
                    <h3 class="text-xl font-bold">Bagaimana Pelayanan Kami?</h3>
                    <p class="text-blue-100 text-sm mt-1">Bantu kami meningkatkan kualitas layanan Helpdesk UPA TIK.</p>
                </div>

                <form action="{{ route('tickets.survey.store', $ticket->uuid) }}" method="POST"
                    @submit="return validate()" class="p-6 md:p-8 space-y-8">
                    @csrf

                    {{-- 1. BINTANG UTAMA --}}
                    <div class="text-center">
                        <label
                            class="block text-sm font-bold text-muted-light dark:text-muted-dark mb-3 uppercase tracking-wider">Kepuasan
                            Keseluruhan</label>
                        <div class="flex items-center justify-center gap-2 cursor-pointer"
                            @mouseleave="hoverRating = 0">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" @click="rating = {{ $i }}"
                                    @mouseover="hoverRating = {{ $i }}"
                                    class="focus:outline-none transition-transform duration-100 hover:scale-110">
                                    <span class="material-icons-round text-5xl transition-colors duration-200"
                                        :class="(hoverRating || rating) >= {{ $i }} ? 'text-yellow-400' :
                                            'text-gray-200 dark:text-slate-700'">
                                        star
                                    </span>
                                </button>
                            @endfor
                        </div>
                        <input type="hidden" name="overall_rating" :value="rating">
                        <p class="text-sm font-medium mt-2 h-5 transition-all duration-300"
                            :class="rating > 0 ? 'text-secondary opacity-100' : 'opacity-0'">
                            <span
                                x-text="['Sangat Buruk', 'Buruk', 'Cukup', 'Puas', 'Sangat Puas'][rating-1] || ''"></span>
                        </p>
                    </div>

                    <hr class="border-border-light dark:border-border-dark">

                    {{-- 2. PERTANYAAN SKALA (Grid Layout) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        @foreach ($questions as $q)
                            <div class="space-y-2">
                                <label class="text-sm font-semibold text-text-light dark:text-text-dark block">
                                    {{ $q->question }} <span class="text-red-500">*</span>
                                </label>

                                {{-- Radio Scale 1-5 --}}
                                <div
                                    class="flex items-center justify-between bg-gray-50 dark:bg-slate-800/50 rounded-lg p-1.5 border border-border-light dark:border-border-dark">
                                    @foreach (range(1, 5) as $val)
                                        <label class="flex-1 text-center cursor-pointer group relative">
                                            <input type="radio" name="answers[{{ $q->id }}]"
                                                value="{{ $val }}" required class="peer sr-only">
                                            <div
                                                class="w-full py-2 rounded-md text-sm font-medium text-muted-light dark:text-muted-dark transition-all duration-200
                                                    peer-checked:bg-white dark:peer-checked:bg-slate-700 peer-checked:text-secondary peer-checked:shadow-sm
                                                    group-hover:bg-gray-100 dark:group-hover:bg-slate-700/50">
                                                {{ $val }}
                                            </div>
                                        </label>
                                    @endforeach
                                </div>
                                <div
                                    class="flex justify-between text-[10px] text-muted-light dark:text-muted-dark px-1">
                                    <span>Sangat Buruk</span>
                                    <span>Sangat Baik</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- 3. FEEDBACK TEXT --}}
                    <div>
                        <label for="feedback" class="block text-sm font-bold text-text-light dark:text-text-dark mb-2">
                            Masukan & Saran <span class="text-red-500">*</span>
                        </label>
                        <textarea name="feedback" id="feedback" rows="3" required
                            class="w-full rounded-xl border-border-light dark:border-border-dark bg-white dark:bg-slate-800 text-text-light dark:text-text-dark focus:ring-secondary focus:border-secondary placeholder-muted-light text-sm p-4 shadow-sm"
                            placeholder="Apa yang bisa kami tingkatkan lagi?"></textarea>
                    </div>

                    {{-- SUBMIT BUTTON --}}
                    <div class="pt-4">
                        <button type="submit"
                            class="w-full flex items-center justify-center gap-2 py-3 px-6 bg-secondary hover:bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all transform active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                            <span x-show="!submitLoading" class="material-icons-round">send</span>
                            <span x-show="!submitLoading">Kirim Penilaian</span>
                            <span x-show="submitLoading" class="material-icons-round animate-spin">refresh</span>
                            <span x-show="submitLoading">Memproses...</span>
                        </button>
                    </div>

                </form>
            </div>
        @endif
    </div>
@endif
