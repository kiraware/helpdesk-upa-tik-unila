<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSurvey extends Model
{
    /** @use HasFactory<\Database\Factories\TicketSurveyFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'score_access',
        'score_speed',
        'score_solution',
        'score_attitude',
        'score_overall',
        'feedback_comment',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Accessor: Menghitung rata-rata skor individu secara otomatis
     * Penggunaan: $survey->avg_score
     */
    public function getAvgScoreAttribute(): float
    {
        // Menghitung rata-rata dari 5 dimensi
        return round((
            $this->score_access +
            $this->score_speed +
            $this->score_solution +
            $this->score_attitude +
            $this->score_overall
        ) / 5, 2);
    }
}
