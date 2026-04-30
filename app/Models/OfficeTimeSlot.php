<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfficeTimeSlot extends Model
{
    protected $fillable = [
        'office_id',
        'day_of_week',
        'start_time',
        'end_time',
        'max_capacity',
        'is_active',
    ];

    public function office()
    {
        return $this->belongsTo(Office::class);
    }
}