<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public function manage(Request $request)
    {
        $order_sources = Order::pluck('order_source')->unique();
        if(count($request->all()) > 0){
            $data = Order::with('user')
            ->when($request->input('invoice'), function ($query) use ($request) {
                $query->where('code', $request->input('invoice'));
            })
            ->when($request->input('customer'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->whereHas('user', function ($query) use ($request) {
                        $query->where(function ($query) use ($request) {
                            $query->where('name', 'like', '%' . $request->input('customer') . '%')
                                ->orWhere('phone', 'like', '%' . $request->input('customer') . '%');
                        });
                    })
                    ->orWhereJsonContains('shipping_address', ['phone' => $request->input('customer')])
                    ->orWhereJsonContains('shipping_address', ['name' => $request->input('customer')]);
                });
            })
            ->when($request->input('date'), function ($query) use ($request) {
                $dateRange = explode(' to ', $request->input('date'));
                if(count($dateRange) !== 2) {
                    return $query;
                }
                $dateRange = array_map('trim', $dateRange);
                $dateRange[0] = Carbon::parse($dateRange[0])->startOfDay();
                $dateRange[1] = Carbon::parse($dateRange[1])->endOfDay();
                $query->whereBetween('date', $dateRange);
            })
            ->when($request->input('source'), function ($query) use ($request) {
                $query->where('order_source', $request->input('source'));
            })
            ->orderBy('date', 'desc')
            ->paginate(25);
        }

        return view('backend.services.manage', [
            'orders' => $data ?? collect(),
            'date' => $request->input('date'),
            'invoice' => $request->input('invoice'),
            'customer' => $request->input('customer'),
            'source' => $request->input('source'),
            'order_sources' => $order_sources,
        ]);
    }
}
