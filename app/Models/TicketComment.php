<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\TicketCommentObserver;

#[ObservedBy([TicketCommentObserver::class])]
class TicketComment extends Model
{
    #[Fillable]
    protected $fillable = ['ticket_id', 'user_id', 'type', 'content', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
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
        return $this->hasMany(Attachment::class, 'comment_id');
    }
}
