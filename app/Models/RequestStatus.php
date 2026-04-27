<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestStatus extends Model
{
    protected $fillable = ['name'];

    public function serviceRequests()
    {
        return $this->hasMany(ServiceRequest::class, 'status_id');
    }
}
