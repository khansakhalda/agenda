<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Participant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
    ];

    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_participants');
    }
}
