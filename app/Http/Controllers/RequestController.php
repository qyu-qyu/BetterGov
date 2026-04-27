<?php

namespace App\Http\Controllers;

use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use App\Models\StatusHistory;
use App\Models\Notification;

class RequestController extends Controller
{
   

   public function store(HttpRequest $request)
   {
       $data = $request->validate([
           'service_id' => 'required|exists:services,id',
           'office_id' => 'required|exists:offices,id',
           'notes' => 'nullable|string'
       ]);

       $data['user_id'] = auth()->id();

       $req = Request::create($data);

       return response()->json([
           'message' => 'Request created successfully',
           'request' => $req
       ], 201);
   }

public function updateStatus(HttpRequest $request, $id)
{
   $data = $request->validate([
       'status' => 'required|string|in:pending,approved,rejected,processing,completed'
   ]);

   $req = Request::findOrFail($id);

   if ($req->status !== $data['status']) {
       StatusHistory::create([
           'request_id' => $req->id,
           'old_status' => $req->status,
           'new_status' => $data['status'],
           'changed_by' => auth()->id(),
       ]);

       $req->status = $data['status'];
       $req->save();

       Notification::create([
           'user_id' => $req->user_id,
           'request_id' => $req->id,
           'message' => 'Your request status has been updated to ' . $req->status,
           'is_read' => false,
       ]);
   }

   return response()->json([
       'message' => 'Request status updated successfully',
       'request' => $req
   ], 200);
}

public function index()
{
   return Request::with([
       'user',
       'service',
       'office',
       'statusHistories',
       'payments',
       'messages'
   ])->get();
}

public function show($id)
{
   return Request::with([
       'user',
       'service',
       'office',
       'statusHistories',
       'payments',
       'messages'
   ])->findOrFail($id);
}



}

