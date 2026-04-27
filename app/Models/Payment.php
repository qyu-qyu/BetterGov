<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
   protected $fillable = [
       'request_id',
       'user_id',
       'amount',
       'payment_method',
       'status'
   ];

   public function request()
   {
       return $this->belongsTo(Request::class);
   }

   public function user()
   {
       return $this->belongsTo(User::class);
   }
}


