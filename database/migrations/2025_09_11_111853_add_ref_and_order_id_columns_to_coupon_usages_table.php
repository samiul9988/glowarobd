<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRefAndOrderIdColumnsToCouponUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->integer('order_id')->nullable()->after('coupon_id');
            $table->integer('ref_id')->unsigned()->nullable()->after('order_id');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('ref_id')->references('id')->on('users')->onDelete('set null');

            $table->integer('user_id')->change()->index();
            $table->integer('coupon_id')->change()->index();
        });

        // Move existing data from OrderCoupon to CouponUsage
        $orderCoupons = DB::table('order_coupons')->get();
        foreach ($orderCoupons as $orderCoupon) {
            $couponUsage = new \App\Models\CouponUsage();
            $couponUsage->coupon_id = $orderCoupon->coupon_id;
            $couponUsage->user_id = $orderCoupon->customer_id;
            $couponUsage->order_id = $orderCoupon->order_id;
            $couponUsage->ref_id = $orderCoupon->ref_id;
            $couponUsage->save();
        }

        // Drop the OrderCoupon table if no longer needed
        Schema::dropIfExists('order_coupons');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropForeign('coupon_usages_order_id_foreign');
            $table->dropForeign('coupon_usages_ref_id_foreign');
            $table->dropIndex('coupon_usages_user_id_index');
            $table->dropIndex('coupon_usages_coupon_id_index');
            $table->dropColumn('order_id');
            $table->dropColumn('ref_id');
        });
    }
}
