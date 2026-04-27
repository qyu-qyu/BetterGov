<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Office extends Model
{
   protected $fillable = [
       'name',
       'municipality_id',
       'office_type_id',
       'address',
       'phone',
       'email',
   ];

   public function municipality(): BelongsTo
   {
       return $this->belongsTo(Municipality::class);
   }

   public function officeType(): BelongsTo
   {
       return $this->belongsTo(OfficeType::class);
   }
}
