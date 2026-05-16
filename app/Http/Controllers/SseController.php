<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SseController extends Controller
{
    /**
     * SSE stream endpoint.
     *
     * EventSource cannot send Authorization headers, so we accept
     * the Sanctum token via ?token= query parameter and authenticate manually.
     */
    public function stream(Request $request): StreamedResponse
    {
        // ── Authenticate via query string token ─────────────────────────────
        $rawToken = $request->query('token');

        if (!$rawToken) {
            abort(401, 'Token required.');
        }

        $token = PersonalAccessToken::findToken($rawToken);

        if (!$token || !$token->tokenable) {
            abort(401, 'Invalid token.');
        }

        $user   = $token->tokenable;
        $userId = $user->id;

        return response()->stream(function () use ($userId) {

            if (ob_get_level()) ob_end_flush();

            $this->send('connected', ['userId' => $userId]);

            // Send current unread count immediately
            $unread = Notification::where('user_id', $userId)
                ->where('is_read', false)->count();
            $this->send('notification', ['unread' => $unread]);

            $elapsed   = 0;
            $maxTime   = 55;
            $pollEvery = 2;
            $heartbeat = 20;

            while ($elapsed < $maxTime) {
                if (connection_aborted()) break;

                sleep($pollEvery);
                $elapsed += $pollEvery;

                $cacheKey = "sse_notify_user_{$userId}";
                if (Cache::has($cacheKey)) {
                    Cache::forget($cacheKey);

                    $unread = Notification::where('user_id', $userId)
                        ->where('is_read', false)->count();

                    $latest = Notification::where('user_id', $userId)->latest()->first();

                    $this->send('notification', [
                        'unread' => $unread,
                        'latest' => $latest ? [
                            'id'         => $latest->id,
                            'message'    => $latest->message,
                            'type'       => $latest->type ?? 'status_change',
                            'request_id' => $latest->request_id,
                            'created_at' => (string) $latest->created_at,
                        ] : null,
                    ]);
                }

                if ($elapsed % $heartbeat === 0) {
                    echo ": heartbeat\n\n";
                    flush();
                }
            }

            $this->send('reconnect', ['delay' => 3000]);

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }

    private function send(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data) . "\n\n";
        flush();
    }
}