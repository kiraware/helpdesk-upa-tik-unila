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
        if ($ticket->survey()->exists()) {
            return back()->with('error', 'Survei untuk tiket ini sudah pernah diisi sebelumnya.');
        }

        if (! in_array($ticket->status, [TicketStatus::DONE, TicketStatus::REJECT])) {
            return back()->with('error', 'Survei hanya dapat diisi untuk tiket yang sudah selesai atau ditolak.');
        }

        $isOwner = auth()->check() && auth()->id() === $ticket->user_id;
        $isGuest = ! $ticket->user_id;
        if (! $isOwner && ! $isGuest) {
            return back()->with('error', 'Anda tidak memiliki akses untuk mengisi survei ini.');
        }

        $questions = SurveyQuestion::active()->get();
        $rules = [
            'feedback' => 'required|string|max:255',
            'satisfaction' => 'required|array',
            'importance' => 'required|array',
        ];

        foreach ($questions as $q) {
            $rules["satisfaction.{$q->id}"] = 'required|integer|min:1|max:5';
            $rules["importance.{$q->id}"] = 'required|integer|min:1|max:5';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($ticket, $validated, $questions) {

            $totalWeightScore = 0;
            $totalImportance = 0;

            foreach ($questions as $q) {
                $satScore = $validated['satisfaction'][$q->id];
                $impScore = $validated['importance'][$q->id];

                $totalWeightScore += ($satScore * $impScore);

                $totalImportance += $impScore;
            }

            $weightedRating = $totalImportance > 0 ? ($totalWeightScore / $totalImportance) : 0;

            $csiScore = ($weightedRating / 5) * 100;

            $survey = $ticket->survey()->create([
                'overall_rating' => round($weightedRating),
                'feedback' => $validated['feedback'],
                'csi_score' => $csiScore,
            ]);

            foreach ($questions as $q) {
                $survey->answers()->create([
                    'survey_question_id' => $q->id,
                    'satisfaction_score' => $validated['satisfaction'][$q->id],
                    'importance_score' => $validated['importance'][$q->id],
                ]);
            }
        });

        return back()->with('success', 'Terima kasih atas penilaian Anda!');
    }
}
