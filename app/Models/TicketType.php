<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketType extends Model
{
    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'description',
        'threshold_days',
    ];

    /**
     * Get the roles associated with the ticket type.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_ticket_type');
    }

    /**
     * Get the categories for the ticket type.
     */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Get the tickets for the ticket type.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
