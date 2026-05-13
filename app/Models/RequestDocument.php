<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestDocument extends Model
{

    protected $fillable = [
        'request_id',
        'document_type_id',
        'file_path',
        'file_name',
    ];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentType::class);
    }
}
