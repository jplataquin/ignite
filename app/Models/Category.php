<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

class Category extends Model
{
    #[Fillable]
    protected $fillable = ['name', 'ticket_type_id'];

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function ancestorClosures(): HasMany
    {
        return $this->hasMany(CategoryClosure::class, 'descendant_id');
    }

    public function descendantClosures(): HasMany
    {
        return $this->hasMany(CategoryClosure::class, 'ancestor_id');
    }

    public function tickets1(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_1_id');
    }

    public function tickets2(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_2_id');
    }

    public function tickets3(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_3_id');
    }
}
