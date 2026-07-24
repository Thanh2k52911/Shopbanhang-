<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailVerificationOtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public User $user,
        public string $otp,
        public int $expiresInMinutes
    ) {
    }

    /**
     * Tiêu đề email.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Mã xác minh email - Cosmetic Shop'
        );
    }

    /**
     * Giao diện nội dung email.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.auth.email-verification-otp'
        );
    }

    /**
     * File đính kèm.
     */
    public function attachments(): array
    {
        return [];
    }
}
