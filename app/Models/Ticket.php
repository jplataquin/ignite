<?php

namespace App\Models;

use App\Observers\TicketObserver;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([TicketObserver::class])]
class Ticket extends Model
{
    public ?string $temp_system_comment = null;

    #[Fillable]
    protected $fillable = [
        'ticket_number', 'title', 'description', 'ticket_type_id', 'priority_option_id', 'stage_id', 'status', 'approval_mode',
        'division_id', 'department_id', 'location_id', 'created_by', 'assigned_id',
        'deadline_date', 'category_1_id', 'category_2_id', 'category_3_id',
    ];

    protected $casts = [
        'deadline_date' => 'datetime',
    ];

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function priorityOption(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'priority_option_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(TicketStage::class, 'stage_id');
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_id');
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

    public function approvals(): HasMany
    {
        return $this->hasMany(TicketApproval::class)->orderBy('sequence_order', 'asc');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class)->orderBy('created_at', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Get the dynamically formatted ticket number with the ticket type's code prefix.
     */
    public function getTicketNumberAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $ticketType = $this->ticketType;
        if ($ticketType && ! empty($ticketType->code)) {
            $prefix = $ticketType->code;
            $parts = explode('-', $value);
            if (count($parts) >= 2) {
                array_shift($parts); // Remove old prefix

                return $prefix.'-'.implode('-', $parts);
            }
        }

        return $value;
    }

    /**
     * Get the calculated deadline based on date created and priority SLA days.
     */
    public function getCalculatedDeadlineAttribute()
    {
        if ($this->deadline_date) {
            return $this->deadline_date;
        }

        if (! $this->created_at || ! $this->priorityOption) {
            return null;
        }

        $priorityName = strtolower($this->priorityOption->name);
        if (! in_array($priorityName, ['low', 'high', 'critical'])) {
            return null;
        }

        $settingKey = "sla_days_{$priorityName}";
        $days = Setting::where('key', $settingKey)->value('value');

        if (is_numeric($days)) {
            return $this->created_at->copy()->addDays((int) $days);
        }

        return null;
    }
}
