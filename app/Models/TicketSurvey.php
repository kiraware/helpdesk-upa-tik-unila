<?php

namespace App\Models;

use Database\Factories\TicketSurveyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketSurvey extends Model
{
    /** @use HasFactory<TicketSurveyFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'overall_rating',
        'feedback',
        'csi_score',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TicketSurveyAnswer::class);
    }
}
