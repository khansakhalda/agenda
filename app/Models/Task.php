<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'is_starred',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'is_starred'   => 'boolean',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function participants()
    {
        // relasi ke participant kalender, pivot khusus tugas
        return $this->belongsToMany(Participant::class, 'task_participant');
    }
}
