<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FallbackMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $content;
    public function __construct($content)
    {
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Your Opinion Matters - Help Us Improve ' . config('app.name'))->view('emails.fallback_email', [
            'content' => $this->content,
        ]);
    }
}
