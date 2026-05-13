<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
   public function index()
   {
       return Message::with(['request', 'sender'])->get();
   }

   public function store(Request $request)
{
   $data = $request->validate([
       'request_id' => 'required|exists:requests,id',
       'message' => 'required|string|max:1000'
   ]);

   $data['sender_id'] = Auth::id();

   $msg = Message::create($data);

   return response()->json([
       'message' => 'Message sent successfully',
       'data' => $msg
   ], 201);
}


   public function show(int $id)
   {
       $message = Message::with(['request', 'sender:id,name,email'])->findOrFail($id);
       return response()->json(['success' => true, 'data' => $message]);
   }

   public function byRequest(int $requestId)
   {
       $messages = Message::with('sender:id,name')
           ->where('request_id', $requestId)
           ->oldest()
           ->get();

       return response()->json(['success' => true, 'data' => $messages]);
   }
}
