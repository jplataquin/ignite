<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    /** @var array<int, string> */
    protected $fillable = [
        'name',
    ];

    /**
     * Get the departments for the division.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get the tickets for the division.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }
}
