<?php
namespace App\Http\Controllers;

use App\Jobs\SendBulkSmsJob;
use App\Models\Smsuser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SmsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = Cache::remember('sms_registered_users', now()->addHours(6), function () {
            return User::where('banned', 0)
                ->where('user_type', 'customer')
                ->whereNotNull('phone')
                ->whereRaw('LENGTH(phone) >= 11')
                ->pluck('name', 'phone')
                ->unique()
                ->toArray();
        });

        $smsusers = Cache::remember('sms_unregistered_users', now()->addHours(6), function () {
            return Smsuser::where('status', 1)
                ->whereNotNull('mobile_number')
                ->whereRaw('LENGTH(mobile_number) >= 11')
                ->pluck('mobile_number')
                ->unique()
                ->toArray();
        });

        return view('otp_systems.sms.index', compact('users', 'smsusers'));
    }

    /**
     * Send message to multiple users via job queue.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function send(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
        ]);
        $data = [
            'content'               => $request->content,
            'template_id'           => $request->template_id,
            'register_type'         => $request->register_type,
            'user_phones'           => $request->user_phones,
            'unregister_type'       => $request->unregister_type,
            'unregister_user_phones'=> $request->unregister_user_phones,
        ];

        SendBulkSmsJob::dispatch($data);

        flash(('SMS has been queued for sending.'))->success();
        return redirect()->back();
    }
}
