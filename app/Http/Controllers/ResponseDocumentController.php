<?php

namespace App\Http\Controllers;

use App\Models\ResponseDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResponseDocumentController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'request_id' => 'required|exists:requests,id',
            'file'       => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'title'      => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $path = $file->store('response_documents', 'public');

        $document = ResponseDocument::create([
            'request_id'  => $data['request_id'],
            'uploaded_by' => Auth::id(),
            'title'       => $data['title'] ?? null,
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $path,
        ]);

        return response()->json(['success' => true, 'message' => 'Document uploaded.', 'data' => $document], 201);
    }

    public function getByRequest(string $requestId)
    {
        $documents = ResponseDocument::where('request_id', $requestId)->get();

        return response()->json(['success' => true, 'data' => $documents], 200);
    }
}
