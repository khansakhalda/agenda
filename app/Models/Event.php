<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    /**
     * Kolom yang boleh di–mass assign.
     * Menyertakan skema terpisah & gabungan agar fleksibel.
     */
    protected $fillable = [
        'title',
        'description',

        // skema terpisah
        'start_date',
        'start_time',
        'end_date',
        'end_time',

        // skema gabungan (kompatibilitas dengan Calendar Admin lama)
        'start_date_time',
        'end_date_time',

        // atribut lain
        'all_day',
        'color',
        'type',
<<<<<<< HEAD
=======
        'is_starred',

        // completed
        'is_completed',
        'completed_at',
>>>>>>> ab25711ba86dbfadf661ddccb58f1f3e884e42b0
    ];

    /**
     * Casting kolom → tipe PHP/Carbon.
     */
    protected $casts = [
<<<<<<< HEAD
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
=======
        'start_date'       => 'date',
        'end_date'         => 'date',
        'start_date_time'  => 'datetime',
        'end_date_time'    => 'datetime',
        'all_day'          => 'boolean',
        'is_starred'       => 'boolean',
        'is_completed'     => 'boolean',
        'completed_at'     => 'datetime',
    ];

    /**
     * ACCESSORS
     * Prioritas:
     * 1) kalau kolom gabungan ada isinya, gunakan itu,
     * 2) kalau tidak, gabungkan dari kolom terpisah.
     */
    public function getStartDateTimeAttribute($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        if (!is_null($value)) {
            return Carbon::parse($value);
        }

        if ($this->start_date) {
            $t = $this->start_time ?: '00:00:00';
            return Carbon::parse($this->start_date->format('Y-m-d') . ' ' . $t);
        }

        return null;
    }

    public function getEndDateTimeAttribute($value)
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        if (!is_null($value)) {
            return Carbon::parse($value);
        }

        if ($this->end_date) {
            $t = $this->end_time ?: '23:59:59';
            return Carbon::parse($this->end_date->format('Y-m-d') . ' ' . $t);
        }

        return null;
>>>>>>> ab25711ba86dbfadf661ddccb58f1f3e884e42b0
    }

    /**
     * MUTATORS
     * Jika ada yang set kolom gabungan, otomatis pecah ke kolom terpisah.
     */
    public function setStartDateTimeAttribute($value): void
    {
        $dt = $value ? Carbon::parse($value) : null;
        $this->attributes['start_date_time'] = $dt?->toDateTimeString();

        if ($dt) {
            $this->attributes['start_date'] = $dt->toDateString();
            $this->attributes['start_time'] = $dt->format('H:i:s');
        }
    }

    public function setEndDateTimeAttribute($value): void
    {
        $dt = $value ? Carbon::parse($value) : null;
        $this->attributes['end_date_time'] = $dt?->toDateTimeString();

        if ($dt) {
            $this->attributes['end_date'] = $dt->toDateString();
            $this->attributes['end_time'] = $dt->format('H:i:s');
        }
    }

    /**
     * Hook penyelaras:
     * - sebelum menyimpan, kalau hanya kolom terpisah yang terisi, isi juga kolom gabungannya
     *   (untuk kompatibilitas tampilan kalender lama),
     * - jika all_day = true, paksa jam ke 00:00:00–23:59:59.
     */
    protected static function booted(): void
    {
        static::saving(function (Event $event) {
            // Sinkron dari terpisah → gabungan
            if (!$event->start_date_time && $event->start_date) {
                $t = $event->start_time ?: '00:00:00';
                $event->attributes['start_date_time'] =
                    Carbon::parse($event->start_date->format('Y-m-d') . ' ' . $t)->toDateTimeString();
            }

            if (!$event->end_date_time && $event->end_date) {
                $t = $event->end_time ?: '23:59:59';
                $event->attributes['end_date_time'] =
                    Carbon::parse($event->end_date->format('Y-m-d') . ' ' . $t)->toDateTimeString();
            }

            // Konsistensi all day
            if ($event->all_day) {
                $event->start_time = '00:00:00';
                $event->end_time   = '23:59:59';
            }
        });
    }
}
