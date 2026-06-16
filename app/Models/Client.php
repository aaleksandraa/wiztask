<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'contact_person', 'email', 'phone', 'website', 'city',
        'country', 'address', 'note', 'status', 'default_hourly_rate', 'currency',
    ];

    protected $casts = [
        'default_hourly_rate' => 'decimal:2',
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
