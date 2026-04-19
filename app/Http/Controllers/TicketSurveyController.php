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
        // 1. Validasi Keberadaan Survei
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

            // --- PERHITUNGAN CSI BERDASARKAN JURNAL ---
            $totalWeightScore = 0;
            $totalImportance = 0;

            foreach ($questions as $q) {
                $satScore = $validated['satisfaction'][$q->id];
                $impScore = $validated['importance'][$q->id];

                // Menghitung Weight Score (Kepuasan x Kepentingan)
                $totalWeightScore += ($satScore * $impScore);

                // Mengumpulkan Total Importance (sebagai pembagi / Weight Factor)
                $totalImportance += $impScore;
            }

            // Menghitung Rating Bintang Tertimbang (Skala 1-5)
            $weightedRating = $totalImportance > 0 ? ($totalWeightScore / $totalImportance) : 0;

            // Mengkonversi Rating Tertimbang menjadi Persentase (Skor CSI)
            $csiScore = ($weightedRating / 5) * 100;
            // ------------------------------------------

            // 1. Simpan Header Survei
            $survey = $ticket->survey()->create([
                // overall_rating kita simpan dari hasil pembulatan weighted rating
                // karena secara default strukturnya kemungkinan integer.
                'overall_rating' => round($weightedRating),
                'feedback' => $validated['feedback'],
                'csi_score' => $csiScore,
            ]);

            // 2. Simpan Detail Jawaban (Satisfaction & Importance)
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
