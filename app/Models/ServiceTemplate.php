<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTemplate extends Model
{
    protected $fillable = [
        'category',
        'name_en',
        'name_ar',
        'description',
        'required_documents',
        'estimated_days',
        'is_active',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'is_active'          => 'boolean',
    ];
}
