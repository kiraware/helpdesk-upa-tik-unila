<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketSurveyAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\TicketSurveyAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_survey_id',
        'survey_question_id',
        'score',
    ];

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class, 'survey_question_id');
    }
}
