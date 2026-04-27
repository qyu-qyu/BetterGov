<?php

namespace App\Http\Controllers;

use App\Models\Municipality;

class MunicipalityController extends Controller
{
    public function index()
    {
        return response()->json(Municipality::all(), 200, [], JSON_PRETTY_PRINT);
    }
}
