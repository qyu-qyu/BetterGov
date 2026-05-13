<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';

    protected $fillable = [
        'user_id',
        'service_request_id',
        'request_id',
        'comment',
        'rating',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function serviceRequest()
    {
        return $this->belongsTo(Request::class, 'service_request_id');
    }

    public function request()
    {
        return $this->belongsTo(Request::class, 'request_id');
    }

    public function responses()
{
    return $this->hasMany(FeedbackResponse::class);
}
}