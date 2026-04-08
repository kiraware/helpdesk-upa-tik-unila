<?php

namespace App\Http\Controllers;

use App\Enums\TicketStatus;
use App\Models\SurveyQuestion;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TicketSurveyController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        // 1. Validasi Keberadaan Survei: Pastikan tiket ini belum pernah dinilai
        if ($ticket->survey()->exists()) {
            return back()->with('error', 'Survei untuk tiket ini sudah pernah diisi sebelumnya.');
        }

        // 2. Validasi Keamanan (Status Tiket)
        if (! in_array($ticket->status, [TicketStatus::DONE, TicketStatus::REJECT])) {
            return back()->with('error', 'Survei hanya dapat diisi untuk tiket yang sudah selesai atau ditolak.');
        }

        // 3. Cek otorisasi (Owner / Guest)
        $isOwner = auth()->check() && auth()->id() === $ticket->user_id;
        $isGuest = ! $ticket->user_id;
        if (! $isOwner && ! $isGuest) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengisi survei ini.');
        }

        // 4. Validasi Input
        $questions = SurveyQuestion::active()->get();
        $rules = [
            'overall_rating' => 'required|integer|min:1|max:5',
            'feedback' => 'required|string|max:255',
            'answers' => 'required|array',
        ];

        foreach ($questions as $q) {
            $rules["answers.{$q->id}"] = 'required|integer|min:1|max:5';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($ticket, $validated) {
            // Hitung CSI Score
            $totalScore = collect($validated['answers'])->sum();
            $maxScore = count($validated['answers']) * 5;
            $csiScore = ($totalScore / $maxScore) * 100;

            // 1. Simpan Header
            $survey = $ticket->survey()->create([
                'overall_rating' => $validated['overall_rating'],
                'feedback' => $validated['feedback'],
                'csi_score' => $csiScore,
            ]);

            // 2. Simpan Detail Jawaban
            foreach ($validated['answers'] as $questionId => $score) {
                $survey->answers()->create([
                    'survey_question_id' => $questionId,
                    'score' => $score,
                ]);
            }
        });

        return back()->with('success', 'Terima kasih atas penilaian Anda!');
    }
}
