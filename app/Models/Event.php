<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'start_time',
        'end_date',
        'end_time',
        'all_day',
        'color',
        'type',
    ];

    protected $casts = [
        'all_day' => 'boolean',
    ];

    public function participants()
    {
        return $this->belongsToMany(Participant::class, 'event_participants');
    }

    // Accessor untuk mendapatkan start_date_time sebagai Carbon instance
    public function getStartDateTimeAttribute()
    {
        try {
            if ($this->all_day) {
                return Carbon::parse($this->start_date)->startOfDay();
            }

            if ($this->start_time) {
                return Carbon::parse($this->start_date . ' ' . $this->start_time);
            }

            return Carbon::parse($this->start_date)->startOfDay();
        } catch (\Exception $e) {
            return Carbon::parse($this->start_date)->startOfDay();
        }
    }

    // Accessor untuk mendapatkan end_date_time sebagai Carbon instance
    public function getEndDateTimeAttribute()
    {
        try {
            if ($this->all_day) {
                return Carbon::parse($this->end_date)->endOfDay();
            }

            if ($this->end_time) {
                return Carbon::parse($this->end_date . ' ' . $this->end_time);
            }

            return Carbon::parse($this->end_date)->endOfDay();
        } catch (\Exception $e) {
            return Carbon::parse($this->end_date)->endOfDay();
        }
    }

    // Helper method untuk mendapatkan start_date sebagai Carbon
    public function getStartDateCarbonAttribute()
    {
        return Carbon::parse($this->start_date);
    }

    // Helper method untuk mendapatkan end_date sebagai Carbon
    public function getEndDateCarbonAttribute()
    {
        return Carbon::parse($this->end_date);
    }
}