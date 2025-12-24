<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TicketAttachment extends Model
{
    /** @use HasFactory<\Database\Factories\TicketAttachmentFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'name',
        'path',
        'mime_type',
        'size',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
