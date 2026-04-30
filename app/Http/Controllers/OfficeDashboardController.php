<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\OfficeTimeSlot;
use App\Models\ResponseDocument;
use App\Models\Feedback;
use App\Models\FeedbackResponse;


class OfficeDashboardController extends Controller
{
    public function requestStatusSummary()
    {
        $summary = [
            'total_requests' => RequestModel::count('*'),
            'pending' => RequestModel::where('status', '=', 'pending', 'and')->count(),
            'processing' => RequestModel::where('status', '=', 'processing', 'and')->count(),
            'approved' => RequestModel::where('status', '=', 'approved', 'and')->count(),
            'rejected' => RequestModel::where('status', '=', 'rejected', 'and')->count(),
            'completed' => RequestModel::where('status', '=', 'completed', 'and')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Request status summary retrieved successfully',
            'data' => $summary
        ], 200);
    }

    public function appointmentSummary()
{
    $summary = [
        'total_slots' => OfficeTimeSlot::count('*'),
        'active_slots' => OfficeTimeSlot::where('is_active', '=', true, 'and')->count(),
        'inactive_slots' => OfficeTimeSlot::where('is_active', '=', false, 'and')->count(),
    ];

    return response()->json([
        'success' => true,
        'message' => 'Appointment summary retrieved successfully',
        'data' => $summary
    ], 200);
}

public function documentSummary()
{
    $summary = [
        'total_documents' => ResponseDocument::count('*'),
    ];

    return response()->json([
        'success' => true,
        'message' => 'Response document summary retrieved successfully',
        'data' => $summary
    ], 200);
}




public function dashboard()
{
    $data = [
        'requests' => [
            'total_requests' => RequestModel::count('*'),
            'pending' => RequestModel::where('status', '=', 'pending', 'and')->count(),
            'processing' => RequestModel::where('status', '=', 'processing', 'and')->count(),
            'approved' => RequestModel::where('status', '=', 'approved', 'and')->count(),
            'rejected' => RequestModel::where('status', '=', 'rejected', 'and')->count(),
            'completed' => RequestModel::where('status', '=', 'completed', 'and')->count(),
        ],

        'appointments' => [
            'total_slots' => OfficeTimeSlot::count('*'),
            'active_slots' => OfficeTimeSlot::where('is_active', '=', true, 'and')->count(),
            'inactive_slots' => OfficeTimeSlot::where('is_active', '=', false, 'and')->count(),
        ],

        'response_documents' => [
            'total_documents' => ResponseDocument::count('*'),
        ],
    ];

    return response()->json([
        'success' => true,
        'message' => 'Office dashboard data retrieved successfully',
        'data' => $data
    ], 200);
}
}