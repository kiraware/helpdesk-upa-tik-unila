<?php

namespace App\Models;

use App\Enums\TicketPriority;
use App\Enums\TicketStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Mews\Purifier\Facades\Purifier;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $fillable = [
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
            if (empty($ticket->ticket_code)) {
                $ticket->ticket_code = 'TIK-'.now()->format('Ymd').'-'.mt_rand(1000, 9999);
            }
        });
    }

    /**
     * Use ticket_code as the identifier in the URL (Route Model Binding).
     */
    public function getRouteKeyName(): string
    {
        return 'ticket_code';
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

    /**
     * Mutator: Setiap kali field 'description' di-set, otomatis dibersihkan
     */
    public function setDescriptionAttribute($value)
    {
        $this->attributes['description'] = Purifier::clean($value);
    }
}
