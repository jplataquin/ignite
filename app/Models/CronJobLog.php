<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

class CronJobLog extends Model
{
    #[Fillable]
    protected $fillable = [
        'command', 'status', 'started_at', 'completed_at', 'duration_ms', 'output', 'error'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];
}
