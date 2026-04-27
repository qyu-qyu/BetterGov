<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
   protected $fillable = [
       'user_id',
       'request_id',
       'message',
       'is_read'
   ];
}
