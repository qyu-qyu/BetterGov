<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'name',
        'service_category_id',
        'service_type_id',
        'office_id',
        'fee',
        'estimated_time'
    ];

    public function category()
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function type()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function requests()
    {
        return $this->hasMany(ServiceRequest::class);
    }


}
