<?php

namespace App\Mail;

use App\Models\MailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SecondEmailVerifyMailManager extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public $array;

    public function __construct($array)
    {
        $this->array = $array;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if (isset($this->array['link']) && !empty($this->array['link'])) {
            $mail_template = MailTemplate::where('type', 'Change Mail')->where('status', 1)->first();
            $arr = [
                "logo" => '<img src="' . uploaded_asset(get_setting('header_logo')) . '" width="191" alt="" />',
                "app_url" => config('app.frontend'),
                "app_name" => config('app.name'),
                "verify_link" => $this->array['link'],
                "current_year" => date('Y')
            ];
        } else {
            $mail_template = MailTemplate::where('type', 'Forget Password')->where('status', 1)->first();
            $arr = [
                "logo" => '<img src="' . uploaded_asset(get_setting('header_logo')) . '" width="191" alt="" />',
                "app_url" => config('app.frontend'),
                "app_name" => config('app.name'),
                "verify_code" => $this->array['content'],
                "current_year" => date('Y')
            ];
        }

        if ($mail_template) :
            $content = templateReplace($mail_template->content, $arr);
            return $this->view('emails.all_mail', ['content' => $content])
                ->from($this->array['from'], env('MAIL_FROM_NAME'))
                ->subject(templateReplace($mail_template->subject, $arr));
        endif;
    }
}
