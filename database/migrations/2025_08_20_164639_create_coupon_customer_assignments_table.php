<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCouponCustomerAssignmentsTable extends Migration
{
    public function up()
    {
        Schema::create('coupon_customer_assignments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('coupon_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('assigned_by')->nullable()->index();
            $table->boolean('is_used')->default(false);

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->index(['coupon_id', 'customer_id']);
            $table->index(['customer_id', 'is_used']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupon_customer_assignments');
    }
}
