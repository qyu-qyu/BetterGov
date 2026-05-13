<?php

namespace App\Http\Controllers;

use App\Models\RequestDocument;
use App\Models\Request as ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class RequestDocumentController extends Controller
{
    public function index()
    {
        $docs = RequestDocument::with(['documentType'])
            ->whereHas('request', fn($q) => $q->where('user_id', Auth::id()))
            ->get();

        return response()->json(['success' => true, 'data' => $docs]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'request_id'       => 'required|exists:requests,id',
            'document_type_id' => 'nullable|exists:document_types,id',
            'file'             => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $req = ServiceRequest::where('id', $data['request_id'])
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $file = $request->file('file');
        $path = $file->store('request_documents', 'public');

        $doc = RequestDocument::create([
            'request_id'       => $req->id,
            'document_type_id' => $data['document_type_id'] ?? null,
            'file_path'        => $path,
            'file_name'        => $file->getClientOriginalName(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document uploaded successfully.',
            'data'    => $doc->load('documentType'),
        ], 201);
    }

    public function getByRequest(int $requestId)
    {
        $docs = RequestDocument::with(['documentType'])
            ->where('request_id', $requestId)
            ->get();

        return response()->json(['success' => true, 'data' => $docs]);
    }
}
