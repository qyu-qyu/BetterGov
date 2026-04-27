<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;

class DocumentTypeController extends Controller
{
    public function index()
    {
        return response()->json(DocumentType::all(), 200, [], JSON_PRETTY_PRINT);
    }
}
