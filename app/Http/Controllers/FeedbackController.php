<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use App\Models\FeedbackResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;    

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'request_id' => 'required|exists:requests,id',
            'rating'     => 'required|integer|min:1|max:5',
            'comment'    => 'nullable|string|max:2000',
        ]);

        $existing = \App\Models\Feedback::where('user_id', Auth::id())
            ->where('request_id', $data['request_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'You have already submitted feedback for this request.'], 422);
        }

        $feedback = \App\Models\Feedback::create([
            'user_id'    => Auth::id(),
            'request_id' => $data['request_id'],
            'rating'     => $data['rating'],
            'comment'    => $data['comment'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully.',
            'data'    => $feedback,
        ], 201);
    }

    public function index()
    {
        $feedback = Feedback::with([
            'user:id,name,email',
            'serviceRequest:id,service_id,office_id,status,notes'
        ])
        ->latest()
        ->get()
        ->map(function ($item) {
            return [
                'id' => $item->id,
                'citizen_name' => $item->user->name ?? null,
                'citizen_email' => $item->user->email ?? null,
                'request_id' => $item->service_request_id,
                'request_status' => $item->serviceRequest->status ?? null,
                'rating' => $item->rating,
                'comment' => $item->comment,
                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Feedback retrieved successfully',
            'data' => $feedback
        ], 200);
    }

    public function show($id)
{
    $feedback = Feedback::with([
        'user:id,name,email',
        'serviceRequest:id,service_id,office_id,status,notes'
    ])->findOrFail($id);

    return response()->json([
        'success' => true,
        'message' => 'Feedback details retrieved successfully',
        'data' => $feedback
    ], 200);
}


public function respond(Request $request, $id)
{
    $data = $request->validate([
        'response' => 'required|string|max:2000',
    ]);

    $feedback = Feedback::findOrFail($id);

    $response = FeedbackResponse::create([
        'feedback_id' => $feedback->id,
        'responded_by' => Auth::id(),
        'response' => $data['response'],
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Feedback response added successfully',
        'data' => $response
    ], 201);
}
}