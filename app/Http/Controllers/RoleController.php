<?php

namespace App\Http\Controllers;

use App\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::all(), 200, [], JSON_PRETTY_PRINT);
    }
}
