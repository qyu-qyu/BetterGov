<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
   public function handle($request, Closure $next, $role)
{
   $user = auth()->user();

   if (!$user) {
       return response()->json(['message' => 'Unauthorized'], 401);
   }

   // 🔥 IMPORTANT FIX
   if (!$user->role_id) {
       return response()->json(['message' => 'Unauthorized. No role assigned.'], 403);
   }

   // Check role by ID instead of relation
   if ($user->role_id != 1) { // 1 = admin
       return response()->json(['message' => 'Forbidden. Admins only.'], 403);
   }

   return $next($request);
}

}

