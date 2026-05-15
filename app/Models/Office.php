<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Office extends Model
{
   protected $fillable = [
       'name',
       'municipality_id',
       'office_type_id',
       'office_type',
       'address',
       'phone',
       'email',
       'latitude',
       'longitude',
       'working_hours',
   ];

   public function municipality(): BelongsTo
   {
       return $this->belongsTo(Municipality::class);
   }

   public function officeType(): BelongsTo
   {
       return $this->belongsTo(OfficeType::class);
   }

   public function services()
   {
       return $this->hasMany(Service::class);
   }

   public function timeSlots()
   {
       return $this->hasMany(OfficeTimeSlot::class);
   }

   public function requests()
   {
       return $this->hasMany(\App\Models\Request::class);
   }
}
