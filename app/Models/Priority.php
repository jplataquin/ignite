<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

class Priority extends Model
{
    #[Fillable]
    protected $fillable = ['name', 'level'];

    /**
     * Get the tickets for the priority.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'priority_option_id');
    }
}
