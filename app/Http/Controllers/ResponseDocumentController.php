<?php

namespace App\Http\Controllers;

use App\Models\ResponseDocument;
use App\Models\Request as ServiceRequest;
use App\Services\NotificationService;
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

        // ── Notify citizen + push via SSE ───────────────────────────────────
        $serviceRequest = ServiceRequest::find($data['request_id']);
        if ($serviceRequest) {
            $docTitle = $data['title'] ?? $file->getClientOriginalName();
            NotificationService::notify(
                $serviceRequest->user_id,
                $serviceRequest->id,
                "A document \"{$docTitle}\" was uploaded to your request #{$serviceRequest->id}.",
                NotificationService::TYPE_DOCUMENT_UPLOADED
            );
        }

        return response()->json(['success' => true, 'message' => 'Document uploaded.', 'data' => $document], 201);
    }

    public function getByRequest(string $requestId)
    {
        // Fixed: was passing 4 args to where() — correct is 3
        $documents = ResponseDocument::where('request_id', $requestId)->get();
        return response()->json(['success' => true, 'data' => $documents], 200);
    }

    public function download(int $id)
    {
        $doc  = ResponseDocument::with('request')->findOrFail($id);
        $user = Auth::user();
        $role = $user->role?->name;

        if ($role !== 'admin') {
            if ($role === 'office') {
                if ($doc->request?->office_id !== $user->office_id) {
                    return response()->json(['message' => 'Forbidden.'], 403);
                }
            } elseif ($doc->request?->user_id !== $user->id) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        $fullPath = storage_path('app/public/' . $doc->file_path);
        if (!file_exists($fullPath)) {
            return response()->json(['message' => 'File not found.'], 404);
        }

        return response()->download($fullPath, $doc->file_name ?? basename($doc->file_path));
    }
}