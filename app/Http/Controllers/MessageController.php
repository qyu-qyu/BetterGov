<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;

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

   $data['sender_id'] = auth()->id();

   $msg = Message::create($data);

   return response()->json([
       'message' => 'Message sent successfully',
       'data' => $msg
   ], 201);
}


   public function show($id)
   {
       return Message::with([
   'request',
   'sender:id,name,email'
])->get();
 }
}
