<?php

namespace App\Models;

use Database\Factories\CommentAttachmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CommentAttachment extends Model
{
    /** @use HasFactory<CommentAttachmentFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_comment_id',
        'name',
        'path',
        'mime_type',
        'size',
    ];

    public function comment(): BelongsTo
    {
        return $this->belongsTo(TicketComment::class, 'ticket_comment_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::url($this->path);
    }
}
