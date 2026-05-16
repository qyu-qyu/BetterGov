<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Request extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'office_id',
        'status',
        'notes',
        'qr_token',
    ];

    public function user()           { return $this->belongsTo(User::class); }
    public function service()        { return $this->belongsTo(Service::class); }
    public function office()         { return $this->belongsTo(Office::class); }
    public function statusHistories(){ return $this->hasMany(StatusHistory::class); }
    public function payments()       { return $this->hasMany(Payment::class); }
    public function messages()       { return $this->hasMany(Message::class); }
    public function responseDocuments() { return $this->hasMany(ResponseDocument::class, 'request_id'); }
    public function requestDocuments()  { return $this->hasMany(RequestDocument::class, 'request_id'); }

    // qr token generation and tracking URL
    public function generateQrToken(): string // generate and persist a unique QR token for this request.
    {
        $token = Str::random(32);
        $this->update(['qr_token' => $token]);
        return $token;
    }

    public function trackingUrl(): string
    {
        return url('/track/' . $this->qr_token);
    }
}