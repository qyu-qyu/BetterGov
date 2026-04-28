<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseDocument extends Model
{
    protected $fillable = [
    'request_id',
    'uploaded_by',
    'title',
    'file_name',
    'file_path',
];

}
