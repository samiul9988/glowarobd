<?php
namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $data;
    public string $type;

    /**
     * Create a new message instance.
     */
    public function __construct(array $data, string $type)
    {
        $this->data = $data;
        $this->type = $type;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = data_get($this->data, "subject") ?? "";
        if (!$subject) {
            $subject = match ($this->type) {
                'appointment-letter' => 'Appointment Letter - ' . data_get($this->data, 'role', config('app.name')),
                'joining-letter' => 'Joining Letter - ' . data_get($this->data, 'role', config('app.name')),
                'noc' => 'No Objection Certificate - ' . config('app.name'),
                'promotion-letter' => 'Promotion Letter - ' . config('app.name'),
                'increment-letter' => 'Increment Letter - ' . config('app.name'),
                default => 'Document from ' . config('app.name'),
            };
        }
        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $view = match ($this->type) {
            'appointment-letter' => 'emails.appointment_letter',
            'joining-letter' => 'emails.joining_letter',
            'noc' => 'emails.noc',
            'promotion-letter' => 'emails.promotion_letter',
            'increment-letter' => 'emails.increment_letter',
            default => '',
        };
        if ($view === '') {
            throw new \Exception('No email template found for ' . $this->type . '. Please set up the template and try again.');
        }
        return new Content(
            view: $view,
            with: [
                'candidate_name' => data_get($this->data, 'candidate_name', 'Candidate'),
                'role'           => data_get($this->data, 'role', 'Role'),
                'joining_date'   => data_get($this->data, 'joining_date', ''),
                'reporting_time' => data_get($this->data, 'reporting_time', ''),
                'working_shift'  => data_get($this->data, 'working_shift', ''),
                'working_hours'  => data_get($this->data, 'working_hours', ''),
                'salary'         => data_get($this->data, 'salary', 0),
                'deadline'       => data_get($this->data, 'deadline', ''),
                'content'        => data_get($this->data, 'content', null),
                'employee_id'    => data_get($this->data, 'employee_id', null),
                'issue_date'     => data_get($this->data, 'issue_date', null),
            ],
        );
    }

    /**
     * Attachments (optional)
     */
    public function attachments(): array
    {
        $attachment = data_get($this->data, 'attachment', null);
        if (!$attachment || is_integer($attachment)) {
            return [];
        }
        return [
            // Example of attaching a file from storage
            // Storage::disk('local')->exists($this->data['attachment']) ? Storage::disk('local')->path($this->data['attachment']) : null,
            $attachment ?? null,
        ];
    }
}
