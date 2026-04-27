<?php

namespace App\Http\Controllers;

use App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        return response()->json(Service::all(), 200, [], JSON_PRETTY_PRINT);
    }
}
