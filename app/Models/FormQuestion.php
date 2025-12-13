<?php

namespace App\Models;

use App\Enums\FormQuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FormQuestion extends Model
{
    /** @use HasFactory<\Database\Factories\FormQuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'form_id',
        'question_text',
        'type',
        'options',
        'is_required',
        'order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'type' => FormQuestionType::class,
        'options' => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(FormAnswer::class, 'question_id');
    }
}
