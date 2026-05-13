<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'user_id',
        'office_id',
        'office_time_slot_id',
        'appointment_date_only',
        'appointment_date',
        'status',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(OfficeTimeSlot::class, 'office_time_slot_id');
    }
}
