<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComment extends Model
{
    /** @use HasFactory<\Database\Factories\TicketCommentFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'attachment_path',
        'is_internal_note',
    ];

    protected $casts = [
        'is_internal_note' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope untuk mengambil hanya komentar publik (untuk dilihat user biasa/guest)
     */
    public function scopePublic(Builder $query): void
    {
        $query->where('is_internal_note', false);
    }

    /**
     * Mendapatkan nama pengirim secara dinamis
     * Cara pakai di Blade: {{ $comment->sender_name }}
     */
    public function getSenderNameAttribute(): string
    {
        if ($this->user) {
            return $this->user->name.' ('.ucfirst($this->user->role->value).')';
        }

        // Jika user_id null, berarti Guest (ambil dari data tiket)
        // Kita akses relasi guestDetail dari parent ticket
        return $this->ticket->guestDetail->full_name ?? 'Guest User';
    }
}
