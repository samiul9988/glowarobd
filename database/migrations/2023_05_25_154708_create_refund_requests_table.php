<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('user_id');
            $table->integer('order_id');
            $table->double('refund_amount', 20, 2)->nullable()->default(0);
            $table->string('payment_type');
            $table->longText('payment_details')->nullable();
            $table->mediumText('reason')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refund_requests');
    }
};
