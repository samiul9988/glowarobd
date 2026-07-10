<?php

namespace App\Mail;

use App\Models\MailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustomerCouponAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $info;
    protected $template;
    protected $dynamicVariables = [];
    public function __construct(array $info)
    {
        $this->info = $info;
        $this->template = MailTemplate::where('type', 'Coupon Assignment')->where('status', 1)->first();
    }

    public function build()
    {
        $content = $this->prepareContent();
        $subject = $this->prepareSubject();
        if ($content) {
            return $this->subject($subject)
                ->view('emails.all_mail', ['content' => $content]);
        }
        // If no content, do nothing and mark as completed
        return null;
    }

    private function prepareSubject()
    {
        if($this->template && $this->template->subject){
            return templateReplace($this->template->subject, $this->dynamicVariables);
        }
        return '🥳 Surprise! Your Exclusive Coupon Has Arrived!';
    }

    private function prepareContent()
    {
        if ($this->template && $this->template->content) {
            $logo = get_setting('header_logo');
            if ($logo) {
                $logo = uploaded_asset($logo);
            } else {
                $logo = static_asset('assets/img/logo.png');
            }

            $this->dynamicVariables =[
                "logo" => $logo ? '<img src="'.$logo.'" alt="'.config('app.name').'" style="max-width:150px; margin-bottom:10px;">' : '',
                "app_url" => config('app.frontend'),
                "app_name" => config('app.name'),
                "current_year" => date('Y'),
                "customer" => $this->info['customer'] ?? '',
                "coupon" => $this->info['coupon'] ?? '',
                "validity" => $this->info['validity'] ?? '',
                "app_links" => $this->getAppLinks(),
                "social_links" => $this->getSocialLinks()
            ];

            return templateReplace($this->template->content, $this->dynamicVariables);
        }
        return null;
    }

    private function getAppLinks()
    {
        $app_links = '';
        if(filled(get_setting('play_store_link'))){
            $app_links .= '<a style="font-size:16px; background-color: rgb(0, 0, 0);width:28px;height:28px;border-radius: 50px; display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer;" target="_blank" href="'.get_setting('play_store_link').'"><img src="'.static_asset('assets/img/play-store.svg').'" alt=""></a>';
        }
        if(filled(get_setting('app_store_link'))){
            $app_links .= '<a style="font-size:16px; background-color: rgb(59, 89, 152);width:28px;height:28px;border-radius: 50px; display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer;" target="_blank" href="'.get_setting('app_store_link').'"><img src="'.static_asset('assets/img/app-store.svg').'" alt=""></a>';
        }
        return $app_links;
    }

    private function getSocialLinks()
    {
        $social_links = '';
        if(filled(get_setting('facebook_link'))){
            $social_links .= '<a style="font-size:16px; background-color: rgb(59, 89, 152);width:28px;height:28px;border-radius: 50px; display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer; " target="_blank" href="'.get_setting('facebook_link').'"><img src="'.static_asset('assets/img/facebook.svg').'" alt=""></a>';
        }
        if(filled(get_setting('twitter_link'))){
            $social_links .= '<a style="font-size:16px; background-color: rgb(29, 161, 242);width:28px;height:28px;border-radius: 50px; display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer; " target="_blank" href="'.get_setting('twitter_link').'"><img src="'.static_asset('assets/img/twitter.svg').'" alt=""></a>';
        }
        if(filled(get_setting('instagram_link'))){
            $social_links .= '<a style="font-size:16px; background-color: rgb(225, 48, 108);width:28px;height:28px;border-radius: 50px; display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer; " target="_blank" href="'.get_setting('instagram_link').'"><img src="'.static_asset('assets/img/instagram.svg').'" alt=""></a>';
        }
        if(filled(get_setting('youtube_link'))){
            $social_links .= '<a style="font-size:16px; background-color: rgb(255, 0, 0);width:28px;height:28px;border-radius: 50px; display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer; " target="_blank" href="'.get_setting('youtube_link').'"><img src="'.static_asset('assets/img/youtube.svg').'" alt=""></a>';
        }
        if(filled(get_setting('linkedin_link'))){
            $social_links .= '<a style="font-size:16px; background-color: rgb(0, 119, 181);width:28px;height:28px;border-radius: 50px; display:flex;align-items: center;justify-content: center; color:#fff;cursor: pointer; " target="_blank" href="'.get_setting('linkedin_link').'"><img src="'.static_asset('assets/img/linkedin.svg').'" alt=""></a>';
        }
        return $social_links;
    }
}
