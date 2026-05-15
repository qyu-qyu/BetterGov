<?php

namespace App\Http\Controllers;

use App\Models\Request as ServiceRequest;
use App\Models\Appointment;
use App\Models\Feedback;
use App\Models\Office;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OfficePortalController extends Controller
{
    private function officeId(): int
    {
        return Auth::user()->office_id;
    }

    public function dashboard(): JsonResponse
    {
        $officeId = $this->officeId();

        $requests = ServiceRequest::where('office_id', $officeId);

        $stats = [
            'total'             => (clone $requests)->count(),
            'pending'           => (clone $requests)->where('status', 'pending')->count(),
            'processing'        => (clone $requests)->where('status', 'processing')->count(),
            'approved'          => (clone $requests)->where('status', 'approved')->count(),
            'completed'         => (clone $requests)->where('status', 'completed')->count(),
            'rejected'          => (clone $requests)->where('status', 'rejected')->count(),
            'missing_documents' => (clone $requests)->where('status', 'missing_documents')->count(),
        ];

        $revenue = Payment::whereHas('request', fn($q) => $q->where('office_id', $officeId))
            ->where('status', 'paid')
            ->sum('amount');

        $appointments = Appointment::where('office_id', $officeId)->count();

        $avgRating = Feedback::whereHas('request', fn($q) => $q->where('office_id', $officeId))
            ->avg('rating');

        $recentRequests = ServiceRequest::with(['user:id,name,email', 'service:id,name'])
            ->where('office_id', $officeId)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn($r) => [
                'id'           => $r->id,
                'citizen_name' => $r->user->name ?? null,
                'service_name' => $r->service->name ?? null,
                'status'       => $r->status,
                'created_at'   => $r->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'requests'       => $stats,
                'revenue'        => $revenue,
                'appointments'   => $appointments,
                'avg_rating'     => $avgRating ? round($avgRating, 1) : null,
                'recent_requests'=> $recentRequests,
            ],
        ]);
    }

    public function profile(): JsonResponse
    {
        $office = Office::with(['municipality', 'officeType', 'services'])
            ->findOrFail($this->officeId());

        return response()->json(['success' => true, 'data' => $office]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $office = Office::findOrFail($this->officeId());

        $data = $request->validate([
            'name'          => 'sometimes|string|max:255',
            'address'       => 'nullable|string|max:500',
            'phone'         => 'nullable|string|max:30',
            'email'         => 'nullable|email|max:255',
            'latitude'      => 'nullable|numeric|between:-90,90',
            'longitude'     => 'nullable|numeric|between:-180,180',
            'working_hours' => 'nullable|string|max:500',
        ]);

        $office->update($data);

        return response()->json(['success' => true, 'message' => 'Profile updated.', 'data' => $office]);
    }

    public function notifications(): JsonResponse
    {
        $officeId = $this->officeId();

        $notifications = \App\Models\Notification::whereHas(
            'request', fn($q) => $q->where('office_id', $officeId)
        )->latest()->limit(20)->get();

        return response()->json(['success' => true, 'data' => $notifications]);
    }
}
