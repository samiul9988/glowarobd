<div class="aiz-user-sidenav-wrap position-relative z-1  border-0 bg-white py-2 rounded-sm shadow-none">
    <div class="aiz-user-sidenav rounded overflow-auto c-scrollbar-light pb-5 pb-xl-0">
        <div class="px-4 py-3">
            <h4 class="fs-24 fw-500 my-0">My Account</h4>
        </div>
        <div class="sidemnenu mb-3">
            <ul class="aiz-side-nav-list " data-toggle="aiz-side-menu">

                <li class="aiz-side-nav-item">
                    <a href="{{ route('dashboard') }}" class="aiz-side-nav-link {{ areActiveRoutes(['dashboard'])}}">
                        <span class="dash-icon ">
                            <span class="aiz-side-nav-icon">
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.75 3.75H3.75V8.75H8.75V3.75Z" stroke="#576370" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16.25 3.75H11.25V8.75H16.25V3.75Z" stroke="#576370" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M8.75 11.25H3.75V16.25H8.75V11.25Z" stroke="#576370" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M16.25 11.25H11.25V16.25H16.25V11.25Z" stroke="#576370" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>

                            </span>
                        </span>
                        <span class="aiz-side-nav-text">{{ ('Dashboard') }}</span>
                    </a>
                </li>

                @if(Auth::user()->user_type == 'delivery_boy')
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('assigned-deliveries') }}" class="aiz-side-nav-link {{ areActiveRoutes(['completed-delivery'])}}">
                            <span class="dash-icon>
                                <i class="las la-hourglass-half aiz-side-nav-icon"></i>
                            </span>
                            <span class="aiz-side-nav-text">
                                {{ ('Assigned Delivery') }}
                            </span>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('pickup-deliveries') }}" class="aiz-side-nav-link {{ areActiveRoutes(['completed-delivery'])}}">
                            <span class="dash-icon>
                                <i class="las la-luggage-cart aiz-side-nav-icon"></i>
                            </span>
                            <span class="aiz-side-nav-text">
                                {{ ('Pickup Delivery') }}
                            </span>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('on-the-way-deliveries') }}" class="aiz-side-nav-link {{ areActiveRoutes(['completed-delivery'])}}">
                            <span class="dash-icon>
                                <i class="las la-running aiz-side-nav-icon"></i>
                            </span>
                            <span class="aiz-side-nav-text">
                                {{ ('On The Way Delivery') }}
                            </span>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('completed-deliveries') }}" class="aiz-side-nav-link {{ areActiveRoutes(['completed-delivery'])}}">
                            <span class="dash-icon>
                                <i class="las la-check-circle aiz-side-nav-icon"></i>
                            </span>
                            <span class="aiz-side-nav-text">
                                {{ ('Completed Delivery') }}
                            </span>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('pending-deliveries') }}" class="aiz-side-nav-link {{ areActiveRoutes(['pending-delivery'])}}">
                            <span class="dash-icon>
                                <i class="las la-clock aiz-side-nav-icon"></i>
                            </span>
                            <span class="aiz-side-nav-text">
                                {{ ('Pending Delivery') }}
                            </span>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('cancelled-deliveries') }}" class="aiz-side-nav-link {{ areActiveRoutes(['cancelled-delivery'])}}">
                            <span class="dash-icon">
                                <i class="las la-times-circle aiz-side-nav-icon"></i>
                            </span>
                            <span class="aiz-side-nav-text">
                                {{ ('Cancelled Delivery') }}
                            </span>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('cancel-request-list') }}" class="aiz-side-nav-link {{ areActiveRoutes(['cancel-request-list'])}}">
                            <span class="dash-icon>
                                <i class="las la-times-circle aiz-side-nav-icon"></i>
                            </span>
                            <span class="aiz-side-nav-text">
                                {{ ('Request Cancelled Delivery') }}
                            </span>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('total-collection') }}" class="aiz-side-nav-link {{ areActiveRoutes(['today-collection'])}}">
                            <i class="las la-comment-dollar aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">
                                {{ ('Total Collections') }}
                            </span>
                        </a>
                    </li>
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('total-earnings') }}" class="aiz-side-nav-link {{ areActiveRoutes(['total-earnings'])}}">
                            <i class="las la-comment-dollar aiz-side-nav-icon"></i>
                            <span class="aiz-side-nav-text">
                                {{ ('Total Earnings') }}
                            </span>
                        </a>
                    </li>
                @else
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('wishlists.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['wishlists.index'])}}">
                            <span class="dash-icon">
                                <i class="far fa-heart aiz-side-nav-icon"></i>
                            </span>
                            <span class="aiz-side-nav-text">{{ ('Wishlist') }}</span>
                        </a>
                    </li>
                    @php
                        $delivery_viewed = App\Models\Order::where('user_id', Auth::user()->id)->where('delivery_viewed', 0)->get()->count();
                        $payment_status_viewed = App\Models\Order::where('user_id', Auth::user()->id)->where('payment_status_viewed', 0)->get()->count();
                    @endphp
                    <li class="aiz-side-nav-item">
                        <a href="{{ route('purchase_history.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['purchase_history.index'])}}">
                        <span class=" dash-icon">
                            <span class="aiz-side-nav-icon">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12.615 15.9844H3.40217C2.88962 15.9843 2.38387 15.8671 1.92349 15.6419C1.46311 15.4166 1.06027 15.0891 0.745676 14.6845C0.431087 14.2799 0.213067 13.8087 0.108247 13.307C0.00342596 12.8053 0.0145734 12.2863 0.140839 11.7895L2.00797 4.16747C2.04159 4.03119 2.12088 3.91055 2.23263 3.82562C2.34438 3.74069 2.48185 3.6966 2.62215 3.70069H12.9098C13.0452 3.69448 13.179 3.7333 13.2901 3.81107C13.4012 3.88884 13.4834 4.0012 13.524 4.13062L15.8456 11.5991C16.0043 12.1064 16.0409 12.644 15.9523 13.1681C15.8637 13.6922 15.6524 14.1879 15.3358 14.6148C15.0244 15.0435 14.6149 15.3915 14.1416 15.6298C13.6683 15.868 13.1448 15.9896 12.615 15.9844ZM3.08279 4.92906L1.30779 12.0843C1.22875 12.4 1.22242 12.7295 1.28927 13.048C1.35612 13.3665 1.49441 13.6657 1.69372 13.923C1.89303 14.1802 2.14814 14.3889 2.43983 14.5332C2.73152 14.6775 3.05217 14.7537 3.3776 14.756H12.5904C12.9246 14.7566 13.2543 14.6778 13.5521 14.5262C13.85 14.3746 14.1077 14.1545 14.304 13.8839C14.5059 13.6118 14.6407 13.2958 14.6972 12.9617C14.7538 12.6276 14.7306 12.2849 14.6295 11.9615L12.4676 4.92906H3.08279Z" fill="#576370"/>
                                <path d="M10.4515 8.61417C10.2886 8.61417 10.1324 8.54946 10.0172 8.43428C9.90203 8.3191 9.83732 8.16288 9.83732 7.99999V3.0865C9.83732 2.59782 9.64319 2.12916 9.29765 1.78361C8.9521 1.43806 8.48344 1.24394 7.99476 1.24394C7.50608 1.24394 7.03742 1.43806 6.69187 1.78361C6.34633 2.12916 6.1522 2.59782 6.1522 3.0865V7.99999C6.1522 8.16288 6.08749 8.3191 5.97231 8.43428C5.85713 8.54946 5.70091 8.61417 5.53801 8.61417C5.37512 8.61417 5.2189 8.54946 5.10372 8.43428C4.98854 8.3191 4.92383 8.16288 4.92383 7.99999V3.0865C4.92383 2.27203 5.24737 1.49093 5.82328 0.915019C6.39919 0.339108 7.1803 0.015564 7.99476 0.015564C8.80922 0.015564 9.59032 0.339108 10.1662 0.915019C10.7421 1.49093 11.0657 2.27203 11.0657 3.0865V7.99999C11.0657 8.16288 11.001 8.3191 10.8858 8.43428C10.7706 8.54946 10.6144 8.61417 10.4515 8.61417Z" fill="#576370"/>
                            </svg>

                            </span>
                        </span>

                        <span class="aiz-side-nav-text">{{ ('Purchase History') }}</span>
                           <!-- @if($delivery_viewed > 0 || $payment_status_viewed > 0)<span class="badge badge-inline badge-success">{{ ('New') }}</span>@endif -->
                        </a>
                    </li>

                    @if (addon_is_activated('refund_request'))
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('customer_refund_request') }}" class="aiz-side-nav-link {{ areActiveRoutes(['customer_refund_request'])}}">
                                <span class="dash-icon">
                                    <i class="las la-backward aiz-side-nav-icon"></i>
                                </span>
                                <span class="aiz-side-nav-text">{{ ('Sent Refund Request') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(Auth::user()->user_type == 'seller')
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('seller.products') }}" class="aiz-side-nav-link {{ areActiveRoutes(['seller.products', 'seller.products.upload', 'seller.products.edit'])}}">
                                <i class="lab la-sketch aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Products') }}</span>
                            </a>
                        </li>
                        <li class="aiz-side-nav-item">
                            <a href="{{route('product_bulk_upload.index')}}" class="aiz-side-nav-link {{ areActiveRoutes(['product_bulk_upload.index'])}}">
                                <i class="las la-upload aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Product Bulk Upload') }}</span>
                            </a>
                        </li>
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('seller.digitalproducts') }}" class="aiz-side-nav-link {{ areActiveRoutes(['seller.digitalproducts', 'seller.digitalproducts.upload', 'seller.digitalproducts.edit'])}}">
                                <i class="lab la-sketch aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Digital Products') }}</span>
                            </a>
                        </li>
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('my_uploads.all') }}" class="aiz-side-nav-link {{ areActiveRoutes(['my_uploads.new'])}}">
                                <i class="las la-folder-open aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Uploaded Files') }}</span>
                            </a>
                        </li>
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('seller.coupon.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['my_uploads.new'])}}">
                                <i class="las la-bullhorn aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Coupon') }}</span>
                            </a>
                        </li>
                    @endif

                    @if(get_setting('classified_product') == 1)
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('customer_products.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['customer_products.index', 'customer_products.create', 'customer_products.edit'])}}">
                                <i class="lab la-sketch aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Classified Products') }}</span>
                            </a>
                        </li>
                    @endif


                    @if(addon_is_activated('auction'))
                        <li class="aiz-side-nav-item">
                            <a href="javascript:void(0);" class="aiz-side-nav-link">
                                <i class="las la-gavel aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Auction') }}</span>
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                @if (Auth::user()->user_type == 'seller')
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('auction_products.seller.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['auction_products.seller.index','auction_products.create','auction_products.edit'])}}">
                                            <span class="aiz-side-nav-text">{{ ('All Auction Products') }}</span>
                                        </a>
                                    </li>
                                    <li class="aiz-side-nav-item">
                                        <a href="{{ route('auction_products_orders.seller') }}" class="aiz-side-nav-link {{ areActiveRoutes(['auction_products_orders.seller'])}}">
                                            <span class="aiz-side-nav-text">{{ ('Auction Product Orders') }}</span>
                                        </a>
                                    </li>
                                @endif
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('auction_product_bids.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Bidded Products') }}</span>
                                    </a>
                                </li>

                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('auction_product.purchase_history') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Purchase History') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @if(Auth::user()->user_type == 'seller')
                        @if (addon_is_activated('pos_system'))
                            @if (\App\Models\BusinessSetting::where('type', 'pos_activation_for_seller')->first() != null && get_setting('pos_activation_for_seller') != 0)
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('poin-of-sales.seller_index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['poin-of-sales.seller_index'])}}">
                                        <i class="las la-fax aiz-side-nav-icon"></i>
                                        <span class="aiz-side-nav-text">{{ ('POS Manager') }}</span>
                                    </a>
                                </li>
                            @endif
                        @endif
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('orders.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['orders.index'])}}">
                                <i class="las la-money-bill aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Orders') }}</span>
                            </a>
                        </li>

                        @if (addon_is_activated('refund_request'))
                            <li class="aiz-side-nav-item">
                                <a href="{{ route('vendor_refund_request') }}" class="aiz-side-nav-link {{ areActiveRoutes(['vendor_refund_request','reason_show'])}}">
                                    <i class="las la-backward aiz-side-nav-icon"></i>
                                    <span class="aiz-side-nav-text">{{ ('Received Refund Request') }}</span>
                                </a>
                            </li>
                        @endif
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('reviews.seller') }}" class="aiz-side-nav-link {{ areActiveRoutes(['reviews.seller'])}}">
                                <i class="las la-star-half-alt aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Product Reviews') }}</span>
                            </a>
                        </li>

                        <li class="aiz-side-nav-item">
                            <a href="{{ route('shops.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['shops.index'])}}">
                                <i class="las la-cog aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Shop Setting') }}</span>
                            </a>
                        </li>

                        <li class="aiz-side-nav-item">
                            <a href="{{ route('payments.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['payments.index'])}}">
                                <i class="las la-history aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Payment History') }}</span>
                            </a>
                        </li>

                        <li class="aiz-side-nav-item">
                            <a href="{{ route('withdraw_requests.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['withdraw_requests.index'])}}">
                                <i class="las la-money-bill-wave-alt aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Money Withdraw') }}</span>
                            </a>
                        </li>

                        <li class="aiz-side-nav-item">
                            <a href="{{ route('commission-log.index') }}" class="aiz-side-nav-link">
                                <i class="las la-file-alt aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Commission History') }}</span>
                            </a>
                        </li>

                    @endif

                    @if (get_setting('conversation_system') == 1)
                        @php
                            $conversation = \App\Models\Conversation::where('sender_id', Auth::user()->id)->where('sender_viewed', 0)->get();
                        @endphp
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('conversations.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['conversations.index', 'conversations.show'])}}">
                                <i class="las la-comment aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Conversations') }}</span>
                                @if (count($conversation) > 0)
                                    <span class="badge badge-success">({{ count($conversation) }})</span>
                                @endif
                            </a>
                        </li>
                    @endif


                    @if (get_setting('wallet_system') == 1)
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('wallet.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['wallet.index'])}}">
                                <i class="las la-dollar-sign aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('My Wallet')}}</span>
                            </a>
                        </li>
                    @endif

                    @if (addon_is_activated('club_point'))
                        <li class="aiz-side-nav-item">
                            <a href="{{ route('earnng_point_for_user') }}" class="aiz-side-nav-link {{ areActiveRoutes(['earnng_point_for_user'])}}">
                                <i class="las la-dollar-sign aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Earning Points')}}</span>
                            </a>
                        </li>
                    @endif

                    @if (addon_is_activated('affiliate_system') && Auth::user()->affiliate_user != null && Auth::user()->affiliate_user->status)
                        <li class="aiz-side-nav-item">
                            <a href="javascript:void(0);" class="aiz-side-nav-link {{ areActiveRoutes(['affiliate.user.index', 'affiliate.payment_settings'])}}">
                                <i class="las la-dollar-sign aiz-side-nav-icon"></i>
                                <span class="aiz-side-nav-text">{{ ('Affiliate') }}</span>
                                <span class="aiz-side-nav-arrow"></span>
                            </a>
                            <ul class="aiz-side-nav-list level-2">
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('affiliate.user.index') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Affiliate System') }}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('affiliate.user.payment_history') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Payment History') }}</span>
                                    </a>
                                </li>
                                <li class="aiz-side-nav-item">
                                    <a href="{{ route('affiliate.user.withdraw_request_history') }}" class="aiz-side-nav-link">
                                        <span class="aiz-side-nav-text">{{ ('Withdraw request history') }}</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @endif

                    @php
                        $support_ticket = DB::table('tickets')
                                    ->where('client_viewed', 0)
                                    ->where('user_id', Auth::user()->id)
                                    ->count();
                    @endphp

                    <li class="aiz-side-nav-item">
                        <a href="{{ route('tickets.index') }}" class="aiz-side-nav-link {{ areActiveRoutes(['tickets.index'])}}">
                            <span class=" dash-icon">
                                <i class="las la-ticket-alt aiz-side-nav-icon"></i>
                            </span>
                            <span class="aiz-side-nav-text">{{ ('Support Ticket')}}</span>
                        </a>
                    </li>
                @endif
                <li class="aiz-side-nav-item">
                    <a href="{{ route('profile') }}" class="aiz-side-nav-link {{ areActiveRoutes(['profile'])}}">
                        <span class="dash-icon">
                            <i class="far fa-user aiz-side-nav-icon"></i>
                        </span>
                        <span class="aiz-side-nav-text">{{ ('Manage Profile')}}</span>
                    </a>
                </li>
                <li class="aiz-side-nav-item d-none">
                    <a href="{{ route('complain-suggestions') }}" class="aiz-side-nav-link {{ areActiveRoutes(['complain-suggestions'])}}">
                        <span class="dash-icon">
                            <i class="far fa-comment aiz-side-nav-icon"></i>
                        </span>
                        {{-- Use Table: User Feedbacks --}}
                        <span class="aiz-side-nav-text">Complaint / Suggestions</span>
                    </a>
                </li>

            </ul>
        </div>
        @if (get_setting('vendor_system_activation') == 1 && Auth::user()->user_type == 'customer')
            <div>
                <a href="{{ route('shops.create') }}" class="btn btn-block btn-soft-primary rounded-0">
                    </i>{{ ('Be A Seller') }}
                </a>
            </div>
        @endif
        @if(Auth::user()->user_type == 'seller')
          <hr>
          <h4 class="h5 fw-600 text-center">{{ ('Sold Amount')}}</h4>
          @php
              $date = date("Y-m-d");
              $days_ago_30 = date('Y-m-d', strtotime('-30 days', strtotime($date)));
              $days_ago_60 = date('Y-m-d', strtotime('-60 days', strtotime($date)));
          @endphp
          <div class="widget-balance pb-3 pt-1">
            <div class="text-center">
                <div class="heading-4 strong-700 mb-4">
                    @php
                        $orderTotal = \App\Models\Order::where('seller_id', Auth::user()->id)->where("payment_status", 'paid')->where('created_at', '>=', $days_ago_30)->sum('grand_total');
                        //$orderDetails = \App\Models\OrderDetail::where('seller_id', Auth::user()->id)->where('created_at', '>=', $days_ago_30)->get();
                        //$total = 0;
                        //foreach ($orderDetails as $key => $orderDetail) {
                            //if($orderDetail->order != null && $orderDetail->order != null && $orderDetail->order->payment_status == 'paid'){
                                //$total += $orderDetail->price;
                            //}
                        //}
                    @endphp
                    <small class="d-block fs-12 mb-2">{{ ('Your sold amount (current month)')}}</small>
                    <span class="btn btn-primary fw-600 fs-18">{{ single_price($orderTotal) }}</span>
                </div>
                <table class="table table-borderless">
                    <tr>
                        @php
                            $orderTotal = \App\Models\Order::where('seller_id', Auth::user()->id)->where("payment_status", 'paid')->sum('grand_total');
                        @endphp
                        <td class="p-1" width="60%">
                            {{ ('Total Sold')}}:
                        </td>
                        <td class="p-1 fw-600" width="40%">
                            {{ single_price($orderTotal) }}
                        </td>
                    </tr>
                    <tr>
                        @php
                            $orderTotal = \App\Models\Order::where('seller_id', Auth::user()->id)->where("payment_status", 'paid')->where('created_at', '>=', $days_ago_60)->where('created_at', '<=', $days_ago_30)->sum('grand_total');
                        @endphp
                        <td class="p-1" width="60%">
                            {{ ('Last Month Sold')}}:
                        </td>
                        <td class="p-1 fw-600" width="40%">
                            {{ single_price($orderTotal) }}
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

    </div>

    <div class="fixed-bottom d-xl-none bg-white border-top d-flex justify-content-between px-2" style="box-shadow: 0 -5px 10px rgb(0 0 0 / 10%);">
        <a class="btn btn-sm p-2 d-flex align-items-center" href="{{ route('logout') }}">
            <i class="las la-sign-out-alt fs-18 mr-2"></i>
            <span>{{ ('Logout') }}</span>
        </a>
        <button class="btn btn-sm p-2 " data-toggle="class-toggle" data-backdrop="static" data-target=".aiz-mobile-side-nav" data-same=".mobile-side-nav-thumb">
            <i class="las la-times la-2x"></i>
        </button>
    </div>
</div>
