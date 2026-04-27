<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
   public function index()
   {
       return Payment::with(['request', 'user'])->get();
   }

   public function store(Request $request)
   {
       $data = $request->validate([
           'request_id' => 'required|exists:requests,id',
           'amount' => 'required|numeric|min:0',
           'payment_method' => 'required|string|max:255',
           'status' => 'nullable|string|in:pending,paid,failed'
       ]);

       $data['user_id'] = auth()->id();
       $data['status'] = $data['status'] ?? 'pending';

       $payment = Payment::create($data);

       return response()->json([
           'message' => 'Payment created successfully',
           'payment' => $payment->load(['request', 'user'])
       ], 201);
   }

   public function show($id)
   {
       return Payment::with(['request', 'user'])->findOrFail($id);
   }
}

