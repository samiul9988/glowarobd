<table class="table">
    @if ($returnRequest->status == 'pending')
        <tbody>
            <tr>
                <td>
                    <strong class="text-muted">{{ 'Sub Total' }}</strong>
                </td>
                <td class="px-3">
                    <strong>:</strong>
                </td>
                <td>
                    @if ($returnRequest->is_partial)
                        <span class="d-block text-danger">
                            <del>{{ single_price($order->allOrderDetails->sum('price')) }}</del>
                        </span>
                        <span class="d-block text-success">
                            {{ single_price($sub_total) }}
                        </span>
                    @else
                        {{ single_price($order->allOrderDetails->sum('price')) }}
                    @endif
                </td>
            </tr>
            <tr>
                <td>
                    <strong class="text-muted">{{ 'Tax' }}</strong>
                </td>
                <td class="px-3">
                    <strong>:</strong>
                </td>
                <td>
                    {{ single_price($order->allOrderDetails->sum('tax')) }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong class="text-muted">{{ 'Shipping' }}</strong>
                </td>
                <td class="px-3">
                    <strong>:</strong>
                </td>
                <td>
                    {{ single_price($order->allOrderDetails->sum('shipping_cost')) }}
                </td>
            </tr>
            @if ($order->coupon_discount > 0)
                <tr>
                    <td>
                        <strong class="text-muted">{{ 'Discount' }} @if (@$order->allOrderDetails[0]->coupon_code != null)
                                ({{ $order->allOrderDetails[0]->coupon_code }})
                            @endif
                        </strong>
                    </td>
                    <td class="px-3">
                        <strong>:</strong>
                    </td>
                    <td>
                        {{ single_price($order->coupon_discount) }}
                    </td>
                </tr>
            @endif
            @if ($order->reward_point_discount > 0)
                <tr>
                    <td>
                        <strong class="text-muted">{{ 'Reward point discount' }} @if (@$order->allOrderDetails[0]->reward_point_discount != null)
                                ({{ $order->allOrderDetails[0]->reward_point_discount }})
                            @endif
                        </strong>
                    </td>
                    <td class="px-3">
                        <strong>:</strong>
                    </td>
                    <td>
                        {{ single_price($order->reward_point_discount) }}
                    </td>
                </tr>
            @endif


            @php
                $paidAmount = $order->payments?->sum('amount') ?? 0;
                $totalDiscounts = ($order->coupon_discount ?? 0) + ($order->reward_point_discount ?? 0);
                $total = $sub_total + $order->allOrderDetails->sum('tax') + $order->allOrderDetails->sum('shipping_cost') - ($totalDiscounts + $paidAmount);
            @endphp
            @if ($order->payment_status != 'unpaid' && $paidAmount > 0)
                <tr>
                    <td>
                        <strong class="text-muted">Paid Amount</strong>
                    </td>
                    <td class="px-3">
                        <strong>:</strong>
                    </td>
                    <td>
                        {{ single_price($paidAmount) }} (-)
                    </td>
                </tr>
            @endif
            <tr>
                <td>
                    <strong class="text-muted">NET TOTAL</strong>
                </td>
                <td class="px-3">
                    <strong>:</strong>
                </td>
                <td class="h6">
                    @if ($returnRequest->is_partial)
                        <span class="d-block text-danger">
                            <del>{{ single_price(get_order_grand_total($order)) }}</del>
                        </span>
                        <span class="d-block text-success total-amount">
                            {{ single_price(max(0, $total)) }}
                        </span>
                    @else
                        <span class="total-amount">
                            {{ single_price(get_order_grand_total($order)) }}
                        </span>
                    @endif
                </td>
            </tr>
        </tbody>
    @else
        <tbody>
            <tr>
                <td>
                    <strong class="text-muted">{{ 'Sub Total' }}</strong>
                </td>
                <td class="px-3">
                    <strong>:</strong>
                </td>
                <td>
                    {{ single_price($order->allOrderDetails->sum('price')) }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong class="text-muted">{{ 'Tax' }}</strong>
                </td>
                <td class="px-3">
                    <strong>:</strong>
                </td>
                <td>
                    {{ single_price($order->allOrderDetails->sum('tax')) }}
                </td>
            </tr>
            <tr>
                <td>
                    <strong class="text-muted">{{ 'Shipping' }}</strong>
                </td>
                <td class="px-3">
                    <strong>:</strong>
                </td>
                <td>
                    {{ single_price($order->allOrderDetails->sum('shipping_cost')) }}
                </td>
            </tr>
            @if ($order->coupon_discount > 0)
                <tr>
                    <td>
                        <strong class="text-muted">{{ 'Discount' }} @if (@$order->allOrderDetails[0]->coupon_code != null)
                                ({{ $order->allOrderDetails[0]->coupon_code }})
                            @endif
                        </strong>
                    </td>
                    <td class="px-3">
                        <strong>:</strong>
                    </td>
                    <td>
                        {{ single_price($order->coupon_discount) }}
                    </td>
                </tr>
            @endif
            @if ($order->reward_point_discount > 0)
                <tr>
                    <td>
                        <strong class="text-muted">{{ 'Reward point discount' }} @if (@$order->allOrderDetails[0]->reward_point_discount != null)
                                ({{ $order->allOrderDetails[0]->reward_point_discount }})
                            @endif
                        </strong>
                    </td>
                    <td class="px-3">
                        <strong>:</strong>
                    </td>
                    <td>
                        {{ single_price($order->reward_point_discount) }}
                    </td>
                </tr>
            @endif


            @php
                $paidAmount = $order->payments?->sum('amount') ?? 0;
                $totalDiscounts = ($order->coupon_discount ?? 0) + ($order->reward_point_discount ?? 0);
                $total =
                    $sub_total +
                    $order->allOrderDetails->sum('tax') +
                    $order->allOrderDetails->sum('shipping_cost') -
                    ($totalDiscounts + $paidAmount);
            @endphp
            @if ($order->payment_status != 'unpaid' && $paidAmount > 0)
                <tr>
                    <td>
                        <strong class="text-muted">Paid Amount</strong>
                    </td>
                    <td class="px-3">
                        <strong>:</strong>
                    </td>
                    <td>
                        {{ single_price($paidAmount) }} (-)
                    </td>
                </tr>
            @endif
            <tr>
                <td>
                    <strong class="text-muted">NET TOTAL</strong>
                </td>
                <td class="px-3">
                    <strong>:</strong>
                </td>
                <td class="h6">
                    <span class="total-amount">
                        {{ single_price(get_order_grand_total($order)) }}
                    </span>
                </td>
            </tr>
        </tbody>
    @endif
</table>
