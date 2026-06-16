<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'name', 'description', 'status', 'start_date', 'due_date',
        'billing_type', 'fixed_price', 'currency', 'note',
    ];

    protected $casts = [
        'start_date' => 'date',
        'due_date' => 'date',
        'fixed_price' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function totalMinutes(): int
    {
        return (int) $this->timeEntries()->sum('total_minutes');
    }

    public function totalValue(): float
    {
        return (float) $this->tasks()->sum('total_price');
    }
}
