<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'psychologist_id',
        'patient_id',
        'start_time',
        'end_time',
        'platform',
        'meeting_link',
        'status',
        'psychologist_notes'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function psychologist()
    {
        return $this->belongsTo(User::class, 'psychologist_id');
    }

    // Helper to calculate duration in hours for reporting
    public function getDurationInHoursAttribute()
    {
        return $this->start_time->diffInHours($this->end_time);
    }
}
