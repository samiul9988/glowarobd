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
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('group_name', 255);
            $table->string('group_image', 255)->nullable();
            $table->string('group_icon', 255);
            $table->double('min_order_amount', 20, 2);
            $table->integer('min_order_qty');
            $table->string('discount_type', 255);
            $table->double('discount', 20, 2);
            $table->bigInteger('start_date')->nullable();
            $table->bigInteger('end_date')->nullable();
            $table->longText('message')->nullable();
            $table->integer('discount_status');
            $table->integer('ordering');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->tinyInteger('delivery_discount')->default(0);
            $table->double('delivery_discount_amount', 20, 2)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customer_groups');
    }
};
