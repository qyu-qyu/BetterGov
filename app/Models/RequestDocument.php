<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestDocument extends Model
{

protected $fillable = [
    'service_request_id',
    'document_type_id',
    'file_path'
];


    public function serviceRequest()
{
    return $this->belongsTo(ServiceRequest::class);
}

public function documentType()
{
    return $this->belongsTo(DocumentType::class);
}
}
