<?php

namespace App\Models;

use Database\Factories\SurveyQuestionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends Model
{
    /** @use HasFactory<SurveyQuestionFactory> */
    use HasFactory;

    protected $fillable = [
        'aspect_name',
        'satisfaction_question',
        'importance_question',
        'sort_order',
        'is_active',
    ];

    public function answers(): HasMany
    {
        return $this->hasMany(TicketSurveyAnswer::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
