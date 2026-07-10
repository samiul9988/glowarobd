<?php

namespace App\Http\Controllers;

use App\Mail\SupportMailManager;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketLog;
use App\Models\TicketReply;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SupportTicketController extends Controller
{
    protected function log_ticket($ticket, $action, $userId = null)
    {
        try{
            $log = TicketLog::updateOrCreate([
                    'ticket_id' => $ticket->id,
                    'user_id' => $userId ?? Auth::user()->id,
                    'action' => $action,
                ], [
                    'created_at' => now(),
                ]);
            return $log;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function index()
    {
        $tickets = Ticket::with('ticketReplies')->where('user_id', Auth::user()->id)->orderBy('created_at', 'desc')->paginate(9);
        return view('frontend.user.support_ticket.index', compact('tickets'));
    }

    public function admin_index_old(Request $request)
    {
        $sort_search =null;
        $tickets = Ticket::with('ticketReplies')->orderBy('created_at', 'desc');
        if ($request->has('search')){
            $sort_search = $request->search;
            $tickets = $tickets->where('code', 'like', '%'.$sort_search.'%');
        }
        $tickets = $tickets->paginate(15);
        return view('backend.support.support_tickets.index-old', compact('tickets', 'sort_search'));
    }

    public function admin_index(Request $request)
    {
        $employee = $request->input('employee');
        $status = $request->input('status');
        $date = $request->input('date');
        $search = $request->input('search');
        $category_id = $request->input('category');
        $tickets = Ticket::with('category:id,name,parent_id', 'order', 'ticketReplies')
            ->when($category_id, function ($query) use ($category_id) {
                $query->where(function ($query) use ($category_id) {
                    $query->where('ticket_category_id', $category_id)
                        ->orWhereHas('category', function ($q) use ($category_id) {
                            $q->where('parent_id', $category_id);
                        });
                });
            })
            ->when($request->input('date'), function ($query) use ($request) {
                $breakDate = explode(' to ', $request->date);

                if (count($breakDate) === 2) {
                    $start_date = Carbon::parse($breakDate[0])->startOfDay()->format('Y-m-d H:i:s');
                    $end_date = Carbon::parse($breakDate[1])->endOfDay()->format('Y-m-d H:i:s');

                    $query->whereBetween('created_at', [$start_date, $end_date]);
                }
            });
        // dd($tickets->toSql(), $tickets->getBindings());
        $priorityWiseCount = (clone $tickets)
            ->selectRaw('priority, COUNT(*) as count')
            ->groupBy('priority')
            ->get()
            ->pluck('count', 'priority')
            ->toArray();
        $tickets = $tickets->with('user:id,name', 'assignedTo:id,name')->when($request->input('search'), function ($query) use ($request) {
                $query->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->input('search') . '%')
                        ->orWhere('phone', 'like', '%' . $request->input('search') . '%');
                });
            })
            ->when($request->priority, function ($query) use ($request) {
                $query->where('priority', $request->priority);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($employee, function ($query) use ($employee) {
                $query->where(function ($query) use ($employee) {
                    $query->where('assign_to', $employee)
                        ->orWhere('user_id', $employee);
                });
            })
            ->orderByRaw("
                CASE
                    WHEN status = 'open' THEN 1
                    WHEN status = 'working' THEN 2
                    WHEN status = 'closed' THEN 3
                    ELSE 4
                END
            ")
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        $statusWiseCount = Ticket::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        $staffs = Cache::remember('support_staffs_' . Auth::id(), now()->addHours(5), function () {
            return User::where('user_type', 'staff')
                ->when(Auth::user()->user_type !== 'admin', function ($query) {
                    $query->where('id', Auth::id());
                })
                ->get();
        });

        // dd($tickets);
        // $staffs->prepend((object) ['id' => null, 'name' => ('All Staff')]);

        // dd($issueWiseCount, $priorityWiseCount, $statusWiseCount);
        return view('backend.support.support_tickets.index', compact('tickets', 'priorityWiseCount', 'statusWiseCount', 'status', 'date', 'search', 'employee', 'staffs', 'category_id'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id = null)
    {
        $order = null;
        if($id){
            $order = Order::find($id);
        }
        $staffs = Cache::remember('support_staffs', now()->addHours(5), function () {
            return User::where('user_type', 'staff')->get();
        });

        $categories = Cache::remember('support_parent_categories', now()->addHours(5), function () {
            return \App\Models\TicketCategory::active()->whereNull('parent_id')->pluck('name', 'id');
        });
        return view('backend.support.support_tickets.create', compact('order', 'staffs', 'categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_old(Request $request)
    {
        //dd();
        $ticket = new Ticket;
        $ticket->code = max(100000, (Ticket::latest()->first() != null ? Ticket::latest()->first()->code + 1 : 0)).date('s');
        $ticket->user_id = Auth::user()->id;
        $ticket->subject = $request->subject;
        $ticket->details = $request->details;
        $ticket->files = $request->attachments;

        if($ticket->save()){
            $this->send_support_mail_to_admin($ticket);
            flash(('Ticket has been sent successfully'))->success();
            return redirect()->route('support_ticket.index');
        }
        else{
            flash(('Something went wrong'))->error();
        }
    }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required|regex:/^(\+?\d{1,3}[- ]?)?\d{11}$/',
            'subject' => 'nullable',
            'category' => 'required|exists:ticket_categories,id',
            'issue' => 'nullable|exists:ticket_categories,slug',
            'related' => 'nullable|exists:orders,code',
            'priority' => 'required|in:low,medium,high,critical',
            'status' => 'required|in:open,closed,working',
            'attachments' => 'nullable|string',
        ]);
        $orderId = null;
        $userId = Auth::id();
        if(filled($request->input('related'))){
            $order = Order::where('code', $request->input('related'))->first();
            if($order && !is_null($order->user_id)){
                $userId = $order->user_id;
            }
            $orderId = $order->id ?? null;
        }
        // dd($request->all());
        $ticket = new Ticket();
        $ticket->name = $request->input('name');
        $ticket->phone = $request->input('phone');
        $ticket->subject = $request->input('subject');
        $ticket->ticket_category_id = $request->input('category');
        $ticket->issue = filled($request->input('issue')) ? $request->input('issue') : TicketCategory::find($request->input('category'))->slug;
        $ticket->code = 'TCK'.date('Ymdhis');
        $ticket->order_id = $orderId;
        $ticket->details = $request->input('message');
        $ticket->priority = $request->input('priority');
        $ticket->status = $request->input('status');
        $ticket->assign_to = $request->input('assign_to', Auth::id());
        if($request->input('status') == 'closed'){
            $ticket->closed_at = now();
            $ticket->closed_by = Auth::user()->id;
        }
        $ticket->user_id = $userId;
        $ticket->files = $request->input('attachments');
        if($ticket->save()){
            // $this->send_support_mail_to_admin($ticket);
            $this->log_ticket($ticket, 'created');
            flash(('Ticket has been created successfully'))->success();
            return redirect()->route('tickets.admin_index');
        }else{
            flash(('Something went wrong'))->error();
            return back()->withInput();
        }
    }
    public function user_store(Request $request)
    {
        $request->validate([
            'category' => 'required|exists:ticket_categories,id',
            'issue' => 'nullable|exists:ticket_categories,slug',
            'details' => 'required',
            'related' => 'nullable|exists:orders,id',
        ]);
        $orderId = null;
        $name = Auth::user()->name;
        $phone = Auth::user()->phone;
        if(filled($request->input('related'))){
            $order = Order::find($request->input('related'));
            if($order){
                $orderId = $order->id ?? null;
                $shipping_address = json_decode($order->shipping_address, true);
                $name = $shipping_address['name'] ?? Auth::user()->name;
                $phone = $shipping_address['phone'] ?? Auth::user()->phone;
            }
        }
        $ticket = new Ticket();
        $ticket->name = $name;
        $ticket->phone = $phone;
        $ticket->ticket_category_id = $request->input('category');
        $ticket->issue = filled($request->input('issue')) ? $request->input('issue') : TicketCategory::find($request->input('category'))->slug;
        $ticket->code = 'TCK'.date('Ymdhis');
        $ticket->order_id = $orderId;
        $ticket->status = 'open';
        $ticket->details = $request->input('details');
        $ticket->user_id = auth()->user()->id;
        $ticket->files = $request->input('attachments');
        if($ticket->save()){
            $this->send_support_mail_to_admin($ticket);
            flash(('Ticket has been created successfully'))->success();
            return redirect()->route('tickets.index');
        }else{
            flash(('Something went wrong'))->error();
        }
    }

    public function send_support_mail_to_admin($ticket){
        $array['view'] = 'emails.support';
        $array['subject'] = 'Support ticket Code is:- '.$ticket->code;
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = 'Hi. A ticket has been created about ' . Str::headline($ticket->issue) . 'Please check the ticket.';
        $array['link'] = route('tickets.admin_show', encrypt($ticket->id));
        $array['sender'] = $ticket->name;
        $array['details'] = $ticket->details;

        // dd($array);
        // dd(User::where('user_type', 'admin')->first()->email);
        try {
            Mail::to(User::where('user_type', 'admin')->first()->email)->queue(new SupportMailManager($array));
        } catch (\Exception $e) {
            // dd($e->getMessage());
        }
    }

    public function send_support_reply_email_to_user($ticket, $tkt_reply){
        $array['view'] = 'emails.support';
        $array['subject'] = 'Support ticket Code is:- '.$ticket->code;
        $array['from'] = env('MAIL_FROM_ADDRESS');
        $array['content'] = 'Hi. A ticket has been created about ' . Str::headline($ticket->issue) . 'Please check the ticket.';
        $array['link'] = route('tickets.show', encrypt($ticket->id));
        $array['sender'] = $tkt_reply->user->name;
        $array['details'] = $tkt_reply->reply;

        try {
            Mail::to($ticket->user->email)->queue(new SupportMailManager($array));
        } catch (\Exception $e) {
            //dd($e->getMessage());
        }
    }

    public function admin_store_old(Request $request)
    {
        $ticket_reply = new TicketReply;
        $ticket_reply->ticket_id = $request->ticket_id;
        $ticket_reply->user_id = Auth::user()->id;
        $ticket_reply->reply = $request->reply;
        $ticket_reply->files = $request->attachments;
        $ticket_reply->ticket->client_viewed = 0;
        $ticket_reply->ticket->status = $request->status;
        $ticket_reply->ticket->save();

        if($ticket_reply->save()){
            flash(('Reply has been sent successfully'))->success();
            $this->send_support_reply_email_to_user($ticket_reply->ticket, $ticket_reply);
            return back();
        }
        else{
            flash(('Something went wrong'))->error();
        }
    }

    // Reply Store
    public function admin_store(Request $request)
    {
        // dd($request->all());
        $ticket = Ticket::findOrFail($request->ticket_id);
        if($ticket->status == 'closed'){
            flash(('This ticket is already closed'))->error();
            return back();
        }
        $ticket_reply = new TicketReply;
        $ticket_reply->ticket_id = $request->ticket_id;
        $ticket_reply->user_id = Auth::user()->id;
        $ticket_reply->reply = $request->reply;
        $ticket_reply->files = $request->attachments;


        if($ticket_reply->save()){
            $this->log_ticket($ticket, 'replied');
            $ticket->client_viewed = 0;
            $ticket->status = $request->status;
            if($request->status == 'closed'){
                $ticket->closed_at = now();
                $ticket->closed_by = Auth::user()->id;
                $this->log_ticket($ticket, 'closed');
            }
            $ticket->save();
            flash(('Reply has been sent successfully'))->success();
            $this->send_support_reply_email_to_user($ticket, $ticket_reply);
            return back();
        }
        else{
            flash(('Something went wrong'))->error();
        }
    }

    public function seller_store(Request $request)
    {
        $ticket = Ticket::findOrFail($request->ticket_id);
        if($ticket->status == 'closed'){
            flash(('This ticket is already closed'))->error();
            return back();
        }
        $ticket_reply = new TicketReply;
        $ticket_reply->ticket_id = $request->ticket_id;
        $ticket_reply->user_id = Auth::user()->id;
        $ticket_reply->reply = $request->reply;
        $ticket_reply->files = $request->attachments;
        if($ticket_reply->save()){
            $ticket->viewed = 0;
            $ticket->save();
            flash(('Reply has been sent successfully'))->success();
            return back();
        }
        else{
            flash(('Something went wrong'))->error();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $ticket = Ticket::with('ticketReplies.user')->findOrFail(decrypt($id));
        $ticket->client_viewed = 1;
        $ticket->save();
        $ticket_replies = $ticket->ticketReplies;
        return view('frontend.user.support_ticket.show', compact('ticket','ticket_replies'));
    }

    public function admin_show_old($id)
    {
        $ticket = Ticket::findOrFail(decrypt($id));
        $ticket->viewed = 1;
        $ticket->save();
        return view('backend.support.support_tickets.show-old', compact('ticket'));
    }

    public function admin_show($id)
    {
        $ticket = Ticket::with('logs.user', 'user', 'order.orderDetails', 'order.payments', 'ticketReplies.user:id,name,user_type')->findOrFail(decrypt($id));
        $ticket->viewed = 1;
        $ticket->save();
        // dd($ticket->logs);
        $this->log_ticket($ticket, 'viewed');
        return view('backend.support.support_tickets.show', compact('ticket'));
    }

    public function message(){
        return view('backend.support.support_tickets.message');
    }

    public function storeRating(Request $request){
        $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:255',
        ]);

        $ticket = Ticket::findOrFail($request->ticket_id);
        $ticket->rating = $request->rating;
        $ticket->review = $request->review;
        $ticket->save();

        return response()->json([
            'success' => true,
            'message' => ('Thank you for your feedback.'),
        ]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function bulk_status(Request $request)
    {
        try{
            $ids = $request->id;
            $status = $request->status;
            $tickets = Ticket::with('ticketReplies')->whereIn('id', $ids)->get();

            $tickets->each(function ($ticket) use ($status) {
                $action = 'updated';
                if(filled($status)){
                    $ticket->status = $status;
                }
                if($status == 'closed'){
                    $ticket->closed_at = now();
                    $ticket->closed_by = Auth::user()->id;
                    $action = 'closed';
                }
                $ticket->save();
                $this->log_ticket($ticket, $action);
            });
            flash(('Tickets status have been changed successfully'))->success();

            return 1;
        } catch (\Exception $e) {
            flash(('Something went wrong'))->error();
            return 0;
        }
    }

    public function bulk_delete(Request $request)
    {
        try{
            $ids = $request->id;
            $tickets = Ticket::with('ticketReplies')->whereIn('id', $ids)->get();

            $tickets->each(function ($ticket) {
                $ticket->ticketReplies()->delete();
            });
            $tickets->each(function ($ticket) {
                // $ticket->logs()->delete();
                // $this->log_ticket($ticket, 'deleted');
                $ticket->delete();
            });
            flash(('Tickets have been deleted successfully'))->success();

            return 1;
        } catch (\Exception $e) {
            flash(('Something went wrong'))->error();
            return 0;
        }
    }
}
