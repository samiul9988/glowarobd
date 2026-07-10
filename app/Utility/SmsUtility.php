<?php
namespace App\Utility;

use App\Models\Order;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SmsUtility
{
    public static function phone_number_verification(?User $user)
    {
        if (!$user) { return; }

        try {
            $sms_template   = SmsTemplate::where('identifier','phone_number_verification')->first();
            $sms_body       = $sms_template->sms_body ?? "Your verification code is [[code]]. Thank you for registering with [[site_name]].";
            $sms_body       = str_replace('[[code]]', $user->verification_code, $sms_body);
            $sms_body       = str_replace('[[site_name]]', env('APP_NAME'), $sms_body);
            $template_id    = $sms_template->template_id ?? null;
            sendSMS($user->phone, $sms_body, $template_id, 'phone_number_verification', $user->id ?? null);
        } catch (\Exception $e) {
            Log::channel('sms')->error('Failed to send phone number verification SMS: ' . $e->getMessage());
        }
    }

    public static function user_created(?User $user, string $password)
    {
        if (!$user) { return; }

        try {
            $sms_template   = SmsTemplate::where('identifier','user_created')->first();
            if ($sms_template) {
                $sms_body       = $sms_template->sms_body;
                $sms_body       = str_replace('[[phone]]', $user->phone, $sms_body);
                $sms_body       = str_replace('[[password]]', $password, $sms_body);
                $sms_body       = str_replace('[[site_name]]', env('APP_NAME'), $sms_body);
                $template_id    = $sms_template->template_id;
            } else {
                $sms_body = "Welcome to ".env('APP_NAME').". Your account has been created successfully. Your login credentials are Phone: ".$user->phone." and Password: ".$password.". Please change your password after logging in.";
                $template_id = null;
            }

            sendSMS($user->phone, $sms_body, $template_id, 'user_created', $user->id ?? null);
        } catch (\Exception $e) {
            Log::channel('sms')->error('Failed to send user created SMS: ' . $e->getMessage());
        }
    }

    public static function password_reset(?User $user)
    {
        if (!$user) { return; }

        try {
            $sms_template   = SmsTemplate::where('identifier','password_reset')->first();
            $sms_body       = $sms_template->sms_body ?? "Your password reset code is [[code]].";
            $sms_body       = str_replace('[[code]]', $user->verification_code, $sms_body);
            $template_id    = $sms_template->template_id ?? null;
            sendSMS($user->phone, $sms_body, $template_id, 'password_reset', $user->id ?? null);
        } catch (\Exception $e) {
            Log::channel('sms')->error('Failed to send password reset SMS: ' . $e->getMessage());
        }
    }

    public static function order_placement(string $phone = '', ?Order $order = null)
    {
        if (!$order) { return; }

        try {
            if(currency_symbol() == '৳'){
                $amount =  str_replace('৳', 'Tk ', single_price($order->grand_total));
            }else{
                $amount =  single_price($order->grand_total);
            }
            $sms_template   = SmsTemplate::where('identifier','order_placement')->first();
            $sms_body       = $sms_template->sms_body;
            $sms_body       = str_replace('[[customer_name]]', $order->user->name, $sms_body);
            $sms_body       = str_replace('[[order_code]]', $order->code, $sms_body);
            $sms_body       = str_replace('[[order_amount]]', $amount, $sms_body);
            $sms_body       = str_replace('[[hotline]]', get_setting('helpline_number'), $sms_body);
            $sms_body       = str_replace('[[site_name]]', env('APP_NAME'), $sms_body);
            $template_id    = $sms_template->template_id ?? null;
            sendSMS($phone, $sms_body, $template_id, 'order_placement', $order->user_id ?? null);
        } catch (\Exception $e) {
            Log::channel('sms')->error('Failed to send order placement SMS: ' . $e->getMessage());
        }
    }

    public static function delivery_status_change(string $phone='', ?Order $order = null)
    {
        if (!$order || !$phone) { return; }

        try {
            // $amount =  str_replace('৳', 'Tk ', single_price($order->grand_total));
            $dueAmount = get_order_due_amount($order);
            if ($order->payment_status == 'unpaid' && $order->grand_total != $dueAmount) {
                return; // Skip sending SMS if payment is pending and due amount is not equal to grand total
            }
            $amount =  str_replace('৳', 'Tk ', single_price($dueAmount));
            $sms_template   = SmsTemplate::where('identifier','delivery_status_change')->first();
            $sms_body       = $sms_template->sms_body;
            $delivery_status = ucwords(str_replace('_', ' ', $order->delivery_status));

            $sms_body       = str_replace('[[delivery_status]]', $delivery_status, $sms_body);
            $sms_body       = str_replace('[[customer_name]]', $order->user->name, $sms_body);
            $sms_body       = str_replace('[[order_code]]', $order->code, $sms_body);
            $sms_body       = str_replace('[[order_amount]]', $amount, $sms_body);
            $sms_body       = str_replace('[[hotline]]', get_setting('helpline_number'), $sms_body);
            $sms_body       = str_replace('[[site_name]]', env('APP_NAME'), $sms_body);
            $template_id    = $sms_template->template_id ?? null;
            sendSMS($phone, $sms_body, $template_id, 'delivery_status_change', $order->user_id ?? null);
        } catch (\Exception $e) {
            Log::channel('sms')->error('Failed to send delivery status change SMS: ' . $e->getMessage());
        }
    }

    public static function payment_status_change(string $phone='', ?Order $order = null)
    {
        if (!$order) { return; }

        try {
            $sms_template   = SmsTemplate::where('identifier','payment_status_change')->first();
            $sms_body       = $sms_template->sms_body;
            $sms_body       = str_replace('[[payment_status]]', $order->payment_status, $sms_body);
            $sms_body       = str_replace('[[order_code]]', $order->code, $sms_body);
            $template_id    = $sms_template->template_id ?? null;
            sendSMS($phone, $sms_body, $template_id, 'payment_status_change', $order->user_id ?? null);
        } catch (\Exception $e) {
            Log::channel('sms')->error('Failed to send payment status change SMS: ' . $e->getMessage());
        }
    }

    public static function assign_delivery_boy(string $phone='', string $code='')
    {
        try {
            $sms_template   = SmsTemplate::where('identifier','assign_delivery_boy')->first();
            $sms_body       = $sms_template->sms_body;
            $sms_body       = str_replace('[[order_code]]', $code, $sms_body);
            $template_id    = $sms_template->template_id ?? null;
            sendSMS($phone, $sms_body, $template_id, 'assign_delivery_boy', null);
        } catch (\Exception $e) {
            Log::channel('sms')->error('Failed to send assign delivery boy SMS: ' . $e->getMessage());
        }
    }

    public static function coupon_assigned(array $data)
    {
        if (empty($data)) { return; }

        try {
            $sms_template   = SmsTemplate::where('identifier','coupon_assignment')->first();
            $sms_body       = $sms_template->sms_body;
            $sms_body       = str_replace('[[coupon]]', $data['coupon'], $sms_body);
            // $sms_body       = str_replace('[[discount]]', $data['discount'], $sms_body);
            $sms_body       = str_replace('[[validity]]', $data['validity'], $sms_body);
            $sms_body       = str_replace('[[site_name]]', env('APP_NAME'), $sms_body);
            $template_id    = $sms_template->template_id ?? null;
            sendSMS($data['phone'], $sms_body, $template_id, 'coupon_assignment', data_get($data, 'user_id', null));
        } catch (\Exception $e) {
            Log::channel('sms')->error('Failed to send coupon assigned SMS: ' . $e->getMessage());
        }
    }

    public static function send_custom_sms(string $phone, string $message)
    {
        try {
            return sendSMS($phone, $message);
        } catch (\Exception $e) {
            Log::channel('sms')->error('Failed to send phone number verification SMS: ' . $e->getMessage());
        }
    }

}
