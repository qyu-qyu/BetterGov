<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
   use HasApiTokens, HasFactory, Notifiable;

   protected $fillable = [
       'name',
       'email',
       'password',
       'role_id',
       'office_id',
       'is_active',
   ];

   protected $hidden = [
       'password',
       'remember_token',
   ];

   protected function casts(): array
   {
       return [
           'email_verified_at' => 'datetime',
           'password'          => 'hashed',
           'is_active'         => 'boolean',
       ];
   }

   public function role(): BelongsTo
   {
       return $this->belongsTo(Role::class);
   }

   public function office(): BelongsTo
   {
       return $this->belongsTo(Office::class);
   }

   public function serviceRequests(): HasMany
   {
       return $this->hasMany(ServiceRequest::class);
   }
}

