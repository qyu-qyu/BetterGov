<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Request as ServiceRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    // ─── List ─────────────────────────────────────────────────────────────────

    public function index(): JsonResponse
    {
        $user = Auth::user();
        $role = $user->role?->name;

        $query = Payment::with(['request.service:id,name', 'request.office:id,name'])->latest();

        if ($role === 'office') {
            $query->whereHas('request', fn($q) => $q->where('office_id', $user->office_id));
        } elseif ($role !== 'admin') {
            $query->where('user_id', $user->id);
        }

        $payments = $query->get()->map(fn($p) => [
            'id'             => $p->id,
            'request_id'     => $p->request_id,
            'service_name'   => $p->request?->service?->name,
            'office_name'    => $p->request?->office?->name,
            'amount'         => $p->amount,
            'currency'       => $p->currency ?? 'USD',
            'payment_method' => $p->payment_method,
            'status'         => $p->status,
            'transaction_id' => $p->transaction_id,
            'created_at'     => $p->created_at,
        ]);

        return response()->json(['success' => true, 'data' => $payments]);
    }

    public function show(int $id): JsonResponse
    {
        $payment = Payment::with(['request.service', 'request.office', 'user'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $payment]);
    }

    // ─── Stripe: create Payment Intent ────────────────────────────────────────

    public function stripeIntent(Request $request): JsonResponse
    {
        $data = $request->validate(['request_id' => 'required|exists:requests,id']);

        $serviceRequest = ServiceRequest::with('service:id,name,fee')->findOrFail($data['request_id']);

        if (Payment::where('request_id', $serviceRequest->id)->where('status', 'paid')->exists()) {
            return response()->json(['message' => 'This request has already been paid.'], 422);
        }

        $amountCents = (int) round(floatval($serviceRequest->service->fee ?? 0) * 100);
        if ($amountCents <= 0) {
            return response()->json(['message' => 'This service has no fee.'], 422);
        }

        $response = Http::withToken(config('services.stripe.secret'))
            ->asForm()
            ->post('https://api.stripe.com/v1/payment_intents', [
                'amount'                    => $amountCents,
                'currency'                  => 'usd',
                'automatic_payment_methods' => ['enabled' => 'true'],
                'metadata'                  => ['request_id' => $serviceRequest->id, 'user_id' => Auth::id()],
            ]);

        if (!$response->successful()) {
            Log::error('Stripe PI creation failed', ['body' => $response->body()]);
            return response()->json(['message' => 'Payment gateway error. Please try again.'], 502);
        }

        $intent  = $response->json();
        $payment = Payment::create([
            'request_id'       => $serviceRequest->id,
            'user_id'          => Auth::id(),
            'amount'           => $serviceRequest->service->fee,
            'currency'         => 'USD',
            'payment_method'   => 'card',
            'status'           => 'pending',
            'transaction_id'   => $intent['id'],
            'gateway_metadata' => ['stripe_pi_id' => $intent['id']],
        ]);

        return response()->json([
            'success'       => true,
            'client_secret' => $intent['client_secret'],
            'payment_id'    => $payment->id,
            'amount'        => $serviceRequest->service->fee,
            'publishable_key' => config('services.stripe.key'),
        ]);
    }

    // ─── Stripe: confirm ──────────────────────────────────────────────────────

    public function stripeConfirm(Request $request): JsonResponse
    {
        $data = $request->validate([
            'payment_id'        => 'required|exists:payments,id',
            'payment_intent_id' => 'required|string',
        ]);

        $payment  = Payment::findOrFail($data['payment_id']);
        $response = Http::withToken(config('services.stripe.secret'))
            ->get("https://api.stripe.com/v1/payment_intents/{$data['payment_intent_id']}");

        if (!$response->successful()) {
            return response()->json(['message' => 'Could not verify payment.'], 502);
        }

        $intent = $response->json();
        if ($intent['status'] === 'succeeded') {
            $payment->update(['status' => 'paid']);
            return response()->json(['success' => true, 'payment' => $payment->fresh()]);
        }

        return response()->json(['message' => 'Payment not confirmed. Status: ' . $intent['status']], 422);
    }

    // ─── Stripe webhook ───────────────────────────────────────────────────────

    public function stripeWebhook(Request $request): JsonResponse
    {
        $payload    = $request->getContent();
        $sigHeader  = $request->header('Stripe-Signature', '');
        $secret     = config('services.stripe.webhook_secret');

        // Parse signature header
        $parts = array_reduce(explode(',', $sigHeader), function ($carry, $part) {
            [$k, $v] = array_pad(explode('=', $part, 2), 2, '');
            if ($k === 't') $carry['timestamp'] = $v;
            if ($k === 'v1') $carry['signatures'][] = $v;
            return $carry;
        }, ['timestamp' => '', 'signatures' => []]);

        $expected = hash_hmac('sha256', "{$parts['timestamp']}.{$payload}", $secret);
        $valid    = array_reduce($parts['signatures'], fn($ok, $sig) => $ok || hash_equals($expected, $sig), false);

        if (!$valid) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);
        $pi    = $event['data']['object'] ?? [];

        if ($event['type'] === 'payment_intent.succeeded') {
            $payment = Payment::where('transaction_id', $pi['id'])->first();
            if ($payment && !$payment->isPaid()) $payment->update(['status' => 'paid']);
        }

        if ($event['type'] === 'payment_intent.payment_failed') {
            $payment = Payment::where('transaction_id', $pi['id'])->first();
            if ($payment) $payment->update(['status' => 'failed']);
        }

        return response()->json(['received' => true]);
    }

    // ─── Manual record (admin) ─────────────────────────────────────────────────

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'request_id'     => 'required|exists:requests,id',
            'amount'         => 'required|numeric|min:0',
            'payment_method' => 'required|string|max:255',
            'status'         => 'nullable|string|in:pending,paid,failed',
        ]);

        $data['user_id'] = Auth::id();
        $data['status']  = $data['status'] ?? 'pending';
        $data['currency'] = 'USD';

        $payment = Payment::create($data);

        return response()->json(['success' => true, 'message' => 'Payment recorded.', 'data' => $payment], 201);
    }
}