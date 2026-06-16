<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'project_id', 'task_id', 'work_date', 'description',
        'hours', 'minutes', 'total_minutes', 'hourly_rate', 'total_price', 'is_billable',
    ];

    protected $casts = [
        'work_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_billable' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (TimeEntry $entry) {
            $entry->total_minutes = ((int) $entry->hours * 60) + (int) $entry->minutes;
            $entry->total_price = round($entry->total_minutes / 60 * (float) $entry->hourly_rate, 2);
        });

        static::saved(fn (TimeEntry $entry) => $entry->task?->recalcTotalPrice());
        static::deleted(fn (TimeEntry $entry) => $entry->task?->recalcTotalPrice());
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }
}
