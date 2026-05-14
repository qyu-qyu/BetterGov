<?php

namespace App\Http\Controllers;

use App\Models\Request as RequestModel;
use App\Models\OfficeTimeSlot;
use App\Models\ResponseDocument;
use App\Models\Payment;
use App\Models\Office;
use Illuminate\Support\Facades\DB;

class OfficeDashboardController extends Controller
{
    public function dashboard()
    {
        $data = [
            'requests' => [
                'total'      => RequestModel::count(),
                'pending'    => RequestModel::where('status', 'pending')->count(),
                'processing' => RequestModel::where('status', 'processing')->count(),
                'approved'   => RequestModel::where('status', 'approved')->count(),
                'rejected'   => RequestModel::where('status', 'rejected')->count(),
                'completed'  => RequestModel::where('status', 'completed')->count(),
            ],
            'appointments' => [
                'total_slots'    => OfficeTimeSlot::count(),
                'active_slots'   => OfficeTimeSlot::where('is_active', true)->count(),
                'inactive_slots' => OfficeTimeSlot::where('is_active', false)->count(),
            ],
            'response_documents' => [
                'total' => ResponseDocument::count(),
            ],
            'revenue' => [
                'total' => Payment::where('status', 'paid')->sum('amount'),
            ],
        ];

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function requestStatusSummary()
    {
        $summary = [
            'total'      => RequestModel::count(),
            'pending'    => RequestModel::where('status', 'pending')->count(),
            'processing' => RequestModel::where('status', 'processing')->count(),
            'approved'   => RequestModel::where('status', 'approved')->count(),
            'rejected'   => RequestModel::where('status', 'rejected')->count(),
            'completed'  => RequestModel::where('status', 'completed')->count(),
        ];

        return response()->json(['success' => true, 'data' => $summary]);
    }

    public function appointmentSummary()
    {
        $summary = [
            'total_slots'    => OfficeTimeSlot::count(),
            'active_slots'   => OfficeTimeSlot::where('is_active', true)->count(),
            'inactive_slots' => OfficeTimeSlot::where('is_active', false)->count(),
        ];

        return response()->json(['success' => true, 'data' => $summary]);
    }

    public function documentSummary()
    {
        return response()->json([
            'success' => true,
            'data'    => ['total_documents' => ResponseDocument::count()],
        ]);
    }

    public function requestsPerOffice()
    {
        $data = Office::select('offices.id', 'offices.name')
            ->withCount('requests as total_requests')
            ->withCount(['requests as pending_requests'    => fn($q) => $q->where('status', 'pending')])
            ->withCount(['requests as processing_requests' => fn($q) => $q->where('status', 'processing')])
            ->withCount(['requests as completed_requests'  => fn($q) => $q->where('status', 'completed')])
            ->withCount(['requests as rejected_requests'   => fn($q) => $q->where('status', 'rejected')])
            ->orderByDesc('total_requests')
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function revenueReport()
    {
        $total = Payment::where('status', 'paid')->sum('amount');

        $perOffice = Office::select('offices.id', 'offices.name')
            ->leftJoin('requests', 'requests.office_id', '=', 'offices.id')
            ->leftJoin('payments', function ($join) {
                $join->on('payments.request_id', '=', 'requests.id')
                     ->where('payments.status', '=', 'paid');
            })
            ->selectRaw('offices.id, offices.name, COALESCE(SUM(payments.amount),0) as revenue, COUNT(DISTINCT payments.id) as payment_count')
            ->groupBy('offices.id', 'offices.name')
            ->orderByDesc('revenue')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => ['total_revenue' => $total, 'per_office' => $perOffice],
        ]);
    }
}
