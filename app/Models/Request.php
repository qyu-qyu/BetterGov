<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\StatusHistory;
use App\Models\Payment;
use App\Models\Message;
use App\Models\ResponseDocument;

class Request extends Model
{
   use HasFactory;

   protected $fillable = [
       'user_id',
       'service_id',
       'office_id',
       'status',
       'notes'
   ];

   public function user()
   {
       return $this->belongsTo(User::class);
   }

   public function service()
   {
       return $this->belongsTo(Service::class);
   }

   public function office()
   {
       return $this->belongsTo(Office::class);
   }

   public function statusHistories()
   {
       return $this->hasMany(StatusHistory::class);
   }

   public function payments()
   {
       return $this->hasMany(Payment::class);
   }

   public function messages()
   {
       return $this->hasMany(Message::class);
   }
   
   public function responseDocuments()
{
    return $this->hasMany(ResponseDocument::class, 'request_id');
}
}
