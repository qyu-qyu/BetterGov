<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;

class ServiceRequestController extends Controller
{
    public function index()
    {
        return response()->json(ServiceRequest::all(), 200, [], JSON_PRETTY_PRINT);
    }
}
