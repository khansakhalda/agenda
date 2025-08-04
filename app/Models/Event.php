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
        'type'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'all_day' => 'boolean',
    ];

    public function getStartDateTimeAttribute()
    {
        return $this->start_date->setTimeFromTimeString($this->start_time);
    }

    public function getEndDateTimeAttribute()
    {
        return $this->end_date->setTimeFromTimeString($this->end_time);
    }
}