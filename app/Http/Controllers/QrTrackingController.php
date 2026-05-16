<?php

namespace App\Http\Controllers;

use App\Models\Request as ServiceRequest;
use App\Services\QrCodeService;
use Illuminate\Http\Request;

class QrTrackingController extends Controller
{
    /**
     * Public tracking page — no auth required.
     */
    public function show(string $token)
    {
        return view('tracking.show', ['token' => $token]);
    }

    /**
     * Returns the QR code as an inline SVG for a given request ID.
     * Auth required — citizen can only get their own request's QR.
     */
    public function qrImage(int $requestId)
    {
        $request = ServiceRequest::where('id', $requestId)
            ->whereNotNull('qr_token')
            ->firstOrFail();

        $url = $request->trackingUrl();
        $svg = QrCodeService::generate($url, 200);

        return response($svg, 200)
            ->header('Content-Type', 'image/svg+xml')
            ->header('Cache-Control', 'public, max-age=86400');
    }

    /**
     * API endpoint returning the request status for a given QR token.
     * Public — no auth required.
     */
    public function status(string $token)
    {
        $request = ServiceRequest::where('qr_token', $token)
            ->with([
                'service:id,name',
                'office:id,name,address,phone',
                'statusHistories' => fn($q) => $q->latest()->take(5),
            ])
            ->first();

        if (!$request) {
            return response()->json(['success' => false, 'message' => 'Request not found.'], 404);
        }

        $statusLabels = [
            'pending'           => 'Pending',
            'processing'        => 'In Review',
            'approved'          => 'Approved',
            'rejected'          => 'Rejected',
            'completed'         => 'Completed',
            'missing_documents' => 'Missing Documents',
        ];

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $request->id,
                'status'       => $request->status,
                'status_label' => $statusLabels[$request->status] ?? ucfirst($request->status),
                'service_name' => $request->service?->name,
                'office_name'  => $request->office?->name,
                'office_phone' => $request->office?->phone,
                'office_address' => $request->office?->address,
                'submitted_at' => $request->created_at,
                'updated_at'   => $request->updated_at,
                'history'      => $request->statusHistories->map(fn($h) => [
                    'status'     => $statusLabels[$h->new_status] ?? ucfirst($h->new_status),
                    'comment'    => $h->comment,
                    'created_at' => $h->created_at,
                ]),
            ],
        ]);
    }
}