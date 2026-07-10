<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InterviewMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $data;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->data['subject'] ?? 'Interview Invitation - '.$this->data['role'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.interview',
            with: [
                'candidate_name'   => $this->data['candidate_name'],
                'role'             => $this->data['role'],
                'company_name'     => $this->data['company_name'],
                'interview_date'   => $this->data['interview_date'],
                'interview_time'   => $this->data['interview_time'],
                'year'             => now()->year,
                'content'         => $this->data['content'] ?? null,
            ],
        );
    }

    /**
     * Attachments (optional)
     */
    public function attachments(): array
    {
        return [];
    }
}
