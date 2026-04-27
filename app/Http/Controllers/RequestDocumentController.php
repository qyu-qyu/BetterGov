<?php

namespace App\Http\Controllers;

use App\Models\RequestDocument;

class RequestDocumentController extends Controller
{
    public function index()
    {
        return response()->json(RequestDocument::all(), 200, [], JSON_PRETTY_PRINT);
    }
}
