<?php

namespace App\Jobs;

use App\Mail\NotificationMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int    $tries   = 3;
    public int    $backoff  = 60; // seconds between retries

    public function __construct(
        public readonly int    $userId,
        public readonly string $message,
        public readonly string $type,
        public readonly ?int   $requestId
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);

        if (!$user) {
            Log::warning('SendNotificationEmail: user not found', ['user_id' => $this->userId]);
            return;
        }

        // Respect the user's email notification preference
        if (isset($user->email_notifications) && !$user->email_notifications) {
            return;
        }

        // Build the action URL based on the user's role
        $role      = $user->role?->name ?? 'citizen';
        $actionUrl = '';

        if ($this->requestId) {
            $actionUrl = $role === 'office'
                ? url("/office/requests/{$this->requestId}")
                : url("/citizen/requests/{$this->requestId}");
        }

        $actionLabel = match($this->type) {
            'new_request'       => 'View Request',
            'new_message'       => 'Open Conversation',
            'document_uploaded' => 'Download Document',
            default             => 'View Request',
        };

        Mail::to($user->email)->send(new NotificationMail(
            notifMessage:  $this->message,
            notifType:     $this->type,
            requestId:     $this->requestId,
            recipientName: $user->name ?? 'User',
            actionUrl:     $actionUrl,
            actionLabel:   $actionLabel,
        ));
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendNotificationEmail job failed', [
            'user_id' => $this->userId,
            'error'   => $e->getMessage(),
        ]);
    }
}