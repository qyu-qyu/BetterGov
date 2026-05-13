<?php

namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use App\Models\StatusHistory;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class RequestController extends Controller
{
   

   public function store(HttpRequest $request)
   {
       $data = $request->validate([
           'service_id' => 'required|exists:services,id',
           'office_id' => 'required|exists:offices,id',
           'notes' => 'nullable|string'
       ]);

       $data['user_id'] = Auth::id();

       $req = Request::create($data);

       return response()->json([
           'message' => 'Request created successfully',
           'request' => $req
       ], 201);
   }

public function updateStatus(HttpRequest $request, $id)
{
$data = $request->validate([
    'status' => 'required|string|in:pending,processing,approved,rejected,completed',
    'comment' => 'nullable|string|max:1000'
]);

    $serviceRequest = Request::findOrFail($id);

    $oldStatus = $serviceRequest->status;
    $newStatus = $data['status'];

    if ($oldStatus === $newStatus) {
        return response()->json([
            'success' => false,
            'message' => 'Request already has this status',
            'data' => $serviceRequest
        ], 200);
    }

    $serviceRequest->update([
        'status' => $newStatus
    ]);

StatusHistory::create([
    'request_id' => $serviceRequest->id,
    'old_status' => $oldStatus,
    'new_status' => $newStatus,
    'changed_by' => Auth::id(),
    'comment' => $data['comment'] ?? null,
]);

    Notification::create([
        'user_id' => $serviceRequest->user_id,
        'request_id' => $serviceRequest->id,
        'message' => 'Your request status has been updated from ' . $oldStatus . ' to ' . $newStatus,
        'is_read' => false,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Request status updated successfully',
        'data' => $serviceRequest
    ], 200);
}

public function index()
{
    $requests = Request::with([
        'user:id,name,email',
        'service:id,name',
        'office:id,name'
    ])
    ->where('user_id', Auth::id())
    ->latest()
    ->get()
    ->map(function ($req) {
        return [
            'id' => $req->id,
            'citizen_name' => $req->user->name ?? null,
            'citizen_email' => $req->user->email ?? null,
            'service_name' => $req->service->name ?? null,
            'office_name' => $req->office->name ?? null,
            'status' => $req->status,
            'notes' => $req->notes,
            'created_at' => $req->created_at,
        ];
    });

    return response()->json([
        'success' => true,
        'message' => 'Requests retrieved successfully',
        'data' => $requests
    ], 200);
}

public function show($id)
{
    $request = Request::with([
        'user:id,name,email',
        'service:id,name,fee,estimated_time',
        'office:id,name,email,phone,address',
        'statusHistories',
        'payments',
        'messages'
    ])->findOrFail($id);

    return response()->json([
        'success' => true,
        'message' => 'Request details retrieved successfully',
        'data' => $request
    ], 200);
}



}

