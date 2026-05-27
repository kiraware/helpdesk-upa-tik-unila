<?php

namespace App\Models;

use Database\Factories\TicketSurveyAnswerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSurveyAnswer extends Model
{
    /** @use HasFactory<TicketSurveyAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_survey_id',
        'survey_question_id',
        'satisfaction_score',
        'importance_score',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(TicketSurvey::class, 'ticket_survey_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }
}
