<?php
namespace App\Http\Controllers;

use App\Mail\EmailManager;
use App\Models\Subscriber;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        $userEmails = Cache::remember("all_user_emails", now()->addDay(), function () use ($request) {
            $emails = User::whereNotNull('email')->where('user_type', 'customer')->where('email', '!=', '')->pluck('email')->toArray();
            return array_filter($emails, function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) > 0;
            });
        });
        $subscriberEmails = Cache::remember("all_subscriber_emails", now()->addDay(), function () use ($request) {
            $emails = Subscriber::where('email', '!=', '')->pluck('email')->toArray();
            return array_filter($emails, function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) > 0;
            });
        });

        return view('backend.marketing.newsletters.index', compact('userEmails', 'subscriberEmails'));
    }

    public function send(Request $request)
    {
        if (env('MAIL_FROM_ADDRESS') != null) {
            //sends newsletter to selected users
            if ($request->has('user_emails')) {
                foreach ($request->user_emails as $key => $email) {
                    $array['view']    = 'emails.newsletter';
                    $array['subject'] = $request->subject;
                    $array['from']    = env('MAIL_FROM_ADDRESS');
                    $array['content'] = $request->content;

                    try {
                        Mail::to($email)->queue(new EmailManager($array));
                    } catch (\Exception $e) {
                        //dd($e);
                    }
                }
            }

            //sends newsletter to subscribers
            if ($request->has('subscriber_emails')) {
                foreach ($request->subscriber_emails as $key => $email) {
                    $array['view']    = 'emails.newsletter';
                    $array['subject'] = $request->subject;
                    $array['from']    = env('MAIL_FROM_ADDRESS');
                    $array['content'] = $request->content;

                    try {
                        Mail::to($email)->queue(new EmailManager($array));
                    } catch (\Exception $e) {
                        //dd($e);
                    }
                }
            }
        } else {
            flash(('Please configure SMTP first'))->error();
            return back();
        }

        flash(('Newsletter has been send'))->success();
        return redirect()->route('admin.dashboard');
    }

    public function testEmail(Request $request)
    {
        $array['view']    = 'emails.newsletter';
        $array['subject'] = "SMTP Test";
        $array['from']    = env('MAIL_FROM_ADDRESS');
        $array['content'] = "This is a test email.";

        try {
            Mail::to($request->email)->queue(new EmailManager($array));
        } catch (\Exception $e) {
            dd($e);
        }

        flash(('An email has been sent.'))->success();
        return back();
    }
}
