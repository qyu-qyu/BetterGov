<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;

class DocumentTypeController extends Controller
{
    public function index()
    {
        return response()->json(DocumentType::orderBy('name')->get());
    }
}
