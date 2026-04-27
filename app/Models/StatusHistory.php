<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusHistory extends Model
{
   protected $fillable = [
       'request_id',
       'old_status',
       'new_status',
       'changed_by'
   ];

   public function request()
   {
       return $this->belongsTo(Request::class);
   }

   public function changedBy()
   {
       return $this->belongsTo(User::class, 'changed_by');
   }
}
