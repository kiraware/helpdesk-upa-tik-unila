<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormAnswer extends Model
{
    /** @use HasFactory<\Database\Factories\FormAnswerFactory> */
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'question_id',
        'answer_value',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(FormQuestion::class);
    }
}
