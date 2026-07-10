<?php

namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\MailTemplate;


class EmailVerificationNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $notifiable->verification_code = encrypt($notifiable->id);
        $notifiable->save();

        $mail_template = MailTemplate::where('type', 'Email Verification')->where('status', 1)->first();

        if($mail_template):
            $arr =[
                "logo" => '<img src="'.uploaded_asset(get_setting('header_logo')) .'" width="191" alt="" />',
                "app_url" => config('app.frontend'),
                "app_name" => config('app.name'),
                "verify_link" => route('email.verification.confirmation', $notifiable->verification_code),
                "current_year" => date('Y')
            ];

            $content = templateReplace($mail_template->content, $arr);

            return (new MailMessage)
                ->view('emails.all_mail', ['content' => $content])
                ->subject(templateReplace($mail_template->subject,$arr));
        endif;
    }

    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
