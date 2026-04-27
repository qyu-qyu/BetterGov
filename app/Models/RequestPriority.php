<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestPriority extends Model
{
    protected $fillable = ['name'];

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'priority_id');
    }
}
