<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'status_id',
        'priority_id',
        'description',
        'submitted_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function status()
    {
        return $this->belongsTo(RequestStatus::class, 'status_id');
    }

    public function priority()
    {
        return $this->belongsTo(RequestPriority::class, 'priority_id');
    }

    public function documents()
    {
        return $this->hasMany(RequestDocument::class);
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
