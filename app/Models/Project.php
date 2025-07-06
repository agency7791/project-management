<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'client_id',
        'status',
        'priority',
        'start_date',
        'end_date',
        'budget',
        'hourly_rate',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
            'hourly_rate' => 'decimal:2',
        ];
    }

    // Relationships
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function teams()
    {
        return $this->hasMany(Team::class);
    }

    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class);
    }

    public function files()
    {
        return $this->hasMany(ProjectFile::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper methods
    public function getTotalHoursAttribute()
    {
        return $this->timeEntries()->sum('duration_minutes') / 60;
    }

    public function getTotalCostAttribute()
    {
        return $this->timeEntries()
            ->where('is_billable', true)
            ->get()
            ->sum(function ($entry) {
                $rate = $entry->hourly_rate ?? $this->hourly_rate ?? 0;
                return ($entry->duration_minutes / 60) * $rate;
            });
    }
}
