<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id', 'project_id', 'title', 'description', 'status', 'priority',
        'task_date', 'due_date', 'billing_type', 'hourly_rate', 'fixed_price',
        'total_price', 'is_billable', 'payment_status', 'internal_note', 'archived_at',
    ];

    protected $casts = [
        'task_date' => 'date',
        'due_date' => 'date',
        'archived_at' => 'datetime',
        'hourly_rate' => 'decimal:2',
        'fixed_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'is_billable' => 'boolean',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived($query)
    {
        return $query->whereNotNull('archived_at');
    }

    public function totalMinutes(): int
    {
        return (int) $this->timeEntries()->sum('total_minutes');
    }

    /**
     * Pravila obračuna prema specifikaciji.
     */
    public function recalcTotalPrice(): void
    {
        $total = match ($this->billing_type) {
            'po_satu' => round($this->totalMinutes() / 60 * (float) $this->hourly_rate, 2),
            'fiksno' => (float) $this->fixed_price,
            // Uključeno u paket: vrijeme se vodi, ali ne ulazi u dodatnu naplatu.
            'ukljuceno_u_paket', 'bez_naplate' => 0.0,
            default => 0.0,
        };

        $this->total_price = $total;
        $this->is_billable = ! in_array($this->billing_type, ['bez_naplate', 'ukljuceno_u_paket'], true);
        $this->saveQuietly();
    }
}
