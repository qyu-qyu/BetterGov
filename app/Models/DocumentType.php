<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $fillable = ['name'];

    public function requestDocuments()
    {
        return $this->hasMany(RequestDocument::class);
    }
    public function services()
{
    return $this->belongsToMany(Service::class, 'service_document_type');
}
}
