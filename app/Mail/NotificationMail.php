<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    // All public properties are auto-passed to the Blade view
    public string $notifMessage;
    public string $notifType;
    public ?int   $requestId;
    public string $recipientName;
    public string $actionUrl;
    public string $actionLabel;

    // Resolved in constructor so the template stays logic-free
    public string $barColor;
    public string $pillBg;
    public string $typeLabel;
    public string $typeIcon;

    public function __construct(
        string $notifMessage,
        string $notifType,
        ?int   $requestId,
        string $recipientName,
        string $actionUrl   = '',
        string $actionLabel = 'View Request'
    ) {
        $this->notifMessage  = $notifMessage;
        $this->notifType     = $notifType;
        $this->requestId     = $requestId;
        $this->recipientName = $recipientName;
        $this->actionUrl     = $actionUrl ?: url('/');
        $this->actionLabel   = $actionLabel;

        // Resolve type-specific values here — keeps the template simple
        $barColors = [
            'status_change'     => '#1a56db',
            'new_message'       => '#059669',
            'document_uploaded' => '#7c3aed',
            'new_request'       => '#d97706',
        ];
        // Solid light backgrounds instead of broken alpha hex
        $pillBgs = [
            'status_change'     => '#dbeafe',
            'new_message'       => '#d1fae5',
            'document_uploaded' => '#ede9fe',
            'new_request'       => '#fef3c7',
        ];
        $typeLabels = [
            'status_change'     => 'Status Update',
            'new_message'       => 'New Message',
            'document_uploaded' => 'Document Uploaded',
            'new_request'       => 'New Request',
        ];
        // Text icons instead of emoji — better email client compatibility
        $typeIcons = [
            'status_change'     => '[~]',
            'new_message'       => '[msg]',
            'document_uploaded' => '[doc]',
            'new_request'       => '[new]',
        ];

        $this->barColor  = $barColors[$notifType]  ?? '#1a56db';
        $this->pillBg    = $pillBgs[$notifType]    ?? '#dbeafe';
        $this->typeLabel = $typeLabels[$notifType] ?? 'Notification';
        $this->typeIcon  = $typeIcons[$notifType]  ?? '[!]';
    }

    public function envelope(): Envelope
    {
        $subjects = [
            'status_change'     => 'Your request status has been updated',
            'new_message'       => 'New message on your request',
            'document_uploaded' => 'A document has been uploaded to your request',
            'new_request'       => 'New service request received',
        ];

        return new Envelope(
            subject: $subjects[$this->notifType] ?? 'BetterGov Notification',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'notifMessage'  => $this->notifMessage,
                'notifType'     => $this->notifType,
                'requestId'     => $this->requestId,
                'recipientName' => $this->recipientName,
                'actionUrl'     => $this->actionUrl,
                'actionLabel'   => $this->actionLabel,
                'barColor'      => $this->barColor,
                'pillBg'        => $this->pillBg,
                'typeLabel'     => $this->typeLabel,
                'typeIcon'      => $this->typeIcon,
            ],
        );
    }
}