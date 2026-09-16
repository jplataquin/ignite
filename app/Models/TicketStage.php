<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketStage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ticket_stages';

    protected $fillable = [
        'name',
        'slug',
        'color_code',
    ];

    /**
     * Get the tickets for the stage.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'stage_id');
    }
}
