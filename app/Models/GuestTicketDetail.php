<?php

namespace App\Models;

use App\Enums\IdentityType;
use Database\Factories\GuestTicketDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuestTicketDetail extends Model
{
    /** @use HasFactory<GuestTicketDetailFactory> */
    use HasFactory;

    protected $table = 'guest_ticket_details';

    protected $fillable = [
        'ticket_id',
        'full_name',
        'email',
        'phone',
        'identity_number',
        'department_id',
        'entity_type',
        'photo_identity_path',
        'photo_selfie_path',
    ];

    protected $casts = [
        'entity_type' => IdentityType::class,
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function routeNotificationForWhatsapp($notification)
    {
        return $this->phone;
    }
}
