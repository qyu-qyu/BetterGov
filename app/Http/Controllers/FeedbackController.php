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

        $existing = Feedback::where('user_id', Auth::id())
            ->where('request_id', $data['request_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'You have already submitted feedback for this request.'], 422);
        }

        $feedback = Feedback::create([
            'user_id'    => Auth::id(),
            'request_id' => $data['request_id'],
            'rating'     => $data['rating'],
            'comment'    => $data['comment'] ?? null,
        ]);

        return response()->json(['success' => true, 'message' => 'Feedback submitted.', 'data' => $feedback], 201);
    }

    public function index()
    {
        $user     = Auth::user();
        $role     = $user->role?->name;

        $query = Feedback::with(['user:id,name,email', 'request:id,office_id,status', 'responses'])->latest();

        if ($role === 'office') {
            $query->whereHas('request', fn($q) => $q->where('office_id', $user->office_id));
        }

        $feedback = $query->get()->map(fn($item) => [
            'id'            => $item->id,
            'citizen_name'  => $item->user->name  ?? null,
            'citizen_email' => $item->user->email ?? null,
            'request_id'    => $item->request_id,
            'rating'        => $item->rating,
            'comment'       => $item->comment,
            'responses'     => $item->responses,
            'created_at'    => $item->created_at,
        ]);

        return response()->json(['success' => true, 'data' => $feedback], 200);
    }

    public function show(string $id)
    {
        $feedback = Feedback::with(['user:id,name,email', 'request', 'responses.responder:id,name'])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $feedback], 200);
    }

    public function respond(Request $request, string $id)
    {
        $data = $request->validate([
            'response' => 'required|string|max:2000',
        ]);

        $feedback = Feedback::findOrFail($id);

        // Office users may only respond to feedback on their own office's requests
        $user = Auth::user();
        if ($user->role?->name === 'office') {
            $feedback->loadMissing('request');
            if ($feedback->request->office_id !== $user->office_id) {
                return response()->json(['message' => 'Forbidden.'], 403);
            }
        }

        $response = FeedbackResponse::create([
            'feedback_id'  => $feedback->id,
            'responded_by' => Auth::id(),
            'response'     => $data['response'],
        ]);

        return response()->json(['success' => true, 'data' => $response], 201);
    }
}
