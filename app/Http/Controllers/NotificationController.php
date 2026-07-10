<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class NotificationController extends Controller
{
    public function index() {
        // Previous Code
        // $notifications = auth()->user()->notifications()->paginate(15);
        // auth()->user()->unreadNotifications->markAsRead();

        // New and optimized code
        $notifications = auth()->user()->notifications()
        ->where('created_at', '>=', now()->subDays(30)) // Fetch last 30 days
        ->paginate(15);

        // Mark only displayed unread notifications as read
        auth()->user()->unreadNotifications()
            ->where('created_at', '>=', now()->subDays(30))
            ->whereIn('id', $notifications->pluck('id'))
            ->update(['read_at' => now()]);
        
        if(Auth::user()->user_type == 'admin') {
            return view('backend.notification.index', compact('notifications'));
        }
        
        if(Auth::user()->user_type == 'seller') {
            return view('frontend.user.seller.notification.index', compact('notifications'));
        }
        
        if(Auth::user()->user_type == 'customer') {
            return view('frontend.user.customer.notification.index', compact('notifications'));
        }
        
    }
}
