<?php

namespace App\Services;

use App\Jobs\SendNotificationEmail;
use App\Models\Notification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    const TYPE_STATUS_CHANGE     = 'status_change';
    const TYPE_NEW_MESSAGE       = 'new_message';
    const TYPE_DOCUMENT_UPLOADED = 'document_uploaded';
    const TYPE_NEW_REQUEST       = 'new_request';

    /**
     * Create a notification, signal SSE cache, and queue an email.
     */
    public static function notify(
        int    $userId,
        int    $requestId,
        string $message,
        string $type = self::TYPE_STATUS_CHANGE
    ): ?Notification {
        try {
            // 1. Save to DB
            $notification = Notification::create([
                'user_id'    => $userId,
                'request_id' => $requestId,
                'message'    => $message,
                'type'       => $type,
                'is_read'    => false,
            ]);

            // 2. Signal the polling endpoint (cache flag)
            Cache::put("sse_notify_user_{$userId}", true, 60);

            // 3. Queue the email — dispatched async, won't slow down the request
            SendNotificationEmail::dispatch($userId, $message, $type, $requestId)
                ->onQueue('emails');

            return $notification;

        } catch (\Throwable $e) {
            Log::error('NotificationService::notify failed', [
                'user_id'    => $userId,
                'request_id' => $requestId,
                'error'      => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Notify all office staff for a given office.
     */
    public static function notifyOfficeStaff(
        int    $officeId,
        int    $requestId,
        string $message,
        string $type = self::TYPE_NEW_REQUEST
    ): void {
        try {
            $officeUsers = \App\Models\User::whereHas('role', fn($q) => $q->where('name', 'office'))
                ->where('office_id', $officeId)
                ->pluck('id');

            foreach ($officeUsers as $userId) {
                static::notify($userId, $requestId, $message, $type);
            }
        } catch (\Throwable $e) {
            Log::error('NotificationService::notifyOfficeStaff failed', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}