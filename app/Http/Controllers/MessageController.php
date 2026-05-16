<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Request as ServiceRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $role = $user->role?->name;

        $query = Message::with(['request', 'sender:id,name']);

        if ($role !== 'admin') {
            $query->whereHas('request', function ($q) use ($user, $role) {
                if ($role === 'office') {
                    $q->where('office_id', $user->office_id);
                } else {
                    $q->where('user_id', $user->id);
                }
            });
        }

        return response()->json(['success' => true, 'data' => $query->latest()->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'request_id' => 'required|exists:requests,id',
            'message'    => 'required|string|max:1000',
        ]);

        $data['sender_id'] = Auth::id();
        $msg = Message::create($data);

        // ── Notify the other party + push via SSE ───────────────────────────
        $serviceRequest = ServiceRequest::find($data['request_id']);
        if ($serviceRequest) {
            $sender     = Auth::user();
            $senderRole = $sender->role?->name;
            $senderName = $sender->name ?? 'Someone';

            if ($senderRole === 'citizen') {
                // Citizen messaged → notify office staff
                NotificationService::notifyOfficeStaff(
                    $serviceRequest->office_id,
                    $serviceRequest->id,
                    "New message from {$senderName} on request #{$serviceRequest->id}.",
                    NotificationService::TYPE_NEW_MESSAGE
                );
            } else {
                // Office/admin messaged → notify citizen
                NotificationService::notify(
                    $serviceRequest->user_id,
                    $serviceRequest->id,
                    "New message from the office on your request #{$serviceRequest->id}.",
                    NotificationService::TYPE_NEW_MESSAGE
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Message sent.',
            'data'    => $msg->load('sender:id,name'),
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