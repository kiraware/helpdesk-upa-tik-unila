<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'uuid',
        'ticket_code',
        'user_id',
        'service_id',
        'assigned_to',
        'priority',
        'status',
        'title',
        'description',
        'assigned_at',
        'closed_at',
    ];

    protected $casts = [
        'priority' => TicketPriority::class,
        'status' => TicketStatus::class,
        'assigned_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Ticket $ticket) {
            if (empty($ticket->uuid)) {
                $ticket->uuid = (string) Str::uuid();
            }

            if (empty($ticket->ticket_code)) {
                $ticket->ticket_code = 'TIK-'.now()->format('Ymd').'-'.mt_rand(1000, 9999);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function guestDetail(): HasOne
    {
        return $this->hasOne(GuestTicketDetail::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function survey(): HasOne
    {
        return $this->hasOne(TicketSurvey::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }
}
