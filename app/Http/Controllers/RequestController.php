<?php

namespace App\Http\Controllers;

use App\Models\Request as ServiceRequest;
use Illuminate\Http\Request as HttpRequest;
use App\Models\StatusHistory;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
    public function index(HttpRequest $httpRequest)
    {
        $user  = Auth::user();
        $role  = $user->role?->name;
        $query = ServiceRequest::with([
            'user:id,name,email',
            'service:id,name',
            'office:id,name',
        ])->latest();

        if ($role === 'office') {
            $query->where('office_id', $user->office_id);
        } elseif ($role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        if ($httpRequest->filled('status')) {
            $query->where('status', $httpRequest->status);
        }

        if ($httpRequest->filled('office_id')) {
            $query->where('office_id', $httpRequest->office_id);
        }

        $requests = $query->get()->map(fn($req) => [
            'id'            => $req->id,
            'citizen_name'  => $req->user->name  ?? null,
            'citizen_email' => $req->user->email ?? null,
            'service_name'  => $req->service->name ?? null,
            'office_name'   => $req->office->name  ?? null,
            'status'        => $req->status,
            'notes'         => $req->notes,
            'created_at'    => $req->created_at,
        ]);

        return response()->json(['success' => true, 'data' => $requests]);
    }

    public function store(HttpRequest $request)
    {
        $data = $request->validate([
            'service_id' => 'required|exists:services,id',
            'office_id'  => 'required|exists:offices,id',
            'notes'      => 'nullable|string',
        ]);

        $data['user_id'] = Auth::id();

        $req = ServiceRequest::create($data);

        return response()->json(['success' => true, 'message' => 'Request created.', 'data' => $req], 201);
    }

    public function show(int $id)
    {
        $request = ServiceRequest::with([
            'user:id,name,email',
            'service:id,name,fee,estimated_time',
            'office:id,name,email,phone,address',
            'statusHistories',
            'payments',
            'messages',
            'requestDocuments',
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $request]);
    }

    public function updateStatus(HttpRequest $request, int $id)
    {
        $data = $request->validate([
            'status'  => 'required|in:pending,processing,approved,rejected,completed,missing_documents',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user           = Auth::user();
        $role           = $user->role?->name;
        $serviceRequest = ServiceRequest::findOrFail($id);

        // Office users may only update their own office's requests
        if ($role === 'office' && $serviceRequest->office_id !== $user->office_id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $oldStatus = $serviceRequest->status;
        $newStatus = $data['status'];

        if ($oldStatus === $newStatus) {
            return response()->json(['success' => false, 'message' => 'Request already has this status.', 'data' => $serviceRequest]);
        }

        $serviceRequest->update(['status' => $newStatus]);

        StatusHistory::create([
            'request_id' => $serviceRequest->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'changed_by' => Auth::id(),
            'comment'    => $data['comment'] ?? null,
        ]);

        Notification::create([
            'user_id'    => $serviceRequest->user_id,
            'request_id' => $serviceRequest->id,
            'message'    => "Your request status changed from {$oldStatus} to {$newStatus}.",
            'is_read'    => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Status updated.', 'data' => $serviceRequest]);
    }
}
