<?php

namespace App\Models;

use Database\Factories\TicketCommentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Mews\Purifier\Facades\Purifier;

class TicketComment extends Model
{
    /** @use HasFactory<TicketCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(CommentAttachment::class, 'ticket_comment_id');
    }

    /**
     * Mendapatkan nama pengirim secara dinamis
     * Cara pakai di Blade: {{ $comment->sender_name }}
     */
    public function getSenderNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name;
        }

        // Jika user_id null, berarti Guest (ambil dari data tiket)
        // Kita akses relasi guestDetail dari parent ticket
        return $this->ticket->guestDetail->full_name;
    }

    /**
     * Mutator: Setiap kali field 'message' di-set, otomatis dibersihkan
     */
    public function setMessageAttribute($value)
    {
        $this->attributes['message'] = Purifier::clean($value);
    }
}
