@props(['ticket'])

@php
    $survey = $ticket->survey;
    $questions = \App\Models\SurveyQuestion::active()->get();

    $currentUser = auth()->user();
    $isUserRole = $currentUser && $currentUser->role === \App\Enums\UserRole::USER;
    $isStaffRole =
        $currentUser && in_array($currentUser->role, [\App\Enums\UserRole::ADMIN, \App\Enums\UserRole::SUPERUSER]);

    $isGuestTicket = is_null($ticket->user_id);
    $guestCanFill = $isGuestTicket && !$currentUser;

    $finishedStatuses = [\App\Enums\TicketStatus::DONE, \App\Enums\TicketStatus::REJECT];

    $canFill =
        in_array($ticket->status, $finishedStatuses) && !$survey && !$isStaffRole && ($isUserRole || $guestCanFill);

    $getRatingColor = fn($score) => match (true) {
        $score >= 4.5 => 'text-emerald-500',
        $score >= 3.0 => 'text-yellow-500',
        default => 'text-red-500',
    };
@endphp

@if (in_array($ticket->status, $finishedStatuses))
    <div class="mt-8">

        {{-- BAGIAN 1: HASIL SURVEI --}}
        @if ($survey)
            <div
                class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark overflow-hidden p-6 md:p-8">
                <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">

                    {{-- Kiri: Overall Score & CSI --}}
                    <div
                        class="flex flex-col items-center justify-center text-center md:w-1/3 border-b md:border-b-0 md:border-r border-border-light dark:border-border-dark pb-6 md:pb-0 md:pr-6 w-full">

                        {{-- Menghitung kembali desimal Bintang dari CSI (CSI / 100 * 5) --}}
                        @php
                            $calculatedStar = ($survey->csi_score / 100) * 5;
                        @endphp

                        <div
                            class="text-xs font-bold uppercase tracking-wider text-muted-light dark:text-muted-dark mb-2">
                            Skor Kepuasan (CSI)
                        </div>
                        <div class="text-6xl font-black text-text-light dark:text-text-dark mb-2">
                            {{ number_format($survey->csi_score, 2) }}<span class="text-2xl text-muted-light">%</span>
                        </div>

                        {{-- Bintang Rata-rata Tertimbang --}}
                        <div class="text-xl font-bold text-yellow-500 flex items-center gap-2 mb-4">
                            <span class="material-icons-round">star</span>
                            {{ number_format($calculatedStar, 2) }} / 5.00
                        </div>

                        <div
                            class="px-4 py-2 bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 rounded-full text-xs font-bold flex items-center gap-2">
                            <span class="material-icons-round text-sm">check_circle</span>
                            Survei Terkirim
                        </div>
                    </div>

                    {{-- Kanan: Detail & Feedback --}}
                    <div class="flex-1 w-full space-y-6 min-w-0">
                        <div>
                            <h4 class="font-bold text-text-light dark:text-text-dark mb-4">Detail Penilaian per Aspek
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach ($survey->answers as $ans)
                                    <div
                                        class="bg-gray-50 dark:bg-slate-800/50 p-3 rounded-lg border border-border-light dark:border-border-dark">
                                        <div class="font-bold text-sm text-text-light dark:text-text-dark mb-2">
                                            {{ $ans->question->aspect_name ?? 'Aspek Penilaian' }}
                                        </div>

                                        {{-- Skor Kepuasan --}}
                                        <div class="flex justify-between items-center text-xs mb-1">
                                            <span class="text-muted-light dark:text-muted-dark">Kepuasan:</span>
                                            <span
                                                class="font-bold {{ $getRatingColor($ans->satisfaction_score) }}">{{ $ans->satisfaction_score }}/5</span>
                                        </div>

                                        {{-- Skor Kepentingan --}}
                                        <div class="flex justify-between items-center text-xs">
                                            <span class="text-muted-light dark:text-muted-dark">Kepentingan:</span>
                                            <span class="font-bold text-blue-500">{{ $ans->importance_score }}/5</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div
                            class="bg-gray-50 dark:bg-slate-800/50 p-4 rounded-xl border border-border-light dark:border-border-dark">
                            <p class="text-xs font-bold text-muted-light dark:text-muted-dark uppercase mb-1">Masukan &
                                Saran</p>
                            <p class="text-text-light dark:text-text-dark italic break-all">"{{ $survey->feedback }}"
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- BAGIAN 2: FORMULIR INPUT --}}
        @elseif($canFill)
            <div x-data="{
                submitLoading: false,
                validate() {
                    this.submitLoading = true;
                    return true;
                }
            }"
                class="bg-surface-light dark:bg-surface-dark rounded-xl shadow-lg border border-secondary/20 dark:border-secondary/20 overflow-hidden relative">

                {{-- Header --}}
                <div class="bg-linear-to-r from-secondary to-blue-600 p-6 text-white text-center">
                    <h3 class="text-xl font-bold">Bagaimana Pelayanan Kami?</h3>
                    <p class="text-blue-100 text-sm mt-1">Bantu kami meningkatkan kualitas layanan Helpdesk UPA TIK.</p>
                </div>

                <form action="{{ route('tickets.survey.store', $ticket) }}" method="POST" @submit="return validate()"
                    class="p-6 md:p-8 space-y-8">
                    @csrf

                    {{-- PERTANYAAN (Kepentingan & Kepuasan per Aspek) --}}
                    <div class="space-y-8">
                        @foreach ($questions as $index => $q)
                            <div
                                class="bg-gray-50 dark:bg-slate-800/30 p-5 rounded-xl border border-border-light dark:border-border-dark space-y-5">

                                <h4
                                    class="font-bold text-secondary text-lg border-b border-border-light dark:border-border-dark pb-2">
                                    {{ $index + 1 }}. {{ $q->aspect_name ?? 'Aspek Penilaian' }}
                                </h4>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    {{-- 1. Tingkat Kepentingan --}}
                                    <div class="flex flex-col h-full">
                                        <label
                                            class="text-sm font-semibold text-text-light dark:text-text-dark block mb-3 grow">
                                            {{ $q->importance_question }} <span class="text-red-500">*</span>
                                        </label>
                                        <div class="space-y-2">
                                            <div
                                                class="flex items-center justify-between bg-white dark:bg-slate-900 rounded-lg p-1.5 border border-border-light dark:border-border-dark shadow-sm">
                                                @foreach (range(1, 5) as $val)
                                                    <label class="flex-1 text-center cursor-pointer group relative">
                                                        <input type="radio" name="importance[{{ $q->id }}]"
                                                            value="{{ $val }}" required class="peer sr-only">
                                                        <div
                                                            class="w-full py-2 rounded-md text-sm font-medium text-muted-light dark:text-muted-dark transition-all duration-200 peer-checked:bg-blue-500 peer-checked:text-white peer-checked:shadow-sm hover:bg-gray-100 dark:hover:bg-slate-800">
                                                            {{ $val }}
                                                        </div>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div
                                                class="flex justify-between text-[10px] text-muted-light dark:text-muted-dark px-1">
                                                <span>Sangat Tidak Penting</span>
                                                <span>Sangat Penting</span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 2. Tingkat Kepuasan --}}
                                    <div class="flex flex-col h-full">
                                        <label
                                            class="text-sm font-semibold text-text-light dark:text-text-dark block mb-3 grow">
                                            {{ $q->satisfaction_question }} <span class="text-red-500">*</span>
                                        </label>
                                        <div class="space-y-2">
                                            <div
                                                class="flex items-center justify-between bg-white dark:bg-slate-900 rounded-lg p-1.5 border border-border-light dark:border-border-dark shadow-sm">
                                                @foreach (range(1, 5) as $val)
                                                    <label class="flex-1 text-center cursor-pointer group relative">
                                                        <input type="radio" name="satisfaction[{{ $q->id }}]"
                                                            value="{{ $val }}" required class="peer sr-only">
                                                        <div
                                                            class="w-full py-2 rounded-md text-sm font-medium text-muted-light dark:text-muted-dark transition-all duration-200 peer-checked:bg-yellow-500 peer-checked:text-white peer-checked:shadow-sm hover:bg-gray-100 dark:hover:bg-slate-800">
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
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- FEEDBACK TEXT --}}
                    <div x-data="{ count: 0, max: 255 }">
                        <label for="feedback" class="block text-sm font-bold text-text-light dark:text-text-dark mb-2">
                            Masukan & Saran <span class="text-red-500">*</span>
                        </label>
                        <textarea name="feedback" id="feedback" rows="4" required maxlength="255"
                            @input="count = $event.target.value.length"
                            class="w-full rounded-xl border-border-light dark:border-border-dark bg-white dark:bg-slate-800 text-text-light dark:text-text-dark focus:ring-secondary focus:border-secondary placeholder-muted-light text-sm p-4 shadow-sm"
                            placeholder="Apa yang bisa kami tingkatkan lagi?"></textarea>
                        <div class="flex justify-end items-center mt-1 text-xs">
                            <p class="text-muted-light dark:text-muted-dark">
                                <span x-text="count"></span>/<span x-text="max"></span>
                            </p>
                        </div>
                    </div>

                    {{-- SUBMIT BUTTON --}}
                    <div class="pt-4">
                        <button type="submit" :disabled="submitLoading"
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
