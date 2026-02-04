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
        // Validasi Keamanan
        abort_if(
            ! in_array($ticket->status, [TicketStatus::DONE, TicketStatus::REJECT]),
            403,
            'Survei hanya dapat diisi untuk tiket yang sudah selesai atau ditolak.'
        );

        // Cek otorisasi (Owner / Guest)
        $isOwner = auth()->check() && auth()->id() === $ticket->user_id;
        $isGuest = ! $ticket->user_id; // Asumsi validasi akses guest sudah di middleware/route binding
        abort_if(! $isOwner && ! $isGuest, 403, 'Unauthorized');

        // Validasi Input
        $questions = SurveyQuestion::active()->get();
        $rules = [
            'overall_rating' => 'required|integer|min:1|max:5',
            'feedback' => 'required|string|max:1000',
            'answers' => 'required|array',
        ];

        foreach ($questions as $q) {
            $rules["answers.{$q->id}"] = 'required|integer|min:1|max:5';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($ticket, $validated) {
            // Hitung CSI Score (Rata-rata jawaban pertanyaan spesifik dikali 20 untuk skala 100)
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
