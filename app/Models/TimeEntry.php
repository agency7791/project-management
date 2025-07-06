<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class TimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'project_id',
        'task_name',
        'description',
        'date',
        'start_time',
        'end_time',
        'duration_minutes',
        'is_billable',
        'hourly_rate',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'is_billable' => 'boolean',
            'hourly_rate' => 'decimal:2',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // Mutators
    public function setStartTimeAttribute($value)
    {
        $this->attributes['start_time'] = Carbon::createFromFormat('H:i', $value)->format('H:i:s');
    }

    public function setEndTimeAttribute($value)
    {
        if ($value) {
            $this->attributes['end_time'] = Carbon::createFromFormat('H:i', $value)->format('H:i:s');
            $this->calculateDuration();
        }
    }

    // Helper methods
    public function calculateDuration()
    {
        if ($this->start_time && $this->end_time) {
            $start = Carbon::createFromFormat('H:i:s', $this->start_time);
            $end = Carbon::createFromFormat('H:i:s', $this->end_time);
            $this->duration_minutes = $end->diffInMinutes($start);
        }
    }

    public function getDurationHoursAttribute()
    {
        return $this->duration_minutes ? round($this->duration_minutes / 60, 2) : 0;
    }

    public function getTotalCostAttribute()
    {
        if (!$this->is_billable || !$this->duration_minutes) {
            return 0;
        }
        
        $rate = $this->hourly_rate ?? $this->project->hourly_rate ?? $this->user->hourly_rate ?? 0;
        return ($this->duration_minutes / 60) * $rate;
    }

    // Scopes
    public function scopeBillable($query)
    {
        return $query->where('is_billable', true);
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }
}
