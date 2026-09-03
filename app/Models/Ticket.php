<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\TicketObserver;

#[ObservedBy([TicketObserver::class])]
class Ticket extends Model
{
    #[Fillable]
    protected $fillable = [
        'ticket_number', 'title', 'ticket_type_id', 'priority_id', 'status_id', 
        'division_id', 'department_id', 'created_by', 'assigned_to', 
        'deadline_date', 'category_1_id', 'category_2_id', 'category_3_id'
    ];

    protected $casts = [
        'deadline_date' => 'datetime',
    ];

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(TicketPriority::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(TicketStatus::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category1(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_1_id');
    }

    public function category2(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_2_id');
    }

    public function category3(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_3_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(TicketSubscription::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }
}
